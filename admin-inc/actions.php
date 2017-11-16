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

switch ($a)
{
    case 'set_produk_status':
        $pr = isset($_GET['produk']) ? $_GET['produk'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        if (array_key_exists($pr, $produk) && in_array($status, array('on', 'off')))
        {
            $produk->{$pr}->status = $status;
            $q = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
            $q->execute(array(json_encode($produk), 'produk'));
        }
        header("Location: " . ADM_URL . "?c=index#produk");
        exit();
        break;

    default:
        header("Location: " . ADM_URL . "?c=index#produk");
        exit();
        break;
}
