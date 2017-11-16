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
$operators = $pdo->query("SELECT * FROM operator ORDER BY op_produk ASC, op_nama ASC")->
    fetchAll();
$page_title = 'Order | ' . $set['site_name'];
$active_page = 'order';

$metode_pembayaran = json_decode($set['metode_pembayaran']);
$confirm = false;
$errors = array();

if (isset($_POST['submit']))
{
    $oper = isset($_REQUEST['operator']) ? $_REQUEST['operator'] : '';
    $voucher = isset($_REQUEST['voucher']) ? $_REQUEST['voucher'] : '';
    $nomor_hp = isset($_REQUEST['nomor_hp']) ? $_REQUEST['nomor_hp'] : '';
    $id_pln = isset($_REQUEST['id_pln']) ? $_REQUEST['id_pln'] : '';
    $pembayaran = isset($_REQUEST['pembayaran']) ? $_REQUEST['pembayaran'] : '';

    $query = $pdo->prepare('SELECT voucher.*, operator.* FROM voucher ' .
        'LEFT JOIN operator ON voucher.op_id = operator.op_id WHERE voucher.op_id = ? AND voucher.vo_kode = ? AND voucher.vo_status = ?');
    $query->execute(array($oper, $voucher, '1'));
    if ($query->rowCount() == 0)
    {
        $errors[] = 'Nominal tidak benar';
    }
    $op = $query->fetch();
    if ($produk->{$op->op_produk}->status != 'on') {
        $errors[] = 'Produk tidak benar';
    }
    elseif ($op->vo_harga > $set['saldo']) {
        $errors[] = 'Sisa saldo kami tidak cukup untuk melakukan transaksi ini';
    }
    elseif (strlen($nomor_hp) < 8 || strlen($nomor_hp) > 14 || !ctype_digit($nomor_hp))
    {
        $errors[] = 'Nomor HP tidak benar';
    }
    elseif (!array_key_exists($pembayaran, $metode_pembayaran))
    {
        $errors[] = 'Pembayaran tidak benar';
    }
    elseif ($pembayaran == 'bank_bca' && $op->vo_harga < 10000)
    {
        $errors[] = 'Pembayaran melalui Bank BCA minimal Rp. 10.000';
    }
    elseif ($metode_pembayaran->{$pembayaran}->status != 'on')
    {
        $errors[] = 'Pembayaran tidak tersedia';
    }
    if ($op->op_produk == 'token_pln' && (strlen($id_pln) < 8 || strlen($id_pln) >
        16 || !ctype_digit($id_pln)))
    {
        $errors[] = 'ID PLN tidak benar';
    }
    if (!$errors) {
        $confirm = true;
    }
    if ($_POST['submit'] == 'konfirmasi')
    {
        $kode = @$_POST['kode'];
        if (!$kode || !isset($_SESSION['code']) || mb_strlen($kode) < 4 || strtolower($kode) !=
            strtolower($_SESSION['code']))
        {
            $errors = 'Kode keamanan tidak benar.';
        }
        else
        {
            $fields = array(
                'op_id' => '?',
                'op_produk' => '?',
                'op_nama' => '?',
                'vo_id' => '?',
                'vo_kode' => '?',
                'vo_nominal' => '?',
                'tr_id_pln' => '?',
                'tr_no_hp' => '?',
                'tr_harga' => '?',
                'tr_harga2' => '?',
                'tr_rate' => '?',
                'tr_pembayaran' => '?',
                'tr_status_pembayaran' => '?',
                'tr_id_pembayaran' => '?',
                'tr_status' => '?',
                'tr_tanggal' => '?',
                );
            $inv_id = 'inv' . substr(time(), -6);
            $harga_unik = $op->vo_harga;
            
            if ($pembayaran != 'paypal')
            {
                $waktu = time() - (3600 * 48); // Harga unik kembali ke 1 dalam 2 hari
                $q = $pdo->prepare("SELECT MAX(tr_harga) AS max_harga FROM transaksi WHERE tr_harga2 = ? AND tr_tanggal > ?");
                $q->execute(array(
                    $op->vo_harga,
                    $waktu,
                    ));
                $res = $q->fetch();
                if (!is_null($res->max_harga))
                {
                    $harga_unik = $res->max_harga + 1;
                }
                else
                {
                    $harga_unik = $op->vo_harga + 1;
                }
            }
            
            $q = $pdo->prepare("INSERT INTO transaksi (" . implode(', ', array_keys($fields)) .
                ") VALUES (" . implode(', ', array_values($fields)) . ");");
            $q->execute(array(
                $op->op_id,
                $op->op_produk,
                $op->op_nama,
                $op->vo_id,
                $op->vo_kode,
                $op->vo_nominal,
                $id_pln,
                $nomor_hp,
                $harga_unik,
                $op->vo_harga,
                ($pembayaran == 'paypal' ? $metode_pembayaran->paypal->rate : 0),
                $pembayaran,
                'pending',
                $inv_id,
                'pending',
                time(),
                ));
            header("Location: " . SITE_URL . "status.php?id=" . $pdo->lastInsertId());
            exit();
        }
        unset($_SESSION['code']);
    }
}
$head = '<link href="'.$set['site_url'].'assets/css/timeline.css" rel="stylesheet"/>';
include ('includes/header.php');

?>
<h3>Order</h3>
<?php if ($confirm):?>
<div class="row">
  <div class="col-sm-8">
    <table class="table table-striped table-bordered">
      <tbody>
        <tr>
          <td style="width: 50%;">
            Jenis Produk
          </td>
          <td style="vertical-align: middle;">
            <?php echo $produk->{$op->op_produk}->nama;?>
          </td>
        </tr>
        <tr>
          <td style="width: 50%;">
            Provider
          </td>
          <td style="vertical-align: middle;">
            <?php echo $op->op_nama?>
          </td>
        </tr>
        <?php if ($op->op_produk == 'token_pln'):?>
        <tr>
          <td>
            ID PLN
          </td>
          <td style="vertical-align: middle;">
            <?php echo $id_pln?>
          </td>
        </tr>
        <?php endif;?>
        <tr>
          <td>
            Nomor HP
          </td>
          <td style="vertical-align: middle;">
            <?php echo $nomor_hp?>
          </td>
        </tr>
        <tr>
          <td>
            Voucher
          </td>
          <td style="vertical-align: middle;">
            <?php echo __e($op->vo_nominal);?>
          </td>
        </tr>
        <tr>
          <td>
            Harga
          </td>
          <td style="vertical-align: middle;">
            <?php echo ($pembayaran == 'paypal' ? '$'.round($op->vo_harga / $metode_pembayaran->paypal->rate, 2).' USD' : 'Rp. '.format_uang($op->vo_harga));?>
          </td>
        </tr>
        <tr>
          <td>
            Pembayaran
          </td>
          <td style="vertical-align: middle;">
            <?php echo $metode_pembayaran->{$pembayaran}->nama; ?>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="col-sm-4">
    <?php if ($errors):?>
    <div class="alert alert-danger">
      Kode keamanan tidak benar.
    </div>
    <?php else:?>
    <div class="alert alert-warning">
      Sebelum Anda melakukan pembelian pastikan data - data sudah benar.
      <br />
      Kesalahan data tersebut bukan tanggung jawab Kami.
    </div>
    <?php endif;?>
    <form method="post" action="<?php echo SITE_URL;?>order.php">
      <input type="hidden" name="operator" value="<?php echo $oper;?>"/>
      <input type="hidden" name="voucher" value="<?php echo $voucher;?>"/>
      <input type="hidden" name="id_pln" value="<?php echo __e($id_pln);?>"/>
      <input type="hidden" name="nomor_hp" value="<?php echo $nomor_hp;?>"/>
      <input type="hidden" name="pembayaran" value="<?php echo $pembayaran;?>"/>
      <div class="form-group">
        <label for="kode" class=" control-label">
          Kode Keamanan
        </label>
        <div class="input-group">
          <span class="input-group-addon" style="padding: 0;">
            <img src="<?php echo SITE_URL;?>captcha.php" style="" alt="Loading...."/>
          </span>
          <input type="text" class="form-control input-lg" name="kode" id="kode" maxlength="5" size="5" required="required"/>
        </div>
      </div>
      <div class="form-group">
        <button class="btn btn-primary btn-lg btn-block" type="submit" name="submit" value="konfirmasi">
          Beli Sekarang
        </button>
      </div>
    </form>
  </div>
</div>
<?php else:?>
<div class="row">
  <div class="col-sm-8">
    <?php if ($errors):?>
    <div class="alert alert-danger"><ol><li><?php echo implode('</li><li>',$errors);?></li></ol></div>
    <?php endif;?>
    <div class="well well-sm order-form">
    <form method="post" action="<?php echo SITE_URL?>order.php">
      <div class="form-group">
        <label for="operator">
          Provider
        </label>
        <select class="form-control" name="operator" id="operator" required>
          <option value="">
          </option>
          <?php
          $my_produk = array();
          foreach ($operators as $operator) {
            if ($produk->{$operator->op_produk}->status == 'off')
                continue;
            if (!array_key_exists($operator->op_produk, $my_produk)) {
                echo (count($my_produk) == 0 ? '': '</optgroup>') . PHP_EOL;
                echo '<optgroup label="'.$produk->{$operator->op_produk}->nama.'" id="produk-'.$operator->op_produk.'">' . PHP_EOL;
            }
            $my_produk[$operator->op_produk][$operator->op_id] = $operator->op_nama;
            echo '<option value="'.$operator->op_id.'">'.$operator->op_nama.'</option>' . PHP_EOL;
          }
          ?>
          </optgroup>
        </select>
      </div>
      <div class="form-group">
        <label for="voucher">
          Voucher
        </label>
        <select class="form-control" name="voucher" id="voucher" required>
        </select>
      </div>
      <div id="data_value1">
      </div>
      <div id="data_value2">
        <div class="form-group">
          <label for="nomor_hp">
            Nomor HP
          </label>
          <input type="text" class="form-control" name="nomor_hp" id="nomor_hp" placeholder="08xxxxxxxxxx" maxlength="13" minlength="8" required/>
        </div>
      </div>
      <div id="data_value3">
      </div>
      <div class="form-group">
        <label for="pembayaran">
          Pembayaran
        </label>
        <select class="form-control" name="pembayaran" id="pembayaran" required>
          <option value="">
          </option>
          <?php foreach ($metode_pembayaran as $b_k=>
            $b_v): if ($b_v->status == 'off') continue;?>
            <option value="<?php echo $b_k?>">
              <?php echo $b_v->nama;?>
            </option>
            <?php endforeach?>
        </select>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary" name="submit" value="next">
          Selanjutnya &raquo;
        </button>
      </div>
    </form>
    </div>
  </div>
  <div class="col-sm-4">
    <?php if ($set['smsg_aktif'] > (time() - 300)):?>
    <div class="alert alert-success" data-toggle="tooltip" data-title="Transaksi pulsa akan diproses dengan cepat."><strong><i class="glyphicon glyphicon-signal"></i> SMS Gateway ON!</strong></div>
    <?php else:?>
    <div class="alert alert-warning" data-toggle="tooltip" data-title="Transaksi pulsa akan diproses dengan cepat."><strong><i class="glyphicon glyphicon-signal"></i> SMS Gateway OFF!</strong></div>
    <?php endif?>
    <ul class="timeline" style="margin-left: 25px;">
      <li class="timeline-inverted">
        <div class="timeline-badge info">
          <i class="glyphicon glyphicon-shopping-cart">
          </i>
        </div>
        <div class="timeline-panel">
          <div class="timeline-heading">
            <h4 class="timeline-title">
              Beli Voucher
            </h4>
          </div>
          <div class="timeline-body">
            <p>
              Pilih produk / voucher.
            </p>
          </div>
        </div>
      </li>
      <li class="timeline-inverted">
        <div class="timeline-badge danger">
          <i class="glyphicon glyphicon-credit-card">
          </i>
        </div>
        <div class="timeline-panel">
          <div class="timeline-heading">
            <h4 class="timeline-title">
              Lakukan Pembayaran
            </h4>
          </div>
          <div class="timeline-body">
            <p>
              Pembayaran maksimal <?php echo $set['jam_pembayaran'];?> jam.
            </p>
          </div>
        </div>
      </li>
      <li class="timeline-inverted">
        <div class="timeline-badge success">
          <i class="glyphicon glyphicon-thumbs-up">
          </i>
        </div>
        <div class="timeline-panel">
          <div class="timeline-heading">
            <h4 class="timeline-title">
              Transaksi Selesai
            </h4>
          </div>
          <div class="timeline-body">
            <p>
              Pulsa Anda akan terisi.
            </p>
          </div>
        </div>
      </li>
    </ul>
  </div>
</div>
<?php
$foot = '<script>$("#operator").change(function() {
				var selectedValue = $("#operator option:selected").val();
                var dataProduk = jQuery.parseJSON(\''.str_replace("'","\'",json_encode($my_produk)).'\');
                if (dataProduk.token_pln != undefined && dataProduk.token_pln[selectedValue] != undefined) {
                    $("#data_value1").html(\'<div class="form-group"><label for="id_pln">ID PLN</label><input type="text" class="form-control" name="id_pln" id="id_pln" maxlength="18" minlength="8" required/></div>\');
                }
                else {
                    $("#data_value1").html("");
                }
                $.ajax({
					type: "POST",
					url: "'.SITE_URL.'ajax.php?c=get_vouchers",
					data: {
						operator: selectedValue
					}
				}).done(function(data) {
					$("#voucher").html(data);
				});
			});</script>';
?>
<?php endif;?>
<?php include ('includes/footer.php') ?>