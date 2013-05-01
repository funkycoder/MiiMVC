<?php use Mii\Lib;
if ($SHOW_LOGIN_FORM):
?>
<div class="title">
    <h4>Login</h4>
</div>
<form action="<?php echo ACTION_URL ?>?action=login" method="post" accept-charset="utf-8">
    <p>
        <?php
        if (isset($user->errors['login'])) {
            echo '<span class="error">' . $user->errors['login'] . '</span><br />';
        }
        ?>
        <label for="useremail"><strong>Email Address/ Username</strong></label><br />
        <?php Lib\Form::create_form_input('useremail', $user->useremail, 'text', $user->errors); ?><br />
        <label for="password"><strong>Password</strong></label><br />
        <?php Lib\Form::create_form_input('password', $user->password, 'password', $user->errors); ?><br/>
        <a href="forgot_password.php" align="right">Quên mật khẩu?</a><br />
        <input type="submit" value="Login &rarr;">
    </p>
</form>
<?php endif;?>