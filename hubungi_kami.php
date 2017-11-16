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
$page_title = 'Hubungi Kami | ' . $set['site_name'];
$active_page = 'hubungi_kami';
$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$no_hp = isset($_POST['no_hp']) ? trim($_POST['no_hp']) : '';
$pesan = isset($_POST['pesan']) ? trim($_POST['pesan']) : '';
$kode = isset($_POST['kode']) ? trim($_POST['kode']) : '';
$inv_id = isset($_REQUEST['inv_id']) ? trim($_REQUEST['inv_id']) : '';
if (isset($_POST['submit']))
{
    $err = array();
    if (strlen($nama) > 12 || strlen($nama) < 2)
        $err[] = 'Panjang Nama harus 2 s/d 12 karakter.';
    if (!ctype_digit($no_hp) || strlen($no_hp) > 12 || strlen($no_hp) < 8)
        $err[] = 'Panjang Nomor HP harus 2 s/d 12 digit.';
    elseif (substr($no_hp, 0, 1) != '0')
        $err[] = 'Nomor HP harus diawali angka 0';
    if (strlen($pesan) > 160 || strlen($pesan) < 2)
        $err[] = 'Panjang Pesan harus 2 s/d 160 karakter.';
    if (!$kode || !isset($_SESSION['code']) || mb_strlen($kode) < 4 || strtolower($kode) !=
        strtolower($_SESSION['code']))
        $err[] = 'Kode keamanan tidak benar.';
    unset($_SESSION['code']);
    if ($inv_id)
    {
        $q = $pdo->prepare("SELECT * FROM transaksi WHERE tr_id_pembayaran = ?");
        $q->execute(array($inv_id));
        if ($q->rowCount() == 0)
            $inv_id = '';
    }
    if (empty($err))
    {
        $q = $pdo->prepare("INSERT INTO feedback (nama, no_hp, inv_id, pesan, tanggal) VALUES (?, ?, ?, ?, ?)");
        $q->execute(array(
            $nama,
            $no_hp,
            $inv_id,
            $pesan,
            time()));
        header("Location: " . SITE_URL . "hubungi_kami.php?ok=1");
    }
    else
    {
        $error = '<div class="alert alert-danger"><ol><li>' . implode('</li><li>', $err) .
            '</li></ol></div>';
    }
}
include ('includes/header.php');

?>
<h3>Hubungi Kami</h3>
<div class="row">
  <div class="col-sm-8">
    <?php if (isset($error)): ?>
    <?php echo $error ?>
    <?php elseif (isset($_GET['ok'])): ?>
    <div class="alert alert-success">Pesan Anda telah dikirm, dan akan segera Kami konfirmasi.</div>
    <?php endif ?>
    <form class="form-horizontal" method="post" action="<?php echo SITE_URL ?>hubungi_kami.php">
      <div class="form-group">
        <label for="nama" class="col-sm-2 control-label">
          Nama
        </label>
        <div class="col-sm-10">
          <input type="text" class="form-control" name="nama" id="nama" maxlength="12" required="required" value="<?php echo __e($nama) ?>"/>
        </div>
      </div>
      <div class="form-group">
        <label for="no_hp" class="col-sm-2 control-label">
          Nomor HP
        </label>
        <div class="col-sm-10">
          <input type="text" class="form-control" name="no_hp" id="no_hp" maxlength="12" placeholder="08xxxxxxxxxx" required="required" value="<?php echo __e($no_hp) ?>"/>
        </div>
      </div>
      <?php if ($inv_id): ?>
      <div class="form-group">
        <label for="inv_id" class="col-sm-2 control-label">
          Invoice ID
        </label>
        <div class="col-sm-10">
          <input type="text" class="form-control" name="inv_id" id="inv_id" maxlength="32" required="required" value="<?php echo __e($inv_id) ?>"/>
        </div>
      </div>
      <?php endif; ?>
      <div class="form-group">
        <label for="pesan" class="col-sm-2 control-label">
          Pesan
        </label>
        <div class="col-sm-10">
          <textarea class="form-control" name="pesan" id="pesan" required="required" maxlength="160" rows="8"><?php echo __e($pesan) ?></textarea>
        </div>
      </div>
      <div class="form-group">
        <label for="kode" class="col-sm-2 control-label">
          Kode Keamanan
        </label>
        <div class="col-sm-10">
          <div class="input-group">
            <span class="input-group-addon" style="padding: 0;">
              <img src="<?php echo SITE_URL ?>captcha.php" style="" alt="Loading...."/>
            </span>
            <input type="text" class="form-control input-lg" name="kode" id="kode" maxlength="5" size="5" required="required" style="width: auto;"/>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
          <button type="submit" name="submit" class="btn btn-primary" value="1">
            Kirim
          </button>
        </div>
      </div>
    </form>
  </div>
  <div class="col-sm-4">
    <div class="alert alert-info">Gunakan form ini untuk bantuan, laporan kesalahan atau komplain transaksi pengisian pulsa.</div>
    <div class="well well-sm">
        <dl>
            <dt>ALAMAT</dt>
            <dd>Manado, Sulawesi Utara - Politeknik </dd>
            <dt>SMS / TELP / WA</dt>
            <dd>085211999858</dd>
            <dt>PIN BBM</dt>
            <dd>51832AD8</dd>
        </dl>
    </div>
  </div>
</div>
<?php include ('includes/footer.php'); ?>