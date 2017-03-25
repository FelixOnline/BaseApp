<?php
namespace FelixOnline\Core;

use FelixOnline\Exceptions\InternalException;
/*
 * Utility Class
 *
 * Collection of static functions
 */
class Utility {
    /*
     * Public Static: Get current page url
     *
     * Returns string
     *
     * @codeCoverageIgnore
     */
    public static function currentPageURL() {
        $pageURL = 'http';
        if (array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        }
        return $pageURL;
    }

    /*
     * Public Static: Trim text
     *
     * $string - String to trim
     * $limit - Character limit for string
     *
     * Returns string
     */
    public static function trimText($string, $limit, $strip = true) {
        if($strip) {
                $string = strip_tags($string); // strip tags
        }
        if(strlen($string) <= $limit) {
            return $string;
        } else {
            return substr($string, 0, $limit).' ... ';
        }
    }

    /*
     * Public Static: Creates CSRF protection token
     *
     * $form_name - name of form (token is unique for form)
     * $max_length - time for which token is valid (in seconds) [optional, default is 1 hour]
     *
     * @codeCoverageIgnore
     */
    public static function generateCSRFToken($form_name, $max_length = 3600) {
        $rand = mt_rand(9, 99999999);
        $time = time();
        $hash = $time * $rand;
        $hash = $hash.$form_name.$max_length;
        $hash = sha1($hash);

        setcookie('felixonline_csrf_'.$form_name, $hash, time() + $max_length, '/');

        return $hash;
    }

    /*
     * Public Static: Urlise text
     * Make url friendly text from string
     *
     * $string
     *
     * Returns string
     */
    public static function urliseText($string) {
        $title = strtolower($string); // Make title lowercase
        $title= preg_replace('/[^\w\d_ -]/si', '', $title); // Remove special characters
        $dashed = str_replace( " ", "-", $title); // Replace spaces with hypens
        $utf8 = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $dashed); // Remove non printable characters
        return $utf8;
    }

    /*
     * Public static: Get URL
     * Using curl request url but don't get body
     *
     * $url - url to ping
     *
     * @codeCoverageIgnore
     */
    public static function getURL($url) {
        $timeout = 5;

        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /*
     * Public static: Redirect users
     * Redirect a user by sending a new header location
     *
     * $goto - url to redirect to
     * $params - array of parameters to add to url [optional]
     * $hash - anchor tag to jump to [optional]
     *
     * @codeCoverageIgnore
     */
    public static function redirect($goto, $params = NULL, $hash = NULL) {
        if($params) {
            $i = 0;
            if(!$goto) $goto = STANDARD_URL;
            foreach($params as $key => $value) {
                if(strpos($goto,'?')) {
                    $goto .= '&'.$key.'='.$value;
                } else if ($i == 0) {
                    $goto .= '?'.$key.'='.$value;
                }
                $i++;
            }
        }
        if($hash) {
            $goto .= '#'.$hash;
        }
        header('Location: '.$goto);
    }

    /*
     * Public: Add http
     * Adds http to url if doesn't exist
     */
    public static function addhttp($url) {
        $url = trim($url);
        if($url != '' && $url != 'http://') {
            if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                $url = "http://" . $url;
            }
        } else {
            $url = '';
        }
        return $url;
    }

    /**
     * Decode JSON string and throw error if fails
     *
     * @param string $string - JSON string to decode
     *
     * @static
     *
     * @return mixed - associative array
     * @throws \Exception if json decode fails with message about why
     *
     * @codeCoverageIgnore
     */
    public static function jsonDecode($string)
    {
        if ($string) {
            $json = json_decode($string, true);

            // if json_decode failed
            if ($json === null) {
                self::jsonLastError();
            }

            return $json;
        } else {
            return false;
        }
    }

    /**
     * Encode as JSON and throw error if fails
     *
     * @param mixed $data - data to encode
     *
     * @static
     *
     * @return string - json string
     * @throws \Exception if json decode fails with message about why
     *
     * @codeCoverageIgnore
     */
    public static function jsonEncode($data)
    {
        $json = json_encode($data);

        // if json_encode failed
        if ($json === false) {
            self::jsonLastError();
        }

        return $json;
    }

    /**
     * Throw json last error
     *
     * @codeCoverageIgnore
     */
    public static function jsonLastError()
    {
        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                throw new InternalException('Maximum stack depth exceeded');
                break;
            case JSON_ERROR_STATE_MISMATCH:
                throw new InternalException('Underflow or the modes mismatch');
                break;
            case JSON_ERROR_CTRL_CHAR:
                throw new InternalException('Unexpected control character found');
                break;
            case JSON_ERROR_SYNTAX:
                throw new InternalException('Syntax error, malformed JSON');
                break;
            case JSON_ERROR_UTF8:
                throw new InternalException('Malformed UTF-8 characters, possibly incorrectly encoded');
                break;
            default:
                throw new InternalException('Unknown error');
                break;
        }
    }
}
