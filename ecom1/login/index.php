<?php

namespace Ecom1;

require '..\..\config\AppConfigEcom1.php';
require MII_URI . 'lib/Form.inc.php';

use Ecom1\Login\Model; //Get the user class

require 'Model/User.php';
$user = new Model\User();

define('ACTION_URL', BASE_URL . '/login/index.php'); // This page is the command center 
DEFINE('VIEW_FORM', BASE_URI . 'login/View/form.html.php');
DEFINE('VIEW_LOGIN', BASE_URI . 'login/View/login.html.php');
$SHOW_LOGIN_FORM = TRUE;
$message = ''; //Display message to content area

include VIEW_HEADER; //Get the header
//logged-in?
if ($user->checkLogin()) {
    $SHOW_LOGIN_FORM = FALSE;
    if (isset($_GET['action']) && $_GET['action'] == 'update') {
        //yes! user want to update ther profile
        $title = 'Update Profile';
        $action = 'update';
        //they submit the update form?
        if ($_POST) {
            //cancel action?
            if (isset($_POST['cancel_button'])) {
                $user->properties = $_SESSION['user'];
                $user->redirect();
                exit();
            }
            //get the data now
            $user->form_filled();
            //update
            if ($user->validate() && $user->update()) {
                $message = 'Cập nhật thành công.';
                $_SESSION['user'] = $user->properties; //update session
            }
        }// end of checking update action
        include VIEW_FORM;
//they don't want to update profile, then why require this page?
//redirect to homepage
    } else {
        $user->redirect();
        exit();
    }
}
//Not logged-in yet
else {
    if (isset($_GET['action']) && $_GET['action'] == 'login') {
        //get the input data now
        $user->form_filled();
        $USE_EMAIL = TRUE;
        $useremailOK = $user->checkFormat('useremail', $user->useremail, FALSE);
        if (!$useremailOK) {
            $user->username = $user->useremail;
            $usernameOK = $user->checkFormat('username', $user->username, FALSE);
            if ($usernameOK) {
                $USE_EMAIL = FALSE;
            } else {
                $user->setError('useremail', 'Vui lòng kiểm tra lại.');
            }
        }
        $passwordOK = $user->checkFormat('password', $user->password);
        if (!$passwordOK)
            $user->setError('password', 'Vui lòng kiểm tra lại.');
        $validateOK = ($useremailOK || $usernameOK) && $passwordOK;
        if ($validateOK) {
            if ($user->login($USE_EMAIL)) {
                $user->redirect();
                exit();
            }
        }
        $message = 'Đăng nhập thất bại.';
    } elseif (isset($_GET['action']) && $_GET['action'] == 'register') {
        $title = 'Register a new account';
        $action = 'register';
        $SHOW_LOGIN_FORM = FALSE;
        //they submit the register form?
        if ($_POST) {
            //get the data now
            $user->form_filled();
            //Validate ok and register ok?
            if ($user->validate() && $user->register()) {
                //TODO what do you want after the user registered?
                $user->login();
                //to the main page
                $user->redirect();
                exit();
            }
        }// there must be some error while proceeding the registration
        //display the form and errors (if any)
        include VIEW_FORM;
    }// end registration
}//not logged-in , just come here by url. Display nothing in the content section
echo $message;
include VIEW_FOOTER;
?>