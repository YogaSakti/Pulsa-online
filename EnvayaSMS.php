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

include ('includes/base.php');
header('Content-Type: application/json; charset=utf-8');

function compute_signature($url, $data, $password)
{
    ksort($data);

    $input = $url;
    foreach ($data as $key => $value)
        $input .= ",$key=$value";
    $input .= ",$password";

    return base64_encode(sha1($input, true));
}

function is_validated($correct_password)
{
    if (isset($_GET['p']) && $_GET['p'] == $correct_password)
    {
        return true;
    }
    $signature = @$_SERVER['HTTP_X_REQUEST_SIGNATURE'];
    if (!$signature)
    {
        return false;
    }

    $is_secure = (!empty($_SERVER['HTTPS']) and filter_var($_SERVER['HTTPS'],
        FILTER_VALIDATE_BOOLEAN));
    $protocol = $is_secure ? 'https' : 'http';
    $full_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    $correct_signature = compute_signature($full_url, $_POST, $correct_password);
    return $signature === $correct_signature;
}
$cfg = get_set(array(
    'envaya_sms',
    'pin',
    ));
$sms_center = isset($_POST['phone_number']) ? $_POST['phone_number'] : false;
$env = json_decode($cfg['envaya_sms']);
if (!is_validated($env->password))
{
    header('HTTP/1.1 401 Unauthorized', true, 401);
    echo '{"error":{"message":"Password Salah"}}';
    exit();
}
elseif (!$sms_center || !ctype_digit($sms_center))
{
    header('HTTP/1.1 401 Unauthorized', true, 401);
    echo '{"error":{"message":"SMS Center \/ Phone Number hanya digit yang diperbolehkan"}}';
    exit();
}

if ($set['smsg_aktif'] <= (time() - 60))
{
    $pdo->query("UPDATE setelan SET set_val = '" . time() .
        "' WHERE set_key = 'smsg_aktif'");
}
$action = isset($_POST['action']) ? $_POST['action'] : '';
switch ($action)
{
    case 'send_status':
        $sid = explode('.', $_POST['id']);
        if ($sid[0] == 'sms')
        {
            $query = $pdo->prepare("UPDATE sms_keluar SET out_status = ?, out_error = ?, out_send_date = ? WHERE out_id = ?");
            $query->execute(array(
                $_POST['status'],
                $_POST['error'],
                time(),
                $sid[1],
                ));
        }
        elseif ($sid[0] == 'isi')
        {
            $query = $pdo->prepare("UPDATE transaksi SET tr_status = ?, tr_terkirim = ? WHERE tr_id = ?");
            $query->execute(array(
                (!empty($_POST['error']) ? 'gagal' : 'sukses'),
                time(),
                $sid[1],
                ));
            if ($query->rowCount() == 1 && empty($_POST['error']))
            {
                $get = $pdo->prepare("SELECT tr_harga FROM transaksi WHERE tr_id = ?");
                $get->execute(array($sid[1]));
                $tr = $get->fetch();
                $sisa_saldo = $set['saldo'] - $tr->tr_harga;
                $upd = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
                $upd->execute(array($sisa_saldo, 'saldo'));
            }
        }
        break;

    case 'incoming':
        $message_type = $_POST['message_type'];
        $message = $_POST['message'];
        $timestamp = $_POST['timestamp'];
        $from = $_POST['from'];
        if ($message_type == 'sms')
        {
            $query = $pdo->prepare("INSERT INTO sms_masuk (in_from, in_message, in_timestamp) VALUES(?, ?, ?)");
            $query->execute(array(
                $from,
                $message,
                substr($timestamp, 0, 10),
                ));
        }
        break;
}
$query = $pdo->query("SELECT * FROM transaksi WHERE tr_status_pembayaran = 'success' AND tr_status = 'pending' ORDER BY tr_id ASC LIMIT 10");
$sms = array();
$sms_id = array();
if ($query->rowCount())
{
    $trxs = $query->fetchAll();
    foreach ($trxs as $trx)
    {
        $sms_id[] = $trx->tr_id;
        $sms[] = array(
            'id' => "isi." . $trx->tr_id,
            'to' => $sms_center,
            'message' => strtr($produk->{$trx->op_produk}->format_trx, array(
                '{KODE}' => $trx->vo_kode,
                '{NO_HP}' => $trx->tr_no_hp,
                '{ID_PLN}' => $trx->tr_id_pln,
                '{PIN}' => $cfg['pin'],
                )),
            );
    }
}
if ($sms_id)
{
    $pdo->query("UPDATE transaksi SET tr_status = '-' WHERE tr_id IN (" . implode(', ',
        $sms_id) . ")");
}
$query1 = $pdo->query("SELECT * FROM sms_keluar WHERE out_status = '' ORDER BY out_id ASC LIMIT 10");
$sms1_id = array();
if ($query1->rowCount())
{
    $trxs1 = $query1->fetchAll();
    foreach ($trxs1 as $trx)
    {
        $sms1_id[] = $trx->out_id;
        $sms[] = array(
            'id' => "sms." . $trx->out_id,
            'to' => $trx->out_to,
            'message' => $trx->out_message,
            );
    }
}
if ($sms1_id)
{
    $pdo->query("UPDATE sms_keluar SET out_status = '-' WHERE out_id IN (" . implode
        (', ', $sms1_id) . ")");
}
echo json_encode(array('events' => array(array(
            'event' => 'send',
            'messages' => $sms,
            ))));
