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
$page_title = 'Testimonial | ' . $set['site_name'];
$active_page = 'testimonial';
$nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
$no_hp = isset($_POST['no_hp']) ? trim($_POST['no_hp']) : '';
$pesan = isset($_POST['pesan']) ? trim($_POST['pesan']) : '';
$kode = isset($_POST['kode']) ? trim($_POST['kode']) : '';
if (isset($_POST['submit']) && !isset($_SESSION['testi']))
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
    if (empty($err)) {
        $q = $pdo->prepare("INSERT INTO testimonial (nama, no_hp, pesan, tanggal) VALUES (?, ?, ?, ?)");
        $q->execute(array($nama,$no_hp,$pesan,time()));
        $_SESSION['testi'] = 1;
        header("Location: ".SITE_URL."testimonial.php?page=$page&ok=1#alert");
    }
    else {
        $error = '<div class="alert alert-danger" id="alert"><ol><li>'.implode('</li><li>',$err).'</li></ol></div>';
    }
}
$q = $pdo->query("SELECT COUNT(*) FROM testimonial WHERE moderasi = '0'");
$total = $q->fetchColumn();
if ($total && !isset($_GET['page']) && $total > $set['list_per_page']) {
    $last_page = ceil($total / $set['list_per_page']);
    header("Location: ".SITE_URL."testimonial.php?page=".$last_page);
    exit();
}
    
include ('includes/header.php');

?>
<h3>Testimonial</h3>
<div class="row">
  <div class="col-sm-8">
    <?php
    if ($total) {
        echo '<ul class="list-group">';
        $q = $pdo->query("SELECT * FROM testimonial WHERE moderasi = '0' ORDER BY tanggal ASC LIMIT $start, {$set['list_per_page']}");
        foreach ($q->fetchAll() as $testi) {
            echo '<li class="list-group-item"><div class="list-group-item-heading">'.
                '<span class="text-muted pull-right"><small>' . format_tanggal($testi->tanggal) .
                '</small></span><strong>' . __e($testi->nama) . '</strong>&nbsp;<span class="text-muted">('.substr($testi->no_hp,0,-3).'xxx)</span></div>'.
                '<div class="list-group-item-text">' . nl2br(__e($testi->pesan)) . '</div></li>';
        }
        echo '</ul>';
        echo '<div style="margin: 0 auto;text-align:center;">'.pagination(SITE_URL.'testimonial.php?', $start, $total, $set['list_per_page']).'</div>';
    }
    ?>
  </div>
  <div class="col-sm-4">
    <div class="well well-sm">
    <h4>Kirimkan Testimonial</h4>
    <?php if (isset($error)):?>
    <?php echo $error?>
    <?php elseif (isset($_GET['ok'])):?>
    <div class="alert alert-success" id="alert">Pesan Anda telah dikirm, dan akan ditampilkan setelah disetujui Administrator.</div>
    <?php endif?>
    <form id="form1" class="" method="post" action="<?php echo SITE_URL?>testimonial.php?page=<?php echo $page?>#alert">
      <div class="form-group">
        <label for="nama" class="control-label">
          Nama
        </label>
        <input type="text" class="form-control" name="nama" id="nama" maxlength="12" required="required" value="<?php echo __e($nama)?>"<?php echo (isset($_SESSION['testi']) ? ' disabled="disabled"' : '')?>/>
      </div>
      <div class="form-group">
        <label for="no_hp" class="control-label">
          Nomor HP
        </label>
        <input type="text" class="form-control" name="no_hp" id="no_hp" maxlength="12" placeholder="08xxxxxxxxxx" required="required" value="<?php echo __e($no_hp)?>"<?php echo (isset($_SESSION['testi']) ? ' disabled="disabled"' : '')?>/>
      </div>
      <div class="form-group">
        <label for="pesan" class="control-label">
          Pesan
        </label>
        <textarea class="form-control" name="pesan" id="pesan" required="required" maxlength="160" rows="4"<?php echo (isset($_SESSION['testi']) ? ' disabled="disabled"' : '')?>><?php echo __e($pesan)?></textarea>
      </div>
      <div class="form-group">
        <label for="kode" class="control-label">
          Kode Keamanan
        </label>
          <div class="input-group">
            <span class="input-group-addon" style="padding: 0;">
              <img src="<?php echo SITE_URL?>captcha.php" style="" alt="Loading...."/>
            </span>
            <input type="text" class="form-control input-lg" name="kode" id="kode" maxlength="5" size="5" required="required"<?php echo (isset($_SESSION['testi']) ? ' disabled="disabled"' : '')?>/>
          </div>
      </div>
      <div class="form-group">
          <button type="submit" name="submit"  value="1" class="btn btn-primary<?php echo (isset($_SESSION['testi']) ? ' disabled' : '')?>">
            Kirim
          </button>
      </div>
    </form>
    </div>
  </div>
</div>
<?php

include ('includes/footer.php');

?>