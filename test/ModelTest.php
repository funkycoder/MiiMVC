<?php
namespace Mii;
use \Mii\Model;
require_once '../core/DataService.php';
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ModelTest
 *
 * @author Quan Nguyen
 */
class User extends Model\DataService{
    public function __construct() {
        parent::__construct('id',array('username','password','timestamp'),'users');
    }
}
$user = new User();

$user->username = 'Mii2';
$user->password = 'password';
$user->timestamp= '123456';
$user->insert();


?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <title>Test Results</title>
        <link href="styles/admin.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <h1>Insert New User</h1>
       <?php echo 'Your new user has id of :' . $user->id.' '. $user->username ;?>
    </body>
</html>