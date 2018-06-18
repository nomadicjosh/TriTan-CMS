<?php

namespace TriTan\Laci;

use Closure;
use TriTan\Exception\DirectoryNotFoundException;
use TriTan\Exception\InvalidJsonException;
use TriTan\Exception\UndefinedMethodException;

class Collection
{

    const KEY_ID = '_id';
    const KEY_OLD_ID = '_old';
    const UPDATING = 'updating';
    const UPDATED = 'updated';
    const INSERTING = 'inserting';
    const INSERTED = 'inserted';
    const DELETING = 'deleting';
    const DELETED = 'deleted';
    const CHANGED = 'changed';

    protected $filepath = null;
    protected $resolver = null;
    protected $events = [];
    protected $transactionMode = false;
    protected $transactionData = null;
    protected $macros = [];

    public function __construct($filepath, array $options = array())
    {
        $this->filepath = $filepath;
        $this->options = array_merge([
            'save_format' => JSON_PRETTY_PRINT,
            'key_prefix' => '',
            'more_entropy' => false,
                ], $options);
    }

    public function macro($name, callable $callback)
    {
        $this->macros[$name] = $callback;
    }

    public function hasMacro($name)
    {
        return array_key_exists($name, $this->macros);
    }

    public function getMacro($name)
    {
        return $this->hasMacro($name) ? $this->macros[$name] : null;
    }

    public function getKeyId()
    {
        return static::KEY_ID;
    }

    public function getKeyOldId()
    {
        return static::KEY_OLD_ID;
    }

    public function isModeTransaction()
    {
        return true === $this->transactionMode;
    }

    public function begin()
    {
        $this->transactionMode = true;
    }

    public function commit()
    {
        $this->transactionMode = false;
        return $this->save($this->transactionData);
    }

    public function rollback()
    {
        $this->transactionMode = false;
        $this->transactionData = null;
    }

    public function truncate()
    {
        return $this->persists([]);
    }

    public function on($event, callable $callback)
    {
        if (!isset($this->events[$event])) {
            $this->events[$event] = [];
        }
        $this->events[$event][] = $callback;
    }

    protected function trigger($event, array &$args)
    {
        $events = isset($this->events[$event]) ? $this->events[$event] : [];
        foreach ($events as $callback) {
            call_user_func_array($callback, $args);
        }
    }

    public function loadData()
    {
        if ($this->isModeTransaction() AND ! empty($this->transactionData)) {
            return $this->transactionData;
        }
        if (!file_exists($this->filepath)) {
            $data = [];
        } else {
            $content = file_get_contents($this->filepath);
            $data = json_decode($content, true);
            if (is_null($data)) {
                throw new InvalidJsonException("Failed to load data. File '{$this->filepath}' contain invalid JSON format.");
            }
        }
        return $data;
    }

    public function setResolver(callable $resolver)
    {
        $this->resolver = $resolver;
    }

    public function getResolver()
    {
        return $this->resolver;
    }

    public function query()
    {
        return new Query($this);
    }

    public function where($key)
    {
        return call_user_func_array([$this->query(), 'where'], func_get_args());
    }

    public function filter(Closure $closure)
    {
        return $this->query()->filter($closure);
    }

    public function map(Closure $mapper)
    {
        return $this->query()->map($mapper);
    }

    public function sortBy($key, $asc = 'asc')
    {
        return $this->query()->sortBy($key, $asc);
    }

    public function sort(Closure $value)
    {
        return $this->query()->sort($value);
    }

    public function skip($offset)
    {
        return $this->query()->skip($offset);
    }

    public function take($limit, $offset = 0)
    {
        return $this->query()->take($limit, $offset);
    }

    public function all()
    {
        return array_values($this->loadData());
    }

    public function find($id)
    {
        $data = $this->loadData();
        return isset($data[$id]) ? $data[$id] : null;
    }

    public function lists($key, $resultKey = null)
    {
        return $this->query()->lists($key, $resultKey);
    }

    public function sum($key)
    {
        return $this->query()->sum($key);
    }

    public function count()
    {
        return $this->query()->count();
    }

    public function avg($key)
    {
        return $this->query()->avg($key);
    }

    public function min($key)
    {
        return $this->query()->min($key);
    }

    public function max($key)
    {
        return $this->query()->max($key);
    }

    public function insert(array $data)
    {
        return $this->execute($this->query(), Query::TYPE_INSERT, $data);
    }

    public function inserts(array $listData)
    {
        $this->begin();
        foreach ($listData as $data) {
            $this->insert($data);
        }
        return $this->commit();
    }

    public function update(array $data)
    {
        return $this->query()->update();
    }

    public function delete()
    {
        return $this->query()->delete();
    }

    public function withOne($relation, $as, $otherKey, $operator = '=', $thisKey = null)
    {
        return $this->query()->withOne($relation, $as, $otherKey, $operator, $thisKey ?: static::KEY_ID);
    }

    public function withMany($relation, $as, $otherKey, $operator = '=', $thisKey = null)
    {
        return $this->query()->withMany($relation, $as, $otherKey, $operator, $thisKey ?: static::KEY_ID);
    }

    public function generateKey()
    {
        return uniqid($this->options['key_prefix'], (bool) $this->options['more_entropy']);
    }

    public function execute(Query $query, $type, $arg = null)
    {
        if ($query->getCollection() != $this) {
            throw new \InvalidArgumentException("Cannot execute query. Query is for different collection");
        }
        switch ($type) {
            case Query::TYPE_GET: return $this->executeGet($query);
            case Query::TYPE_SAVE: return $this->executeSave($query);
            case Query::TYPE_INSERT: return $this->executeInsert($query, $arg);
            case Query::TYPE_UPDATE: return $this->executeUpdate($query, $arg);
            case Query::TYPE_DELETE: return $this->executeDelete($query);
        }
    }

    protected function executePipes(array $pipes)
    {
        $data = $this->loadData() ?: [];
        foreach ($pipes as $pipe) {
            $data = $pipe->process($data);
        }
        return $data;
    }

    protected function executeInsert(Query $query, array $new)
    {
        $data = $this->loadData();
        $key = isset($new[static::KEY_ID]) ? $new[static::KEY_ID] : $this->generateKey();

        $newExtra = new ArrayExtra([]);
        $newExtra->merge($new);
        $args = [$newExtra];
        $this->trigger(static::INSERTING, $args);
        $data[$key] = array_merge([
            static::KEY_ID => $key
                ], $args[0]->toArray());
        $success = $this->persists($data);
        $args = [$data[$key]];
        $this->trigger(static::INSERTED, $args);

        $args = [$data];
        $this->trigger(static::CHANGED, $args);
        return $success ? $data[$key] : null;
    }

    protected function executeUpdate(Query $query, array $new)
    {
        $data = $this->loadData();
        $args = [$query, $new];
        $this->trigger(static::UPDATING, $args);

        $pipes = $query->getPipes();
        $rows = $this->executePipes($pipes);
        $count = count($rows);
        if (0 == $count) {
            return true;
        }

        $updatedData = [];
        foreach ($rows as $key => $row) {
            $record = new ArrayExtra($data[$key]);
            $record->merge($new);
            $data[$key] = $record->toArray();
            if (isset($new[static::KEY_ID])) {
                $data[$new[static::KEY_ID]] = $data[$key];
                unset($data[$key]);
                $key = $new[static::KEY_ID];
            }
            $updatedData[$key] = $data[$key];
        }
        $success = $this->persists($data);
        $args = [$updatedData];
        $this->trigger(static::UPDATED, $args);

        $args = [$data];
        $this->trigger(static::CHANGED, $args);

        return $success ? $count : 0;
    }

    protected function executeDelete(Query $query)
    {
        $data = $this->loadData();
        $args = [$query];
        $this->trigger(static::DELETING, $args);
        $pipes = $query->getPipes();
        $rows = $this->executePipes($pipes);
        $count = count($rows);
        if (0 == $count) {
            return true;
        }
        foreach ($rows as $key => $row) {
            unset($data[$key]);
        }
        $success = $this->persists($data);
        $args = [$rows];
        $this->trigger(static::DELETED, $args);
        $args = [$data];
        $this->trigger(static::CHANGED, $args);
        return $success ? $count : 0;
    }

    protected function executeGet(Query $query)
    {
        $pipes = $query->getPipes();
        $data = $this->executePipes($pipes);
        return array_values($data);
    }

    protected function executeSave(Query $query)
    {
        $data = $this->loadData();
        $pipes = $query->getPipes();
        $processed = $this->executePipes($pipes);
        $count = count($processed);
        foreach ($processed as $key => $row) {
            // update ID if there is '_old' key
            if (isset($row[static::KEY_OLD_ID])) {
                unset($data[$row[static::KEY_OLD_ID]]);
            }
            // keep ID if there is no '_id'
            if (!isset($row[static::KEY_ID])) {
                $row[static::KEY_ID] = $key;
            }
            $data[$key] = $row;
        }
        $success = $this->persists($data);
        return $success ? $count : 0;
    }

    public function persists(array $data)
    {
        if ($this->resolver) {
            $data = array_map($this->getResolver(), $data);
        }
        return $this->save($data);
    }

    protected function save(array $data)
    {
        if ($this->isModeTransaction()) {
            $this->transactionData = $data;
            return true;
        } else {
            if (empty($data)) {
                $data = new \stdClass;
            }
            $json = json_encode($data, $this->options['save_format']);
            $filepath = $this->filepath;
            $pathinfo = pathinfo($filepath);
            $dir = $pathinfo['dirname'];
            if (!is_dir($dir)) {
                throw new DirectoryNotFoundException("Cannot save database. Directory {$dir} not found or it is not directory.");
            }
            return file_put_contents($filepath, $json, LOCK_EX);
        }
    }

    public function __call($method, $args)
    {
        $macro = $this->getMacro($method);
        if ($macro) {
            return call_user_func_array($macro, array_merge([$this->query()], $args));
        } else {
            throw new UndefinedMethodException("Undefined method or macro '{$method}'.");
        }
    }

}
