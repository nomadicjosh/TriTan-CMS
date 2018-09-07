<?php
namespace TriTan\Common;

use TriTan\Interfaces\HtmlPurifierInterface;

class HtmlPurifier implements HtmlPurifierInterface
{
    protected $never_allowed_str = [
        'document.cookie' => '[removed]',
        'document.write' => '[removed]',
        '.parentNode' => '[removed]',
        '.innerHTML' => '[removed]',
        'window.location' => '[removed]',
        '-moz-binding' => '[removed]',
        '<!--' => '&lt;!--',
        '-->' => '--&gt;',
        '<![CDATA[' => '&lt;![CDATA[',
        '<comment>' => '&lt;comment&gt;'
    ];

    /**
     * List of never allowed regex replacement
     *
     * @var array
     * @access protected
     */
    protected $never_allowed_regex = [
        'javascript\s*:',
        'expression\s*(\(|&\#40;)', // CSS and IE
        'vbscript\s*:', // IE, surprise!
        'Redirect\s+302',
        "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
    ];

    /**
     * Remove bad attributes such as style, onclick and xmlns
     *
     * @var array
     * @access public
     */
    public $xss_disalowed_attibutes = ['on\w*', /*'style',*/ 'xmlns', 'formaction'];

    /**
     * If a tag containing any of the words in the list below is found,
     * the tag gets converted to entities.
     *
     * @var string
     * @access public
     */
    public $xss_naughty_html = 'alert|applet|audio|basefont|base|behavior|bgsound|'
            . 'blink|body|embed|expression|form|frameset|frame|head|html|ilayer|'
            . 'input|isindex|layer|link|meta|object|plaintext|script|textarea|title|'
            . 'video|xml|xss';

    /**
     * Smilar to $this->xss_naughty_html, but instead of looking for tags it
     * looks for PHP and JavaScript commands that are disallowed.  Rather than
     * removing the code, it simply converts the parenthesis to entities
     * rendering the code un-executable.
     *
     * @var type
     * @access public
     */
    public $xss_naughty_scripts = 'alert|cmd|passthru|eval|exec|expression|system|'
            . 'fopen|fsockopen|file|file_get_contents|readfile|unlink';

    /**
     * Your mb_string encoding, default is 'utf-8'. Do not change, if not sure.
     *
     * @var string
     * @access public
     */
    public $mbencoding = 'utf-8';

    public function __construct()
    {
    }

    /**
     * Escaping for rich text.
     * 
     * This method should only be used on output. With the exception of uploading
     * images, never use this method on input. All inputted data should be
     * accepted and then purified on output for optimal results. For output of images,
     * make sure to escape with esc_url().
     *
     * @since 0.9.9
     * @param string $string
     * @param bool   $is_image   Description
     * @return string Escaped rich text.
     */
    public function purify($string, $is_image = false)
    {
        /*
         * Is the string an array?
         *
         */
        if (is_array($string)) {
            while (list($key) = each($string)) {
                $string[$key] = $this->purify($string[$key]);
            }

            return $string;
        }

        /*
         * Remove Invisible Characters
         */
        $string = $this->removeInvisibleCharacters($string);

        // Validate Entities in URLs
        $string = $this->validateEntities($string);

        /*
         * URL Decode
         *
         * Just in case stuff like this is submitted:
         *
         * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
         *
         * Note: Use rawurldecode() so it does not remove plus signs
         *
         */
        if (stripos($string, '%') !== false) {
            do {
                $oldstr = $string;
                $string = rawurldecode($string);
                $string = preg_replace_callback('#%(?:\s*[0-9a-f]){2,}#i', [$this, 'urlDecodeSpaces'], $string);
            } while ($oldstr !== $string);
            unset($oldstr);
        }

        /*
         * Convert character entities to ASCII
         *
         * This permits our tests below to work reliably.
         * We only convert entities that are within tags since
         * these are the ones that will pose security problems.
         *
         */

        $string = preg_replace_callback("/[a-z]+=([\'\"]).*?\\1/si", [$this, 'convertAttribute'], $string);

        $string = preg_replace_callback("/<\w+.*?(?=>|<|$)/si", [$this, 'decodeEntity'], $string);

        /*
         * Remove Invisible Characters Again!
         */
        $string = $this->removeInvisibleCharacters($string);

        /*
         * Convert all tabs to spaces
         *
         * This prevents strings like this: j a v a s c r i p t
         * NOTE: we deal with spaces between characters later.
         * NOTE: preg_replace was found to be amazingly slow here on
         * large blocks of data, so we use str_replace.
         */

        if (strpos($string, "\t") !== false) {
            $string = str_replace("\t", ' ', $string);
        }

        /*
         * Capture converted string for later comparison
         */
        $converted_string = $string;

        // Remove Strings that are never allowed
        $string = $this->neverAllowed($string);

        /*
         * Makes PHP tags safe
         *
         * Note: XML tags are inadvertently replaced too:
         *
         * <?xml
         *
         * But it doesn't seem to pose a problem.
         */
        if ($is_image === true) {
            // Images have a tendency to have the PHP short opening and
            // closing tags every so often so we skip those and only
            // do the long opening tags.
            $string = preg_replace('/<\?(php)/i', "&lt;?\\1", $string);
        } else {
            $string = str_replace(['<?', '?' . '>'], ['&lt;?', '?&gt;'], $string);
        }

        /*
         * Compact any exploded words
         *
         * This corrects words like:  j a v a s c r i p t
         * These words are compacted back to their correct state.
         */
        $words = [
            'javascript', 'expression', 'vbscript', 'jscript', 'wscript',
            'vbs', 'script', 'base64', 'applet', 'alert', 'document',
            'write', 'cookie', 'window', 'confirm', 'prompt', 'eval'
        ];

        foreach ($words as $word) {
            $temp = '';

            for ($i = 0, $wordlen = strlen($word); $i < $wordlen; $i++) {
                $temp .= substr($word, $i, 1) . "\s*";
            }

            // We only want to do this when it is followed by a non-word character
            // That way valid stuff like "dealer to" does not become "dealerto"
            $string = preg_replace_callback(
                '#(' . substr($temp, 0, -3) . ')(\W)#is',
                [$this, 'compactExplodedWords'],
                $string
            );
        }

        /*
         * Remove disallowed Javascript in links or img tags
         * We used to do some version comparisons and use of stripos for PHP5,
         * but it is dog slow compared to these simplified non-capturing
         * preg_match(), especially if the pattern exists in the string
         */
        do {
            $original = $string;

            if (preg_match("/<a/i", $string)) {
                $string = preg_replace_callback("#<a\s+([^>]*?)(>|$)#si", [$this, 'jsLinkRemoval'], $string);
            }

            if (preg_match("/<img/i", $string)) {
                $string = preg_replace_callback("#<img\s+([^>]*?)(\s?/?>|$)#si", [$this, 'jsImgRemoval'], $string);
            }

            if (preg_match("/script/i", $string) or preg_match("/xss/i", $string)) {
                $string = preg_replace("#<(/*)(script|xss)(.*?)\>#si", '[removed]', $string);
            }
        } while ($original != $string);

        unset($original);

        // Remove evil attributes such as style, onclick and xmlns
        $string = $this->removeEvilAttributes($string, $is_image);

        /*
         * Sanitize naughty HTML elements
         *
         * If a tag containing any of the words in the list
         * below is found, the tag gets converted to entities.
         *
         * So this: <blink>
         * Becomes: &lt;blink&gt;
         */
        $string = preg_replace_callback(
            '#<(/*\s*)(' . $this->xss_naughty_html . ')([^><]*)([><]*)#is',
            [$this, 'sanitizeNaughtyHtml'],
            $string
        );

        /*
         * Sanitize naughty scripting elements
         *
         * Similar to above, only instead of looking for
         * tags it looks for PHP and JavaScript commands
         * that are disallowed.  Rather than removing the
         * code, it simply converts the parenthesis to entities
         * rendering the code un-executable.
         *
         * For example: eval('some code')
         * Becomes: eval&#40;'some code'&#41;
         */
        $string = preg_replace(
            '#(' . $this->xss_naughty_scripts . ')(\s*)\((.*?)\)#si',
            "\\1\\2&#40;\\3&#41;",
            $string
        );


        // Final clean up
        // This adds a bit of extra precaution in case
        // something got through the above filters
        $string = $this->neverAllowed($string);

        /*
         * Images are Handled in a Special Way
         * - Essentially, we want to know that after all of the character
         * conversion is done whether any unwanted, likely XSS, code was found.
         * If not, we return true, as the image is clean.
         * However, if the string post-conversion does not matched the
         * string post-removal of XSS, then it fails, as there was unwanted XSS
         * code found and removed/changed during processing.
         */
        if ($is_image === true) {
            return ($string === $converted_string);
        }

        return $string;
    }

    /**
     * HTML Entities Decode
     *
     * This function is a replacement for html_entity_decode()
     *
     * The reason we are not using html_entity_decode() by itself is because
     * while it is not technically correct to leave out the semicolon
     * at the end of an entity most browsers will still interpret the entity
     * correctly.  html_entity_decode() does not convert entities without
     * semicolons, so we are left with our own little solution here. Bummer.
     *
     * @param string
     * @param string
     * @return string
     */
    public function entityDecode($string, $charset = 'UTF-8')
    {
        if (stristr($string, '&') === false) {
            return $string;
        }

        $string = html_entity_decode($string, ENT_COMPAT, $charset);

        $string = preg_replace_callback('~&#x(0*[0-9a-f]{2,5})~i', function ($matches) {
            foreach ($matches as $match) {
                return chr(hexdec($match));
            }
        }, $string);

        return preg_replace_callback('~&#([0-9]{2,4})~', function ($matches) {
            foreach ($matches as $match) {
                return chr($match);
            }
        }, $string);
    }

    /**
     * URL-decode taking spaces into account
     *
     * @param array $matches
     * @return string
     */
    protected function urlDecodeSpaces($matches)
    {
          $input    = $matches[0];
          $nospaces = preg_replace('#\s+#', '', $input);
          return ($nospaces === $input)
              ? $input
              : rawurldecode($nospaces);
    }

    /**
     * Compact Exploded Words
     *
     * Callback function for $this->purify() to remove whitespace from
     * things like j a v a s c r i p t
     *
     * @param type
     * @return type
     */
    protected function compactExplodedWords($matches)
    {
        return preg_replace('/\s+/s', '', $matches[1]) . $matches[2];
    }

    /*
     * Remove Evil HTML Attributes (like evenhandlers and style)
     *
     * It removes the evil attribute and either:
     * - Everything up until a space
     *    For example, everything between the pipes:
     *    <a |style=document.write('hello');alert('world');| class=link>
     *  - Everything inside the quotes
     *    For example, everything between the pipes:
     *    <a |style="document.write('hello'); alert('world');"| class="link">
     *
     * @param string $string The string to check
     * @param boolean $is_image true if this is an image
     * @return string The string with the evil attributes removed
     */

    protected function removeEvilAttributes($string, $is_image)
    {
        // All javascript event handlers (e.g. onload, onclick, onmouseover), style, and xmlns
        //$evil_attributes = array('on\w*', 'style', 'xmlns', 'formaction');
        $evil_attributes = $this->xss_disalowed_attibutes;

        if ($is_image === true) {
            /*
             * Adobe Photoshop puts XML metadata into JFIF images,
             * including namespacing, so we have to allow this for images.
             */
            unset($evil_attributes[array_search('xmlns', $evil_attributes)]);
        }

        do {
            $count = 0;
            $attribs = [];

            // find occurrences of illegal attribute strings with quotes (042 and 047 are octal quotes)
            preg_match_all(
                '/(' . implode('|', $evil_attributes) . ')\s*=\s*(\042|\047)([^\\2]*?)(\\2)/is',
                $string,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $attr) {
                $attribs[] = preg_quote($attr[0], '/');
            }

            // find occurrences of illegal attribute strings without quotes
            preg_match_all(
                '/(' . implode('|', $evil_attributes) . ')\s*=\s*([^\s>]*)/is',
                $string,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $attr) {
                $attribs[] = preg_quote($attr[0], '/');
            }

            // replace illegal attribute strings that are inside an html tag
            if (count($attribs) > 0) {
                $string = preg_replace(
                    '/(<?)(\/?[^><]+?)([^A-Za-z<>\-])(.*?)(' . implode('|', $attribs) . ')(.*?)([\s><]?)([><]*)/i',
                    '$1$2 $4$6$7$8',
                    $string,
                    -1,
                    $count
                );
            }
        } while ($count);

        return $string;
    }

    /**
     * Sanitize Naughty HTML
     *
     * Callback function for $this->purify() to remove naughty HTML elements
     *
     * @param array
     * @return string
     */
    protected function sanitizeNaughtyHtml($matches)
    {
        // encode opening brace
        $string = '&lt;' . $matches[1] . $matches[2] . $matches[3];

        // encode captured opening or closing brace to prevent recursive vectors
        $string .= str_replace(['>', '<'], ['&gt;', '&lt;'], $matches[4]);

        return $string;
    }

    /**
     * JS Link Removal
     *
     * Callback function for $this->purify() to sanitize links
     * This limits the PCRE backtracks, making it more performance friendly
     * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
     * PHP 5.2+ on link-heavy strings
     *
     * @param array
     * @return string
     */
    protected function jsLinkRemoval($match)
    {
        return str_replace(
            $match[1],
            preg_replace(
                '#href=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|data\s*:)#si',
                '',
                $this->filterAttributes(str_replace(['<', '>'], '', $match[1]))
            ),
            $match[0]
        );
    }

    /**
     * JS Image Removal
     *
     * Callback function for $this->purify() to sanitize image tags
     * This limits the PCRE backtracks, making it more performance friendly
     * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
     * PHP 5.2+ on image tag heavy strings
     *
     * @param array
     * @return string
     */
    protected function jsImgRemoval($match)
    {
        return str_replace(
            $match[1],
            preg_replace(
                '#src=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si',
                '',
                $this->filterAttributes(str_replace(['<', '>'], '', $match[1]))
            ),
            $match[0]
        );
    }

    /**
     * Attribute Conversion
     *
     * Used as a callback for Purify
     *
     * @param array
     * @return string
     */
    protected function convertAttribute($match)
    {
        return str_replace(['>', '<', '\\'], ['&gt;', '&lt;', '\\\\'], $match[0]);
    }

    /**
     * Filter Attributes
     *
     * Filters tag attributes for consistency and safety
     *
     * @param string
     * @return string
     */
    protected function filterAttributes($string)
    {
        $out = '';

        if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $string, $matches)) {
            foreach ($matches[0] as $match) {
                $out .= preg_replace("#/\*.*?\*/#s", '', $match);
            }
        }

        return $out;
    }

    /**
     * HTML Entity Decode Callback
     *
     * Used as a callback for Purify
     *
     * @param array
     * @return string
     */
    protected function decodeEntity($match)
    {
        return $this->entityDecode($match[0], strtoupper($this->mbencoding));
    }

    /**
     * Validate URL entities
     *
     * Called by $this->purify()
     *
     * @param string
     * @return string
     */
    protected function validateEntities($string)
    {
        /*
         * Validate standard character entities
         *
         * Add a semicolon if missing.  We do this to enable
         * the conversion of entities to ASCII later.
         *
         */
        $string = preg_replace('#(&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i', "\\1;\\2", $string);

        /*
         * Validate UTF16 two byte encoding (x00)
         *
         * Just as above, adds a semicolon if missing.
         *
         */
        $string = preg_replace('#(&\#x?)([0-9A-F]+);?#i', "\\1\\2;", $string);

        return $string;
    }

    /**
     * Never Allowed
     *
     * A utility function for $this->purify()
     *
     * @param string
     * @return string
     */
    protected function neverAllowed($string)
    {
        $string = str_replace(array_keys($this->never_allowed_str), $this->never_allowed_str, $string);

        foreach ($this->never_allowed_regex as $regex) {
            $string = preg_replace('#' . $regex . '#is', '[removed]', $string);
        }

        return $string;
    }

    protected function removeInvisibleCharacters($string, $url_encoded = true)
    {
        $non_displayables = [];

        // every control character except newline (dec 10)
        // carriage return (dec 13), and horizontal tab (dec 09)

        if ($url_encoded) {
            $non_displayables[] = '/%0[0-8bcef]/'; // url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/'; // url encoded 16-31
        }

        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'; // 00-08, 11, 12, 14-31, 127

        do {
            $string = preg_replace($non_displayables, '', $string, -1, $count);
        } while ($count);

        return $string;
    }
}
