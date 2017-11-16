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

define('INC_PATH', dirname(__file__));
define('APP_PATH', dirname(INC_PATH));

@ini_set('session.use_trans_sid', '0');
@ini_set('arg_separator.output', '&amp;');
date_default_timezone_set('UTC');
@mb_internal_encoding('UTF-8');

session_name('sess');
session_start();

if (!file_exists(INC_PATH . '/config.php'))
{
    die('File includes/config.php tidak ditemukan.');
}
include (INC_PATH . '/config.php');
include (INC_PATH . '/koneksi.php');
include (INC_PATH . '/fungsi.php');
$set = get_set();
if ($set['maintenance'] == 'on' && !defined('ADM_PANEL'))
{
    header("Location: " . $set['site_url'] . "maintenance.html");
    exit();
}
$produk = json_decode($set['produk']);

define('SITE_URL', $set['site_url']);
$page = isset($_REQUEST['page']) && (ctype_digit($_REQUEST['page'])) && ($_REQUEST['page'] >
    0) ? intval($_REQUEST['page']) : 1;
$start = isset($_REQUEST['page']) ? $page * $set['list_per_page'] - $set['list_per_page'] :
    0;
