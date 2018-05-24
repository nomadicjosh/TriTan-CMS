<?php namespace TriTan\Exception;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * Tritan CMS Exception Class
 * 
 * This extends the default `LitenException` class to allow converting
 * exceptions to and from `Error` objects.
 * 
 * Unfortunately, because an `Error` object may contain multiple messages and error
 * codes, only the first message for the first error code in the instance will be
 * accessible through the exception's methods.
 *  
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
class Exception extends \TriTan\Exception\BaseException
{
    
}
