<?php
namespace TriTan\Traits;

trait TraversableTrait
{
    use HasDataTrait;

    public function getIterator()
    {
        return new \ArrayIterator($this->getData());
    }
}
