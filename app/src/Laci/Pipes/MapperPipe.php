<?php

namespace TriTan\Laci\Pipes;

use Closure;

class MapperPipe implements PipeInterface
{

    protected $mappers = [];

    public function process(array $data)
    {
        foreach($this->mappers as $mapper)
        {
            $data = array_map($mapper, $data);
        }

        return $data;
    }

    public function add(Closure $mapper)
    {
        $this->mappers[] = $mapper;
    }

}
