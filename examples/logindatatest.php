<?php
namespace Mii;
use Mii\Login\Model;
use Mii\Login\Service;
require_once '..\Login\Model\User.php';

$user = new Model\User();

$user->useremail = 'toan@gmail.com';
$user->username='Toan';
$user->password='Pique2983';
$user->userphone='940394034';
$user->setControlFields('remember', 'on');
//$user->insert();



$test = $user->login();
?>