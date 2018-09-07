<?php
namespace TriTan\Interfaces;

interface SerializerInterface
{
    public function serialize(string $data);
    
    public function unserialize(string $data);
}
