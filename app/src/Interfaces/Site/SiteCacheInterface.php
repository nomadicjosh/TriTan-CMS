<?php
namespace TriTan\Interfaces\Site;

interface SiteCacheInterface
{
    /**
     * Update site caches.
     *
     * @since 0.9.9
     * @param Site|null $site Site or site id to be cached.
     */
    public function update($site);

    /**
     * Clean site caches.
     *
     * @since 0.9.9
     * @param object|int $site Site or site id to be cleaned from the cache.
     */
    public function clean($site);
}
