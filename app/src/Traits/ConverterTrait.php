<?php
namespace TriTan\Traits;

trait ConverterTrait
{
    /**
     * Takes an array and turns it into an object.
     *
     * @since 0.9.9
     * @param array $array Array of data.
     */
    abstract public function toObject(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->toObject($value);
            }
        }
        return (object) $array;
    }

    /**
     * Takes an object and turns it into an array.
     *
     * @since 0.9.9
     * @param object $object Object data.
     */
    abstract public function toArray($object)
    {
        return get_object_vars($object);
    }
}
