<?php
namespace Mii;
use Mii\Login\Model;
use Mii\Login\Service;
require_once '..\Login\Model\User.php';

$user = new Model\User();
$user->unsetUserSession();
$user->checkLogin();
$user->unsetUserCookie();
?>