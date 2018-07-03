<?php

namespace TriTan\Laci\Pipes;

use Closure;

class LimiterPipe implements PipeInterface
{
    protected $limit;
    protected $offset;

    public function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function setOffset($offset)
    {
        if (!is_null($offset)) {
            $this->offset = $offset;
        }
        return $this;
    }

    public function process(array $data)
    {
        $limit = (int) $this->limit ?: count($data);
        $offset = (int) $this->offset;
        return array_slice($data, $offset, $limit);
    }
}
