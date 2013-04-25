<?php
use Mii\Lib;
require_once MII_URI . 'lib/Form.inc.php';

$errors = isset($user->errors)? $user->errors : array();
$actionURI = BASE_URI . 'login/index.php';
$title = isset($title)? $title : 'Login';
?>
<div class="title">
    <h4><?php echo $title; ?></h4>
</div>
<form action="<?php echo $actionURI ?>?action=login" method="post" accept-charset="utf-8">
    <p>
        <?php
        if (array_key_exists('login', $errors)) {
            echo '<span class="error">' . $errors['login'] . '</span><br />';
        }
        ?>
        <label for="useremail"><strong>Email Address/ Username</strong></label><br />
        <?php Lib\Mii_Form::create_form_input('useremail', $user->useremail, 'text', $errors); ?><br />
        <label for="password"><strong>Password</strong></label><br />
        <?php Lib\Mii_Form::create_form_input('password', $user->password, 'password', $errors); ?> 
        <a href="forgot_password.php" align="right">Forgot?</a><br />
        <input type="submit" value="Login &rarr;">
    </p>
</form>