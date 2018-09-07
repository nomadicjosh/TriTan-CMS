<?php
namespace TriTan\Common;

use TriTan\Interfaces\Hooks\ActionFilterHookInterface;

class Uri
{
    public $hook;

    public function __construct(ActionFilterHookInterface $hook)
    {
        $this->hook = $hook;
    }

    /**
     * Redirects to another page.
     *
     * Uses `ttcms_redirect` and `ttcms_redirect_status` filter hooks.
     *
     * @since 0.9.9
     * @param string $location The path to redirect to
     * @param int $status Status code to use
     * @return bool False if $location is not set
     */
    public function redirect(string $location, int $status = 302)
    {
        /**
         * Filters the redirect location.
         *
         * @since 0.9.9
         *
         * @param string $location The path to redirect to.
         * @param int    $status   Status code to use.
         */
        $_location = $this->hook->{'applyFilter'}('ttcms_redirect', $location, $status);
        /**
         * Filters the redirect status code.
         *
         * @since 0.9.9
         *
         * @param int    $status   Status code to use.
         * @param string $_location The path to redirect to.
         */
        $_status = $this->hook->{'applyFilter'}('ttcms_redirect_status', $status, $_location);

        if (!$_location) {
            return false;
        }

        header("Location: $_location", true, $_status);
        return true;
    }

    /**
     * Url shortening function.
     *
     * @since 0.9.9
     * @param string $url URL
     * @param int $length Characters to check against.
     * @return string
     */
    public function shorten(string $url, int $length = 80)
    {
        if (strlen($url) > $length) {
            $strlen = $length - 30;
            $first = substr($url, 0, $strlen);
            $last = substr($url, -15);
            $short_url = $first . "[ ... ]" . $last;
            return $short_url;
        } else {
            return $url;
        }
    }

    public function getPathInfo($relative)
    {
        $base = basename(BASE_PATH);
        if (strpos($_SERVER['REQUEST_URI'], DS . $base . $relative) === 0) {
            return $relative;
        } else {
            return $_SERVER['REQUEST_URI'];
        }
    }

    /**
     * Whether the current request is for an administrative interface.
     *
     * e.g. `/admin/`
     *
     * @since 0.9.9
     * @return bool True if an admin screen, otherwise false.
     */
    public function isAdmin(): bool
    {
        if (strpos($this->getPathInfo('/admin'), "/admin") === 0) {
            return true;
        }
        return false;
    }
    
    /**
     * Whether the current request is for a login interface.
     *
     * e.g. `/login/`
     *
     * @since 0.9.9
     * @return bool True if login screen, otherwise false.
     */
    public function isLogin(): bool
    {
        if (strpos($this->getPathInfo('/login'), "/login") === 0) {
            return true;
        }
        return false;
    }
}
