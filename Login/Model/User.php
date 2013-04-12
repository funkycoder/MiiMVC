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
    protected $nameFields = array('username', 'name');
    protected $phoneFields = array('userphone');
  

    public function __construct() {
        parent::__construct(new UserData(), 'userid', array('username', 'useremail', 'password', 'userphone'), array('salt', 'hash', 'timestamp'), 'users');
    }

    public function login() {
        return $this->dataService->login();
    }

    public function checkLogin(){
        return $this->dataService->checkLogin();
    }
    
    //TODO DElete this after debugging
    public  function unsetUserSession (){
        $this->dataService->unsetUserSession();
    }
    
    //TODO Delete this after debugging
    public function unsetUserCookie(){
        $this->dataService->unsetUserCookie();
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
            $this->errors[$field]=$error;
            return FALSE;
        }
        return TRUE;
    }


}

?>
