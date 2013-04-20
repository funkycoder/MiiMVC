<?php

namespace Mii\Login\Model;

use Mii;
use Mii\Core;

require_once __DIR__ . '\UserData.php';
require_once '..\Core\ObjectModel.php';

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

    protected $mailFields = array('useremail');
    protected $passwordFields = array('password', 'newpassword', 'newpasswordagain');
    protected $nameFields = array('username');
    protected $phoneFields = array('userphone');

    public function __construct() {
        $expectedFields = array('username', 'first_name', 'last_name', 'useremail', 'password', 'userphone');
        $optionalFields = array('type', 'salt', 'hash', 'date_created', 'date_modified', 'date_expires');
        parent::__construct(new UserData(), 'userid', $expectedFields, $optionalFields, 'users');
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
        if (isset($_SESSION['user']) && isset($_SESSION['start'])) {
            $this->properties = $_SESSION['user'];
            $hashOK = $this->dataService->checkHash();
            if (!$hashOK) {
                $this->errors['login'] = 'Account này đang được đăng nhập ở một máy khác. Vui lòng đăng nhập lại.';
            } elseif ($this->sessionTimeOut()) {
                $this->errors['login'] = 'Phiên làm việc hết giờ (Session timeout). Vui lòng đăng nhập lại.';
            } else {
                //hash ok and session is not timeout
                return TRUE;
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
            return TRUE;
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
    public function login() {
        $success = $this->dataService->checkPassword();
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
        return TRUE;
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
        $emailTaken = $this->dataService->checkEmailTaken();
        if ($emailTaken) {
            $this->errors['register'] = 'Email đã đăng ký. Xin vui lòng chọn email khác.';
            return FALSE;
        }
        $success = $this->insert();
        If (!$success) {
            $this->errors['register'] = 'Lỗi hệ thống. Xin vui lòng thử lại.';
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
        $UPDATE_PASSWORD = FALSE;
        if ($this->controlFields['newpassword']) {
            if (!($this->controlFields['newpassword'] == $this->controlFields['newpasswordagain'])) {
                $this->errors['newpasswordagain'] = 'Password mới không trùng khớp.';
                return FALSE;
            } else {
                //password fields match
                $this->password = $this->controlFields['newpassword'];
                $UPDATE_PASSWORD = TRUE;
            }
        }
        try {
            $success = $this->dataService->update($UPDATE_PASSWORD);
        } catch (\PDOException $e) {
            $this->errors['data'] = 'Cập nhật record thất bại. (' . $e->errorInfo[2] . ')';
            return FALSE;
        }
        if (!$success)
            $this->errors['data'] = 'Không có record nào được cập nhật.';;
        return $success;
    }

    /**
     * Logout
     */
    public function logout() {
        $this->unsetUserSession();
        $this->unsetUserCookie();
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
    public function checkFormat($field, $value) {
        switch ($field) {
            case in_array($field, $this->mailFields):
                $regex = "/^[_+a-z0-9-]+(\.[_+a-z0-9-]+)*"
                        . "@[a-z0-9-]+(\.[a-z0-9-]{1,})*"
                        . "\.([a-z]{2,}){1}$/i";
                $error = "Email không hợp lệ.";
                break;
            case in_array($field, $this->passwordFields):
                $regex = "/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/";
                $error = "Mật khẩu dài tối thiểu 8 ký tự và có ít nhất 1 chữ số, 1 chữ hoa và 1 chữ thường";
                break;
            case in_array($field, $this->nameFields):
                $regex = "/^([a-z]){4,100}$/i";
                $error = "Tên truy cập bao gồm các ký tự A-Z không có dấu, không có khoảng trắng với độ dài 4-100 ký tự.";
                break;
            case in_array($field, $this->phoneFields):
                $regex = "/^([0-9])+$/";
                $error = "Số điện thoại không hợp lý.";
                break;
            default:
                return TRUE;
        }
        if (!preg_match($regex, $value)) {
            $this->errors[$field] = $error;
            return FALSE;
        }
        return TRUE;
    }

}

?>