<?php
namespace Mii;
use Mii\Login\Model;
use Mii\Login\Service;
require_once '..\Login\Model\User.php';

$user = new Model\User();
$user->userid=10;
$user->useremail = 'toanthiengiao@gmail.com';
$user->username='Thien';
$user->setControlField('newpassword', 'Pique2983');
$user->setControlField('newpasswordagain', 'Pique2983');

$user->userphone='1234567543548';
//$user->setControlField('remember', 'on');
//$user->insert();
//$user->login();
//$user->checkLogin();
$user->validate();
$user->update();
?>