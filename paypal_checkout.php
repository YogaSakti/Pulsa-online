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
die('Untuk mengaktifkan PayPal Express Checkout silakan hapus LINE ' . __LINE__);

include ('includes/base.php');
$metode_pembayaran = json_decode($set['metode_pembayaran']);
$id = isset($_GET['id']) ? intval($_GET['id']) : '';
$token = isset($_GET['token']) ? strip_tags($_GET['token']) : '';
$payer_id = isset($_GET['PayerID']) ? strip_tags($_GET['PayerID']) : '';

$q = $pdo->prepare("SELECT * FROM transaksi WHERE tr_id = ?");
$q->execute(array($id));
if ($q->rowCount() != 1 || $metode_pembayaran->paypal->status == 'off')
{
    header("Location: " . $set['site_url'] . "status.php");
    exit();
}
$trx = $q->fetch();
if ($trx->tr_pembayaran != 'paypal' || $trx->tr_status_pembayaran != 'pending' ||
    $trx->tr_status != 'pending')
{
    header("Location: " . $set['site_url'] . "status.php?id=" . $trx->tr_id);
    exit();
}

$pajak = ($trx->tr_harga * 4.4) / 100;
$pajak = round($pajak / $trx->tr_rate, 2) + 0.3;

$paypal_details = array(
    'API_username' => $metode_pembayaran->paypal->api->username,
    'API_password' => $metode_pembayaran->paypal->api->password,
    'API_signature' => $metode_pembayaran->paypal->api->signature,
    'sandbox_status' => false,
    );
include ('includes/paypal_ec.php');
$pp = new paypal_ec($paypal_details);

if ($token && $payer_id)
{
    $get_ec_return = $pp->get_ec($token);
    if (isset($get_ec_return['ec_status']) && ($get_ec_return['ec_status'] === true))
    {
        if ($get_ec_return['L_PAYMENTREQUEST_0_NUMBER0'] != $trx->tr_id)
        {
            header("Location: " . $set['site_url'] . "status.php?id=" . $trx->tr_id);
            exit();
        }
        $ec_details = array(
            'token' => $token,
            'payer_id' => $payer_id,
            'currency' => 'USD',
            'amount' => $get_ec_return['PAYMENTREQUEST_0_AMT'],
            'IPN_URL' => $set['site_url'] . 'paypal_checkout.php?action=ipn&id=' . $trx->
                tr_id . '&key=',
            'type' => 'Sale',
            );

        $do_ec_return = $pp->do_ec($ec_details);
        if (isset($do_ec_return['ec_status']) && ($do_ec_return['ec_status'] === true))
        {
            if ($do_ec_return['ACK'] == 'Success' && in_array($do_ec_return['PAYMENTINFO_0_PAYMENTSTATUS'],
                array('Completed')))
            {
                $q = $pdo->prepare("UPDATE transaksi SET tr_status_pembayaran = ? WHERE tr_id = ?");
                $q->execute(array('success', $trx->tr_id));
            }
            header("Location: " . $set['site_url'] . "status.php?id=" . $trx->tr_id);
            exit();
        }
        else
        {
            header("Location: " . $set['site_url'] . "status.php?id=" . $trx->tr_id);
            exit();
        }
    }
    else
    {
        die('Gagal memproses pembayaran!');
    }
}
else
{

    $to_buy = array(
        'currency' => 'USD',
        'type' => 'Sale',
        'return_URL' => $set['site_url'] . 'paypal_checkout.php?id=' . $trx->tr_id,
        'cancel_URL' => $set['site_url'] . 'status.php?id=' . $trx->tr_id,
        'tax_amount' => $pajak,
        'products' => array(array(
                'name' => $trx->op_nama . ' ' . $trx->vo_nominal,
                'desc' => $produk->{$trx->op_produk}->nama . ' ' . $trx->op_nama . ' ' . $trx->
                    vo_nominal . ' ' . substr($trx->tr_no_hp, 0, -3) . 'XXX',
                'quantity' => 1,
                'number' => $trx->tr_id,
                'amount' => round($trx->tr_harga / $trx->tr_rate, 2),
                )),
        );
    $set_ec_return = $pp->set_ec($to_buy);
    if (isset($set_ec_return['ec_status']) && ($set_ec_return['ec_status'] === true))
    {
        $pp->redirect_to_paypal($set_ec_return['TOKEN']);
    }
    else
    {
        die('Gagal memproses permintaan!');
    }
}
