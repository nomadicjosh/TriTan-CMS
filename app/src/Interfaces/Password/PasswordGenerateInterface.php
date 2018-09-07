<?php
namespace TriTan\Interfaces\Password;

interface PasswordGenerateInterface
{
    /**
     * Generates a random password drawn from the defined set of characters.
     *
     * Uses `random_lib` library to create passwords with far less predictability.
     *
     * @since 0.9.9
     * @param int  $length              Optional. The length of password to generate. Default 12.
     * @param bool $special_chars       Optional. Whether to include standard special characters.
     *                                  Default true.
     * @param bool $extra_special_chars Optional. Whether to include other special characters.
     *                                  Default false.
     * @return string The system generated password.
     */
    public function generate(int $length = 12, bool $special_chars = true, bool $extra_special_chars = false);
}