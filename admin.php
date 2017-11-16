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

define('ADM_PANEL', true);
include ('includes/base.php');
define('ADM_URL', SITE_URL . basename(__file__)); // http://domain.tld/admin.php
define('ADM_INC', dirname(__file__) . '/admin-inc');

$page_title = 'Admin Panel | ' . $set['site_name'];
$c = isset($_GET['c']) ? $_GET['c'] : '';
$a = isset($_GET['a']) ? $_GET['a'] : '';
$id = isset($_GET['id']) ? abs(intval($_GET['id'])) : '';
$adm_user = isset($_SESSION['adm_user']) ? $_SESSION['adm_user'] : '';
$adm_pass = isset($_SESSION['adm_pass']) ? base64_decode($_SESSION['adm_pass']) :
    '';
$admin = json_decode(get_set('admin'));
if (($adm_user != $admin->username || $adm_pass != base64_decode($admin->
    password)) && $c != 'masuk')
{
    header("Location: " . ADM_URL . "?c=masuk");
    exit();
}
$controllers = array(
    'berita',
    'operator',
    'keluar',
    'masuk',
    'transaksi',
    'setelan',
    'index',
    'quick_action',
    'sms',
    'testimonial',
    'feedback',
    'actions',
    );
if (in_array($c, $controllers) && file_exists(ADM_INC . '/' . $c . '.php') &&
    is_file(ADM_INC . '/' . $c . '.php'))
{
    include (ADM_INC . '/' . $c . '.php');
}
else
{
    include (ADM_INC . '/index.php');
}
include (APP_PATH . '/includes/footer.php');
