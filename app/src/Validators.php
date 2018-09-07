<?php
namespace TriTan;

use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator;
use TriTan\Container as c;
use Cascade\Cascade;

/**
 * Validators
 *
 * @license GPLv3
 *
 * @since 0.9
 * @package TriTan CMS
 * @author Joshua Parker <joshmac3@icloud.com>
 */
class Validators
{

    /**
     * Validates username.
     *
     * @since 0.9
     * @param string $username Whether given username is valid.
     * @return bool|Exception   Returns true if username is valid, false and exception
     *                          if username is not valid.
     */
    public static function validateUsername($username)
    {
        $usernameValidator = Validator::alnum('-')->length(3, 60)->noWhitespace();
        try {
            $usernameValidator->check($username);
            return true;
        } catch (ValidationException $ex) {
            Cascade::getLogger('error')->error(sprintf('VALIDATOR[%s]: %s', $ex->getCode(), $ex->getMessage()));
            c::getInstance()->get('flash')->{'error'}($ex->getMessage());
            return false;
        }
    }

    /**
     * Validates email.
     *
     * @since 0.9
     * @param string $email Whether given email is valid.
     * @return bool|Exception   Returns true if email is valid, false and exception
     *                          if email is not valid.
     */
    public static function validateEmail($email)
    {
        return Validator::filterVar(FILTER_VALIDATE_EMAIL)->validate($email);
    }
}
