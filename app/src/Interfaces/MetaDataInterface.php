<?php
namespace TriTan\Interfaces;

interface MetaDataInterface
{
    /**
     * Add metadata for the specified array.
     *
     * @since 0.9.9
     * @param string $meta_type  Type of array metadata is for (e.g. post or user)
     * @param int    $array_id   ID of the array metadata is for
     * @param string $meta_key   Metadata key
     * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
     * @return int|false The meta ID on success, false on failure.
     */
    public function create($meta_type, $array_id, $meta_key, $meta_value, $unique = false);

    /**
     * Retrieve metadata for the specified array.
     *
     * @since 0.9.9
     * @param string $meta_type Type of array metadata is for (e.g. post or user)
     * @param int    $array_id ID of the array metadata is for
     * @param string $meta_key  Optional. Metadata key. If not specified, retrieve all metadata for
     *                                    the specified array.
     * @return mixed Array of values
     */
    public function read($meta_type, $array_id, $meta_key = '', $single = false);

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
    public function update($meta_type, $array_id, $meta_key, $meta_value, $prev_value = '');

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
    public function delete($meta_type, $array_id, $meta_key, $meta_value = '', $delete_all = false);

    /**
     * Get meta data by meta ID.
     *
     * @since 0.9.9
     * @param string $meta_type Type of array metadata is for (e.g. post or user).
     * @param int    $meta_id   ID for a specific meta row
     * @return array|false Meta array or false.
     */
    public function readByMid($meta_type, $meta_id);

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
    public function updateByMid($meta_type, $meta_id, $meta_value, $meta_key = false);

    /**
     * Delete meta data by meta ID
     *
     * @since 0.9.9
     * @param string $meta_type Type of array metadata is for (e.g. post or user).
     * @param int    $meta_id   ID for a specific meta row
     * @return bool True on successful delete, false on failure.
     */
    public function deleteByMid($meta_type, $meta_id);
}
