<?php
namespace Mii;
use Mii\Login\Service;
use Mii\Login\Model;
require_once '..\Login\Service\ValidatorService.php';
require_once '..\Login\Model\User.php';

$user = new Model\User();

$user->form_filled();
$ok = $user->controlFields;
if (isset($ok['rock'])&&$ok['rock']){
    $hello = TRUE;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <form method="POST">
            <input type="text" name="username" value="" />
            <input type="text" name="useremail" value="" />
            <input type="password" name="password" value="" />
            <input type="hidden" name="userid" value="10" />
            <input type="hidden" name="rock" value="" />
            <input type="submit" value="Clickme" name="submit" />
            <input type="checkbox" name="ckb" />
        </form>
    </body>
</html>
