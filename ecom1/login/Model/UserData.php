<?php

namespace Ecom1\Login\Model;
use Mii\Core;

require MII_URI.'core/DataModel.php';

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
//Registration expires?

    public function getExpires() {
        return \strtotime($this->date_expires) > \time();
    }

    public function checkPassword($USE_EMAIL = TRUE,$RETRIEVE_DATA = TRUE) {
        $myObject = $this->myObject;
        //Login by email?
        $field = ($USE_EMAIL) ? 'useremail' : 'username';
        $userObject = $this->retrieve_one_by_field($field, $myObject->$field);
        if ($userObject->password == $this->hashPassword($userObject->salt, $myObject->password)) {
            //correct password? get all related data from database
            if ($RETRIEVE_DATA) $myObject->properties = $userObject->properties;
            return TRUE;
        }
        //Wrong password!.
        return FALSE;
    }

    public function hashPassword($salt, $password) {
        //sha256 return 64 chars or 32 bytes binary data
        //(if raw_output =TRUE)
        return hash_hmac('sha256', $password, $salt, TRUE);
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
        $myObject->date_created = date('Y-m-d H:i:s', time());
        $myObject->password = $this->hashPassword($myObject->salt, $myObject->password);
        return parent::insert();
    }

    public function update() {
        $myObject = $this->myObject;
        $myObject->date_modified = date('Y-m-d H:i:s', time());
        $myObject->password = $this->hashPassword($myObject->salt, $myObject->password);
        return parent::update();
    }

}

?>