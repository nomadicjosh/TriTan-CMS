<?php namespace TriTan\Exception;

/**
 * TriTan CMS Exception Class
 * 
 * This extends the framework `LitenException` class to allow converting
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
class BaseException extends \Liten\Exception\LitenException {

	/**
	 * TriTan CMS handles string error codes.
	 * @var string
	 */
	protected $code;

	/**
	 * Error instance.
	 * @var TriTan\Error
	 */
	protected $ttcms_error;

	/**
	 * TriTan CMS exception constructor.
	 *
	 * The class constructor accepts either the framework `\Liten\Exception\LitenException` creation
	 * parameters or an `TriTan\Error` instance in place of the previous exception.
	 *
	 * If an `TriTan\Error` instance is given in this way, the `$message` and `$code`
	 * parameters are ignored in favour of the message and code provided by the
	 * `TriTan\Error` instance.
	 *
	 * Depending on whether an `TriTan\Error` instance was received, the instance is kept
	 * or a new one is created from the provided parameters.
	 *
	 * @param string               $message  Exception message (optional, defaults to empty).
	 * @param string               $code     Exception code (optional, defaults to empty).
	 * @param `\Liten\Exception\LitenException` | `TriTan\Error` $previous Previous exception or error (optional).
	 *
	 * @uses TriTan\Error
	 * @uses TriTan\Error::getErrorCode()
	 * @uses TriTan\Error::getErrorMessage()
	 */
	public function __construct( $message = '', $code = '', $previous = null ) {
		$exception = $previous;
		$ttcms_error  = null;

		if ( $previous instanceof \TriTan\Error ) {
			$code      = $previous->getErrorCode();
			$message   = $previous->getErrorMessage( $code );
			$ttcms_error  = $previous;
			$exception = null;
		}

		parent::__construct( $message, null, $exception );

		$this->code     = $code;
		$this->ttcms_error = $ttcms_error;
	}

	/**
	 * Obtain the exception's `TriTan\Error` object.
	 * 
     * @since 6.1.14
	 * @return `Error` TriTan CMS error.
	 */
	public function get_ttcms_error() {
		return $this->ttcms_error ? $this->ttcms_error : new \TriTan\Error( $this->code, $this->message, $this );
	}

}
