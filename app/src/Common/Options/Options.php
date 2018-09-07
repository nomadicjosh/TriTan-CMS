<?php
namespace TriTan\Common\Options;

use TriTan\Interfaces\Options\OptionsInterface;
use TriTan\Interfaces\Options\OptionsMapperInterface;

class Options implements OptionsInterface
{
    public $mapper;

    public function __construct(OptionsMapperInterface $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Add an option to the table
     */
    public function create($name, $value = '')
    {
        return $this->mapper->{'create'}($name, $value);
    }

    /**
     * Read an option from options_meta.
     * Return value or $default if not found
     */
    public function read($option_key, $default = false)
    {
        return $this->mapper->{'read'}($option_key, $default);
    }

    /**
     * Update (add if doesn't exist) an option to options_meta
     */
    public function update($option_key, $newvalue)
    {
        return $this->mapper->{'update'}($option_key, $newvalue);
    }

    /**
     * Delete an option from the table
     */
    public function delete($name)
    {
        return $this->mapper->{'delete'}($name);
    }

    /**
     * Checks if a key exists in the option table.
     *
     * @since 0.9.9
     * @param string $option_key Key to check against.
     * @return bool
     */
    public function exists($option_key) : bool
    {
        return $this->mapper->{'exists'}($option_key);
    }
}
