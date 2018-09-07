<?php
namespace TriTan\Common\Post;

use TriTan\Interfaces\Cache\ObjectCacheInterface;
use TriTan\Interfaces\Hooks\ActionFilterHookInterface;
use TriTan\Interfaces\Post\PostCacheInterface;
use TriTan\Common\Post\Post;

class PostCache implements PostCacheInterface
{
    public $cache;

    public $hook;

    public function __construct(ObjectCacheInterface $cache, ActionFilterHookInterface $hook)
    {
        $this->cache = $cache;
        $this->hook = $hook;
    }

    /**
     * Update post caches.
     *
     * @since 0.9.9
     * @param object|Post $post Post object to be cached.
     * @return bool|null Returns false on failure.
     */
    public function update($post)
    {
        if (empty($post)) {
            return;
        }

        $this->cache->{'create'}((int) $post->getId(), $post, 'posts');
        $this->cache->{'create'}($post->getSlug(), (int) $post->getId(), 'postslugs');
        $this->cache->{'create'}($post->getPosttype(), (int) $post->getId(), 'post_posttypes');
    }

    /**
     * Clean Post caches.
     *
     * Uses `clean_post_cache` action.
     *
     * @since 0.9.9
     * @param object|Post $post Post object to be cleaned from the cache.
     */
    public function clean($post)
    {
        if (empty($post)) {
            return;
        }

        $post_id = $post;
        $post = get_post($post_id);
        if (!$post) {
            if (!is_numeric($post_id)) {
                return;
            }

            // Make sure a Post object exists even when the post has been deleted.
            $post = new Post();
            $post->setId($post_id);
            $post->setSlug(null);
            $post->setPosttype(null);
        }

        $post_id = $post->getId();

        $this->cache->{'delete'}((int) $post->getId(), 'posts');
        $this->cache->{'delete'}($post->getSlug(), 'postslugs');
        $this->cache->{'delete'}($post->getPosttype(), 'post_posttypes');
        $this->cache->{'delete'}((int) $post->getId(), 'post_meta');

        /**
         * Fires immediately after the given post's cache is cleaned.
         *
         * @since 0.9.9
         * @param int   $post_id Post id.
         * @param array $post    Post object.
         */
        $this->hook->{'doAction'}('clean_post_cache', (int) $post_id, $post);
    }
}
