<?php

namespace TriTan\Laci\Pipes;

use Closure;

class SorterPipe implements PipeInterface
{
    protected $value;
    protected $ascending;

    public function __construct(Closure $value, $ascending = 'asc')
    {
        $this->value = $value;
        $this->ascending = strtolower($ascending);
    }

    public function process(array $data)
    {
        return $this->sort($data, $this->value, $this->ascending);
    }

    public function sort($array, $value, $ascending)
    {
        $values = array_map(function ($row) use ($value) {
            return $value($row);
        }, $array);
        switch ($ascending) {
            case 'asc': asort($values);
                break;
            case 'desc': arsort($values);
                break;
        }
        $keys = array_keys($values);
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $array[$key];
        }
        return $result;
    }
}
