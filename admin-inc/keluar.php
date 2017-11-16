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

session_destroy();
header("Location: " . ADM_URL . "?c=masuk");
exit();
