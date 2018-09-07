<?php
namespace TriTan\Interfaces\User;

use TriTan\Common\User\User;

interface UserCacheInterface
{
    /**
     * Update user caches.
     *
     * @since 0.9.9
     * @param object $user User object to be cached.
     */
    public function update(User $user);

    /**
     * Clean user caches.
     *
     * @since 0.9.9
     * @param object $user User object to be cleaned from the cache.
     */
    public function clean(User $user);
}
