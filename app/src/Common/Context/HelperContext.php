<?php
namespace TriTan\Common\Context;

class HelperContext implements \TriTan\Interfaces\ContextInterface
{
    public $obj = [];

    public function __construct()
    {
        $this->obj['app'] = \Liten\Liten::getInstance();
        $this->obj['hook'] = \TriTan\Common\Hooks\ActionFilterHook::getInstance();
        $this->obj['cache'] = $this->obj['hook']->{'applyFilter'}('object_cache_driver', new \TriTan\Cache\ObjectCache(new \TriTan\Cache\CacheJSON()));
        $this->obj['file'] = new \TriTan\Common\FileSystem($this->obj['hook']);
        $this->obj['serializer'] = new \TriTan\Common\Serializer();
        $this->obj['uri'] = new \TriTan\Common\Uri($this->obj['hook']);
        $this->obj['util'] = new \TriTan\Common\Utils($this->obj['hook']);
        $this->obj['sanitizer'] = new \TriTan\Common\Sanitizer($this->obj['hook']);
        $this->obj['meta'] = new \TriTan\Common\MetaData(new \TriTan\Database(), $this);
        $this->obj['usercache'] = new \TriTan\Common\User\UserCache($this->obj['cache'], $this->obj['hook']);
        $this->obj['postcache'] = new \TriTan\Common\Post\PostCache($this->obj['cache'], $this->obj['hook']);
        $this->obj['sitecache'] = new \TriTan\Common\Site\SiteCache($this->obj['cache'], $this->obj['hook']);
        $this->obj['usermeta'] = new \TriTan\Common\User\UserMetaData($this->obj['meta'], $this->obj['util']);
        $this->obj['date'] = new \TriTan\Common\Date();
        $this->obj['flash'] = new \TriTan\Common\FlashMessages();
        $this->obj['image'] = new \TriTan\Common\Image($this->obj['util']);
        $this->obj['ssl'] = new \TriTan\Common\Ssl();
        $this->obj['escape'] = new \TriTan\Common\Escape();
        $this->obj['html'] = new \TriTan\Common\HtmlPurifier($this->obj['util']);
        return $this->obj;
    }
}
