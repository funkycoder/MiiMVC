<?php

namespace Ecom1\Login\Model;

use Mii\Core;

require __DIR__ . '/UserData.php';
require MII_URI.'core/ObjectModel.php';

//=============================================================================================
// User object
//============================================================================================
/**
 * User object in Login system, this object extends object model MiiMVC core
 * 
 * @version 1.0 (13 of April, 2013)
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @author Quan Nguyen <bsquan2009@yahoo.com> http://drquan.net
 * @copyright (c) 2013, Quan Nguyen 
 */
class User extends Core\ObjectModel {

    public $logged_in = FALSE;
    protected $mailFields = array('useremail');
    protected $passwordFields = array('password', 'pass1', 'pass2');
    protected $nameFields = array('username', 'first_name', 'last_name');
    protected $phoneFields = array('userphone');

    public function __construct() {
        $expectedFields = array('username', 'first_name', 'last_name', 'useremail', 'password', 'userphone');
        $optionalFields = array('type', 'salt', 'hash', 'date_created', 'date_modified', 'date_expires');
        parent::__construct(new UserData(), 'userid', $expectedFields, $optionalFields, USER_TABLE);
        //default type is member
        $this->type = 'member';
        if (!isset($_SESSION)) {
            session_start();
            ob_start();
        }
    }

    /**
     * This user logged in ?
     * 
     * Check whether the correct session is set? then check the hash
     * If session not set, check whether the correct cookie is set?
     * Else clear all sessions and cookies
     * $error['login'] will be set if not successful
     * 
     * @return boolean Logged in?
     */
    public function checkLogin() {
        $this->logged_in = FALSE;
        if (isset($_SESSION['user']) && isset($_SESSION['start'])) {
            $this->properties = $_SESSION['user'];
            $hashOK = $this->dataService->checkHash();
            if (!$hashOK) {
                $this->errors['login'] = 'Account này đang được đăng nhập ở một máy khác. Vui lòng đăng nhập lại.';
            } elseif ($this->sessionTimeOut()) {
                $this->errors['login'] = 'Phiên làm việc hết giờ (Session timeout). Vui lòng đăng nhập lại.';
            } else {
                //hash ok and session is not timeout
                return $this->logged_in = TRUE;
            }
            //error
            $this->unsetUserSession();
            return FALSE;
        } elseif (isset($_COOKIE['userid']) && isset($_COOKIE['hash'])) {
            //correct userid? get properties from database
            $tempObject = $this->dataService->retrieve($_COOKIE['userid']);
            //check hash. if tempObject is empty (wrong userid), this won't match for sure
            if ($tempObject->hash != $_COOKIE['hash']) {
                $this->unsetUserCookie();
                return FALSE;
            }
            $this->properties = $tempObject->properties;
            $this->setUserSession();
            return $this->logged_in = TRUE;
        } else {
            //No session, no cookie?
            return FALSE;
        }
    }

    /**
     * Log this user in
     * 
     * Check the provided password
     * Insert the hash then set the session
     * If remember me set then set cookies
     * $error['login'] will be set if not successful
     * 
     * @return boolean login successful?
     */
    public function login($USE_EMAIL = TRUE) {
        $this->logged_in = FALSE;
        $success = $this->dataService->checkPassword($USE_EMAIL);
        if (!$success) {
            $this->errors['login'] = 'Đăng nhập thất bại. (Email/Password không đúng)';
            return FALSE;
        }
        $hashOK = $this->dataService->insertHash();
        if (!$hashOK) {
            $this->errors['login'] = 'Đăng nhập thất bại. Lỗi hệ thống. Xin thử lại.';
            return FALSE;
        }
        //Set user session now.
        $this->setUserSession();
        //Remember login credentials ? set Cookie
        if (isset($this->controlFields['remember'])) {
            $this->setUserCookie();
        }
        return $this->logged_in = TRUE;
    }

    /**
     * Register the current user to database
     * 
     * Email taken? if not then insert to database
     * $errors['register'] will be set if not successful
     * 
     * @return boolean Register successfuly
     */
    public function register() {
        $success = FALSE;
        $registeredUsers = $this->retrieve_many('username=? OR useremail=?', array($this->username, $this->useremail));
        //This account registered?
        if (count($registeredUsers) > 1) { //Both are taken
            $this->errors['useremail'] = 'Email đã đăng ký. Xin vui lòng chọn email khác.';
            $this->errors['username'] = 'Username đã đăng ký. Xin vui lòng chọn username khác.';
        } else if (count($registeredUsers) == 1) { //Either is taken
            //Get the information
            $regUser = $registeredUsers[0];
            if ($this->useremail == $regUser->useremail)
                $this->errors['useremail'] = 'Email đã đăng ký. Xin vui lòng chọn email khác.';
            if ($this->username == $regUser->username)
                $this->errors['username'] = 'Username đã đăng ký. Xin vui lòng chọn username khác.';
            if ($this->useremail == $regUser->useremail && $this->username == $regUser->username)
                $this->errors['register'] = 'Tài khoản đã được đăng ký. Nếu quên mật khẩu, xin vui lòng dùng link phía bên phải.';
        }
        else { // This account not registered before,ok to proceed
            if ($this->password == $this->controlFields['pass2']) { //Enter the 2 password correct?
                $success = $this->insert();
                if (!$success) {
                    $this->errors['register'] = 'Lỗi hệ thống. Xin vui lòng thử lại.';
                }
            } else {
                $this->errors['pass2'] = 'Mật khẩu không trùng nhau';
            }
        }
        return $success;
    }

    private function sessionTimeOut() {
        $now = time();
        if ($now > $_SESSION['start'] + SESSION_TIME_LIMIT) {
            return true;
        } else {
            $_SESSION['start'] = time();
            return false;
        }
    }

    private function setUserSession() {
        //new session id. This generate $_COOKIE[PHPSESSID]
        session_regenerate_id();
        //store object properties only
        $_SESSION['user'] = $this->properties;
        $_SESSION['start'] = time();
    }

//TODO Change this back to private after debug
    public function unsetUserSession() {
        $_SESSION = array();
        session_destroy();
        //remove also the cookie
        $this->unsetUserCookie();
    }

    private function setUserCookie() {
        setcookie("userid", $this->userid, time() + COOKIE_EXPIRE, COOKIE_PATH);
        setcookie("hash", $this->hash, time() + COOKIE_EXPIRE, COOKIE_PATH);
    }

//TODO change this back to private after debugging
    public function unsetUserCookie() {
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - COOKIE_EXPIRE, COOKIE_PATH);
        }
        if (isset($_COOKIE['userid'])) {
            setcookie('userid', '', time() - COOKIE_EXPIRE, COOKIE_PATH);
        }
        if (isset($_COOKIE['hash'])) {
            setcookie('hash', '', time() - COOKIE_EXPIRE, COOKIE_PATH);
        }
    }

    //Override update() function in ObjectModel
    public function update() {
        //old password ok? check password using email, but do not get data from database
        if (!$this->dataService->checkPassword(TRUE,FALSE)) {
            $this->errors['password'] = 'Mật khẩu không đúng.';
            return FALSE;
        }
        if ($this->controlFields['pass1']) {
            if (!($this->controlFields['pass1'] == $this->controlFields['pass2'])) {
                $this->errors['pass2'] = 'Password mới không trùng khớp.';
                return FALSE;
            } else {
                //password fields match                
               //its time to change the new pasword
                $this->password = $this->controlFields['pass1'];
            }
        }
        try {
            $success = $this->dataService->update();
        } catch (\PDOException $e) {
            $this->errors['update'] = 'Cập nhật record thất bại. (' . $e->errorInfo[2] . ')';
            return FALSE;
        }
        if (!$success)
            $this->errors['update'] = 'Không có record nào được cập nhật.';;
        return $success;
    }

    /**
     * Logout
     */
    public function logout() {
        $this->unsetUserSession();
        $this->unsetUserCookie();
        $this->logged_in = FALSE;
    }

    /**
     * Check whether a value is in correct format using regex
     * 
     * This method must be implemented as the object model only has abstract function
     * $error['$field'] will be set if the value is not in correct format
     * @param type $field field name
     * @param type $value value needs to be checked
     * @return boolean in correct format ?
     */
    public function checkFormat($field, $value, $ERROR_MESSAGE = TRUE) {
        switch ($field) {
            case in_array($field, $this->mailFields):
                $regex = "/^[_+a-z0-9-]+(\.[_+a-z0-9-]+)*"
                        . "@[a-z0-9-]+(\.[a-z0-9-]{1,})*"
                        . "(\.[a-z]{2,}){1}$/i";
                $error = "Email không hợp lệ.";
                break;
            case in_array($field, $this->passwordFields):
                $regex = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/"; //Mật khẩu dài tối thiểu 8 ký tự và có ít nhất 1 chữ số, 1 chữ hoa và 1 chữ thường
                $error = "Mật khẩu không hợp lệ.";
                break;
            case in_array($field, $this->nameFields):
                $regex = "/^([a-z]){4,100}$/i";
                $error = "Tên bao gồm các ký tự A-Z không có dấu, không có khoảng trắng với độ dài 4-100 ký tự.";
                break;
            case in_array($field, $this->phoneFields):
                $regex = "/^([0-9])+$/";
                $error = "Số điện thoại không hợp lý.";
                break;
            default:
                return TRUE;
        }
        if (!preg_match($regex, $value)) {
            if ($ERROR_MESSAGE)
                $this->errors[$field] = $error;
            return FALSE;
        }
        return TRUE;
    }

// ******************************************* //
// ************ REDIRECT FUNCTION ************ //
// This next block is added in Chapter 4.
// This function redirects invalid users.
// It takes two arguments: 
// - The session element to check
// - The destination to where the user will be redirected. 
    public function redirect($protocol = 'http://') {
        // Check for the session item:
        $url = $protocol . REDIRECT_URL; // Define the URL.
        header("Location: $url");
        exit(); // Quit the script.
    }

    //TODO CHeck this time function
    public function getExpires() {
        return ($this->date_expires > date(time()));
    }

}

?>