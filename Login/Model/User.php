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
     //magic function
    function __call($methodname, $arguments) {
        if (method_exists($this->dataService, $methodname)){
            return $this->dataService->$methodname(implode(',', $arguments));
        }
    }
     public function retrieve_one($wherewhat = '', $bindings = '') {
         $this->dataService->retrieve_one($wherewhat, $bindings);
     }
     public function select($selectwhat, $wherewhat, $bindings){
       return $this->dataService->select($selectwhat, $wherewhat, $bindings, \PDO::FETCH_ASSOC);
       }
       public function retrieve_many($wherewhat, $bindings){
           return $this->dataService->retrieve_many($wherewhat, $bindings);
       }
}

?>
