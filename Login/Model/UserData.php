<?php

namespace Mii\Login\Model;

use Mii\Core;

require_once '..\Core\DataModel.php';
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserService
 *
 * @author Quan Nguyen
 */
class UserData extends Core\DataModel {

    public function insert() {
        $myObject = $this->myObject;
        $myObject->salt = time();
        $myObject->timestamp = time();
        $myObject->password = $this->encryptPassword($myObject->salt, $myObject->password);
        return parent::insert();
    }

    public function insertHash() {
        $this->myObject->hash = md5(microtime());
        return parent::update();
    }

    public function update($UPDATE_PASSWORD = FALSE) {
        $myObject = $this->myObject;
        $myObject->timestamp = time();
        IF ($UPDATE_PASSWORD) {
            $myObject->password = $this->encryptPassword($myObject->salt, $myObject->password);
        }
        return parent::update();
    }

    public function checkEmailTaken() {
        $tempObject = retrieve_one_by_field('useremail', $tempObject->myObject->useremail);
        return ($tempObject . isEmpty());
    }

    public function checkPassword() {
        $myObject = $this->myObject;
        $tempObject = $this->retrieve_one_by_field('useremail', $myObject->useremail);
        if ($tempObject->password == $this->encryptPassword($tempObject->salt, $myObject->password)) {
            //correct password? get all related data from database
            $myObject->properties = $tempObject->properties;
            return TRUE;
        }
        //Wrong password!. 
        return FALSE;
    }

    public function checkHash() {
        $myObject = $this->myObject;
        $pkName = $myObject->pkName;
        $tempObject = $this->retrieve($myObject->$pkName);
        return ($tempObject->hash == $myObject->hash);
    }

    private function encryptPassword($salt, $password) {
        //sha1 required 40 characters
        return sha1($salt . $password);
    }

}

?>
