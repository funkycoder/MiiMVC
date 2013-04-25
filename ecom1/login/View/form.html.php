<?php
use Mii\Lib;

require_once MII_URI . 'lib/Form.inc.php';
$errors = $user->errors;
$actionURI = BASE_URI . 'login/index.php';
?>
<div class="title">
    <h3><?php echo $title ?></h3>
</div>
<form action="<?php echo $actionURI ?>?action=<?php echo $action ?>" method="post" accept-charset="utf-8">
    <p>
        <?php
        if (array_key_exists($action, $errors)) {
            echo '<span class="error">' . $errors[$action] . '</span><br />';
        }
        ?>
    </p>
    <p><label for="first_name"><strong>First Name</strong></label><br /><?php Lib\Mii_Form::create_form_input('first_name', $user->first_name, 'text', $errors); ?></p>
    <p><label for="last_name"><strong>Last Name</strong></label><br /><?php Lib\Mii_Form::create_form_input('last_name', $user->last_name, 'text', $errors); ?></p>
    <p><label for="username"><strong>Username</strong></label><br /><?php Lib\Mii_Form::create_form_input('username', $user->username, 'text', $errors); ?> <small>Tên bao gồm các ký tự A-Z không có dấu, không có khoảng trắng với độ dài 4-100 ký tự.</small></p>
    <p><label for="useremail"><strong>Email Address</strong></label><br /><?php Lib\Mii_Form::create_form_input('useremail', $user->useremail, 'text', $errors); ?></p>
    <?php
    if ($action == 'update') {
        echo '<p><label for="password"><strong>Current Password</strong></label><br />';
        Lib\Mii_Form::create_form_input('password', $user->password, 'password', $errors);
    }
    ?>
    <p><label for="pass1"><strong>New Password</strong></label><br /><?php Lib\Mii_Form::create_form_input('pass1', '', 'password', $errors); ?> <small>Mật khẩu dài tối thiểu 8 ký tự và có ít nhất 1 chữ số, 1 chữ hoa và 1 chữ thường.</small></p>
    <p><label for="pass2"><strong>Confirm Password</strong></label><br /><?php Lib\Mii_Form::create_form_input('pass2', '', 'password', $errors); ?></p>
    <input type="submit" name="submit_button" value="Next &rarr;" id="submit_button" class="formbutton" />
</form>