<?php
namespace TriTan\Exception;

/**
 * TriTan CMS Not Found Exception Class
 *
 * This extends the default `LitenException` class to allow converting
 * file not found exceptions to and from `Error` objects.
 *
 * Unfortunately, because an `Error` object may contain multiple messages and error
 * codes, only the first message for the first error code in the instance will be
 * accessible through the exception's methods.
 *
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */
class NotFoundException extends BaseException
{
    public function __construct($message = 'Data requested cannot be found in the data source.', $code = 404, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
