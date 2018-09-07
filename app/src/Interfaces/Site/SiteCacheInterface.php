<?php
namespace TriTan\Interfaces\Site;

use TriTan\Common\Site\Site;

interface SiteCacheInterface
{
    /**
     * Update site caches.
     *
     * @since 0.9.9
     * @param object $site Site object to be cached.
     */
    public function update(Site $site);

    /**
     * Clean site caches.
     *
     * @since 0.9.9
     * @param object $site Site object to be cleaned from the cache.
     */
    public function clean(Site $site);
}
