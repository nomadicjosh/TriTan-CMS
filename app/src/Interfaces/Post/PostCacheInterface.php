<?php
namespace TriTan\Interfaces\Post;

use TriTan\Common\Post\Post;

interface PostCacheInterface
{
    /**
     * Update post caches.
     *
     * @since 0.9.9
     * @param object $post Post object to be cached.
     */
    public function update(Post $post);

    /**
     * Clean post caches.
     *
     * @since 0.9.9
     * @param object $post Post object to be cleaned from the cache.
     */
    public function clean(Post $post);
}
