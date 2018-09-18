<?php
namespace TriTan\Common;

use TriTan\Interfaces\MetaDataInterface;
use TriTan\Interfaces\ContextInterface;
use TriTan\Exception\Exception;
use Cascade\Cascade;

class MetaData implements MetaDataInterface
{

    /**
     * Laci document object.
     *
     * @var object
     */
    public $db;
    
    public $context;

    public function __construct(\TriTan\Database $db, ContextInterface $context)
    {
        $this->db = $db;
        $this->context = $context;
    }
    
    /**
     * Retrieve the name of the metadata table for the specified object type.
     *
     * This public function is not to be used by developers. It's use is only for _metadata
     * methods.
     *
     * @access private
     * @since 0.9.9
     * @param string $type Type of object to get metadata table for (e.g. post or user)
     * @return string Metadata document name.
     */
    protected function table($type)
    {
        $table_name = $type . 'meta';

        return $table_name;
    }

    /**
     * Retrieve metadata for the specified array.
     *
     * @since 0.9.9
     * @param string $meta_type Type of array metadata is for (e.g. post or user)
     * @param int    $array_id ID of the array metadata is for
     * @param string $meta_key  Optional. Metadata key. If not specified, retrieve all metadata for
     *                          the specified array.
     * @return mixed Array of values
     */
    public function read($meta_type, $array_id, $meta_key = '', $single = false)
    {
        if (!$meta_type || !is_numeric($array_id)) {
            return false;
        }

        $array_id = $this->context->obj['util']->{'absint'}($array_id);
        if (!$array_id) {
            return false;
        }

        /**
         * Filters whether to retrieve metadata of a specific type.
         *
         * The dynamic portion of the hook, `$meta_type`, refers to the meta
         * array type (post or user). Returning a non-null value
         * will effectively short-circuit the function.
         *
         * @since 0.9.9
         * @param null|string   $value      The value getMetaData() should return - a single metadata value.
         * @param int           $array_id   Array ID.
         * @param string        $meta_key   Optional. Meta key.
         * @param bool          $single     Whether to return only the first value of the specified $meta_key.
         */
        $check = $this->context->obj['hook']->{'applyFilter'}("get_{$meta_type}_metadata", null, $array_id, $meta_key, $single);
        if (null !== $check) {
            if ($single && is_array($check)) {
                return $check[0];
            } else {
                return $check;
            }
        }
        
        $meta_cache = $this->context->obj['cache']->{'read'}($array_id, $meta_type . '_meta');
        
        if (!$meta_cache) {
            $meta_cache = $this->updateMetaDataCache($meta_type, [$array_id]);
            $meta_cache = $meta_cache[$array_id];
        }
        
        if (!$meta_key) {
            return $meta_cache;
        }
        
        if (isset($meta_cache->{$meta_key})) {
            if ($single) {
                return $this->context->obj['serializer']->{'unserialize'}($meta_cache->{$meta_key}[0]);
            } else {
                return array_map([$this->context->obj['serializer'], 'unserialize'], $meta_cache->{$meta_key});
            }
        }
        
        /**
         * While the cache updates, temporarily load the requested data from the
         * database.
         * 
         * @since 1.0
         */
        if($single) {
            $meta_data = $this->db->table($meta_type . 'meta')
                ->where($meta_type . '_id', $array_id)
                ->where('meta_key', $meta_key)
                ->get();
            return $meta_data[0]['meta_value'];
        } else {
            $meta_data = $this->db->table($meta_type . 'meta')
                ->where($meta_type . '_id', $array_id)
                ->get();
            $meta = [];
            foreach($meta_data as $value) {
                //$meta[$value['meta_key']] = $value['meta_value'];
                $meta[$value['meta_key']] = array_map( [$this->context->obj['serializer'], 'unserialize'], [$value['meta_value']] );
            }
            return $meta;
        }
        

        if ($single) {
            return '';
        } else {
            return [];
        }
    }

    /**
     * Update metadata for the specified array. If no value already exists for the specified array
     * ID and metadata key, the metadata will be added.
     *
     * @since 0.9.9
     * @param string $meta_type  Type of array metadata is for (e.g. post or user)
     * @param int    $array_id   ID of the array metadata is for
     * @param string $meta_key   Metadata key
     * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
     * @param mixed  $prev_value Optional. If specified, only update existing metadata entries with
     *                                     the specified value. Otherwise, update all entries.
     * @return int|bool Meta ID if the key didn't exist, true on successful update, false on failure.
     */
    public function update($meta_type, $array_id, $meta_key, $meta_value, $prev_value = '')
    {
        if (!$meta_type || !$meta_key || !is_numeric($array_id)) {
            return false;
        }

        $array_id = $this->context->obj['util']->{'absint'}($array_id);
        if (!$array_id) {
            return false;
        }

        $table = $this->table($meta_type);
        if (!$table) {
            return false;
        }
        $column = $meta_type . '_id';

        /**
         * Filters whether to update metadata of a specific type.
         *
         * The dynamic portion of the hook, `$meta_type`, refers to the meta
         * array type (post or user). Returning a non-null value
         * will effectively short-circuit the function.
         *
         * @since 0.9.9
         * @param null|bool $check      Whether to allow updating metadata for the given type.
         * @param int       $array_id  Array ID.
         * @param string    $meta_key   Meta key.
         * @param mixed     $meta_value Meta value. Must be serializable if non-scalar.
         * @param mixed     $prev_value Optional. If specified, only update existing
         *                              metadata entries with the specified value.
         *                              Otherwise, update all entries.
         */
        $check = $this->context->obj['hook']->{'applyFilter'}("update_{$meta_type}_metadata", null, $array_id, $meta_key, $meta_value, $prev_value);
        if (null !== $check) {
            return (bool) $check;
        }

        // Compare existing value to new value if no prev value given and the key exists only once.
        if (empty($prev_value)) {
            $old_value = $this->read($meta_type, $array_id, $meta_key);
            if (count($old_value) == 1) {
                if ($old_value === $meta_value) {
                    return false;
                }
            }
        }

        $meta_ids = $this->db->table($table)
                ->where('meta_key', $meta_key)
                ->where($column, $array_id)
                ->first();

        if (empty($meta_ids)) {
            return $this->create($meta_type, $array_id, $meta_key, $meta_value);
        }

        $_newvalue = $this->context->obj['serializer']->{'serialize'}($meta_value);

        foreach ($meta_ids as $meta_id) {
            /**
             * Fires immediately before updating metadata of a specific type.
             *
             * The dynamic portion of the hook, `$meta_type`, refers to the meta
             * array type (post or user).
             *
             * @since 0.9.9
             *
             * @param int    $meta_id    ID of the metadata entry to update.
             * @param int    $array_id  Array ID.
             * @param string $meta_key   Meta key.
             * @param mixed  $meta_value Meta value.
             */
            $this->context->obj['hook']->{'doAction'}("update_{$meta_type}_meta", $meta_id, $array_id, $meta_key, $meta_value);
        }

        $result = $this->db->table($table);
        $result->begin();
        try {
            $result->where($column, $array_id)
                    ->where('meta_key', $meta_key)
                    ->update([
                        'meta_value' => $this->db->{'ifNull'}($_newvalue)
                    ]);
            $result->commit();
        } catch (Exception $ex) {
            $result->rollback();
            Cascade::getLogger('error')->error(sprintf('METADATA[%s]: %s', $ex->getCode(), $ex->getMessage()));
        }


        if (!$result) {
            return false;
        }

        $this->context->obj['cache']->{'delete'}($array_id, $meta_type . '_meta');

        foreach ($meta_ids as $meta_id) {
            /**
             * Fires immediately after updating metadata of a specific type.
             *
             * The dynamic portion of the hook, `$meta_type`, refers to the meta
             * array type (post or user).
             *
             * @since 0.9.9
             * @param int    $meta_id    ID of updated metadata entry.
             * @param int    $array_id  Array ID.
             * @param string $meta_key   Meta key.
             * @param mixed  $meta_value Meta value.
             */
            $this->context->obj['hook']->{'doAction'}("updated_{$meta_type}_meta", $meta_id, $array_id, $meta_key, $meta_value);
        }

        return true;
    }

    /**
     * Add metadata for the specified array.
     *
     * @since 0.9.9
     * @param string $meta_type  Type of array metadata is for (e.g. post or user)
     * @param int    $array_id  ID of the array metadata is for
     * @param string $meta_key   Metadata key
     * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
     * @return int|false The meta ID on success, false on failure.
     */
    public function create($meta_type, $array_id, $meta_key, $meta_value, $unique = false)
    {
        // Make sure the metadata doesn't aleady exist.
        if (null != $this->read($meta_type, $array_id, $meta_key)) {
            return;
        }

        if (!$meta_type || !$meta_key || !is_numeric($array_id)) {
            return false;
        }

        $array_id = $this->context->obj['util']->{'absint'}($array_id);
        if (!$array_id) {
            return false;
        }

        $table = $this->table($meta_type);
        if (!$table) {
            return false;
        }
        $column = $meta_type . '_id';

        // expected_slashed ($meta_key)
        $meta_key = $this->context->obj['util']->{'unslash'}($meta_key);
        $meta_value = $this->context->obj['util']->{'unslash'}($meta_value);

        /**
         * Filters whether to add metadata of a specific type.
         *
         * The dynamic portion of the hook, `$meta_type`, refers to the meta
         * array type (post or user). Returning a non-null value
         * will effectively short-circuit the function.
         *
         * @since 0.9.9
         * @param null|bool $check          Whether to allow adding metadata for the given type.
         * @param int       $array_id      Array ID.
         * @param string    $meta_key       Meta key.
         * @param mixed     $meta_value     Meta value. Must be serializable if non-scalar.
         * @param bool      $unique         Whether the specified meta key should be unique
         *                                  for the array. Optional. Default false.
         */
        $check = $this->context->obj['hook']->{'applyFilter'}("add_{$meta_type}_metadata", null, $array_id, $meta_key, $meta_value, $unique);
        if (null !== $check) {
            return $check;
        }

        if ($unique) {
            $count = $this->db->table($table)
                    ->where('meta_key', $meta_key)
                    ->where($column, $array_id)
                    ->get();
            if (count($count)) {
                return false;
            }
        }

        $_meta_value = $meta_value;
        $meta_value = $this->context->obj['serializer']->{'serialize'}($meta_value);

        /**
         * Fires immediately before meta of a specific type is added.
         *
         * The dynamic portion of the hook, `$meta_type`, refers to the meta
         * array type (post or user).
         *
         * @since 0.9.9
         * @param int    $array_id  Array ID.
         * @param string $meta_key   Meta key.
         * @param mixed  $meta_value Meta value.
         */
        $this->context->obj['hook']->{'doAction'}("add_{$meta_type}_meta", $array_id, $meta_key, $_meta_value);

        $result = $this->db->table($table);
        $result->begin();
        try {
            $result->insert([
                //'meta_id' => $this->db->autoIncrement($table, 'meta_id'),
                $column => (int) $array_id,
                'meta_key' => $this->db->{'ifNull'}($meta_key),
                'meta_value' => $this->db->{'ifNull'}($meta_value)
            ]);
            $result->commit();
        } catch (Exception $ex) {
            $result->rollback();
            Cascade::getLogger('error')->error(sprintf('METADATA[%s]: %s', $ex->getCode(), $ex->getMessage()));
        }


        if (!$result) {
            return false;
        }

        $mid = $this->db->table($table)
                ->where($column, $array_id)
                ->where('meta_key', $meta_key)
                ->where('meta_value', $meta_value)
                ->get([$meta_type . '_id']);

        $this->context->obj['cache']->{'delete'}($array_id, $meta_type . '_meta');

        /**
         * Fires immediately after meta of a specific type is added.
         *
         * The dynamic portion of the hook, `$meta_type`, refers to the meta
         * array type (post or user).
         *
         * @since 0.9.9
         * @param int    $mid        The meta ID after successful update.
         * @param int    $array_id  Array ID.
         * @param string $meta_key   Meta key.
         * @param mixed  $meta_value Meta value.
         */
        $this->context->obj['hook']->{'doAction'}("added_{$meta_type}_meta", $mid, $array_id, $meta_key, $_meta_value);

        return $mid;
    }

    /**
     * Delete metadata for the specified array.
     *
     * @since 0.9.9
     * @param string $meta_type  Type of array metadata is for (e.g. post or user)
     * @param int    $array_id  ID of the array metadata is for
     * @param string $meta_key   Metadata key
     * @param mixed  $meta_value Optional. Metadata value. Must be serializable if non-scalar. If specified, only delete
     *                           metadata entries with this value. Otherwise, delete all entries with the specified meta_key.
     *                           Pass `null, `false`, or an empty string to skip this check. (For backward compatibility,
     *                           it is not possible to pass an empty string to delete those entries with an empty string
     *                           for a value.)
     * @param bool   $delete_all Optional, default is false. If true, delete matching metadata entries for all arrays,
     *                           ignoring the specified array_id. Otherwise, only delete matching metadata entries for
     *                           the specified array_id.
     * @return bool True on successful delete, false on failure.
     */
    public function delete($meta_type, $array_id, $meta_key, $meta_value = '', $delete_all = false)
    {
        if (!$meta_type || !$meta_key || !is_numeric($array_id) && !$delete_all) {
            return false;
        }

        $array_id = $this->context->obj['util']->{'absint'}($array_id);
        if (!$array_id && !$delete_all) {
            return false;
        }

        $table = $this->table($meta_type);
        if (!$table) {
            return false;
        }

        $type_column = $this->context->obj['sanitize']->{'key'}($meta_type . '_id');
        // expected_slashed ($meta_key)
        $meta_key = $this->context->obj['util']->{'unslash'}($meta_key);
        $meta_value = $this->context->obj['util']->{'unslash'}($meta_value);

        /**
         * Filters whether to delete metadata of a specific type.
         *
         * The dynamic portion of the hook, `$meta_type`, refers to the meta
         * array type (post or user). Returning a non-null value
         * will effectively short-circuit the function.
         *
         * @since 0.9.9
         * @param null|bool $delete     Whether to allow metadata deletion of the given type.
         * @param int       $array_id  Array ID.
         * @param string    $meta_key   Meta key.
         * @param mixed     $meta_value Meta value. Must be serializable if non-scalar.
         * @param bool      $delete_all Whether to delete the matching metadata entries
         *                              for all arrays, ignoring the specified $array_id.
         *                              Default false.
         */
        $check = $this->context->obj['hook']->{'applyFilter'}("delete_{$meta_type}_metadata", null, $array_id, $meta_key, $meta_value, $delete_all);
        if (null !== $check) {
            return (bool) $check;
        }

        $_meta_value = $meta_value;
        $meta_value = $this->context->obj['serializer']->{'serialize'}($meta_value);

        if (!$delete_all) {
            if ('' !== $meta_value && null !== $meta_value && false !== $meta_value) {
                $meta_ids = $this->db->table($table)
                        ->where('meta_key', $meta_key)
                        ->where($type_column, $array_id)
                        ->where('meta_value', $meta_value)
                        ->get(['meta_id']);
            }
            $meta_ids = $this->db->table($table)
                    ->where('meta_key', $meta_key)
                    ->where($type_column, $array_id)
                    ->get(['meta_id']);
        } elseif ($delete_all && '' !== $meta_value && null !== $meta_value && false !== $meta_value) {
            $meta_ids = $this->db->table($table)
                    ->where('meta_key', $meta_key)
                    ->where('meta_value', $meta_value)
                    ->get(['meta_id']);
        } else {
            $meta_ids = $this->db->table($table)
                    ->where('meta_key', $meta_key)
                    ->get(['meta_id']);
        }

        $meta_ids = $this->context->obj['util']->{'flattenArray'}($meta_ids);

        if (!count($meta_ids)) {
            return false;
        }

        if ($delete_all) {
            if ('' !== $meta_value && null !== $meta_value && false !== $meta_value) {
                $array_ids = $this->db->table($table)
                        ->where('meta_key', $meta_key)
                        ->where('meta_value', $meta_value)
                        ->get([$type_column]);
            } else {
                $array_ids = $this->db->table($table)
                        ->where('meta_key', $meta_key)
                        ->get([$type_column]);
            }
        }

        /**
         * Fires immediately before deleting metadata of a specific type.
         *
         * The dynamic portion of the hook, `$meta_type`, refers to the meta
         * array type (post or user).
         *
         * @since 0.9.9
         * @param array  $meta_ids   An array of metadata entry IDs to delete.
         * @param int    $array_id  Array ID.
         * @param string $meta_key   Meta key.
         * @param mixed  $meta_value Meta value.
         */
        $this->context->obj['hook']->{'doAction'}("delete_{$meta_type}_meta", $meta_ids, $array_id, $meta_key, $_meta_value);

        $count = $this->db->table($table);
        $count->begin();
        try {
            $count->where('meta_id', 'in', implode(',', $meta_ids))
                    ->delete();
            $count->commit();
        } catch (Exception $ex) {
            $count->rollback();
            Cascade::getLogger('error')->error(sprintf('METADATA[%s]: %s', $ex->getCode(), $ex->getMessage()));
        }


        if (!$count) {
            return false;
        }

        if ($delete_all) {
            foreach ((array) $array_ids as $o_id) {
                $this->context->obj['cache']->{'delete'}($o_id, $meta_type . '_meta');
            }
        } else {
            $this->context->obj['cache']->{'delete'}($array_id, $meta_type . '_meta');
        }

        /**
         * Fires immediately after deleting metadata of a specific type.
         *
         * The dynamic portion of the hook name, `$meta_type`, refers to the meta
         * array type (post or user).
         *
         * @since 0.9.9
         * @param array  $meta_ids   An array of deleted metadata entry IDs.
         * @param int    $array_id  Array ID.
         * @param string $meta_key   Meta key.
         * @param mixed  $meta_value Meta value.
         */
        $this->context->obj['hook']->{'doAction'}("deleted_{$meta_type}_meta", $meta_ids, $array_id, $meta_key, $_meta_value);

        return true;
    }

    /**
     * Determine if a meta key is set for a given array
     *
     * @since 0.9.9
     * @param string $meta_type Type of array metadata is for (e.g. post or user)
     * @param int    $array_id ID of the array metadata is for
     * @param string $meta_key  Metadata key.
     * @return bool True of the key is set, false if not.
     */
    public function exists($meta_type, $array_id, $meta_key)
    {
        if (!$meta_type || !is_numeric($array_id)) {
            return false;
        }

        $array_id = $this->context->obj['util']->{'absint'}($array_id);
        if (!$array_id) {
            return false;
        }

        /** This filter is documented in app/src/Common/MetaData.php */
        $check = $this->context->obj['hook']->{'applyFilter'}("get_{$meta_type}_metadata", null, $array_id, $meta_key, true);
        if (null !== $check) {
            return (bool) $check;
        }

        $meta_cache = $this->context->obj['cache']->{'read'}($array_id, $meta_type . '_meta');

        if (!$meta_cache) {
            $meta_cache = $this->updateMetaDataCache($meta_type, [$array_id]);
            $meta_cache = $meta_cache[$array_id];
        }

        if (isset($meta_cache->{$meta_key})) {
            return true;
        }

        return false;
    }

    /**
     * Get meta data by meta ID.
     *
     * @since 0.9.9
     * @param string $meta_type Type of array metadata is for (e.g. post or user).
     * @param int    $meta_id   ID for a specific meta row
     * @return array|false Meta array or false.
     */
    public function readByMid($meta_type, $meta_id)
    {
        if (!$meta_type || !is_numeric($meta_id)) {
            return false;
        }

        $meta_id = intval($meta_id);
        if ($meta_id <= 0) {
            return false;
        }

        $table = $this->table($meta_type);
        if (!$table) {
            return false;
        }

        $meta = $this->db->table($table)
                ->where('meta_id', $meta_id)
                ->first();

        if (empty($meta)) {
            return false;
        }

        if (isset($meta['meta_value'])) {
            $meta['meta_value'] = $this->context->obj['serializer']->{'unserialize'}($meta['meta_value']);
        }

        return $meta;
    }

    /**
     * Update meta data by meta ID
     *
     * @since 0.9.9
     * @param string $meta_type  Type of array metadata is for (e.g. post or user)
     * @param int    $meta_id    ID for a specific meta row
     * @param string $meta_value Metadata value
     * @param string $meta_key   Optional, you can provide a meta key to update it
     * @return bool True on successful update, false on failure.
     */
    public function updateByMid($meta_type, $meta_id, $meta_value, $meta_key = false)
    {

        // Make sure everything is valid.
        if (!$meta_type || !is_numeric($meta_id)) {
            return false;
        }

        $meta_id = intval($meta_id);
        if ($meta_id <= 0) {
            return false;
        }

        $table = $this->table($meta_type);
        if (!$table) {
            return false;
        }

        // Fetch the meta and go on if it's found.
        if ($meta = $this->readByMid($meta_type, $meta_id)) {
            $original_key = $meta['meta_key'];
            $array_id = $meta[$meta_type . '_id'];

            // If a new meta_key (last parameter) was specified, change the meta key,
            // otherwise use the original key in the update statement.
            if (false === $meta_key) {
                $meta_key = $original_key;
            } elseif (!is_string($meta_key)) {
                return false;
            }

            $_meta_value = $meta_value;
            $meta_value = $this->context->obj['serializer']->{'serialize'}($meta_value);

            $this->context->obj['hook']->{'doAction'}("update_{$meta_type}_meta", $meta_id, $array_id, $meta_key, $_meta_value);

            // Run the update query.
            $result = $this->db->table($table);
            $result->begin();
            try {
                $result->where('meta_id', $meta_id)
                        ->update([
                            'meta_key' => $this->db->{'ifNull'}($meta_key),
                            'meta_value' => $this->db->{'ifNull'}($meta_value)
                        ]);
                $result->commit();
            } catch (Exception $ex) {
                $result->rollback();
                Cascade::getLogger('error')->error(sprintf('METADATA[%s]: Error: %s', $ex->getCode(), $ex->getMessage()));
            }

            if (!$result) {
                return false;
            }

            // Clear the caches.
            $this->context->obj['cache']->{'delete'}($array_id, $meta_type . '_meta');

            $this->context->obj['hook']->{'doAction'}("updated_{$meta_type}_meta", $meta_id, $array_id, $meta_key, $_meta_value);

            return true;
        }

        // And if the meta was not found.
        return false;
    }

    /**
     * Delete meta data by meta ID
     *
     * @since 0.9.9
     * @param string $meta_type Type of array metadata is for (e.g. post or user).
     * @param int    $meta_id   ID for a specific meta row
     * @return bool True on successful delete, false on failure.
     */
    public function deleteByMid($meta_type, $meta_id)
    {

        // Make sure everything is valid.
        if (!$meta_type || !is_numeric($meta_id)) {
            return false;
        }

        $meta_id = intval($meta_id);
        if ($meta_id <= 0) {
            return false;
        }

        $table = $this->table($meta_type);
        if (!$table) {
            return false;
        }

        // Fetch the meta and go on if it's found.
        if ($meta = $this->readByMid($meta_type, $meta_id)) {
            $array_id = $meta[$meta_type . '_id'];

            $this->context->obj['hook']->{'doAction'}("delete_{$meta_type}_meta", (array) $meta_id, $array_id, $meta['meta_key'], $meta['meta_value']);

            // Run the query, will return true if deleted, false otherwise
            $result = (bool) $this->db->table($table)->where('meta_id', $meta_id)->delete();

            // Clear the caches.
            $this->context->obj['cache']->{'delete'}($array_id, $meta_type . '_meta');

            $this->context->obj['hook']->{'doAction'}("deleted_{$meta_type}_meta", (array) $meta_id, $array_id, $meta['meta_key'], $meta['meta_value']);

            return $result;
        }

        // Meta id was not found.
        return false;
    }

    /**
     * Update the metadata cache for the specified arrays.
     *
     * @since 0.9.9
     * @param string    $meta_type  Type of array metadata is for (e.g., post or user)
     * @param int|array $array_ids Array or comma delimited list of array IDs to update cache for
     * @return array|false Metadata cache for the specified arrays, or false on failure.
     */
    public function updateMetaDataCache($meta_type, $array_ids)
    {
        if (!$meta_type || !$array_ids) {
            return false;
        }

        $table = $this->table($meta_type);
        if (!$table) {
            return false;
        }

        $column = $meta_type . '_id';

        if (!is_array($array_ids)) {
            $array_ids = preg_replace('|[^0-9,]|', '', $array_ids);
            $array_ids = explode(',', $array_ids);
        }

        $array_ids = array_map('intval', $array_ids);

        $cache_key = $meta_type . '_meta';
        $ids = [];
        $cache = [];
        foreach ($array_ids as $id) {
            $cached_array = $this->context->obj['cache']->{'read'}($id, $cache_key);
            if (false === $cached_array) {
                $ids[] = $id;
            } else {
                $cache[$id] = $cached_array;
            }
        }

        if (empty($ids)) {
            return $cache;
        }

        // Get meta info
        $id_list = join(',', $ids);
        $meta_list = $this->db->table($table)
            ->where($column, 'in', $id_list)
            ->sortBy('meta_id')
            ->get();

        if (!empty($meta_list)) {
            foreach ($meta_list as $metarow) {
                $mpid = intval($metarow[$column]);
                $mkey = $metarow['meta_key'];
                $mval = $metarow['meta_value'];
                // Force subkeys to be array type:
                if (!isset($cache[$mpid]) || !is_array($cache[$mpid])) {
                    $cache[$mpid] = [];
                }
                if (!isset($cache[$mpid][$mkey]) || !is_array($cache[$mpid][$mkey])) {
                    $cache[$mpid][$mkey] = [];
                }
                // Add a value to the current pid/key:
                $cache[$mpid][$mkey][] = $mval;
            }
        }
        foreach ($ids as $id) {
            if (!isset($cache[$id])) {
                $cache[$id] = [];
            }
            $this->context->obj['cache']->{'create'}($id, $cache[$id], $cache_key);
        }
        return $cache;
    }
}
