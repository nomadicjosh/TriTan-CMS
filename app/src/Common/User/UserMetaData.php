<?php
namespace TriTan\Common\User;

use TriTan\Interfaces\User\UserMetaDataInterface;
use TriTan\Interfaces\MetaDataInterface;
use TriTan\Interfaces\UtilsInterface;

class UserMetaData implements UserMetaDataInterface
{
    public $meta;
    
    public $util;

    public function __construct(MetaDataInterface $meta, UtilsInterface $util)
    {
        $this->meta = $meta;
        $this->util = $util;
    }

    /**
     * Add meta data field to a user.
     *
     * @since 0.9.9
     * @param int    $user_id    User ID.
     * @param string $meta_key   Metadata name.
     * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
     * @param bool   $unique     Optional. Whether the same key should not be added.
     *                           Default false.
     * @return int|false Meta ID on success, false on failure.
     */
    public function create($user_id, $meta_key, $meta_value, $unique = false)
    {
        return $this->meta->{'create'}('user', $user_id, $meta_key, $meta_value, $unique);
    }

    /**
     * Retrieve user meta field for a user.
     *
     * @since 0.9.9
     * @param int    $user_id User ID.
     * @param string $key     Optional. The meta key to retrieve. By default, returns
     *                        data for all keys. Default empty.
     * @param bool   $single  Optional. Whether to return a single value. Default false.
     * @return mixed Will be an array if $single is false. Will be value of meta data
     *               field if $single is true.
     */
    public function read($user_id, $key = '', $single = false)
    {
        return $this->meta->{'read'}('user', $user_id, $key, $single);
    }

    /**
     * Update user meta field based on user ID.
     *
     * Use the $prev_value parameter to differentiate between meta fields with the
     * same key and user ID.
     *
     * If the meta field for the user does not exist, it will be added.
     *
     * @since 0.9.9
     * @param int    $user_id    User ID.
     * @param string $meta_key   Metadata key.
     * @param mixed  $meta_value Metadata value. Must be serializable if non-scalar.
     * @param mixed  $prev_value Optional. Previous value to check before removing.
     *                           Default empty.
     * @return int|bool Meta ID if the key didn't exist, true on successful update,
     *                  false on failure.
     */
    public function update($user_id, $meta_key, $meta_value, $prev_value = '')
    {
        return $this->meta->{'update'}('user', $user_id, $meta_key, $meta_value, $prev_value);
    }

    /**
     * Remove metadata matching criteria from a user.
     *
     * You can match based on the key, or key and value. Removing based on key and
     * value, will keep from removing duplicate metadata with the same key. It also
     * allows removing all metadata matching key, if needed.
     *
     * @since 0.9.9
     * @param int    $user_id    User ID.
     * @param string $meta_key   Metadata name.
     * @param mixed  $meta_value Optional. Metadata value. Must be serializable if
     *                           non-scalar. Default empty.
     * @return bool True on success, false on failure.
     */
    public function delete($user_id, $meta_key, $meta_value = '')
    {
        return $this->meta->{'delete'}('user', $user_id, $meta_key, $meta_value);
    }

    /**
     * Get user meta data by meta ID.
     *
     * @since 0.9.9
     * @param int $mid Meta id.
     * @return array|bool
     */
    public function readByMid($mid)
    {
        return $this->meta->{'readByMid'}('user', $mid);
    }

    /**
     * Update user meta data by meta ID.
     *
     * @since 0.9.9
     * @param int $mid
     * @param string $meta_key Meta key.
     * @param string $meta_value Meta value.
     * @return bool
     */
    public function updateByMid($mid, $meta_key, $meta_value)
    {
        $_meta_key = $this->util->{'unslash'}($meta_key);
        $_meta_value = $this->util->{'unslash'}($meta_value);
        return $this->meta->{'updateByMid'}('user', $mid, $_meta_key, $_meta_value);
    }

    /**
     * Delete user meta data by meta ID.
     *
     * @since 0.9.9
     * @param int $mid Meta id.
     * @return bool
     */
    public function deleteByMid($mid)
    {
        return $this->meta->{'deleteByMid'}('user', $mid);
    }
}
