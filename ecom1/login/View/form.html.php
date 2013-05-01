<?php

 use Mii\Lib; ?>
<div class="title">
    <h3><?php echo $title ?></h3>
</div>
<form action="<?php echo ACTION_URL ?>?action=<?php echo $action ?>" method="post" accept-charset="utf-8">
    <p>
        <?php
        if (isset($user->errors[$action])) {
            echo '<span class="error">' . $user->errors[$action] . '</span><br />';
        }
        ?>
    </p>
    <p><label for="first_name"><strong>First Name</strong></label><br /><?php Lib\Form::create_form_input('first_name', $user->first_name, 'text', $user->errors); ?></p>
    <p><label for="last_name"><strong>Last Name</strong></label><br /><?php Lib\Form::create_form_input('last_name', $user->last_name, 'text', $user->errors); ?></p>
    <p><label for="username"><strong>Username</strong></label><br /><?php Lib\Form::create_form_input('username', $user->username, 'text', $user->errors); ?> <small>Tên bao gồm các ký tự A-Z không có dấu, không có khoảng trắng với độ dài 4-100 ký tự.</small></p>
    <p><label for="useremail"><strong>Email Address</strong></label><br /><?php Lib\Form::create_form_input('useremail', $user->useremail, 'text', $user->errors); ?></p>
    <p><label for="useremail"><strong>Phone number</strong></label><br /><?php Lib\Form::create_form_input('userphone', $user->userphone, 'text', $user->errors); ?></p>
    <?php
    if ($action == 'update') {
        echo '<p><label for="password"><strong>Current Password</strong></label><br />';
        Lib\Form::create_form_input('password', $user->password, 'password', $user->errors);
    }
    ?>
    <p><label for="pass1"><strong>New Password</strong></label><br /><?php Lib\Form::create_form_input(($action=='register')? 'password':'pass1', '', 'password', $user->errors); ?> <small>Mật khẩu dài tối thiểu 8 ký tự và có ít nhất 1 chữ số, 1 chữ hoa và 1 chữ thường.</small></p>
    <p><label for="pass2"><strong>Confirm Password</strong></label><br /><?php Lib\Form::create_form_input('pass2', '', 'password', $user->errors); ?></p>
    <input type="submit" name="submit_button" value="OK" id="submit_button" class="formbutton" />
     <?php
    if ($action == 'update'):?>
         <input type="submit" name="cancel_button" value="Hủy" id="cancel_button" class="formbutton" />
    <?php endif;?>
</form>