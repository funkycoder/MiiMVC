<?php
/**
 * USER CLASS
 * 
 * ----------------------------------------------------------------------------------------
 * Version : 1.0
 * Date : 09/03/2013 at 09:42 PM
 * @author Nguyen Nhu Quan FunkyCoder <bsquan2009@gmail.com>
 * ----------------------------------------------------------------------------------------
 */
namespace Mii;

class Mii_User {
    /**
     * End the current session
     * 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 9 and 17
     * Version : 1.0
     * Date : 09/03/2013
     */
    public static function endSession() {
        // if timelimit has expired, destroy session and redirect
        $_SESSION = array();
        // invalidate the session cookie
        // This uses the function session_name()to get the name of the session dynamically and resets the 
        //session cookie to an empty string and to expire 24 hours ago (86400 is the number of seconds in a day). 
        //The final argument ('/') applies the cookie to the whole domain
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 86400, '/');
        }
        // end session and redirect with query string
        session_destroy();
    }
    /**
     * Check if current session has timeout if not restart the timer
     * redirect to login page if timeout
     * 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 9 and 17
     * Version : 1.0
     * Date : 09/03/2013
     * @param int $timeOutLimit set timeout limit in minutes
     * @param string $loginUrl 
     * @return array $messages error messages
     */
    public static function checkSessionTimeOut($timeOutLimit, $loginUrl) {
        session_start();
        ob_start();
        // set a time limit in seconds
        $timelimit = $timeOutLimit * 60;
        // get the current time
        $now = time();
        // if session variable not set, redirect to login page
        if (!isset($_SESSION['authenticated'])) {
            header("Location: $loginUrl");
            exit;
        } elseif ($now > $_SESSION['start'] + $timelimit) {
            Mii_User::endSession();
            header("Location: {$loginUrl}?expired=yes");
            exit;
        } else {
            // if it's got this far, it's OK, so update start time
            $_SESSION['start'] = time();
        }
    }

    /**
     * Check an input password against provided criterion
     * 
     * Source: PHP Solutions Dynamic Design Made Easy 2nd Edition / David Powers/ FriendsofEd(Apress)/ 2010/ Chapter 9
     * Version : 1.0
     * Date : 27/02/2013
     * Modified date: 27/02/2013
     * Modified by : Nguyen Nhu Quan
     * Reason: Convert to static class function.          
     * 
     * @param string $password input password
     * @param int $minimumChars
     * @param boolean $mixedCase 
     * @param int $minimumNumbers
     * @param int $minimumSymbols
     * @return array $messages error messages
     */
    public static function checkPassword($password, $minimumChars = 8, $mixedCase = FALSE, $minimumNumbers = 0, $minimumSymbols = 0) {
        //prepare error messages
        $messages = array();
        //Password contain spaces?
        if (preg_match('/\s/', $password)) {
            $messages[] = 'Password cannot contain spaces.';
        }
        if (strlen($password) < $minimumChars) {
            $messages[] = "Password must be at least $minimumChars characters.";
        }
        if ($mixedCase) {
            $pattern = '/(?=.*[a-z])(?=.*[A-Z])/';
            if (!preg_match($pattern, $password)) {
                $messages[] = 'Password should include uppercase and lowercase characters.';
            }
        }
        if ($minimumNumbers) {
            $pattern = '/\d/';
            $found = preg_match_all($pattern, $password, $matches);
            if ($found < $minimumNumbers) {
                $messages[] = "Password should include at least $minimumNumbers number(s).";
            }
        }
        if ($minimumSymbols) {
            //concatenating a single-quoted string to a double-quoted one. This is necessary to include
            //single and double quotation marks in the permitted symbols.
            $pattern = "/[-!$%^&*(){}<>[\]'" . '"|#@:;.,?+=_\/\~]/';
            $found = preg_match_all($pattern, $password, $matches);
            if ($found < $minimumSymbols) {
                $messages[] = "Password should include at least $minimumSymbols nonalphanumeric character(s).";
            }
        }

        return $messages;
    }

}