<?php
    /**
     * Core constants
     */

    if(!defined('SESSION_NAME'))                    define('SESSION_NAME', 'felix');
    if(!defined('COOKIE_NAME'))                     define('COOKIE_NAME', 'felixonline');
    if(!defined('SESSION_LENGTH'))                  define('SESSION_LENGTH', 7200); // session length
    if(!defined('LOGIN_CHECK_LENGTH'))              define('LOGIN_CHECK_LENGTH', 300); // length to allow login check (5mins)
    if(!defined('COOKIE_LENGTH'))                   define('COOKIE_LENGTH', 2592000); // cookie length (30 days) (60*60*24*30)
    if(!defined('LOCAL'))                           define('LOCAL', false); // if true then site is hosted locally - don't use pam_auth etc.
