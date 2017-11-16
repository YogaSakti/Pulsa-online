<?php

/**
 * @package Script Pulsa Online
 * @version 1
 * @author Engky Datz
 * @link http://okepulsa.id
 * @link http://facebook.com/Engky09
 * @link http://okepulsa.id
 * @link https://www.bukalapak.com/engky09
 * @copyright 2015 -2016
 */

defined('ADM_INC') or die('Akses Terlarang!');

if (isset($_POST['submit']))
{
    if ($admin->username == @$_POST['adm_username'] && base64_decode($admin->
        password) == @$_POST['adm_password'])
    {
        $_SESSION['adm_user'] = $_POST['adm_username'];
        $_SESSION['adm_pass'] = base64_encode($_POST['adm_password']);
        header("Location: ?");
        exit();
    }
    else
    {
        $error = 'Nama Pengguna atau Kata Sandi tidak benar.';
    }
}
include (APP_PATH . '/includes/header.php');
echo (isset($error) ? '<div class="alert alert-danger">' . $error . '</div>' :
    '');
echo '<form class="form-horizontal" action="' . ADM_URL . '?c=masuk"' .
    ' method="post">';
echo '<div class="form-group"><label for="adm_username"' .
    ' class="col-sm-3 control-label">Nama Pengguna</label><div class="col-sm-9">' .
    '<input type="text" class="form-control" name="adm_username" id="adm_username"' .
    ' maxlength="12" required/></div></div>';
echo '<div class="form-group">' .
    '<label for="adm_password" class="col-sm-3 control-label">Kata Sandi</label>' .
    '<div class="col-sm-9"><input type="password" class="form-control" name="adm_password"' .
    ' id="adm_password" maxlength="12" required/></div></div><div class="form-group">' .
    '<div class="col-sm-offset-3 col-sm-9"><button type="submit" name="submit" ' .
    'class="btn btn-primary" value="1">Masuk</button></div></div></form>';
