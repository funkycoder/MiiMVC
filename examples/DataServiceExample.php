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
class User extends Model\DataService {

    public function __construct() {
        parent::__construct('userid', array('username', 'password', 'text'), 'users');
    }

}

$user = new User();

$user->username = 'Mii';
$user->password = 'password';
$user->text = 'Text';
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
        <?php echo 'Your new user has id of :' . $user->userid; ?>
        <h1>Retrieving an user from database</h1>
        <?php
        $user->retrieve(39);
        echo 'userid: ' . $user->userid . ', username: ' . $user->username . ' ,password: ' . $user->password . ',text: ' . $user->text;
        ?>
        <h1>Update a user in database</h1>
        <?php
        $user->userid = 39;
        $user->username = 'Quan';
        $user->password = 'New password';
        $user->text = 'Crazy';
        $success = $user->update();
        echo 'Update: ' . (($success) ? 'successful.' : 'not succesful.');
        ?>
        <h1>Delete a user in database</h1>
        <?php
        $user->userid = 51;
        $success = $user->delete();
        echo 'Delete: ' . (($success) ? 'successful.' : 'not succesful.');
        ?>
        <h1>Check a user existed in database</h1>
        <?php
        $user->userid = 0;
        $success = $user->exists(true);
        echo 'User: ' . $user->username . (($success) ? ' existed.' : ' not existed.');
        ?>
        <h1>Retrieving one user from database</h1>
        <?php
        $user = new User();
        $user->retrieve_one("username=?", 'Quan');
        echo 'userid: ' . $user->userid . ', username: ' . $user->username . ' ,password: ' . $user->password . ',text: ' . $user->text;
        ?><br><br>
        <?php
        $user->retrieve_one("username=? AND password=? AND text='Text2'", array('Teddy', 'ok'));
        echo 'userid: ' . $user->userid . ', username: ' . $user->username . ' ,password: ' . $user->password . ',text: ' . $user->text;
        ?>
        <h1>Select user from database</h1>
        <?php
        $user = new User();
        $result_array = $user->select("username,password", "username LIKE ?", 'S%');
        print_r($result_array);
        ?> 
        <h1>Retrieving many users from database</h1>
        <?php
        $user = new User();
        $user_array = $user->retrieve_many("username LIKE ?", 'Q%');
        foreach ($user_array as $user)
            echo 'userid: ' . $user->userid . ', username: ' . $user->username . ' ,password: ' . $user->password . ',text: ' . $user->text . '<br><br>';;
        ?>
    </body>
</html>