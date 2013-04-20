<?php
namespace Mii;
use Mii\Login\Model;
require_once '..\Login\Model\User.php';

$user = new Model\User();
//$user->userid=1;
$user->useremail = 'hoadiemt@gmail.com';
$user->username='Thienlu';
$user->first_name='Quan';
$user->last_name='Nguyen';
$user->password='1234Pa5678';
$user->userphone='1234567543548';
$user->type='member';

//$user->setControlField('remember', 'on');
$user->validate();
$user->insert();
//$user->login();
//$user->checkLogin();
//$user->validate();
//$user->update();
?>