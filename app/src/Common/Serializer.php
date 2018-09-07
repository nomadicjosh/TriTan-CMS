<?php
namespace TriTan\Common;

class Serializer
{
    /**
     * Serializes data if necessary.
     *
     * @since 0.9.9
     * @param string $data Data to be serialized.
     * @return string Serialized data or original string.
     * @throws \InvalidArgumentException
     */
    public function serialize($data)
    {
        if (is_resource($data)) {
            throw new \InvalidArgumentException(
                "PHP resources are not serializable."
            );
        }

        if ($this->isSerialized($data)) {
            return serialize($data);
        }

        return $data;
    }

    /**
     * Unserializes data if necessary.
     *
     * @since 0.9.9
     * @param string $data Data that should be unserialzed.
     * @return string Unserialized data or original string.
     * @throws \InvalidArgumentException
     */
    public function unserialize($data)
    {
        /**
         * Check data first to make sure it can be unserialized.
         */
        if ($this->isSerialized($data)) {
            return unserialize($data);
        }

        return $data;
    }

    private function isSerialized($data)
    {
        // if it isn't a string, it isn't serialized
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (!preg_match('/^([adObis]):/', $data, $badions)) {
            return false;
        }
        switch ($badions[1]) {
            case 'a':
            case 'O':
            case 's':
                if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data)) {
                    return true;
                }
                break;
            case 'b':
            case 'i':
            case 'd':
                if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data)) {
                    return true;
                }
                break;
        }
        return false;
    }
}
