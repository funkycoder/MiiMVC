<?php

namespace Mii;
use Mii\Login\Model;

require_once '..\Core\DataModel.php';
require_once '..\Core\ObjectModel.php';
require_once '..\Login\Model\User.php';
require_once '..\Login\Model\UserService.php';

?>
<html>
    <head>
        <meta charset="utf-8">
        <title>Test Results</title>
        <link href="styles/admin.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <?php
        $user = new Model\User();
        $user->username = 'One more now';
        $user->password = 'take it to the max';
        $user->text = 'Text for more';
        echo 'userid: ' . $user->userid . ', username: ' . $user->username . ' ,password: ' . $user->password . ',text: ' . $user->text;
        $user->insert();
        ?>
        <h1>Insert New User</h1>
        <?php echo 'Your new user has id of :' . $user->userid; ?>
        <h1>Retrieving an user from database</h1>
        <?php
        $user->retrieve(6);
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
         $user = new Model\User();
        $user->retrieve_one("username=?", 'Quan');
        echo 'userid: ' . $user->userid . ', username: ' . $user->username . ' ,password: ' . $user->password . ',text: ' . $user->text;
        ?><br><br>
        <?php
        $user->retrieve_one("username=? AND password=?", array('Teddy', 'ok'));
        echo 'userid: ' . $user->userid . ', username: ' . $user->username . ' ,password: ' . $user->password . ',text: ' . $user->text;
        ?>
        <h1>Select user from database</h1>
        <?php
        $user = new Model\User();
        $result_array = $user->select("username,password","username LIKE ?",'Q%');
        print_r($result_array);
        ?> 
        <h1>Retrieving many users from database</h1>
        <?php
        
        $user_array = $user->retrieve_many("username LIKE ?", 'Q%');
        foreach ($user_array as $user)
            echo 'userid: ' . $user->userid . ', username: ' . $user->username . ' ,password: ' . $user->password . ',text: ' . $user->text . '<br><br>';;
        ?>
    </body>
</html>