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

if ($a == 'edit')
{
    $q = $pdo->query("SELECT * FROM transaksi WHERE tr_id = '$id'");
    if ($q->rowCount() == 0)
    {
        header("Location: " . ADM_URL . "?c=transaksi&a=" . urlencode(@$_GET['f']) .
            "&page=" . $page);
        exit();
    }
    $trx = $q->fetch();
    if (isset($_POST['hapus']))
    {
        $q = $pdo->prepare("DELETE FROM transaksi WHERE tr_id = ?");
        $q->execute(array($trx->tr_id));
        header("Location: " . ADM_URL . "?c=transaksi&a=" . urlencode(@$_GET['f']) .
            "&page=" . $page . "#trx-" . $trx->tr_id);
        exit();
    }
    elseif (isset($_POST['submit']))
    {
        $sts = array(
            'sukses' => time(),
            'pending' => $trx->tr_terkirim,
            'gagal' => $trx->tr_terkirim,
            );
        if (array_key_exists($_POST['status'], $sts) && in_array($_POST['status1'],
            array(
            'success',
            'pending',
            'refund',
            )))
        {
            $q = $pdo->prepare("UPDATE transaksi SET tr_status_pembayaran = ?, tr_status = ?, tr_terkirim = ? WHERE tr_id = ?");
            $q->execute(array(
                $_POST['status1'],
                $_POST['status'],
                $sts[$_POST['status']],
                $trx->tr_id,
                ));
        }
        header("Location: " . ADM_URL . "?c=transaksi&a=" . urlencode(@$_GET['f']) .
            "&page=" . $page . "#trx-" . $trx->tr_id);
        exit();
    }
    include (APP_PATH . '/includes/header.php');
    echo '<form class="form-horizontal" action="' . ADM_URL .
        '?c=transaksi&amp;a=edit&amp;id=' . $id . '&amp;f=' . urlencode(@$_GET['f']) .
        '&amp;page=' . $page . '" method="post">';
    echo '<div class="form-group">' .
        '<label class="col-sm-3 control-label">Status Pembayaran</label>' .
        '<div class="col-sm-9"><select class="form-control" name="status1">';
    echo '<option value=""> </option><option value="success"' . ($trx->
        tr_status_pembayaran == 'success' ? ' selected="selected"' : '') .
        '>Sukses</option><option value="pending"' . ($trx->tr_status_pembayaran ==
        'pending' ? ' selected="selected"' : '') .
        '>Pending</option><option value="refund"' . ($trx->tr_status_pembayaran ==
        'refund' ? ' selected="selected"' : '') .
        '>Refund</option></select></div></div>';

    echo '<div class="form-group">' .
        '<label class="col-sm-3 control-label">Status Pengisian Pulsa</label>' .
        '<div class="col-sm-9"><select class="form-control" name="status">';
    echo '<option value=""> </option><option value="sukses"' . ($trx->tr_status ==
        'sukses' ? ' selected="selected"' : '') .
        '>Sukses</option><option value="pending"' . ($trx->tr_status == 'pending' ?
        ' selected="selected"' : '') . '>Pending</option><option value="gagal"' . ($trx->
        tr_status == 'gagal' ? ' selected="selected"' : '') . '>Gagal</option>';
    echo '</select></div></div><div class="form-group">' .
        '<div class="col-sm-offset-3 col-sm-9"><button type="submit" name="submit" ' .
        'class="btn btn-primary" value="1">Simpan</button>&nbsp;<button type="submit" name="hapus" ' .
        'class="btn btn-danger" value="1">Hapus</button></div></div></form>';
    include (APP_PATH . '/includes/footer.php');
    exit();
}
$tgl = explode(' ', format_tanggal(time()));
$metode_pembayaran = json_decode($set['metode_pembayaran']);
$head = '<link id="bsdp-css" href="assets/css/bootstrap-datepicker3.min.css" rel="stylesheet">';
$foot = '<script src="assets/js/bootstrap-datepicker.min.js"></script>' .
    '<script src="assets/js/locales/bootstrap-datepicker.id.min.js" charset="UTF-8">' .
    '</script><script type="text/javascript">$("#dari_tanggal").datepicker({' .
    'format:"dd-mm-yyyy",endDate:"' . str_replace('/', '-', $tgl[0]) .
    '",todayHighlight:true,autoclose: true,language:"id"});$("#ke_tanggal").datepicker(' .
    '{format:"dd-mm-yyyy",endDate:"' . str_replace('/', '-', $tgl[0]) .
    '",todayHighlight:true,autoclose:true,language:"id"});</script>';
include (APP_PATH . '/includes/header.php');
echo '<ol class="breadcrumb"><li><a href="' . ADM_URL .
    '">Admin Panel</a></li><li class="active">' . '<span>Transaksi</span></li></ol>';
$dari_tanggal = isset($_GET['dari_tanggal']) ? $_GET['dari_tanggal'] : '';
$ke_tanggal = isset($_GET['ke_tanggal']) ? $_GET['ke_tanggal'] : '';
$status_pembayaran = isset($_GET['status_pembayaran']) ? $_GET['status_pembayaran'] :
    '';
$status_transaksi = isset($_GET['status_transaksi']) ? $_GET['status_transaksi'] :
    '';

$qw = array();
$url = ADM_URL . '?c=transaksi';
if (in_array($status_pembayaran, array(
    'sukses',
    'pending',
    'refund',
    )))
{
    $qw[] = "tr_status_pembayaran " . ($status_pembayaran == 'sukses' ?
        "= 'success'" : "= '$status_pembayaran'");
    $url .= '&amp;status_pembayaran=' . $status_pembayaran;
}
if (in_array($status_transaksi, array(
    'sukses',
    'pending',
    'gagal',
    'manual',
    )))
{
    $qw[] = "tr_status = '$status_transaksi'";
    $url .= '&amp;status_pembayaran=' . $status_transaksi;
}
if ($dari_tanggal)
{
    $qw[] = "tr_tanggal > '" . strtotime($dari_tanggal . ' 00:00:00') . "'";
    $url .= '&amp;dari_tanggal=' . __e($dari_tanggal);
}
if ($ke_tanggal)
{
    $qw[] = "tr_tanggal < '" . strtotime($ke_tanggal . ' 23:59:00') . "'";
    $url .= '&amp;ke_tanggal=' . __e($ke_tanggal);
}
$inv_id = isset($_GET['inv_id']) ? trim($_GET['inv_id']) : "";
if ($qw)
{
    $q = "WHERE " . implode(' AND ', $qw);
}
elseif ($inv_id && filter_var($inv_id, FILTER_VALIDATE_REGEXP, array('options' =>
        array('regexp' => '/^([A-Z0-9]+)$/'))))
{
    $q = "WHERE tr_id_pembayaran = '" . $inv_id . "'";
}
else
{
    $q = "";
}
echo '<div class="row"><div class="col-sm-12"><form name="form1" action="' .
    ADM_URL . '" method="get">' . '<input type="hidden" name="c" value="transaksi">' .
    '<div class="row"><div class="form-group col-sm-3"><label class="control-label">Dari Tanggal</label>' .
    '<input class="form-control" id="dari_tanggal" type="text" name="dari_tanggal" value="' .
    __e($dari_tanggal) . '" placeholder="hh-bb-yyyy"/></div>' .
    '<div class="form-group col-sm-3"><label class="control-label">Ke Tanggal</label>' .
    '<input class="form-control" id="ke_tanggal" type="text" name="ke_tanggal" value="' .
    __e($ke_tanggal) . '" placeholder="hh-bb-yyyy"/>' .
    '</div><div class="form-group col-sm-2"><label class="control-label">Pembayaran</label>' .
    '<select class="form-control" name="status_pembayaran">' . '<option value=""' . ($status_pembayaran ==
    '' ? ' selected' : '') . '>-- Semua --</option><option value="sukses"' . ($status_pembayaran ==
    'sukses' ? ' selected' : '') . '>Sukses</option>' . '<option value="pending"' . ($status_pembayaran ==
    'pending' ? ' selected' : '') . '>Pending</option><option value="refund"' . ($status_pembayaran ==
    'refund' ? ' selected' : '') . '>Refund</option></select></div>' .
    '<div class="form-group col-sm-2"><label class="control-label">Pengisian Pulsa</label>' .
    '<select class="form-control" name="status_transaksi"><option value=""' . (empty
    ($status_transaksi) ? ' selected' : '') . '>-- Semua --</option>' .
    '<option value="sukses"' . ($status_transaksi == 'sukses' ? ' selected' : '') .
    '>Sukses</option><option value="pending"' . ($status_transaksi == 'pending' ?
    ' selected' : '') . '>Pending</option><option value="manual"' . ($status_transaksi ==
    'manual' ? ' selected' : '') . '>Manual</option><option value="gagal"' . ($status_transaksi ==
    'gagal' ? ' selected' : '') .
    '>Gagal</option></select></div><div class="form-group col-sm-2">' .
    '<button class="btn btn-primary" style="margin-top:23px;" type="submit" value="1">Submit</button></div></div></form></div></div>';
$query = $pdo->query("SELECT COUNT(*)AS total FROM transaksi " . $q);
$total = $query->fetchColumn();
if ($total == 0)
{
    echo '<div class="alert alert-info">Tidak ada transaksi</div>';
    include (APP_PATH . '/includes/footer.php');
    exit();
}
$query = $pdo->query("SELECT * FROM transaksi " . $q .
    " ORDER BY tr_id DESC LIMIT $start, {$set['list_per_page']}");
$transaksi = $query->fetchAll();
echo '<div class="table table-responsive">' .
    '<table class="table table-bordered table-striped">' .
    '<thead><tr><th>Invoice ID</th><th>Produk</th><th>Provider</th>' .
    '<th>No. HP / ID PLN</th><th>Nominal</th>' .
    '<th>Harga</th><th>Pembayaran</th><th>Tanggal Pembelian</th><th>Status Pembayaran</th>' .
    '<th>Status Transaksi</th>' . '<th>' . 'Tindakan</th></tr></thead><tbody>';
foreach ($transaksi as $trx)
{
    echo '<tr id="trx-' . $trx->tr_id . '"><td>' . $trx->tr_id_pembayaran .
        '</td><td>' . $produk->{$trx->op_produk}->nama . '</td><td>' . __e($trx->
        op_nama) . '</td><td><a href="' . ADM_URL . '?c=sms&amp;a=kirim&amp;phone=' . $trx->
        tr_no_hp . '" data-toggle="modal" data-target="#myModal">' . $trx->tr_no_hp .
        '</a> ' . ($trx->tr_id_pln ? ' / ' . $trx->tr_id_pln : '') . '</td><td>' . $trx->
        vo_nominal . '</td><td>Rp.' . format_uang($trx->tr_harga) . '</td><td>' . $metode_pembayaran->{
        $trx->tr_pembayaran}->nama . '</td><td>' . format_tanggal($trx->tr_tanggal) .
        '</td><td>' . ucfirst($trx->tr_status_pembayaran) . '</td><td>' . ucfirst($trx->
        tr_status) . '</td><td class="text-center"><a data-toggle="modal" data-target="#myModal" href="' .
        ADM_URL . '?c=transaksi&amp;a=edit&amp;id=' . $trx->tr_id . '&amp;f=' .
        urlencode($a) . '&amp;page=' . $page . '" title="Edit">Edit</a></td></tr>';
}
echo '</tbody></table></div>' . pagination($url . '&amp;', $start, $total, $set['list_per_page']);
