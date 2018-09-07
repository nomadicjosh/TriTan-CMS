<?php
namespace TriTan\Interfaces\Posttype;

use TriTan\Common\Posttype\Posttype;

interface PosttypeCacheInterface
{
    /**
     * Update posttype caches.
     *
     * @since 0.9.9
     * @param object $posttype Posttype object to be cached.
     */
    public function update(Posttype $posttype);

    /**
     * Clean posttype caches.
     *
     * @since 0.9.9
     * @param object $posttype Posttype object to be cleaned from the cache.
     */
    public function clean(Posttype $posttype);
}
