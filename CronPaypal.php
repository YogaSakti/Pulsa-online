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

//@ignore_user_abort(true);

include ('includes/base.php');

$metode_pembayaran = json_decode($set['metode_pembayaran']);
$last_date = (int)$set['paypal_lastdate'];
$maks_jam = time() - (3600 * (int)$set['jam_pembayaran']);
$q = $pdo->query("SELECT MIN(tr_tanggal) AS tanggal FROM transaksi WHERE tr_pembayaran = 'paypal' AND tr_status_pembayaran = 'pending' AND tr_tanggal > $maks_jam");
$last = $q->fetch();
if ($last_date == 0)
{
    $last_date = !is_null($last->tanggal) ? $last->tanggal : 0;
}
if (!$last_date || is_null($last->tanggal))
{
    echo 'Tidak ada pembayaran tertunda';
    exit();
}
$last_date = $last_date - (3600 * 24);
$start_date = date('Y-m-d', $last_date) . 'T00:00:00Z';
$postdata = implode('&', array(
    'USER=' . urlencode($metode_pembayaran->paypal->api->username),
    'PWD=' . urlencode($metode_pembayaran->paypal->api->password),
    'SIGNATURE=' . urlencode($metode_pembayaran->paypal->api->signature),
    'METHOD=TransactionSearch',
    'TRANSACTIONCLASS=Received',
    'STATUS=Success',
    'CURRENCYCODE=USD',
    'STARTDATE=' . urlencode($start_date),
    'VERSION=94',
    ));

$curl = curl_init('https://api-3t.paypal.com/nvp');
curl_setopt($curl, CURLOPT_FAILONERROR, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
curl_setopt($curl, CURLOPT_HEADER, 0);
curl_setopt($curl, CURLOPT_POST, 1);
$result = curl_exec($curl);

parse_str($result, $params);
$total = (count($params) - 5) / 11;
echo "Results " . $total . " transactions<br/>";
if ($params['ACK'] != 'Success' || !$total)
{
    exit();
}
$date = date('Y-m-d', time());
$q = $pdo->query("SELECT * FROM paypal_trx WHERE trx_date = '$date'");
$count_paypal_trx = $q->rowCount();

if ($count_paypal_trx)
{
    $trx_data = json_decode($q->fetch()->trx_data, true);
}
else
{
    $trx_data = array();
}

$trx_id = array();

for ($i = 0; $i < $total; $i++)
{
    $_trx_id = $params["L_TRANSACTIONID" . $i];
    if (!in_array($_trx_id, $trx_data))
    {
        $trx_id[] = $_trx_id;
    }
}
$pro_trx_id = array_slice($trx_id, 0, 5);

if ($pro_trx_id)
{
    echo "GetTransactionDetails<br/>";
    $inv_id = array();
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_FAILONERROR, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_HEADER, 0);

    for ($i = 0; $i < count($pro_trx_id); $i++)
    {
        $postdata = implode('&', array(
            'USER=' . urlencode($metode_pembayaran->paypal->api->username),
            'PWD=' . urlencode($metode_pembayaran->paypal->api->password),
            'SIGNATURE=' . urlencode($metode_pembayaran->paypal->api->signature),
            'METHOD=GetTransactionDetails',
            'TRANSACTIONID=' . $pro_trx_id[$i],
            'VERSION=94',
            ));
        $url = 'https://api-3t.paypal.com/nvp';
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
        $result1 = curl_exec($curl);
        parse_str($result1, $params1);
        if ($params1['ACK'] == 'Success' && isset($params1['NOTE']))
        {
            $params1['NOTE'] = strtolower($params1['NOTE']);
            preg_match('/(.*?)inv([0-9]{6}+)(.*?)$/', $params1['NOTE'], $matches,
                PREG_OFFSET_CAPTURE);
            if ($matches)
            {
                $inv_id['inv' . $matches[2][0]] = $params1;
            }
        }
    }
    curl_close($curl);
    echo "Processing " . count($inv_id) . " transactions<br/>";
    if ($inv_id)
    {
        $q = $pdo->query("SELECT * FROM transaksi WHERE tr_pembayaran = 'paypal' AND tr_status_pembayaran = 'pending'  AND tr_tanggal > $maks_jam AND tr_id_pembayaran IN('" .
            implode("','", array_keys($inv_id)) . "')");
        if ($q->rowCount())
        {
            $lunas = array();
            $refund = array();
            foreach ($q->fetchAll() as $trx)
            {
                if ($inv_id[$trx->tr_id_pembayaran]['ACK'] == 'Success')
                {
                    $harga = round($trx->tr_harga / $trx->tr_rate, 2);
                    $jumlah_trf = $inv_id[$trx->tr_id_pembayaran]['AMT'] - $inv_id[$trx->
                        tr_id_pembayaran]['FEEAMT'];
                    if ($inv_id[$trx->tr_id_pembayaran]['PAYMENTSTATUS'] == 'Completed' && stripos($inv_id[$trx->
                        tr_id_pembayaran]['NOTE'], $trx->tr_id_pembayaran) !== false)
                    {
                        if ($jumlah_trf == $harga)
                        {
                            $lunas[] = $trx->tr_id;
                        }
                        else
                        {
                            $refund[] = $trx->tr_id;
                            $catatan = 'Jumlah yang harus dibayarkan adalah $' . $harga .
                                ' USD, tetapi kami hanya menerima $' . $jumlah_trf .
                                ' USD setelah dipotong pajak sebesar $' . $inv_id[$trx->tr_id_pembayaran]['FEEAMT'] .
                                ' USD.';
                            $postdata = implode('&', array(
                                'USER=' . urlencode($metode_pembayaran->paypal->api->username),
                                'PWD=' . urlencode($metode_pembayaran->paypal->api->password),
                                'SIGNATURE=' . urlencode($metode_pembayaran->paypal->api->signature),
                                'METHOD=RefundTransaction',
                                'TRANSACTIONID=' . $inv_id[$trx->tr_id_pembayaran]['TRANSACTIONID'],
                                'PAYERID=' . $inv_id[$trx->tr_id_pembayaran]['PAYERID'],
                                'CURRENCYCODE=USD',
                                'REFUNDTYPE=Full',
                                'NOTE=' . urlencode($catatan),
                                'VERSION=94',
                                ));

                            $curl = curl_init('https://api-3t.paypal.com/nvp');
                            curl_setopt($curl, CURLOPT_FAILONERROR, true);
                            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
                            curl_setopt($curl, CURLOPT_HEADER, 0);
                            curl_setopt($curl, CURLOPT_POST, 1);
                            $result = curl_exec($curl);
                        }
                    }
                }
            }
            if ($lunas)
            {
                echo "Completing " . count($lunas) . " selling<br/>";
                $pdo->query("UPDATE transaksi SET tr_status_pembayaran = 'success' WHERE tr_id IN(" .
                    implode(",", $lunas) . ")");
            }
            if ($refund)
            {
                echo "Refund " . count($refund) . " selling<br/>";
                $pdo->query("UPDATE transaksi SET tr_status_pembayaran = 'refund', tr_status = 'gagal' WHERE tr_id IN(" .
                    implode(",", $refund) . ")");
            }
        }
    }

    if ($count_paypal_trx)
    {
        $trx_data = array_merge($trx_data, $pro_trx_id);
        $q = $pdo->prepare("UPDATE paypal_trx SET trx_data = ? WHERE trx_date = ?");
        $q->execute(array(json_encode($trx_data), $date));
    }
    else
    {
        $trx_data = array_merge($trx_data, $pro_trx_id);
        $q = $pdo->prepare("INSERT INTO paypal_trx (trx_data, trx_date) VALUES (?, ?)");
        $q->execute(array(json_encode($trx_data), $date));
    }
}
if (count($pro_trx_id) == count($trx_id))
{
    echo "Updating PayPal lasdate<br/>";
    $pdo->query("UPDATE setelan SET set_val = '" . time() .
        "' WHERE set_key = 'paypal_lastdate'");
}

exit();
