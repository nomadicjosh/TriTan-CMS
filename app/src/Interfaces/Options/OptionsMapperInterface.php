<?php
namespace TriTan\Interfaces\Options;

interface OptionsMapperInterface
{
    /**
     * Add an option to the options document.
     */
    public function create($name, $value = '');
    
    /**
     * Read an option from the options document.
     * Return value or $default if not found
     */
    public function read($option_key, $default = false);
    
    /**
     * Update (add if doesn't exist) an option to options_meta
     */
    public function update($option_key, $newvalue);
    
    /**
     * Delete an option from the options document.
     */
    public function delete($name);
}
