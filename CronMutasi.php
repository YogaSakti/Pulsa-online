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

//@ini_set('display_errors', false);
@ignore_user_abort(true);

header("Content-Type: text/plain");
include ('includes/base.php');
$metode_pembayaran = json_decode($set['metode_pembayaran'], true);

$trx = array();
$transactions = array();

$maks_jam = time() - (3600 * (int)$set['jam_pembayaran']);
$q = $pdo->query("SELECT * FROM transaksi WHERE tr_pembayaran != 'paypal' AND tr_status_pembayaran = 'pending' AND tr_status = 'pending' AND tr_tanggal > $maks_jam ORDER BY tr_cek_mutasi ASC, tr_id DESC LIMIT 20");

if ($q->rowCount() == 0)
{
    die('Tidak ada pembayaran melalui bank yang tertunda');
}

$_trxs = $q->fetchAll(PDO::FETCH_ASSOC);
foreach ($_trxs as $_trx)
{
    $transactions[$_trx['tr_pembayaran']][$_trx['tr_harga']] = $_trx;
    $trx[] = $_trx['tr_id'];
}

foreach ($metode_pembayaran as $met_key => $met_val)
{
    if (!isset($transactions[$met_key]) || $met_val['status'] == 'off' || !
        file_exists(INC_PATH . '/mutasi/' . $met_key . '.php') || !is_file(INC_PATH .
        '/mutasi/' . $met_key . '.php') || ($metode_pembayaran[$met_key]['mutasi']['terakhir'] >
        (time() - (60 * $metode_pembayaran[$met_key]['mutasi']['durasi']))))
    {
        continue;
    }
    include_once (INC_PATH . '/mutasi/' . $met_key . '.php');
    $lunas = $met_key($met_val, $transactions[$met_key]);
    echo $metode_pembayaran[$met_key]['nama'] . ": " . count($lunas) . "\r\n";
    $metode_pembayaran[$met_key]['mutasi']['terakhir'] = time();
}

$pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?")->execute(array
    (json_encode($metode_pembayaran), 'metode_pembayaran'));

if ($trx)
{
    $pdo->query("UPDATE transaksi SET tr_cek_mutasi = '" . time() .
        "' WHERE tr_id IN(" . implode(", ", $trx) . ")");
}
exit();
