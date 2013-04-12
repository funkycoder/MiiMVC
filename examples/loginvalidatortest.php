<?php
namespace Mii;
use Mii\Login\Service;
use Mii\Login\Model;
require_once '..\Login\Model\User.php';

$user = new Model\User();

$user->username='Thienthan23trang';
$user->password='Heihgoiuy869787687';
$user->action=3;
$user->userphone = 2109021;
$user->useremail='Ojo.coo';
$user->text='';
$user->validate();
?>
