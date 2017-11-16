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

$query = $pdo->query("SELECT COUNT(*) AS total FROM transaksi WHERE tr_status_pembayaran = 'success' AND tr_status = 'sukses' " .
    "UNION ALL SELECT COUNT(*) AS total FROM transaksi WHERE tr_status_pembayaran = 'success' AND tr_status = 'pending' " .
    "UNION ALL SELECT COUNT(*) AS total FROM transaksi WHERE tr_status_pembayaran = 'success' AND tr_status = 'gagal' " .
    "UNION ALL SELECT COUNT(*) AS total FROM transaksi WHERE tr_status_pembayaran = 'success' " .
    "UNION ALL SELECT COUNT(*) AS total FROM transaksi WHERE tr_status_pembayaran = 'pending' " .
    "UNION ALL SELECT COUNT(*) AS total FROM feedback WHERE baca = '0' " .
    "UNION ALL SELECT COUNT(*) AS total FROM testimonial WHERE moderasi = '1' " .
    "UNION ALL SELECT SUM(tr_harga) AS total FROM transaksi WHERE tr_status_pembayaran = 'success'" .
    "UNION ALL SELECT COUNT(*) AS total FROM transaksi WHERE tr_status_pembayaran = 'success' AND tr_status = 'manual'" .
    "UNION ALL SELECT COUNT(*) AS total FROM transaksi WHERE tr_status_pembayaran = 'refund'");
$total = $query->fetchAll(PDO::FETCH_ASSOC);
include (APP_PATH . '/includes/header.php');
echo '<h3 class="page-header">Admin Panel</h3>';
echo '<div class="row">';
echo '<div class="col-sm-6">';
echo '<div class="row"><div class="col-xs-6">';
echo '<div class="alert alert-' . ($set['smsg_aktif'] > (time() - 300) ?
    'success' : 'warning') .
    '" data-toggle="tooltip" data-title="SMS Gateway terakhir terhubung"><strong><i class="glyphicon glyphicon-signal"></i> ' .
    format_tanggal($set['smsg_aktif']) . '</strong></div>';
echo '</div><div class="col-xs-6"><div class="alert alert-info" data-toggle="tooltip" ' .
    'data-title="Total pendapatan yang diterima"><strong><i class="glyphicon glyphicon-credit-card"></i> Rp. ' .
    ' ' . ($total[7]['total'] ? format_uang($total[7]['total']) : '0') .
    '</strong></div></div></div>';
echo '<div class="panel panel-default">' .
    '<div class="panel-heading"><strong><i class="glyphicon glyphicon-shopping-cart">' .
    '</i> Transaksi Pengisian Pulsa</strong></div>' .
    '<div class="list-group"><a class="list-group-item" href="' . ADM_URL .
    '?c=transaksi&amp;' . 'status_pembayaran=sukses&amp;status_transaksi=sukses">' .
    'Transaksi Sukses <span class="badge">' . $total[0]['total'] .
    '</span></a><a class="list-group-item" ' . 'href="' . ADM_URL .
    '?c=transaksi&amp;status_pembayaran=sukses&amp;status_transaksi=pending">' .
    'Transaksi Tertunda <span class="badge">' . $total[1]['total'] . '</span></a>' .
    '<a class="list-group-item" ' . 'href="' . ADM_URL .
    '?c=transaksi&amp;status_pembayaran=sukses&amp;status_transaksi=manual">' .
    'Transaksi Manual <span class="badge">' . $total[8]['total'] . '</span></a>' .
    '<a class="list-group-item" href="' . ADM_URL .
    '?c=transaksi&amp;status_pembayaran=sukses&amp;' .
    'status_transaksi=gagal">Transaksi Gagal <span class="badge">' . $total[2]['total'] .
    '</span></a>' . '</div></div>';
echo '<div class="panel panel-default">' .
    '<div class="panel-heading"><strong><i class="glyphicon glyphicon-credit-card">' .
    '</i> Transaksi Pembayaran</strong></div>' .
    '<div class="list-group"><a class="list-group-item" href="' . ADM_URL .
    '?c=transaksi&amp;' . 'status_pembayaran=sukses">' .
    'Pembayaran Sukses <span class="badge">' . $total[3]['total'] . '</span></a>' .
    '<a class="list-group-item" href="' . ADM_URL .
    '?c=transaksi&amp;status_pembayaran=pending">' .
    'Pembayaran Tertunda <span class="badge">' . $total[4]['total'] . '</span></a>' .
    '<a class="list-group-item" href="' . ADM_URL .
    '?c=transaksi&amp;status_pembayaran=refund">' .
    'Pembayaran Refund <span class="badge">' . $total[9]['total'] . '</span></a>' .
    '</div></div>';
echo '<div class="panel panel-default">' .
    '<div class="panel-heading"><strong><i class="glyphicon glyphicon-book">' .
    '</i> Penanda</strong></div>' .
    '<div class="list-group"><a class="list-group-item" href="' . ADM_URL .
    '?c=berita">Berita</a>', '<a class="list-group-item" href="' . ADM_URL .
    '?c=quick_action">Tindakan Cepat</a>' . '<a class="list-group-item" href="' .
    ADM_URL . '?c=sms">SMS</a><a class="list-group-item" href="' . ADM_URL .
    '?c=feedback">Umpan Balik' . ($total[5]['total'] ? ' <span class="badge">' . $total[5]['total'] .
    '</span>' : '') . '</a><a class="list-group-item" href="' . ADM_URL .
    '?c=testimonial">Testimonial' . ($total[6]['total'] ? ' <span class="badge">' .
    $total[6]['total'] . '</span>' : '') . '</a></div></div>';
echo '</div>';
echo '<div class="col-sm-6">';
echo '<div class="panel panel-default" id="produk">' .
    '<div class="panel-heading"><strong><i class="glyphicon glyphicon-book"></i> Provider</strong>' .
    '</div>';
echo '<div class="table-responsive"><table class="table table-bordered">' .
    '<thead><tr><th>Produk</th><th>Status</th>' . '</tr></thead><tbody>';
foreach ($produk as $pr_key => $pr_data)
{
    echo '<tr><td><a href="' . ADM_URL . '?c=operator#produk-' . $pr_key . '">' . $pr_data->
        nama . '</a></td><td>';
    if ($pr_data->status == 'off')
    {
        echo '<a href="' . ADM_URL . '?c=actions&amp;a=set_produk_status&amp;produk=' .
            $pr_key . '&amp;status=on">On</a> | <span>Off</span>';
    }
    else
    {
        echo '<span>On</span> | <a href="' . ADM_URL .
            '?c=actions&amp;a=set_produk_status&amp;produk=' . $pr_key .
            '&amp;status=off">Off</a>';
    }
    echo '</td></tr>';
}
echo '</tbody></table></div>';
echo '</div>';
echo '<div class="panel panel-default">' .
    '<div class="panel-heading"><strong><i class="glyphicon glyphicon-cog"></i> Pengaturan</strong>' .
    '</div><div class="list-group">' . '<a class="list-group-item" href="' . ADM_URL .
    '?c=setelan">Umum</a>' . '<a class="list-group-item" href="' . ADM_URL .
    '?c=setelan&amp;a=saldo_pin">Saldo & PIN</a>' .
    '<a class="list-group-item" href="' . ADM_URL .
    '?c=setelan&amp;a=produk">Produk & Format Transaksi</a><a class="list-group-item" href="' .
    ADM_URL . '?c=setelan&amp;a=metode_pembayaran">Metode Pembayaran</a>' .
    '<a class="list-group-item" href="' . ADM_URL .
    '?c=setelan&amp;a=envaya_sms">Envaya SMS</a>' . '</div></div></div></div>';
echo '<div><a class="btn btn-danger" href="' . ADM_URL . '?c=keluar">Keluar (' .
    __e($admin->username) . ')</a></div>';
