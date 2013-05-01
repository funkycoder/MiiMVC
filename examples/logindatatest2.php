<?php
namespace Mii;
use Mii\Login\Model;
use Mii\Login\Service;
require_once 'C:\xampp\htdocs\MiiMVC\ecom1\login\Model\User.php';
require '..\ecom1\Settings.php'; //Get all the configuration
require \Ecom1\Settings::$APP_CONFIG_FILE;

$user = new \Ecom1\Login\Model\User();
   $registeredUsers = $user->retrieve_many('username=? OR useremail=?', array('',''));
?>