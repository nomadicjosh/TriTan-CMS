<?php

namespace TriTan\Laci\Pipes;

use Closure;

class FilterPipe implements PipeInterface
{

    protected $filters = [];

    public function process(array $data)
    {
        $filters = $this->filters;
        return array_filter($data, function($row) use ($filters) {
            $result = true;
            foreach ($filters as $i => $filter) {
                list($filter, $type) = $filter;
                switch ($type) {
                    case 'and':
                        $result = ($result AND $filter($row));
                        break;
                    case 'or':
                        $result = ($result OR $filter($row));
                        break;
                    default:
                        throw new \InvalidArgumentException("Filter type must be 'AND' or 'OR'.", 1);
                }
            }
            return $result;
        });
    }

    public function add(Closure $filter, $type = 'AND')
    {
        $this->filters[] = [$filter, strtolower($type)];
    }

}
