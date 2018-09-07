<?php
namespace TriTan\Common;

use TriTan\Error;
use TriTan\Interfaces\FileSystemInterface;
use TriTan\Exception\NotFoundException;
use TriTan\Exception\IOException;
use TriTan\Exception\Exception;
use TriTan\Interfaces\Hooks\ActionFilterHookInterface;

class FileSystem implements FileSystemInterface
{
    public $trigger;
    public $hook;

    public function __construct(ActionFilterHookInterface $hook)
    {
        $this->hook = $hook;
    }
    /**
     * Custom make directory function.
     *
     * This function will check if the path is an existing directory,
     * if not, then it will be created with set permissions and also created
     * recursively if needed.
     *
     * @since 0.9.9
     * @param string $path Path to be created.
     * @return bool
     * @throws IOException If session.savepath is not set, path is not writable, or
     *                     lacks permission to mkdir.
     */
    public function mkdir(string $path)
    {
        if ('' == trim($path)) {
            throw new Exception('Invalid directory path: Empty path given.');
        }

        if (session_save_path() == "") {
            throw new IOException(
                sprintf(
                    'Session savepath is not set correctly. It is currently set to: %s',
                    session_save_path()
                )
            );
        }

        if (!is_writable(session_save_path())) {
            throw new IOException(
                sprintf(
                    '"%s" is not writable or TriTan CMS does not have permission to create and write directories and files in this location.',
                    session_save_path()
                )
            );
        }

        if (!is_dir($path)) {
            if (!@mkdir($path, 0755, true)) {
                throw new IOException(
                    sprintf(
                        'The following directory could not be created: %s',
                        $path
                    )
                );
            }
        }
    }

    /**
     * Removes directory recursively along with any files.
     *
     * @since 0.9.9
     * @param string $dir Directory that should be removed.
     */
    public function rmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DS . $object)) {
                        $this->rmdir($dir . DS . $object);
                    } else {
                        unlink($dir . DS . $object);
                    }
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Custom function to use curl, fopen, or use file_get_contents
     * if curl is not available.
     *
     * Uses `trigger_include_path_search`, `resource_context` and `stream_context_create_options`
     * filters.
     *
     * @since 0.9.9
     * @param string $filename Resource to read.
     * @param bool $use_include_path Whether or not to use include path.
     * @param bool $context Whether or not to use a context resource.
     */
    public function getContents(string $filename, bool $use_include_path = false, bool $context = true)
    {
        $opts = [
          'http' => [
              'timeout' => 360.0
          ]
        ];

        /**
         * Filters the stream context create options.
         *
         * @since 0.9.9
         * @param array $opts Array of options.
         * @return mixed
         */
        $opts = $this->hook->{'applyFilter'}('stream_context_create_options', $opts);

        if ($context === true) {
            $context = stream_context_create($opts);
        } else {
            $context = null;
        }

        $result = file_get_contents($filename, $use_include_path, $context);

        if ($result) {
            return $result;
        } else {
            $handle = fopen($filename, "r", $use_include_path, $context);
            $contents = stream_get_contents($handle);
            fclose($handle);
            if ($contents) {
                return $contents;
            } elseif (!function_exists('curl_init')) {
                return false;
            } else {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $filename);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 360);
                $output = curl_exec($ch);
                curl_close($ch);
                if ($output) {
                    return $output;
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * Subdomain as directory function uses the subdomain
     * of the install as a directory.
     *
     * @since 0.9.9
     * @return string
     */
    public function subdomainAsDirectory()
    {
        $subdomain = '';
        $domain_parts = explode('.', $_SERVER['SERVER_NAME']);
        if (count($domain_parts) == 3) {
            $subdomain = $domain_parts[0];
        } else {
            $subdomain = 'www';
        }
        return $subdomain;
    }

    /**
     * Returns an array of function names in a file.
     *
     * @file app/functions/core-function.php
     *
     * @since 0.9
     * @param string $filename
     *            The path to the file.
     * @param bool $sort
     *            If true, sort results by function name.
     */
    public function getFunctions(string $filename, bool $sort = false)
    {
        $file = file($filename);
        $functions = [];
        foreach ($file as $line) {
            $line = trim($line);
            if (substr($line, 0, 8) == 'function') {
                $functions[] = strtolower(substr($line, 9, strpos($line, '(') - 9));
            }
        }
        if ($sort) {
            asort($functions);
            $functions = array_values($functions);
        }
        return $functions;
    }

    /**
     * Checks a given file for any duplicated named user functions.
     *
     * @since 0.9.9
     * @param string $filename
     */
    public function isDuplicateFunction(string $filename)
    {
        if (!$this->exists($filename, false)) {
            return new Error('duplicate_function_error', sprintf('Invalid file name: %s.', $filename));
        }

        $plugin = $this->getFunctions($filename);
        $functions = get_defined_functions();
        $merge = array_merge($plugin, $functions['user']);
        if (count($merge) !== count(array_unique($merge))) {
            $dupe = array_unique(array_diff_assoc($merge, array_unique($merge)));
            foreach ($dupe as $value) {
                return new Error(
                    'duplicate_function_error',
                    sprintf(
                        'The following function is already defined elsewhere: <strong>%s</strong>',
                        $value
                    )
                );
            }
        }
        return false;
    }

    /**
     * Performs a check within a php script and returns any other files
     * that might have been required or included.
     *
     * @since 0.9.9
     * @param string $filename PHP script to check.
     */
    public function checkIncludes(string $filename)
    {
        if (!$this->exists($filename, false)) {
            return sprintf('Invalid file name: %s.', $filename);
        }

        // NOTE that any file coming into this function has already passed the syntax check, so
        // we can assume things like proper line terminations
        $includes = [];
        // Get the directory name of the file so we can prepend it to relative paths
        $dir = dirname($filename);

        // Split the contents of $fileName about requires and includes
        // We need to slice off the first element since that is the text up to the first include/require
        $requireSplit = array_slice(preg_split('/require|include/i', $this->getContents($filename)), 1);

        // For each match
        foreach ($requireSplit as $string) {
            // Substring up to the end of the first line, i.e. the line that the require is on
            $string = substr($string, 0, strpos($string, ";"));

            // If the line contains a reference to a variable, then we cannot analyse it
            // so skip this iteration
            if (strpos($string, "$") !== false) {
                continue;
            }

            // Split the string about single and double quotes
            $quoteSplit = preg_split('/[\'"]/', $string);

            // The value of the include is the second element of the array
            // Putting this in an if statement enforces the presence of '' or "" somewhere in the include
            // includes with any kind of run-time variable in have been excluded earlier
            // this just leaves includes with constants in, which we can't do much about
            if ($include = $quoteSplit[1]) {
                // If the path is not absolute, add the dir and separator
                // Then call realpath to chop out extra separators
                if (strpos($include, ':') === false) {
                    $include = realpath($dir . DS . $include);
                }

                array_push($includes, $include);
            }
        }

        return $includes;
    }

    /**
     * Performs a syntax and error check of a given PHP script.
     *
     * @since 0.9.9
     * @param string $filename PHP script/file to check.
     * @param bool $check_includes If set to true, will check if other files have been included.
     * @return void|\TriTan\Exception\Exception
     * @throws NotFoundException If file does not exist or is not readable.
     * @throws Exception If file contains duplicate function names.
     */
    public function checkSyntax(string $filename, bool $check_includes = true)
    {
        // If file does not exist or it is not readable, throw an exception
        if (!is_file($filename) || !is_readable($filename)) {
            throw new NotFoundException(sprintf('"%s" is not found or is not a regular file.', $filename), 404);
        }

        $dupe_function = $this->isDuplicateFunction($filename);

        if ($dupe_function instanceof Error) {
            throw new Exception($dupe_function->getErrorMessage(), 'php_check_syntax');
        }

        // Sort out the formatting of the filename
        $file_name = realpath($filename);

        // Get the shell output from the syntax check command
        $output = shell_exec('php -l "' . $file_name . '"');

        // Try to find the parse error text and chop it off
        $syntaxError = preg_replace("/Errors parsing.*$/", "", $output, - 1, $count);

        // If the error text above was matched, throw an exception containing the syntax error
        if ($count > 0) {
            return new Exception(trim($syntaxError), 'php_check_syntax');
        }

        // If we are going to check the files includes
        if ($check_includes) {
            foreach ($this->checkIncludes($file_name) as $include) {
                // Check the syntax for each include
                if (is_file($include)) {
                    $this->checkSyntax($include);
                }
            }
        }
    }

    /**
     * Single file writable atribute check.
     * Thanks to legolas558.users.sf.net
     *
     * @file app/functions/core-function.php
     *
     * @since 0.9
     * @param string $path
     * @return bool
     */
    public function winIsWritable(string $path)
    {
        // will work in despite of Windows ACLs bug
        // NOTE: use a trailing slash for folders!!!
        // see http://bugs.php.net/bug.php?id=27609
        // see http://bugs.php.net/bug.php?id=30931

        if ($path{strlen($path) - 1} == '/') { // recursively return a temporary file path
            return $this->winIsWritable($path . uniqid(mt_rand()) . '.tmp');
        } elseif (is_dir($path)) {
            return $this->winIsWritable($path . DS . uniqid(mt_rand()) . '.tmp');
        }
        // check tmp file for read/write capabilities
        $rm = $this->fileExists($path, false);
        $f = fopen($path, 'a');
        if ($f === false) {
            return false;
        }
        fclose($f);
        if (!$rm) {
            unlink($path);
        }
        return true;
    }

    /**
     * Alternative to PHP's native is_writable function due to a Window's bug.
     *
     * @since 0.9.9
     * @param string $path Path to check.
     */
    public function isWritable(string $path)
    {
        if ('WIN' === strtoupper(substr(PHP_OS, 0, 3))) {
            return $this->winIsWritable($path);
        } else {
            return is_writable($path);
        }
    }

    /**
     * Checks whether a file or directory exists.
     *
     * @since 0.9.9
     * @param string $filename  Path to the file or directory.
     * @param bool $throw       Determines whether to do a simple check or throw an exception.
     *                          Default: true.
     * @return boolean <b>true</b> if the file or directory specified by
     * <i>$filename</i> exists; <b>false</b> otherwise.
     * @throws NotFoundException If file does not exist.
     */
    public function exists(string $filename, bool $throw = true)
    {
        if (!file_exists($filename)) {
            if ($throw == true) {
                throw new NotFoundException(sprintf('"%s" does not exist.', $filename));
            }
            return false;
        }
        return true;
    }

    /**
     * Get an array that represents directory tree.
     *
     * @since 0.9.9
     * @param string $dir   Directory path
     * @param string $bool  Include sub directories
     */
    public function directoryListing(string $dir, string $bool = "dirs")
    {
        $truedir = $dir;
        $dir = scandir($dir);
        if ($bool == "files") { // dynamic function based on second pram
            $direct = 'is_dir';
        } elseif ($bool == "dirs") {
            $direct = 'is_file';
        }
        foreach ($dir as $k => $v) {
            if (($direct($truedir . $dir[$k])) || $dir[$k] == '.' || $dir[$k] == '..') {
                unset($dir[$k]);
            }
        }
        $dir = array_values($dir);
        return $dir;
    }

    /**
     * Beautifies a filename for use.
     *
     * Uses `beautified_filename` filter hook.
     *
     * @since 0.9.9
     * @param string $filename Filename to beautify.
     * @return string Beautified filename.
     */
    public function beautifyFilename(string $filename)
    {
        $filename_raw = $filename;

        // reduce consecutive characters
        $filename = preg_replace([
        // "file   name.zip" becomes "file-name.zip"
        '/ +/',
        // "file___name.zip" becomes "file-name.zip"
        '/_+/',
        // "file---name.zip" becomes "file-name.zip"
        '/-+/'
            ], '-', $filename_raw);
        $filename = preg_replace([
        // "file--.--.-.--name.zip" becomes "file.name.zip"
        '/-*\.-*/',
        // "file...name..zip" becomes "file.name.zip"
        '/\.{2,}/'
            ], '.', $filename);

        /**
         * Filters a beautified filename.
         *
         * @since 0.9.9
         * @param string $filename     Beautified filename.
         * @param string $filename_raw The filename prior to beautification.
         */
        $filename = $this->hook->{'applyFilter'}('beautified_filename', $filename, $filename_raw);

        // lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
        $filename = mb_strtolower($filename, mb_detect_encoding($filename));
        // ".file-name.-" becomes "file-name"
        $filename = trim($filename, '.-');
        return $filename;
    }

    /**
     * Sanitizes a filename.
     *
     * Uses `sanitized_filename` filter hook.
     *
     * @since 0.9.9
     * @param string $filename  Name of file to sanitize.
     * @param bool $beautify    Whether or not to beautify the sanitized filename.
     * @return string Sanitized filename for use.
     */
    public function sanitizeFilename(string $filename, bool $beautify = true)
    {
        $filename_raw = $filename;
        // sanitize filename
        $filename = preg_replace(
            '~
        [<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
        [#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
        ~x',
            '-',
            $filename_raw
        );
        // avoids ".", ".." or ".hiddenFiles"
        $filename = ltrim($filename, '.-');
        // avoids %20
        $filename = urldecode($filename);
        // optional beautification
        if ($beautify) {
            $filename = $this->beautifyFilename($filename);
        }

        /**
         * Filters a sanitized filename.
         *
         * @since 0.9.9
         * @param string $filename     Sanitized filename.
         * @param string $filename_raw The filename prior to sanitization.
         */
        $filename = $this->hook->{'applyFilter'}('sanitized_filename', $filename, $filename_raw);

        // maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
        return $filename;
    }

    /**
     * Normalize a filesystem path.
     *
     * @since 0.9.9
     * @param string $path Path to normalize.
     * @return string Normalized path.
     */
    public function normalizePath($path)
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('|(?<=.)/+|', '/', $path);
        if (':' === substr($path, 1, 1)) {
            $path = ucfirst($path);
        }
        return $path;
    }

    /**
     * Removes trailing forward slashes and backslashes if they exist.
     *
     * The primary use of this is for paths and thus should be used for paths. It is
     * not restricted to paths and offers no specific path support.
     *
     * @since 0.9.9
     * @param string $string What to remove the trailing slashes from.
     * @return string String without the trailing slashes.
     */
    public function removeTrailingSlash($string)
    {
        return rtrim($string, '/\\');
    }

    /**
     * Appends a trailing slash.
     *
     * Will remove trailing forward and backslashes if it exists already before adding
     * a trailing forward slash. This prevents double slashing a string or path.
     *
     * The primary use of this is for paths and thus should be used for paths. It is
     * not restricted to paths and offers no specific path support.
     *
     * @since 0.9.9
     * @param string $string What to add the trailing slash to.
     * @return string String with trailing slash added.
     */
    public function addTrailingSlash($string)
    {
        return $this->removeTrailingSlash($string) . '/';
    }
}
