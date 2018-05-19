<?php namespace TriTan\Exception;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

use TriTan\Exception\Exception;

class DirectoryNotFoundException extends Exception
{
}
