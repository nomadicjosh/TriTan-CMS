<?php
namespace TriTan\Interfaces;

interface DatabaseInterface
{
    /**
     * Database table.
     *
     * @param string $name Database table name.
     */
    public function table($name);
}
