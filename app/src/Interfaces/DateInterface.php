<?php
namespace TriTan\Interfaces;

interface DateInterface
{
    public function format($format = 'Y-m-d H:i:s', $date = 'now');
    
    public function current($type, bool $gmt = false);
}