<?php
namespace TriTan\Common\Options;

use TriTan\Interfaces\Options\OptionsMapperInterface;
use TriTan\Interfaces\ContextInterface;
use TriTan\Interfaces\DatabaseInterface;
use TriTan\Container as c;
use Cascade\Cascade;

class OptionsMapper implements OptionsMapperInterface
{
    public $db;
    
    public $context;

    public function __construct(DatabaseInterface $db, ContextInterface $context)
    {
        $this->db = $db;
        $this->context = $context;
    }

    /**
     * Add an option to the table
     */
    public function create($name, $value = '')
    {
        // Make sure the option doesn't already exist
        if ($this->exists($name)) {
            return;
        }

        $_value = $this->context->obj['serializer']->{'serialize'}($value);

        $this->context->obj['hook']->{'doAction'}('add_option', $name, $_value);

        $add = $this->db->table(c::getInstance()->get('tbl_prefix') . 'option');
        $add->begin();
        try {
            $option_value = $_value;
            $add->insert([
                'option_key' => (string) $name,
                'option_value' => $this->db->{'ifNull'}($option_value)
            ]);
            $this->db->option[$name] = $value;
            $add->commit();
            return;
        } catch (Exception $e) {
            $add->rollback();
            Cascade::getLogger('error')->error(sprintf('OPTIONSMAPPER[%s]: Error: %s', $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * Read an option from options_meta.
     * Return value or $default if not found
     */
    public function read($option_key, $default = false)
    {
        $option_key = preg_replace('/\s/', '', $option_key);
        if (empty($option_key)) {
            return false;
        }

        /**
         * Filter the value of an existing option before it is retrieved.
         *
         * The dynamic portion of the hook name, `$option_key`, refers to the option_key name.
         *
         * Passing a truthy value to the filter will short-circuit retrieving
         * the option value, returning the passed value instead.
         *
         * @since 0.9.9
         * @param bool|mixed pre_option_{$option_key} Value to return instead of the option value.
         *                                            Default false to skip it.
         * @param string $option_key Meta key name.
         */
        $pre = $this->context->obj['hook']->{'applyFilter'}('pre_option_' . $option_key, false);

        if (false !== $pre) {
            return $pre;
        }

        if (!isset($this->db->option[$option_key])) {
            $result = $this->db->table(c::getInstance()->get('tbl_prefix') . 'option')
                    ->where('option_key', '=', $option_key)
                    ->first();
            
            if (!$result) {
                return false;
            }

            if (!empty(array_filter($result))) {
                $value = $this->context->obj['html']->{'purify'}($result['option_value']);
            } else {
                $value = $this->context->obj['html']->{'purify'}($default);
            }
            $this->db->option[$option_key] = $this->context->obj['serializer']->{'unserialize'}($value);
        }
        /**
         * Filter the value of an existing option.
         *
         * The dynamic portion of the hook name, `$option_key`, refers to the option name.
         *
         * @since 0.9.9
         * @param mixed $value Value of the option. If stored serialized, it will be
         *                     unserialized prior to being returned.
         * @param string $option_key Option name.
         */
        return $this->context->obj['hook']->{'applyFilter'}('get_option_' . $option_key, $this->db->option[$option_key]);
    }

    /**
     * Update (add if doesn't exist) an option to options_meta
     */
    public function update($option_key, $newvalue)
    {
        $oldvalue = $this->read($option_key);

        // If the new and old values are the same, no need to update.
        if ($newvalue === $oldvalue) {
            return false;
        }

        if (!$this->exists($option_key)) {
            $this->create($option_key, $newvalue);
            return true;
        }

        $_newvalue = $this->context->obj['serializer']->{'serialize'}($newvalue);

        $this->context->obj['hook']->{'doAction'}('update_option', $option_key, $oldvalue, $newvalue);

        $key = $this->db->table(c::getInstance()->get('tbl_prefix') . 'option');
        $key->begin();
        $option_value = $_newvalue;
        try {
            $key->where('option_key', $option_key)->update([
                'option_value' => $this->db->{'ifNull'}($option_value)
            ]);

            if (@count($key) > 0) {
                $this->db->option[$option_key] = $newvalue;
            }
            $key->commit();
        } catch (Exception $e) {
            $key->rollback();
            Cascade::getLogger('error')->error(sprintf('OPTIONSMAPPER[%s]: Error: %s', $e->getCode(), $e->getMessage()));
        }
    }

    /**
     * Delete an option from the table
     */
    public function delete($name)
    {
        $delete = $this->db->table(c::getInstance()->get('tbl_prefix') . 'option');
        $delete->begin();
        try {
            $key = $this->db->table(c::getInstance()->get('tbl_prefix') . 'option')->where('option_key', $name);
            $results = $key->first();

            if (is_null($results) || !$results) {
                return false;
            }

            $this->context->obj['hook']->{'doAction'}('delete_option', $name);

            $delete->where('option_key', $name)
                    ->delete();
            $delete->commit();
            return true;
        } catch (Exception $e) {
            $delete->rollback();
            Cascade::getLogger('error')->error(sprintf('OPTIONSMAPPER[%s]: Error: %s', $e->getCode(), $e->getMessage()));
        }
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
        $key = $this->db->table(c::getInstance()->get('tbl_prefix') . 'option')
            ->where('option_key', '=', $option_key)
            ->first();
        
        return (int) $key['option_id'] > 0;
    }
}
