<?php namespace TriTan\Exception;

if (!defined('BASE_PATH'))
    exit('No direct script access allowed');

/**
 * TriTan CMS UnauthorizedException Class
 * 
 * This extends the default `LitenException` class to allow converting
 * file not found exceptions to and from `Error` objects.
 * 
 * Unfortunately, because an `Error` object may contain multiple messages and error
 * codes, only the first message for the first error code in the instance will be
 * accessible through the exception's methods.
 *  
 * @since       1.0.0
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
class UnauthorizedException extends BaseException
{

    public function __construct($message = 'Unauthorized: The request requires user authentication.', $code = 401, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
