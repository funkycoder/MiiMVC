<?php
namespace Mii\Login\Model;
use Mii\Core;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author Quan Nguyen
 */
class User extends Core\ObjectModel{
    public function __construct() {
        parent::__construct(new UserService(), 'userid', array('username', 'password', 'text'), array(), 'users');
    }
      
}

?>
