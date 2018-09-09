<?php
use TriTan\Container as c;
use TriTan\Common\Hooks\ActionFilterHook as hook;

/**
 * TriTan CMS Text Domain.
 *
 * @license GPLv3
 *
 * @since       0.9
 * @package     TriTan CMS
 * @author      Joshua Parker <joshmac3@icloud.com>
 */

/**
 * Globally register the translator for localization.
 * 
 * @since 0.9.9
 */
$translator = new \Gettext\Translator();
c::getInstance()->set('translator', $translator);

/**
 * Retrieves a list of available locales.
 *
 * @file app/functions/domain-function.php
 *
 * @since 0.9
 * @param string $active
 */
function ttcms_dropdown_languages($active = '')
{
    if (is_ssl()) {
        $protocol = 'https://';
    } else {
        $protocol = 'http://';
    }

    $locales = (
        new \TriTan\Common\FileSystem(
            hook::getInstance()
        )
    )->{'getContents'}(esc_url($protocol . 'tritan-cms.s3.amazonaws.com/api/1.1/locale.json'));
    $json = json_decode($locales, true);
    foreach ($json as $locale) {
        echo '<option value="' . $locale['language'] . '"' . selected($active, $locale['language'], false) . '>' . $locale['native_name'] . '</option>';
    }
}

/**
 * Converts all accent characters to ASCII characters.
 *
 * If there are no accent characters, then the string given is just returned.
 *
 * @file app/functions/domain-function.php
 *
 * @since 0.9
 * @param string $string Text that might have accent characters
 * @return string Filtered string with replaced "nice" characters.
 */
function ttcms_remove_accents($string)
{
    return (
        new \TriTan\Common\Sanitizer(
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'removeAccents'}($string);
}

/**
 * Load a plugin's translated strings.
 *
 * If the path is not given then it will be the root of the plugin directory.
 *
 * @file app/functions/domain-function.php
 *
 * @since 0.9
 * @param string $domain          Unique identifier for retrieving translated strings
 * @param string $plugin_rel_path Optional. Relative path to TTCMS_PLUGIN_DIR where the locale directory resides.
 *                                Default false.
 * @return bool True when textdomain is successfully loaded, false otherwise.
 */
function load_plugin_textdomain($domain, $plugin_rel_path = false)
{
    return (
        new TriTan\Common\TextDomain(
            new TriTan\Common\Options\Options(
                new \TriTan\Common\Options\OptionsMapper(
                    new TriTan\Database(),
                    new \TriTan\Common\Context\HelperContext()
                )
            ),
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'loadPluginTextDomain'}($domain, $plugin_rel_path);
}

/**
 * Load default translated strings based on locale.
 *
 * @file app/functions/domain-function.php
 *
 * @since 0.9
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @param string $path Path to the .mo file.
 * @return bool True on success, false on failure.
 */
function load_default_textdomain($domain, $path)
{
    return (
        new TriTan\Common\TextDomain(
            new TriTan\Common\Options\Options(
                new \TriTan\Common\Options\OptionsMapper(
                    new TriTan\Database(),
                    new \TriTan\Common\Context\HelperContext()
                )
            ),
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'loadDefaultTextDomain'}($domain, $path);
}

/**
 * Load a .mo file into the text domain.
 *
 * @file app/functions/domain-function.php
 *
 * @since 0.9
 * @param string $domain Text domain. Unique identifier for retrieving translated strings.
 * @param string $path Path to the .mo file.
 * @return bool True on success, false on failure.
 */
function load_textdomain($domain, $path)
{
    return (
        new TriTan\Common\TextDomain(
            new TriTan\Common\Options\Options(
                new \TriTan\Common\Options\OptionsMapper(
                    new TriTan\Database(),
                    new \TriTan\Common\Context\HelperContext()
                )
            ),
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'loadTextDomain'}($domain, $path);
}

/**
 * Loads the current or default locale.
 *
 * @file app/functions/domain-function.php
 *
 * @since 0.9
 * @return string The locale.
 */
function load_core_locale()
{
    return (
        new TriTan\Common\TextDomain(
            new TriTan\Common\Options\Options(
                new \TriTan\Common\Options\OptionsMapper(
                    new TriTan\Database(),
                    new \TriTan\Common\Context\HelperContext()
                )
            ),
            \TriTan\Common\Hooks\ActionFilterHook::getInstance()
        )
    )->{'loadCoreLocale'}();
}