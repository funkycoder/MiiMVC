<?php

namespace Mii\Login\Model;

use Mii\Core;

require_once __DIR__ . '\UserData.php';
require_once '..\Core\ObjectModel.php';
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author Quan Nguyen
 */
class User extends Core\ObjectModel {

    protected $mailFields = array('useremail');
    protected $passwordFields = array('password', 'newpassword', 'newpasswordagain');
    protected $nameFields = array('username');
    protected $phoneFields = array('userphone');

    const SESSION_TIME_LIMIT = 900; //15*60 = 15 minutes
    const COOKIE_EXPIRE = 8640000; //60*60*24*100 seconds = 100 days by default
    const COOKIE_PATH = "/"; //Available in whole domain

    public function __construct() {
        parent::__construct(new UserData(), 'userid', array('username', 'useremail', 'password', 'userphone'), array('salt', 'hash', 'timestamp'), 'users');
        if (!isset($_SESSION)) {
            session_start();
            ob_start();
        }        
    }

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
            $tempObject = $this->dataService->retrieve($_COOKIE['userid']);
            //correct userid? get properties from database
            if ($tempObject->isEmpty()) {
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
        if ($now > $_SESSION['start'] + self::SESSION_TIME_LIMIT) {
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
        setcookie("userid", $this->userid, time() + self::COOKIE_EXPIRE, self::COOKIE_PATH);
        setcookie("hash", $this->hash, time() + self::COOKIE_EXPIRE, self::COOKIE_PATH);
    }

//TODO change this back to private after debugging
    public function unsetUserCookie() {
        foreach (array_keys($_COOKIE) as $cookieName) {
            setcookie($cookieName, '', time() - self::COOKIE_EXPIRE, self::COOKIE_PATH);
        }
// if (isset($_COOKIE[session_name()])) {
// setcookie(session_name(), "", time() - self::COOKIE_EXPIRE, self::COOKIE_PATH);
// }
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

    function logout() {
        $this->unsetUserSession();
        $this->unsetUserCookie();
    }

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
                $error = "Tên truy cập bao gồm các ký tự A-Z không có khoảng trắng với độ dài 4-100 ký tự.";
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