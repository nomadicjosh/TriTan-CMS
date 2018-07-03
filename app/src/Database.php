<?php
namespace TriTan;

/**
 * User API: Database Class
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class Database
{

    /**
     * Application object.
     *
     * @var object
     */
    public $app;

    /**
     *
     * @var array
     */
    public $options;

    /**
     * Constructor.
     */
    public function __construct(array $options = [], \Liten\Liten $liten = null)
    {
        $this->app = !empty($liten) ? $liten : \Liten\Liten::getInstance();

        $this->options = $options;
    }

    /**
     * Database table.
     *
     * @param string $name
     *            Database table name.
     * @param array $options
     * @return \TriTan\Laci\Collection Database object, false otherwise.
     */
    public function table($name)
    {
        return new Laci\Collection(TTCMS_NODEQ_PATH . $name . '.json', $this->options);
    }
}
