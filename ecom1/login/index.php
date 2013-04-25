<?php

namespace Ecom1;

use Ecom1\Login\Model;

require 'Model/User.php';
include BASE_URI . 'includes/header.html.php';

$user = new Model\User();
//logged-in?
if ($user->checkLogin()) {
    //yes! user want to update ther profile
    $title = 'Update Profile';
    $action = 'update';
    if (isset($_GET['update']) && $_GET['action'] == 'update') {
        //they submit the update form?
        if (isset($_POST)) {
            //get the data now
            $user->form_filled();
            //update
            if ($user->update()) {
                //success! get the updated info
                //login using the new info
                $user->login();
                //to the main page
                $user->redirect();
            }
            //update failed. Must be some error
            else {
                include BASE_URI . 'login/View/form.html.php';
            }
        }
        //display the form for update
        else {
            include BASE_URI . 'login/View/form.html.php';
        }
    }// end of checking update action
    //they don't want to update profile, then why require this page?
    //redirect to homepage
    else {
        $user->redirect();
        exit();
    }
}
//Not logged-in yet
else {
    //get the input data now
    $user->form_filled();
    if (isset($_GET['action']) && $_GET['action'] == 'login') {
        if ($user->login()) {
            $user->redirect();
        } else {
            echo 'Login error </br>';
        }
    } elseif (isset($_GET['action']) && $_GET['action'] == 'register') {
        $title = 'Register a new account';
        $action = 'register';
        //they submit the register form?
        if (isset($_POST)) {
            //get the data now
            $user->form_filled();
            //update
            if ($user->register()) {
                //success! get the updated info
                //login using the new info
                //TODO what do you want after the user registered?
                $user->login();
                //to the main page
                $user->redirect();
            }
            //Registered failed. Must be some error
            else {
                include BASE_URI . 'login/View/form.html.php';
            }
        }
        //display the form for register
        else {
            include BASE_URI . 'login/View/form.html.php';
        }
    }
}

include BASE_URI . 'includes/footer.html.php';
?>