<?php

namespace Mii\Login\Model;

use Mii\Core;

require_once '..\Core\DataModel.php';
//=============================================================================================
// UserData 
//============================================================================================
/* UserData object
 * 
 * Data object in Login system, this object extends data model MiiMVC core
 * 
 * This class deal with all database operations
 * @version 1.0 (13 of April, 2013)
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @author Quan Nguyen <bsquan2009@yahoo.com> http://drquan.net
 * @copyright (c) 2013, Quan Nguyen 
 */
class UserData extends Core\DataModel {
    #####################################################################################################################
    #                                                                                                                  ##
    #               SPECIFIC USERDATA FUNCTIONS HERE                                                                   ##
    #                                                                                                                  ##
    #####################################################################################################################

    public function checkEmailTaken() {
        $tempObject = $this->retrieve_one_by_field('useremail', $this->myObject->useremail);
        return (!$tempObject->isEmpty());
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

    public function encryptPassword($salt, $password) {
        //sha1 required 40 characters
        return sha1($salt . $password);
    }

    public function checkHash() {
        $myObject = $this->myObject;
        $pkName = $this->pkName;
        $tempObject = $this->retrieve($myObject->$pkName);
        return ($tempObject->hash == $myObject->hash);
    }

    public function insertHash() {
        $this->myObject->hash = md5(microtime());
        return parent::update();
    }

    #####################################################################################################################
    #                                                                                                                  ##
    #               THE FOLLOWING FUNCTIONS HAD EXCEPTION HANDLER DEFINED IN OBJECT MODEL                              ##
    #                                                                                                                  ##
    #####################################################################################################################

    public function insert() {
        $myObject = $this->myObject;
        $myObject->salt = time();
        $myObject->timestamp = time();
        $myObject->password = $this->encryptPassword($myObject->salt, $myObject->password);
        return parent::insert();
    }

    public function update($UPDATE_PASSWORD = FALSE) {
        $myObject = $this->myObject;
        $myObject->timestamp = time();
        IF ($UPDATE_PASSWORD) {
            $myObject->password = $this->encryptPassword($myObject->salt, $myObject->password);
        }
        return parent::update();
    }

}

?>