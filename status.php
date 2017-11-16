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
$page_title = 'Status Order | ' . $set['site_name'];
$active_page = 'status';
$metode_pembayaran = json_decode($set['metode_pembayaran']);
$id = isset($_GET['id']) ? intval($_GET['id']) : '';
$exp_time = time() - (3600 * $set['jam_pembayaran']);

if (isset($_GET['ajax']))
{
    $q = $pdo->query("SELECT * FROM transaksi ORDER BY tr_tanggal DESC LIMIT 20");
    $history = $q->fetchAll();

?>
<?php $i=1; foreach ($history as $trx): ?>
<tr>
  <td>
    <?php echo $i . PHP_EOL; ?>
  </td>
  <td>
    <a href="<?php echo $set['site_url']; ?>status.php?id=<?php echo $trx->tr_id; ?>"><?php echo format_tanggal($trx->tr_tanggal);?></a>
  </td>
  <td>
    <?php echo __e($trx->op_nama) . PHP_EOL;
    ?>
  </td>
  <td>
    <?php echo __e($trx->vo_nominal) . PHP_EOL;
    ?>
  </td>
  <td>
    <?php echo substr($trx->tr_no_hp, 0, -3); ?>XXX
  </td>
  <td class="text-left">
    <?php echo ($trx->tr_pembayaran == 'paypal' ? '$'.round($trx->tr_harga / $trx->tr_rate, 2) : 'Rp. '.format_uang($trx->tr_harga));?>
  </td>
  <td>
    <?php echo $metode_pembayaran->{$trx->tr_pembayaran}->nama . PHP_EOL;
    ?>
  </td>
  <td>
    <?php if ($trx->tr_status_pembayaran == 'pending' && $trx->tr_tanggal < $exp_time):
    ?>
    <span class="badge badge-important" style="width: 34px;">
      CL
    </span>
    <?php elseif ($trx->tr_status_pembayaran == 'pending'):
    ?>
    <span class="badge badge-warning" style="width: 34px;">
      WP
    </span>
    <?php elseif ($trx->tr_status_pembayaran == 'refund'):
    ?>
    <span class="badge" style="width: 34px;">
      RF
    </span>
    <?php elseif ($trx->tr_status_pembayaran == 'success' && $trx->tr_status == 'pending'):
    ?>
    <span class="badge badge-info" style="width: 34px;">
      IP
    </span>
    <?php elseif ($trx->tr_status_pembayaran == 'success' && $trx->tr_status == 'gagal'):
    ?>
    <span class="badge badge-important" style="width: 34px;">
      CL
    </span>
    <?php else: ?>
    <span class="badge badge-success" style="width: 34px;">
      OK
    </span>
    <?php endif; ?>
  </td>
</tr>
<?php $i++; endforeach; ?>
<?php

exit();
}
include ('includes/header.php');
if ($id)
{
    echo '<h3>Data Transaksi</h3>';
    $q = $pdo->prepare("SELECT * FROM transaksi WHERE tr_id = ?");
    $q->execute(array($id));
    if ($q->rowCount() != 1)
    {
        echo '<div class="alert alert-danger">Transaksi tidak ditemukan</div>';
    }
    else
    {
        $trx = $q->fetch();
        echo '<div class="row">';
        echo '<div class="col-sm-6"><div class="table-responsive">' .
            '<table class="table table-bordered table-striped"><tbody>';
        echo '<tr><td>Jenis Produk</td><td>' . $produk->{$trx->op_produk}->nama .
            '</td></tr>';
        echo '<tr><td>Provider</td><td>' . $trx->op_nama . '</td></tr>';
        echo '<tr><td>Nominal</td><td>' . __e($trx->vo_nominal) . '</td></tr>';
        if ($trx->op_produk == 'token_pln')
            echo '<tr><td>ID PLN</td><td>' . substr($trx->tr_id_pln, 0, -4) .
                'XXXX</td></tr>';
        echo '<tr><td>Nomor HP</td><td>' . substr($trx->tr_no_hp, 0, -3) .
            'XXX</td></tr>';
        echo '<tr><td>Harga</td><td>' . ($trx->tr_pembayaran == 'paypal' ? '$' . 
            round($trx->tr_harga / $trx->tr_rate, 2) . ' USD' : 'Rp. '.format_uang($trx->tr_harga)) . '</td></tr>';
        echo '<tr><td>Pembayaran</td><td>' . $metode_pembayaran->{$trx->tr_pembayaran}->
            nama . '</td></tr>';
        echo '<tr><td>Tanggal Pembelian</td><td>' . format_tanggal($trx->tr_tanggal) .
            '</td></tr>';
        echo '<tr><td>Status Pembayaran</td><td>' . ucfirst($trx->tr_status_pembayaran) .
            '</td></tr>';
        echo '<tr><td>Status Pengisian</td><td>' . ucfirst($trx->tr_status) .
            '</td></tr>';
        echo '</tbody></table></div></div>';
        echo '<div class="col-sm-6">';
        if ($trx->tr_status_pembayaran == 'pending' && $trx->tr_tanggal < $exp_time)
        {
            echo '<div class="alert alert-danger"><h3>Tidak Berlaku</h3>Pembayaran belum diselesaikan dan pesanan sudah tidak berlaku lagi</div>';
        }
        elseif ($trx->tr_status_pembayaran == 'pending')
        {
            if ($trx->tr_pembayaran == 'paypal')
            {
                echo '<div class="alert alert-info hidden-print">Silakan lakukan pembayaran ke akun PayPal di bawah ini</div>';
                echo '<div class="table-responsive"><table class="table table-bordered table-striped"><tbody>';
                echo '<tr><td>Email</td><td>' . $metode_pembayaran->{$trx->tr_pembayaran}->
                    nomor_rekening . '</td></tr>';
                echo '<tr><td>Jumlah Pembayaran</td><td>$' . round($trx->tr_harga / $trx->
                    tr_rate, 2) . ' USD</td></tr>';
                echo '<tr><td>Catatan</td><td>' . $trx->tr_id_pembayaran . '</td></tr>';
                echo '</tbody></table></div>';
                echo '<h5>Catatan</h5>';
                echo '<ul class="list-unstyled2">';
                echo '<li>Lakukan pembayaran pribadi / personal / keluarga</li>';
                echo '<li>Masukan catatan / pesan di atas pada saat melakukan transfer</li>';
                echo '<li>Pembayaran berlaku s/d '.format_tanggal($trx->tr_tanggal + (3600 * $set['jam_pembayaran'])).'</li>';
                echo '</ul>';
                
                $pajak = ($trx->tr_harga * 4.4) / 100;
                $pajak = round($pajak / $trx->tr_rate, 2) + 0.3;                
                $express = '<div class="hidden-print" style="margin-top: 20px;">';
                $express .= '<h3>PayPal Express Checkout</h3><p>Jika Anda kesulitan melakukan pembayaran secara '.
                        'manual silakan gunakan pembayaran Express, namun Kami merekomendasikan untuk melakukan '.
                        'pembayaran manual karena pembayaran Express akan dikenakan pajak sebesar <b>$' . $pajak . 
                        ' USD</b> yang dibebankan kepada Anda.</p>';
                $express .= '<p style="margin: 10x auto 0;text-align:center"><a href="' . $set['site_url'] . 
                        'paypal_checkout.php?id=' . $trx->tr_id . '" title="Bayar dengan menggunakan PayPal Express'.
                        ' Checkout" class="btn btn-warning"><i class="glyphicon glyphicon-credit-card"></i> Checkout Now</a></p>';
                $express .= '</div>';
            }
            else
            {
                echo '<div class="alert alert-info hidden-print">Silakan lakukan pembayaran ke rekening bank di bawah ini</div>';
                echo '<div class="table-responsive"><table class="table table-bordered table-striped"><tbody>';
                echo '<tr><td>Bank</td><td>' . $metode_pembayaran->{$trx->tr_pembayaran}->nama .
                    '</td></tr>';
                echo '<tr><td>Nomor Rekening</td><td>' . $metode_pembayaran->{$trx->
                    tr_pembayaran}->nomor_rekening . '</td></tr>';
                echo '<tr><td>Atas Nama</td><td>' . $metode_pembayaran->{$trx->tr_pembayaran}->
                    nama_rekening . '</td></tr>';
                echo '<tr><td>Jumlah Pembayaran</td><td>Rp. ' . format_uang($trx->tr_harga) .
                    '</td></tr>';
                /**
				echo '<tr><td>Berita</td><td>' . $trx->tr_id_pembayaran .
                    '</td></tr>';
                */
				echo '</tbody></table></div>';
                echo '<h5>Catatan</h5>';
                echo '<ul class="list-unstyled2"><li>Lakukan pembayaran sesuai data di atas</li>'.
                    '<li>Pembayaran berlaku s/d '.format_tanggal($trx->tr_tanggal + (3600 * $set['jam_pembayaran'])).'</li></ul>';
            }
        }
        elseif ($trx->tr_status_pembayaran == 'success') {
            echo '<div class="alert alert-success"><h3>LUNAS</h3>Terima kasih telah melakukan pembayaran</div>';
        }
        echo '</div>';
        echo '</div>';
        echo '<div class="hidden-print"><a class="btn btn-default" href="#"' .
            ' onclick="window.print();return false;"><i class="glyphicon glyphicon-print"></i> Print</a> &nbsp; ' .
            '<a class="btn btn-warning" href="' . SITE_URL . 'hubungi_kami.php?inv_id=' . $trx->
            tr_id_pembayaran . '" target="_blank"><i class="glyphicon glyphicon-exclamation-sign"></i> Komplain</a></div>';
        
        if (isset($express)) {
            //echo $express;
        }
    }
}
else
{
    $search = isset($_GET['q']) ? (ctype_digit($_GET['q']) ? $_GET['q'] : '') : '';
    $search = strlen($search) >= 3 && strlen($search) <= 12 ? $search : '';
    if ($search)
    {
        $q = $pdo->query("SELECT * FROM transaksi WHERE tr_no_hp LIKE '%" . $search .
            "%' ORDER BY tr_tanggal DESC LIMIT 20");
    }
    else
    {
        $q = $pdo->query("SELECT * FROM transaksi ORDER BY tr_tanggal DESC LIMIT 20");
    }
    $history = $q->fetchAll();
    echo '<h3>Status Transaksi</h3>';

?>
<div class="row" style="margin-bottom: 15px;">
  <div class="col-sm-4">
    <a href="<?php
    echo $set['site_url'];
    ?>hubungi_kami.php" target="_blank" class="btn btn-danger hidden-xs">Komplain / Pertanyaan</a>
  </div>
  <div class="col-sm-4">
    <div id="refresh" class="text-center">
    </div>
  </div>
  <div class="col-sm-4">
    <span class="pull-right">
      <form method="get">
        <div class="input-group">
          <input type="text" class="form-control" placeholder="Cari no. telepon.." name="q" id="q" value="<?php
          echo $search;
          ?>"/>
          <span class="input-group-btn">
            <button type="submit" class="btn btn-primary">
              <i class="glyphicon glyphicon-search">
              </i>
            </button>
          </span>
        </div>
      </form>
    </span>
  </div>
</div>
<?php if ($search): ?>
<div class="alert alert-info">
  Pencarian Nomor HP &quot;
  <strong>
    <?php echo $search; ?>
  </strong>
  &quot;.
  <a class="alert-link pull-right" href="<?php
  echo $set['site_url'];
  ?>status.php"><i class="glyphicon glyphicon-remove"></i></a>
</div>
<?php endif; ?>
<?php if ($history): ?>
<div class="table-responsive">
  <table class="table table-striped hidden-phone">
    <thead>
      <tr>
        <th>
          #
        </th>
        <th>
          Tanggal
        </th>
        <th>
          Provider
        </th>
        <th>
          Voucher
        </th>
        <th>
          No. Telepon
        </th>
        <th>
          Harga
        </th>
        <th>
          Pembayaran
        </th>
        <th>
          Status
        </th>
      </tr>
    </thead>
    <tbody id="history_transaksi">
      <?php $i=1; foreach ($history as $trx): ?>
      <tr>
        <td>
          <?php echo $i . PHP_EOL; ?>
        </td>
        <td>
          <a href="<?php echo $set['site_url']; ?>status.php?id=<?php echo $trx->tr_id; ?>"><?php echo format_tanggal($trx->tr_tanggal);?></a>
        </td>
        <td>
          <?php echo __e($trx->op_nama) . PHP_EOL;
          ?>
        </td>
        <td>
          <?php echo __e($trx->vo_nominal) . PHP_EOL;
          ?>
        </td>
        <td>
          <?php echo substr($trx->tr_no_hp, 0, -3);?>XXX
        </td>
        <td class="text-left">
          <?php echo ($trx->tr_pembayaran == 'paypal' ? '$'.round($trx->tr_harga / $trx->tr_rate, 2) : 'Rp. '.format_uang($trx->tr_harga));?>
        </td>
        <td>
          <?php echo $metode_pembayaran->{$trx->tr_pembayaran}->nama . PHP_EOL;
          ?>
        </td>
        <td>
          <?php if ($trx->tr_status_pembayaran == 'pending' && $trx->tr_tanggal < $exp_time):
          ?>
          <span class="badge badge-important" style="width: 34px;">
            CL
          </span>
          <?php elseif ($trx->tr_status_pembayaran == 'pending'):
          ?>
          <span class="badge badge-warning" style="width: 34px;">
            WP
          </span>
          <?php elseif ($trx->tr_status_pembayaran == 'refund'):?>
          <span class="badge" style="width: 34px;">
            RF
          </span>
          <?php elseif ($trx->tr_status_pembayaran == 'success' && $trx->tr_status == 'pending'):
          ?>
          <span class="badge badge-info" style="width: 34px;">
            IP
          </span>
          <?php elseif ($trx->tr_status_pembayaran == 'success' && $trx->tr_status == 'gagal'):
          ?>
          <span class="badge badge-important" style="width: 34px;">
            CL
          </span>
          <?php else: ?>
          <span class="badge badge-success" style="width: 34px;">
            OK
          </span>
          <?php endif; ?>
        </td>
      </tr>
      <?php $i++; endforeach; ?>
    </tbody>
  </table>
</div>
<?php else: ?>
<div class="alert alert-warning">
  Tidak ada data transaksi
</div>
<?php endif; ?>
<div>
  <p>
    <b>
      Keterangan Status
    </b>
  </p>
  <table class="table" style="margin-bottom: 15px;">
    <tbody>
      <tr>
        <td style="width: 40px;">
          <span class="badge badge-warning" style="width: 34px;">
            WP
          </span>
        </td>
        <td>
          Menunggu Pembayaran.
        </td>
      </tr>
      <tr>
        <td>
          <span class="badge badge-info" style="width: 34px;">
            IP
          </span>
        </td>
        <td>
          Sedang kami proses.
        </td>
      </tr>
      <tr>
        <td>
          <span class="badge badge-success" style="width: 34px;">
            OK
          </span>
        </td>
        <td>
          Transaksi berhasil.
        </td>
      </tr>
      <tr>
        <td>
          <span class="badge badge-important" style="width: 34px;">
            CL
          </span>
        </td>
        <td>
          Transaksi dibatalkan.
        </td>
      </tr>
      <tr>
        <td>
          <span class="badge" style="width: 34px;">
            RF
          </span>
        </td>
        <td>
          Refund, Nomer habis masa aktif atau Nomer salah.
        </td>
      </tr>
    </tbody>
  </table>
  <p>
    <b>
      Pulsa Telpon, Token, atau Voucher Belum Masuk?
    </b>
  </p>
  <ul>
    <li>
      Terlebih dahulu cek pulsa Anda. Kadang pulsa sudah masuk mendahului report / notifikasi melalui SMS.
    </li>
    <li>
      Khusus Token PLN, Token akan otomatis kami kirimkan ke HP anda atau bisa langsung anda cetak di halaman informasi order.
    </li>
  </ul>
  <p>
    <b>
      Status WP atau Waiting Payment / Belum dibayar?
    </b>
  </p>
  <ul>
    <li>
      Hal ini dikarenakan ibanking Mandiri/BCA/BRI/BNI sedang Offline (sehingga sistem kami tidak bisa mengecek data mutasi atau data mutasi tidak update).
    </li>
    <li>
      Anda salah mentransfer Nominal / Jumlah dan Berita yang telah diinstruksikan oleh sistem? Jika YA, silahkan ulangi transfer anda dengan Nominal yang telah diinfokan oleh sistem (Total harga beserta angka
      unik, tidak dibulatkan). Segera hubungi cs untuk request refund dana sebelumnya.
    </li>
  </ul>
</div>
<?php

if (empty($search)) {
    $foot = '<script>/*<![CDATA[*/' . 'function refreshHistory(){' .
        '$("#refresh").html(\'<img src="' . $set['site_url'] .
        'assets/ajax-loader.gif" width="16px" height="11px" alt="refreshing..."/>\');' .
        '$.get("' . $set['site_url'] . 'status.php?ajax=1", function (data){' .
        '$("#history_transaksi").html(data);' .
        '$("#refresh").html("<!-- // -->"); }); } setInterval(refreshHistory, 10000); /*]]>*/</script>';
}

?>
<?php } include ('includes/footer.php'); ?>