<?php
namespace TriTan;

/**
 * Stop Forum Spam
 *
 * By sending GET request to:
 * http://www.stopforumspam.com/api?email=g2fsehis5e@mail.ru&username=MariFoogwoogy&f=json
 * you get the return i.e.:
 *
 *      {
 *          "success":true,
 *          "email":
 *              {
 *                  "lastseen":"2009-06-25 00:24:29",
 *                  "frequency":2,
 *                  "appears":true
 *              },
 *          "username":
 *              {
 *                  "frequency":0,
 *                  "appears":false
 *              }
 *      }
 *
 * In order to use:
 *
 *      1. Require this file using i.e.: use TriTan\StopForumSpam;
 *      2. Set spamTolerance by directly accessing the property i.e.: TriTan\StopForumSpam::spamTolerance = 10;
 *      3. Verify if a user is classified as spam with the is-functions i.e.:
 *
 *          if (!TriTan\StopForumSpam::isSpamBotByIpOrEmailOrUsername($ip, $email, $alias) {
 *              //do something.
 *          }
 *
 * @author Christian Johansson <christian@cvj.se>
 * @link http://www.stopforumspam.com
 */
class StopForumSpam
{

    /**
     * A number between 0 - 100 indicating how much
     * the confidence meter from StopFormSpam must be above.
     * Lesser value means lesser tolerance.
     *
     * @static
     * @var int
     */
    public static $spamTolerance = 15;

    /**
     * @static
     * @var bool
     */
    public static $enableDebug = false;

    /**
     * @static
     * @param string $ip
     * @param bool [$debug = false]
     * @throws Exception
     * @return bool
     */
    public static function isSpamBotByIp($ip, $debug = false)
    {
        if (!empty($ip)) {
            return self::_isSpamBot($ip, '', '', 'json', $debug);
        } else {
            throw new Exception('Invalid parameters for '
            . __FUNCTION__);
        }
    }

    /**
     * @static
     * @param string $username
     * @param bool [$debug = false]
     * @throws Exception
     * @return bool
     */
    public static function isSpamBotByUsername($username, $debug = false)
    {
        if (!empty($username)) {
            return self::_isSpamBot('', $username, '', 'json', $debug);
        } else {
            throw new Exception('Invalid parameters for '
            . __FUNCTION__);
        }
    }

    /**
     * @static
     * @param string $ip
     * @param string $username
     * @param bool [$debug = false]
     * @throws Exception
     * @return bool
     */
    public static function isSpamBotByIpAndUsername($ip, $username, $debug = false)
    {
        if (!empty($ip) && !empty($username)
        ) {
            return self::_isSpamBot($ip, $username, '', 'json', $debug);
        } else {
            throw new Exception('Invalid parameters for '
            . __FUNCTION__);
        }
    }

    /**
     * @static
     * @param string $ip
     * @param string $username
     * @param bool [$debug = false]
     * @throws Exception
     * @return bool
     */
    public static function isSpamBotByIpOrUsername($ip, $username, $debug = false)
    {
        if (!empty($ip) && !empty($username)
        ) {
            return self::_isSpamBot($ip, $username, '', 'json', $debug, 'OR');
        } else {
            throw new Exception('Invalid parameters for '
            . __FUNCTION__);
        }
    }

    /**
     * @static
     * @param string $email
     * @param bool [$debug = false]
     * @throws Exception
     * @return bool
     */
    public static function isSpamBotByEmail($email, $debug = false)
    {
        if (!empty($email)) {
            return self::_isSpamBot('', '', $email, 'json', $debug);
        } else {
            throw new Exception('Invalid parameters for '
            . __FUNCTION__);
        }
    }

    /**
     * @static
     * @param string $ip
     * @param string $email
     * @param bool [$debug = false]
     * @throws Exception
     * @return bool
     */
    public static function isSpamBotByIpAndEmail($ip, $email, $debug = false)
    {
        if (!empty($ip) && !empty($email)
        ) {
            return self::_isSpamBot($ip, '', $email, 'json', $debug);
        } else {
            throw new Exception('Invalid parameters for '
            . __FUNCTION__);
        }
    }

    /**
     * @static
     * @param string $ip
     * @param string $email
     * @param bool [$debug = false]
     * @throws Exception
     * @return bool
     */
    public static function isSpamBotByIpOrEmail($ip, $email, $debug = false)
    {
        if (!empty($ip) && !empty($email)
        ) {
            return self::_isSpamBot($ip, '', $email, 'json', $debug, 'OR');
        } else {
            throw new Exception('Invalid parameters for '
            . __FUNCTION__);
        }
    }

    /**
     * @static
     * @param string $ip
     * @param string $email
     * @param string $username
     * @param bool [$debug = false]
     * @throws Exception
     * @return bool
     */
    public static function isSpamBotByIpOrEmailOrUsername($ip, $email, $username, $debug = false)
    {
        if (!empty($ip) && !empty($email) && !empty($username)
        ) {
            return self::_isSpamBot($ip, $username, $email, 'json', $debug, 'OR');
        } else {
            throw new Exception('Invalid parameters for '
            . __FUNCTION__);
        }
    }

    /**
     * @static
     * @internal
     * @param string $ip
     * @param string $username
     * @param string $email
     * @param string [$format = 'json']
     * @param bool [$debug = false]
     * @param string [$operator = 'AND']
     * @throws Exception
     * @return bool
     */
    private static function _isSpamBot($ip = '', $username = '', $email = '', $format = 'json', $debug = false, $operator = 'AND')
    {
        if (!empty($ip) || !empty($username) || !empty($email)
        ) {
            $debug = !empty($debug);
            $return = false;

            if ($curl = curl_init()) {
                $url = self::_buildUri($ip, $username, $email, $format);

                self::_debug('url: "' . $url . '"', $debug);

                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_HTTPGET, 1);
                curl_setopt($curl, CURLOPT_FAILONERROR, 1);

                if ($response = curl_exec($curl)) {
                    self::_debug('response: <br />'
                        . $response, $debug);

                    if ($format == 'json') {
                        $decodedResponse = json_decode($response, true);
                    }

                    if (!empty($decodedResponse)) {
                        self::_debug('decoded response: <br />'
                            . '<pre>'
                            . print_r($decodedResponse, true)
                            . '</pre>', $debug);

                        if (!empty($decodedResponse['success'])) {
                            if ($operator == 'AND') {
                                $accBool = true;
                            } else {
                                $accBool = false;
                            }

                            foreach ($decodedResponse as $key => $array) {
                                if ($key != 'success' && isset($array['appears'])
                                ) {
                                    if ($operator == 'AND') {
                                        $accBool = ($accBool && self::_isSpamEntry($array));
                                    } elseif ($operator == 'OR') {
                                        $accBool = (self::_isSpamEntry($array) ? true : $accBool);
                                    }
                                }
                            }

                            if ($accBool) {
                                $return = true;
                            }
                        }
                    }
                }

                curl_close($curl);
            }

            self::_debug(
                'return: "' . (!empty($return) ? 'TRUE' : 'FALSE') . '"',
                $debug
            );
            return $return;
        } else {
            throw new Exception('Invalid parameters for '
            . __FUNCTION__);
        }
    }

    /**
     * @static
     * @internal
     * @param array $array
     * @return bool
     */
    private static function _isSpamEntry($array)
    {
        return (is_array($array) && !empty($array['appears']) && isset($array['confidence']) && $array['confidence'] > self::$spamTolerance);
    }

    /**
     * @static
     * @internal
     * @param string $ip
     * @param string $username
     * @param string $email
     * @param string [$format = 'json']
     * @return string
     */
    private static function _buildUri($ip, $username, $email, $format = 'json')
    {

        $url = 'http://www.stopforumspam.com/api?';
        $getKeys = 0;

        if (!empty($ip)) {
            $url .= 'ip=' . $ip;
            $getKeys++;
        }

        if (!empty($username)) {
            if ($getKeys > 0) {
                $url .= '&';
            }
            $url .= 'username=' . $username;
            $getKeys++;
        }

        if (!empty($email)) {
            if ($getKeys > 0) {
                $url .= '&';
            }
            $url .= 'email=' . $email;
            $getKeys++;
        }

        if (!empty($format)) {
            if ($getKeys > 0) {
                $url .= '&';
            }
            $url .= 'f=' . $format;
            $getKeys++;
        }

        return $url;
    }

    /**
     * @static
     * @internal
     * @param string $string
     * @param bool [$display = false]
     */
    private static function _debug($string, $display = false)
    {
        if (!empty($display)) {
            echo '<div>' . "\n" . $string . "\n" . '</div>';
        }
    }
}
