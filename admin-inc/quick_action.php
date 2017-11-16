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

if (isset($_POST['set_harga_voucher']))
{
    $nominal = @$_POST['nominal'];
    $harga = abs(@$_POST['harga']);
    $q = $pdo->prepare("UPDATE voucher SET vo_harga = ? WHERE vo_nominal = ?");
    $q->execute(array($harga, $nominal));
    $result = '<div class="alert alert-success">Perubahan disimpan.</div>';
}
elseif (isset($_POST['set_status_voucher']))
{
    $nominal = @$_POST['nominal'];
    $status = abs(intval(@$_POST['status']));
    $q = $pdo->prepare("UPDATE voucher SET vo_status = ? WHERE vo_nominal = ?");
    $q->execute(array($status, $nominal));
    $result = '<div class="alert alert-success">Perubahan disimpan.</div>';
}
elseif (isset($_POST['set_status_voucher2']))
{
    $operator = abs(intval(@$_POST['op_id']));
    $status = abs(intval(@$_POST['status']));
    $q = $pdo->prepare("UPDATE voucher SET vo_status = ? WHERE op_id = ?");
    $q->execute(array($status, $operator));
    $result = '<div class="alert alert-success">Perubahan disimpan.</div>';
}
include (APP_PATH . '/includes/header.php');
echo '<ol class="breadcrumb"><li><a href="' . ADM_URL .
    '">Admin Panel</a></li><li class="active">' .
    '<span>Tindakan Cepat</span></li></ol>';
echo (isset($result) ? $result : '');
echo '<div class="row">';

echo '<div class="col-sm-4">';
echo '<div class="panel panel-default"><div class="panel-heading"><strong>Harga Voucher</strong>' .
    '</div><div class="panel-body"><form action="' . ADM_URL .
    '?c=quick_action" method="post">' .
    '<div class="form-group"><label class="control-label">Nominal</label><select class="form-control"' .
    ' name="nominal"><option value="">   </optiton>';
$q = $pdo->query("SELECT vo_nominal FROM voucher GROUP BY vo_nominal ORDER BY vo_nominal ASC");
$voucers = $q->fetchAll();
foreach ($voucers as $vn)
{
    echo '<option value="' . $vn->vo_nominal . '">' . __e($vn->vo_nominal) .
        '</option>';
}
echo '</select></div>' .
    '<div class="form-group"><label class="control-label">Harga</label><input class="form-control"' .
    ' type="text" name="harga" value=""/></div><div class="form-group">' .
    '<button class="btn btn-primary" type="submit" name="set_harga_voucher" value="1">Simpan</button></div></form>';
echo '</div></div>';
echo '</div>';

echo '<div class="col-sm-4">';
echo '<div class="panel panel-default"><div class="panel-heading"><strong>Status Voucher</strong>' .
    '</div><div class="panel-body"><form action="' . ADM_URL .
    '?c=quick_action" method="post">' .
    '<div class="form-group"><label class="control-label">Nominal</label><select class="form-control"' .
    ' name="nominal"><option value="">   </optiton>';
foreach ($voucers as $vn)
{
    echo '<option value="' . $vn->vo_nominal . '">' . __e($vn->vo_nominal) .
        '</option>';
}
echo '</select></div>' .
    '<div class="form-group"><label class="control-label">Status</label><select class="form-control" ' .
    'name="status"><option value=""> </option><option value="1">Tersedia</option><option value="0">' .
    'Tidak Tersedia</option></select></div><div class="form-group">' .
    '<button class="btn btn-primary" type="submit" name="set_status_voucher" value="1">Simpan</button></div></form>';
echo '</div></div>';
echo '</div>';

echo '<div class="col-sm-4">';
echo '<div class="panel panel-default"><div class="panel-heading"><strong>Status Voucher</strong>' .
    '</div><div class="panel-body"><form action="' . ADM_URL .
    '?c=quick_action" method="post">' .
    '<div class="form-group"><label class="control-label">Provider</label><select class="form-control"' .
    ' name="op_id"><option value="">   </optiton>';
$q = $pdo->query("SELECT * FROM operator ORDER BY op_id ASC");
$ops = $q->fetchAll();
foreach ($ops as $op)
{
    echo '<option value="' . $op->op_id . '">' . __e($op->op_nama) . '</option>';
}
echo '</select></div>' .
    '<div class="form-group"><label class="control-label">Status</label><select class="form-control" ' .
    'name="status"><option value=""> </option><option value="1">Tersedia</option><option value="0">' .
    'Tidak Tersedia</option></select></div><div class="form-group">' .
    '<button class="btn btn-primary" type="submit" name="set_status_voucher2" value="1">Simpan</button></div></form>';
echo '</div></div>';
echo '</div>';

echo '</div>';
