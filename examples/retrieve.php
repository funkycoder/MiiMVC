<?php
namespace Mii;
use Mii\Login\Model;
use Mii\Login\Service;
require_once '..\Login\Service\ValidatorService.php';
require_once '..\Login\Model\User.php';

$user = new Model\User();

$temp = $user->retrieve_many('username LIKE ?', 'Q%');

//$temp2 = Model\User::retrieve_one('username LIKE ?', 'Q%');
?>