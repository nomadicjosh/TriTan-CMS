<?php
namespace TriTan\Common;
use TriTan\Interfaces\Hooks\ActionFilterHookInterface;

class Sanitizer implements \TriTan\Interfaces\SanitizerInterface
{
    public $regexes = [
            'date'          => "^[0-9]{1,2}[-/][0-9]{1,2}[-/][0-9]{4}\$",
            'amount'        => "^[-]?[0-9]+\$",
            'number'        => "^[-]?[0-9,]+\$",
            'alphanum'      => "^[0-9a-zA-Z ,.-_\\s\?\!]+\$",
            'not_empty'     => "[a-z0-9A-Z]+",
            'words'         => "^[A-Za-z]+[A-Za-z \\s]*\$",
            'phone'         => "^[0-9]{10,11}\$",
            'zipcode'       => "^[1-9][0-9]{3}[a-zA-Z]{2}\$",
            'plate'         => "^([0-9a-zA-Z]{2}[-]){2}[0-9a-zA-Z]{2}\$",
            'price'         => "^[0-9.,]*(([.,][-])|([.,][0-9]{2}))?\$",
            '2digitopt'     => "^\d+(\,\d{2})?\$",
            '2digitforce'   => "^\d+\,\d\d\$",
            'anything'      => "^[\d\D]{1,}\$"
    ];

    private $validate = [];

    private $sanitize = [];

    private $required = [];

    private $errors = [];

    private $corrects = [];

    private $fields = [];
    
    private $hook;

    public function __construct(ActionFilterHookInterface $hook, $validate = [], $required = [], $sanitize = [])
    {
        $this->validate = $validate;
        $this->sanitize = $sanitize;
        $this->required = $required;
        $this->errors = [];
        $this->corrects = [];
        $this->hook = $hook;
    }

    /**
     * Validates an array of items (if needed).
     *
     * @since 0.9.9
     * @param array $items Items to validate.
     * @return bool True if valid, false otherwise.
     */
    public function validateItems($items): bool
    {
        $this->fields = $items;
        $havefailures = false;
        foreach ($items as $key => $val) {
            if ((strlen($val) == 0 ||
            array_search($key, $this->validate) === false) &&
            array_search($key, $this->required) === false
            ) {
                $this->corrects[] = $key;
                continue;
            }
            $result = $this->validateItem($val, $this->validate[$key]);
            if ($result === false) {
                $havefailures = true;
                $this->addError($key, $this->validate[$key]);
            } else {
                $this->corrects[] = $key;
            }
        }

        return(!$havefailures);
    }

    /**
     * Sanitizes an array of items according to the $this->sanitize[].
     * 
     * Sanitize will be standard of type string, but can also be specified.
     * For ease of use, this syntax is accepted: 
     * 
     *      $sanitize = array('fieldname', 'otherfieldname' => 'float');
     *      $this->items($sanitize);
     * 
     * @since 0.9.9
     * @param array $items    Items to sanitize.
     * @param string $context The context for which the string is being sanitized.
     * @return array Sanitized items.
     */
    public function items(array $items, $context = 'save')
    {
        $raw_items = $items;
        
        foreach ($items as $key => $val) {
            
            if ('save' == $context) {
                $val = $this->removeAccents($val);
            }
            
            if (array_search($key, $this->sanitize) === false && !array_key_exists($key, $this->sanitize)) {
                continue;
            }
            $items[$key] = $this->sanitizeItem($val, $this->validate[$key]);
        }
        
        /**
         * Filters sanitized items.
         *
         * @since 0.9.9
         *
         * @param string $items  Sanitized items.
         * @param string $raw_items The items prior to sanitization.
         * @param string $context The context for which the string is being sanitized.
         */
        return $this->hook->{'applyFilter'}('sanitize_items', $items, $raw_items, $context);
    }

    /**
     *
     * Adds an error to the errors array.
     */
    private function addError($field, $type = 'string')
    {
        $this->errors[$field] = $type;
    }

    /**
     * Sanitizes an item according to type.
     * 
     * @since 0.9.9
     * @param mixed $item     Item to sanitize.
     * @param string $type    Item type (i.e. string, float, int, etc.).
     * @param string $context The context for which the string is being sanitized.
     * @return string|null Sanitized string or null if item is empty.
     */
    public function item($item, $type = 'string', $context = 'save')
    {
        if(null === $item) {
            return null;
        }
        
        $raw_item = $item;
        
        $flags = null;
        
        switch ($type) {
            case 'url':
                $filter = FILTER_SANITIZE_URL;
                break;
            case 'int':
                $filter = FILTER_SANITIZE_NUMBER_INT;
                break;
            case 'float':
                $filter = FILTER_SANITIZE_NUMBER_FLOAT;
                $flags = FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND;
                break;
            case 'email':
                $item = substr($item, 0, 254);
                $filter = FILTER_SANITIZE_EMAIL;
                break;
            case 'string':
            default:
                $filter = FILTER_SANITIZE_STRING;
                $flags = FILTER_FLAG_NO_ENCODE_QUOTES;
                break;
        }
        
        if ('save' == $context) {
            $item = $this->removeAccents($item);
        }
            
        $output = filter_var($item, $filter, $flags);
        
        /**
         * Filters a sanitized item.
         *
         * @since 0.9.9
         *
         * @param string $output   Sanitized item.
         * @param string $raw_item The item prior to sanitization.
         * @param string $context  The context for which the string is being sanitized.
         */
        return $this->hook->{'applyFilter'}('sanitize_item', $output, $raw_item, $context);
    }

    /**
     * Validates a single item according to $type.
     *
     * @since 0.9.9
     * @param mixed $item  Item to validate.
     * @param string $type Item type (i.e. string, float, int, etc.).
     * @return bool True if valid, false otherwise.
     */
    public function validateItem($item, $type): bool
    {
        if (array_key_exists($type, $this->regexes)) {
            $returnval =  filter_var(
                $item,
                FILTER_VALIDATE_REGEXP,
                [
                    "options" => [
                        "regexp" => '!' . $this->regexes[$type] . '!i'
                    ]
                ]
            ) !== false;
            return($returnval);
        }
        
        $filter = false;
        
        switch ($type) {
            case 'email':
                $item = substr($item, 0, 254);
                $filter = FILTER_VALIDATE_EMAIL;
                break;
            case 'int':
                $filter = FILTER_VALIDATE_INT;
                break;
            case 'boolean':
                $filter = FILTER_VALIDATE_BOOLEAN;
                break;
            case 'ip':
                $filter = FILTER_VALIDATE_IP;
                break;
            case 'url':
                $filter = FILTER_VALIDATE_URL;
                break;
        }
        return ($filter === false) ? false : filter_var($item, $filter) !== false ? true : false;
    }

    /**
     * Sanitizes a string key.
     *
     * Keys are used as internal identifiers. Lowercase alphanumeric characters, dashes and underscores are allowed.
     *
     * Uses `sanitize_key` filter hook.
     *
     * @since 0.9.9
     * @param string $key String key
     * @return string Sanitized key
     */
    public function key(string $key)
    {
        $raw_key = $key;
        $key = strtolower($key);
        $key = preg_replace('/[^a-z0-9_\-]/', '', $key);

        /**
         * Filters a sanitized key string.
         *
         * @since 0.9.9
         * @param string $key     Sanitized key.
         * @param string $raw_key The key prior to sanitization.
         */
        return $this->hook->{'applyFilter'}('sanitize_key', $key, $raw_key);
    }

    /**
     * Sanitizes a username, stripping out unsafe characters.
     *
     * Removes tags, octets, entities, and if strict is enabled, will only keep
     * alphanumeric, _, space, ., -, @. After sanitizing, it passes the username,
     * raw username (the username in the parameter), and the value of $strict as
     * parameters for the `sanitize_user` filter.
     *
     * @since 0.9.9
     * @param string    $username The username to be sanitized.
     * @param bool      $strict If set, limits $username to specific characters. Default false.
     * @return string The sanitized username, after passing through filters.
     */
    public function username($username, $strict = false)
    {
        $raw_username = $username;
        $username = $this->removeAccents($username);
        // Trim spaces at the beginning and end
        $username = trim($username);
        // Replace remaining spaces with underscores
        $username = str_replace(' ','_',$username);
        // Kill octets
        $username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
        // Kill entities
        $username = preg_replace('/&.+?;/', '', $username);
        // If strict, reduce to ASCII for max portability.
        if ($strict) {
            $username = preg_replace('|[^a-z0-9 _.\-@]|i', '', $username);
        }
        /**
         * Filters a sanitized username string.
         *
         * @since 0.9
         * @param string $username     Sanitized username.
         * @param string $raw_username The username prior to sanitization.
         * @param bool   $strict       Whether to limit the sanitization to specific characters. Default false.
         */
        return $this->hook->{'applyFilter'}('sanitize_user', $username, $raw_username, $strict);
    }
    
    public function removeAccents(string $string, $encoding = 'UTF-8')
    {
        $string = strip_tags($string);
        $string = htmlentities($string, ENT_NOQUOTES, $encoding);
        // Replace HTML entities to get the first non-accented character
        // Example: "&ecute;" => "e", "&Ecute;" => "E", "Ã " => "a" ...
        $string = preg_replace('#&([A-za-z])(?:acute|grave|cedil|circ|orn|ring|slash|th|tilde|uml);#', '\1', $string);
        // Replace ligatures suce as: Œ, Æ ...
        $string = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $string);
        // Delete apostrophes
        $string = str_replace( "'", '',$string);
        // Delete other special characters.
        $string = preg_replace('#&[^;]+;#', '', $string);
        
        return $string;
    }
}
