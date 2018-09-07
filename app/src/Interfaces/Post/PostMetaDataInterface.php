<?php
namespace TriTan\Interfaces\Post;

interface PostMetaDataInterface
{
    /**
     * Add meta data field to a post.
     *
     * @since 0.9.9
     * @param int    $post_id    Post ID.
     * @param string $meta_key   Metadata name.
     * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
     * @param bool   $unique     Optional. Whether the same key should not be added.
     *                           Default false.
     * @return int|false Meta ID on success, false on failure.
     */
    public function create($post_id, $meta_key, $meta_value, $unique = false);

    /**
     * Retrieve post meta field for a post.
     *
     * @since 0.9.9
     * @param int    $post_id Post ID.
     * @param string $key     Optional. The meta key to retrieve. By default, returns
     *                        data for all keys. Default empty.
     * @param bool   $single  Optional. Whether to return a single value. Default false.
     * @return mixed Will be an array if $single is false. Will be value of meta data
     *               field if $single is true.
     */
    public function read($post_id, $key = '', $single = false);

    /**
     * Update post meta field based on post ID.
     *
     * Use the $prev_value parameter to differentiate between meta fields with the
     * same key and post ID.
     *
     * If the meta field for the post does not exist, it will be added.
     *
     * @since 0.9.9
     * @param int    $post_id    Post ID.
     * @param string $meta_key   Metadata key.
     * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
     * @param mixed  $prev_value Optional. Previous value to check before removing.
     *                           Default empty.
     * @return int|bool Meta ID if the key didn't exist, true on successful update,
     *                  false on failure.
     */
    public function update($post_id, $meta_key, $meta_value, $prev_value = '');

    /**
     * Remove metadata matching criteria from a post.
     *
     * You can match based on the key, or key and value. Removing based on key and
     * value, will keep from removing duplicate metadata with the same key. It also
     * allows removing all metadata matching key, if needed.
     *
     * @since 0.9.9
     * @param int    $post_id    Post ID.
     * @param string $meta_key   Metadata name.
     * @param mixed  $meta_value Optional. Metadata value. Must be serializable if
     *                           non-scalar. Default empty.
     * @return bool True on success, false on failure.
     */
    public function delete($post_id, $meta_key, $meta_value = '');

    /**
     * Get post meta data by meta ID.
     *
     * @since 0.9.9
     * @param int $mid
     * @return array|bool
     */
    public function readByMid($mid);

    /**
     * Update post meta data by meta ID.
     *
     * @since 0.9.9
     * @param int $mid
     * @param string $meta_key
     * @param string $meta_value
     * @return bool
     */
    public function updateByMid($mid, $meta_key, $meta_value);

    /**
     * Delete post meta data by meta ID.
     *
     * @since 0.9.9
     * @param int $mid
     * @return bool
     */
    public function deleteByMid($mid);
}
