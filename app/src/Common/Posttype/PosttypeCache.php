<?php
namespace TriTan\Common\Posttype;

use TriTan\Interfaces\Cache\ObjectCacheInterface;
use TriTan\Interfaces\Hooks\ActionFilterHookInterface;
use TriTan\Interfaces\Posttype\PosttypeCacheInterface;

class PosttypeCache implements PosttypeCacheInterface
{
    public $cache;
    
    public $hook;

    public function __construct(ObjectCacheInterface $cache, ActionFilterHookInterface $hook)
    {
        $this->cache = $cache;
        $this->hook = $hook;
    }

    /**
     * Update user caches.
     *
     * @since 0.9.9
     * @param object|Posttype $posttype Posttype object to be cached.
     * @return bool|null Returns false on failure.
     */
    public function update($posttype)
    {
        if(empty($posttype)) {
            return;
        }
        
        $this->cache->{'create'}((int) $posttype->getId(), $posttype, 'posttypes');
    }

    /**
     * Clean Posttype caches.
     *
     * Uses `clean_posttype_cache` action.
     *
     * @since 0.9.9
     * @param object|Posttype|int $posttype Posttype object or id to be cleaned from the cache.
     */
    public function clean($posttype)
    {
        if(empty($posttype)) {
            return;
        }
        
        $posttype_id = $posttype;
        $posttype = get_posttype($posttype_id);
        if (!$posttype) {
            if (!is_numeric($posttype_id)) {
                return;
            }

            // Make sure a Post object exists even when the posttype has been deleted.
            $posttype = new Posttype();
            $posttype->setId($posttype_id);
        }
        
        $posttype_id = $posttype->getId();
        
        $this->cache->{'delete'}((int) $posttype->getId(), 'posttypes');
        $this->cache->{'delete'}('posttypes', 'posttypes');
        $this->cache->{'flushNamespace'}('posts');
        $this->cache->{'flushNamespace'}('post_meta');

        /**
         * Fires immediately after the given posttype's cache is cleaned.
         *
         * @since 0.9.9
         * @param int   $posttype_id Posttype id.
         * @param array $posttype    Posttype object.
         */
        $this->hook->{'doAction'}('clean_posttype_cache', (int) $posttype_id, $posttype);
    }

}
