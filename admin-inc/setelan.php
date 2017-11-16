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

switch ($a)
{
    case 'envaya_sms':
        $enabled = ''; // <boolean>
        $server_url = ''; // <string>
        $phone_number = ''; // <string>
        $password = ''; // <string>
        $outgoing_interval = ''; // <integer>
        $keep_in_inbox = ''; // <boolean>
        $call_notifications = ''; // <boolean>
        $forward_sent = ''; // <boolean>
        $network_failover = ''; // <boolean>
        $test_mode = ''; // <boolean>
        $auto_add_test_number = ''; // <boolean>
        $ignore_shortcodes = ''; // <boolean>
        $ignore_non_numeric = ''; // <boolean>
        $amqp_enabled = ''; // <boolean>
        $amqp_port = ''; // <integer>
        $amqp_vhost = ''; // <integer>
        $amqp_ssl = ''; // <boolean>
        $amqp_user = ''; // <string>
        $amqp_password = ''; // <string>
        $amqp_queue = ''; // <string>
        $amqp_heartbeat = ''; // <integer>
        $market_version = ''; // <integer>
        $market_version_name = ''; // <string>
        $settings_version = ''; // <integer>

        $env = json_decode(get_set('envaya_sms'));
        if (isset($_POST['submit']))
        {
            $data_env = array('password' => trim(@$_POST['password']));
            if (empty($data_env['password']))
            {
                $result = '<div class="alert alert-danger">Silakan isi semua bidang.</div>';
            }
            else
            {
                $q = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
                $q->execute(array(json_encode($data_env), 'envaya_sms'));
                header('Location: ' . ADM_URL . '?c=setelan&a=envaya_sms&ok=1');
                exit();
            }
        }
        elseif (isset($_GET['ok']))
        {
            $result = '<div class="alert alert-success">Perubahan berhasil disimpan.</div>';
        }
        include (APP_PATH . '/includes/header.php');
        echo '<ol class="breadcrumb"><li><a href="' . ADM_URL .
            '">Admin Panel</a></li><li class="active">' .
            '<span>Pengaturan Envaya SMS</span></li></ol>';
        echo isset($result) ? $result : '';
        echo '<form class="form-horizontal" action="' . ADM_URL .
            '?c=setelan&amp;a=envaya_sms" method="post">';
        echo '<div class="form-group"><label for="input1"' .
            ' class="col-sm-3 control-label">Password</label><div class="col-sm-9">' .
            '<input type="text" class="form-control" name="password" id="input1"' .
            ' value="' . __e($env->password) . '" maxlength="16" required/></div></div>';
        echo '<div class="form-group"><div class="col-sm-offset-3 col-sm-9">' .
            '<button type="submit" class="btn btn-primary" name="submit" value="1">Simpan' .
            '</button></div></div>';
        echo '</form>';
        break;

    case 'saldo_pin':
        $stl = get_set(array('pin'));
        if (isset($_POST['submit']))
        {
            $data_pin = trim(@$_POST['pin']);
            $data_saldo = abs(intval(@$_POST['saldo']));
            if (empty($data_pin))
            {
                $result = '<div class="alert alert-danger">Silakan isi semua bidang.</div>';
            }
            else
            {
                $q = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
                $q->execute(array($data_saldo, 'saldo'));
                $q = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
                $q->execute(array($data_pin, 'pin'));
                header('Location: ' . ADM_URL . '?c=setelan&a=saldo_pin&ok=1');
                exit();
            }
        }
        elseif (isset($_GET['ok']))
        {
            $result = '<div class="alert alert-success">Perubahan berhasil disimpan.</div>';
        }
        include (APP_PATH . '/includes/header.php');
        echo '<ol class="breadcrumb"><li><a href="' . ADM_URL .
            '">Admin Panel</a></li><li class="active">' .
            '<span>Pengaturan Saldo & PIN</span></li></ol>';
        echo isset($result) ? $result : '';
        echo '<form class="form-horizontal" action="' . ADM_URL .
            '?c=setelan&amp;a=saldo_pin" method="post">';
        echo '<div class="form-group"><label for="saldo"' .
            ' class="col-sm-3 control-label">Saldo</label><div class="col-sm-9">' .
            '<input type="number" class="form-control" name="saldo" id="saldo"' . ' value="' .
            __e($set['saldo']) .
            '" maxlength="12" required/><p class="help-block">Jika harga pembelian' .
            ' voucher melebihi jumlah saldo maka transaksi tidak akan diproses.<br/>Kamu dapat ' .
            'menyimpan jumlah saldo sebesar mungkin jika ketersedian saldo selalu cukup untuk ' .
            'memproses semua pembelian voucher.</p></div></div>';
        echo '<div class="form-group"><label for="input2"' .
            ' class="col-sm-3 control-label">PIN</label><div class="col-sm-9">' .
            '<input type="text" class="form-control" name="pin" id="input2"' . ' value="' .
            __e($stl['pin']) . '" maxlength="12" required/></div></div>';
        echo '<div class="form-group"><div class="col-sm-offset-3 col-sm-9">' .
            '<button type="submit" class="btn btn-primary" name="submit" value="1">Simpan' .
            '</button></div></div>';
        echo '</form>';
        break;

    case 'produk':
        $edit = isset($_GET['edit']) ? $_GET['edit'] : '';
        if (isset($_POST['produk']))
        {
            $pr = isset($_POST['produk']) ? $_POST['produk'] : '';
            $pr_name = isset($_POST['nama_produk']) ? $_POST['nama_produk'] : '';
            $pr_format = isset($_POST['format_trx']) ? $_POST['format_trx'] : '';
            $status = isset($_POST['status']) ? $_POST['status'] : '';
            if (property_exists($produk, $pr) && in_array($status, array('on', 'off')))
            {
                $produk->{$pr}->nama = $pr_name;
                $produk->{$pr}->format_trx = $pr_format;
                $produk->{$pr}->status = $status;
                $q = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
                $q->execute(array(json_encode($produk), 'produk'));
            }
            header("Location: " . ADM_URL . "?c=setelan&a=produk");
            exit();
        }
        elseif (isset($_POST['tambah']))
        {
            $pr_name = isset($_POST['nama_produk']) ? $_POST['nama_produk'] : '';
            $pr_format = isset($_POST['format_trx']) ? $_POST['format_trx'] : '';
            $status = isset($_POST['status']) ? $_POST['status'] : '';
            $pr = str_replace('-', '_', str_link($pr_name));
            if (!empty($pr) && !property_exists($pr, $produk) && in_array($status, array('on',
                    'off')))
            {
                $produk->{$pr}->nama = $pr_name;
                $produk->{$pr}->format_trx = $pr_format;
                $produk->{$pr}->status = $status;
                $q = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
                $q->execute(array(json_encode($produk), 'produk'));
            }
            header("Location: " . ADM_URL . "?c=setelan&a=produk");
            exit();
        }
        include (APP_PATH . '/includes/header.php');
        echo '<ol class="breadcrumb"><li><a href="' . ADM_URL .
            '">Admin Panel</a></li><li class="active">' .
            '<span>Produk & Format Transaksi</span></li></ol>';
        echo '<form name="produkForm" method="post" action="' . ADM_URL .
            '?c=setelan&amp;a=produk"><div class="table table-responsive">' .
            '<table class="table table-bordered">' .
            '<thead><tr><th>Nama Produk</th><th>Format Transaksi</th><th>Status</th>' .
            '<th class="text-center">Tindakan</th>' . '</tr></thead><tbody>';
        foreach ($produk as $pr_k => $pr_v)
        {
            if ($edit == $pr_k)
            {
                echo '<tr id="produk-' . $pr_k .
                    '"><td><input type="hidden" name="produk" value="' . __e($pr_k) .
                    '"/><input class="form-control" type="text" name="nama_produk" value="' . __e($pr_v->
                    nama) . '"/></td><td><input class="form-control" type="text" ' .
                    'name="format_trx" value="' . __e($pr_v->format_trx) .
                    '"/></td><td><select name="status" class="form-control"><option value="on"' . ($pr_v->
                    status == 'on' ? ' selected="selected"' : '') .
                    '>On</option><option value="off"' . ($pr_v->status == 'off' ?
                    ' selected="selected"' : '') .
                    '>Off</option></select></td><td class="text-center"><a href="#" class="btn btn-default"' .
                    ' onclick="produkForm.submit();">Simpan</a></td></tr>';
            }
            else
            {
                echo '<tr id="produk-' . $pr_k . '"><td><a href="' . ADM_URL .
                    '?c=operator#produk-' . $pr_k . '">' . __e($pr_v->nama) . '</a></td><td>' . __e($pr_v->
                    format_trx) . '</td><td>' . ucfirst($pr_v->status) .
                    '</td><td class="text-center"><a href="' . ADM_URL .
                    '?c=setelan&amp;a=produk&amp;edit=' . $pr_k . '#produk-' . $pr_k .
                    '">Edit</a></td></tr>';
            }
        }
        if (!$edit)
        {
            echo '<tr><td><input type="hidden" name="tambah" value="1"/><input class="form-control" type="text" name="nama_produk" value=""/></td>' .
                '<td><input class="form-control" type="text" name="format_trx" value=""/></td>' .
                '<td><select name="status" class="form-control"><option value="on">On</option>' .
                '<option value="off" selected="selected">Off</option></select></td><td class="text-center"><a href="#" class="btn btn-default"' .
                ' onclick="produkForm.submit();">Tambah</a></td></tr>';
        }
        echo '</tbody></table></div></form>';
        echo '<p>Kode transaksi yang tersedia adalah: {KODE}, {NO_HP}, {ID_PLN} dan {PIN}.</p>';
        break;

    case 'metode_pembayaran':
        $edit = isset($_GET['edit']) ? $_GET['edit'] : '';
        $metode_pembayaran = json_decode($set['metode_pembayaran']);
        if (isset($_POST['kode_bank']))
        {
            $kode_bank = isset($_POST['kode_bank']) ? $_POST['kode_bank'] : '';
            $nomor_rek = isset($_POST['nomor_rek']) ? $_POST['nomor_rek'] : '';
            $nama_rek = isset($_POST['nama_rek']) ? $_POST['nama_rek'] : '';
            $durasi_mutasi = isset($_POST['durasi_mutasi']) ? $_POST['durasi_mutasi'] : 2;
            $ib_user = isset($_POST['ib_user']) ? $_POST['ib_user'] : '';
            $ib_pin = isset($_POST['ib_pin']) ? $_POST['ib_pin'] : '';
            $status = isset($_POST['status']) ? $_POST['status'] : '';
            if (array_key_exists($kode_bank, $metode_pembayaran) && in_array($status, array
                ('on', 'off')))
            {
                $metode_pembayaran->{$kode_bank}->nomor_rekening = $nomor_rek;
                $metode_pembayaran->{$kode_bank}->nama_rekening = $nama_rek;
                $metode_pembayaran->{$kode_bank}->api->username = $ib_user;
                $metode_pembayaran->{$kode_bank}->mutasi->durasi = abs(intval($durasi_mutasi));
                $metode_pembayaran->{$kode_bank}->api->password = $ib_pin;
                $metode_pembayaran->{$kode_bank}->status = $status;
                $q = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
                $q->execute(array(json_encode($metode_pembayaran), 'metode_pembayaran'));
            }
            header("Location: " . ADM_URL . "?c=setelan&a=metode_pembayaran&saved=1#bank");
            exit();
        }
        elseif (isset($_POST['update_paypal']))
        {
            $email = isset($_POST['email']) ? $_POST['email'] : '';
            $api_username = isset($_POST['api_username']) ? $_POST['api_username'] : '';
            $api_password = isset($_POST['api_password']) ? $_POST['api_password'] : '';
            $api_signature = isset($_POST['api_signature']) ? $_POST['api_signature'] : '';
            $rate = isset($_POST['rate']) ? $_POST['rate'] : '';
            $status = isset($_POST['status']) ? $_POST['status'] : '';
            if (in_array($status, array('on', 'off')))
            {
                $metode_pembayaran->paypal->nomor_rekening = $email;
                $metode_pembayaran->paypal->api->username = $api_username;
                $metode_pembayaran->paypal->api->password = $api_password;
                $metode_pembayaran->paypal->api->signature = $api_signature;
                $metode_pembayaran->paypal->rate = abs(intval($rate));
                $metode_pembayaran->paypal->status = $status;
                $q = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
                $q->execute(array(json_encode($metode_pembayaran), 'metode_pembayaran'));
            }
            header("Location: " . ADM_URL . "?c=setelan&a=metode_pembayaran&saved=2#paypal");
            exit();
        }
        include (APP_PATH . '/includes/header.php');
        echo '<ol class="breadcrumb"><li><a href="' . ADM_URL .
            '">Admin Panel</a></li><li class="active">' .
            '<span>Metode Pembayaran</span></li></ol>';
        echo '<h4 id="bank">Bank</h4>';
        if (isset($_GET['saved']) && $_GET['saved'] == 1)
        {
            echo '<div class="alert alert-success">Perubahan disimpan</div>';
        }
        echo '<form name="produkForm" method="post" action="' . ADM_URL .
            '?c=setelan&amp;a=metode_pembayaran"><div class="table table-responsive">' .
            '<table class="table table-bordered">' .
            '<thead><tr><th rowspan="2" style="vertical-align:middle;" class="text-center">Bank</th>' .
            '<th rowspan="2" style="vertical-align:middle;" class="text-center">Nomor Rekening</th>' .
            '<th rowspan="2" style="vertical-align:middle;" class="text-center">Nama Rekening</th>' .
            '<th colspan="2" class="text-center">Akun Internet Banking</th>' .
            '<th rowspan="2" style="vertical-align:middle;" class="text-center">Durasi Mutasi</th>' .
            '<th rowspan="2" style="vertical-align:middle;" class="text-center">Status</th>' .
            '<th rowspan="2" style="vertical-align:middle;" class="text-center">Tindakan</th>' .
            '</tr>' . '<tr><th style="vertical-align:middle;" class="text-center">Username</th>' .
            '<th style="vertical-align:middle;" class="text-center">PIN</th></tr></thead><tbody>';
        foreach ($metode_pembayaran as $b_k => $b_v)
        {
            if ($b_k == 'paypal')
                continue;
            if ($edit == $b_k)
            {
                echo '<tr id="metode_pembayaran-' . $b_k .
                    '"><td><input type="hidden" name="kode_bank" value="' . __e($b_k) . '"/>' . __e($b_v->
                    nama) . '</td><td><input class="form-control" type="text" name="nomor_rek" value="' .
                    __e($b_v->nomor_rekening) .
                    '"/></td><td><input class="form-control" type="text" ' .
                    'name="nama_rek" value="' . __e($b_v->nama_rekening) .
                    '"/></td><td><input class="form-control" type="text" ' .
                    'name="ib_user" value="' . __e($b_v->api->username) .
                    '"/></td><td><input class="form-control" type="text" ' . 'name="ib_pin" value="' .
                    __e($b_v->api->password) .
                    '"/></td><td><input class="form-control" type="text" ' .
                    'name="durasi_mutasi" value="' . __e($b_v->mutasi->durasi) .
                    '"/></td><td><select name="status" class="form-control"><option value="on"' . ($b_v->
                    status == 'on' ? ' selected="selected"' : '') .
                    '>On</option><option value="off"' . ($b_v->status == 'off' ?
                    ' selected="selected"' : '') .
                    '>Off</option></select></td><td class="text-center"><a href="#" class="btn btn-default"' .
                    ' onclick="produkForm.submit();">Simpan</a></td></tr>';
            }
            else
            {
                echo '<tr id="metode_pembayaran-' . $b_k . '"><td>' . __e($b_v->nama) .
                    '</td><td>' . __e($b_v->nomor_rekening) . '</td><td>' . __e($b_v->nama_rekening) .
                    '</td><td>' . $b_v->api->username . '</td><td>' . $b_v->api->password .
                    '</td><td>' . $b_v->mutasi->durasi . ' menit</td><td>' . ucfirst($b_v->status) .
                    '</td><td class="text-center"><a href="' . ADM_URL .
                    '?c=setelan&amp;a=metode_pembayaran&amp;edit=' . $b_k . '#metode_pembayaran-' .
                    $b_k . '">Edit</a></td></tr>';
            }
        }
        echo '</tbody></table></div></form>';
        echo '<div class="alert alert-info">Apabila ingin mengaktifkan metode pembayaran Bank BNI dan ' .
            'Bank BRI kamu harus mendaftar terlebih dahulu di layanan Billing Otomatis yang disediakan oleh ' .
            'domosquare.com. <a class="alert-link" href="https://member.domosquare.com/cart.php?gid=20" target="_new">' .
            'Klik di sini</a> untuk mendaftar.<br/>Jika sudah mendaftar isi Username Internet Banking ' .
            'dengan format: <span class="text-danger"><b>UsernameDomosquare:UsernameInternetBanking</b>' .
            '</span>.<br/>IP Akses API kamu adalah: <span class="text-danger"><b>127.0.0.1</b></span></div>';
        echo '<h4 id="paypal">PayPal</h4><div class="row"><div class="col-sm-6">';
        if (isset($_GET['saved']) && $_GET['saved'] == 2)
        {
            echo '<div class="alert alert-success">Perubahan disimpan</div>';
        }
        echo '<form method="post" action="' . ADM_URL .
            '?c=setelan&amp;a=metode_pembayaran"><div class="form-group"><label>Email</label>' .
            '<input class="form-control" type="email" name="email" value="' . __e($metode_pembayaran->
            paypal->nomor_rekening) .
            '" maxlength="255"/></div><div class="form-group"><label>API Username</label>' .
            '<input class="form-control" type="text" name="api_username" value="' . __e($metode_pembayaran->
            paypal->api->username) .
            '" maxlength="255"/></div><div class="form-group"><label>API Password</label>' .
            '<input class="form-control" type="text" name="api_password" value="' . __e($metode_pembayaran->
            paypal->api->password) .
            '" maxlength="255"/></div><div class="form-group"><label>API Signature</label>' .
            '<input class="form-control" type="text" name="api_signature" value="' . __e($metode_pembayaran->
            paypal->api->signature) .
            '" maxlength="255"/></div><div class="form-group"><label>Rate</label>' .
            '<input class="form-control" type="number" name="rate" value="' . __e($metode_pembayaran->
            paypal->rate) . '" maxlength="5"/></div><div class="form-group"><label>Status</label>' .
            '<select class="form-control" name="status"/><option value="on"' . ($metode_pembayaran->
            paypal->status == 'on' ? ' selected="selected"' : '') .
            '>On</option><option value="off"' . ($metode_pembayaran->paypal->status == 'off' ?
            ' selected="selected"' : '') .
            '>Off</option></select></div><div class="form-group">' .
            '<button class="btn btn-primary" type="submit" name="update_paypal" value="1">Simpan</button>' .
            '</div></form></div></div>';
        break;

    default:
        if (isset($_POST['submit']))
        {
            $data_site_name = trim(@$_POST['site_name']);
            $data_site_url = trim(@$_POST['site_url']);
            $data_mnt = trim(@$_POST['mnt_status']);
            $data_zona_waktu = trim(@$_POST['zona_waktu']);
            $data_page = abs(intval(trim(@$_POST['list_per_page'])));
            $data_adm = array(
                'username' => trim(@$_POST['adm_username']),
                'password' => base64_encode(trim(@$_POST['adm_password'])),
                );
            if (empty($data_adm['username']) || empty($data_adm['password']) || empty($data_site_name) ||
                empty($data_zona_waktu))
            {
                $result = '<div class="alert alert-danger">Silakan isi semua bidang.</div>';
            }
            else
            {
                $q = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
                $q->execute(array($data_site_name, 'site_name'));
                $q = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
                $q->execute(array($data_site_url, 'site_url'));
                $q = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
                $q->execute(array($data_zona_waktu, 'zona_waktu'));
                $q = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
                $q->execute(array($data_page, 'list_per_page'));
                $q = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
                $q->execute(array(json_encode($data_adm), 'admin'));
                $q = $pdo->prepare("UPDATE setelan SET set_val = ? WHERE set_key = ?");
                $q->execute(array($data_mnt, 'maintenance'));
                $_SESSION['adm_user'] = $data_adm['username'];
                $_SESSION['adm_pass'] = $data_adm['password'];
                header('Location: ' . ADM_URL . '?c=setelan&ok=1');
                exit();
            }
        }
        elseif (isset($_GET['ok']))
        {
            $result = '<div class="alert alert-success">Perubahan berhasil disimpan.</div>';
        }
        include (APP_PATH . '/includes/header.php');
        echo '<ol class="breadcrumb"><li><a href="' . ADM_URL .
            '">Admin Panel</a></li><li class="active">' .
            '<span>Pengaturan Umum</span></li></ol>';
        echo isset($result) ? $result : '';
        echo '<form class="form-horizontal" action="' . ADM_URL .
            '?c=setelan" method="post">';
        echo '<div class="form-group"><label for="input1s"' .
            ' class="col-sm-3 control-label">Nama Situs</label><div class="col-sm-9">' .
            '<input type="text" class="form-control" name="site_name" id="input1s"' .
            ' value="' . __e($set['site_name']) . '" maxlength="32" required/></div></div>';
        echo '<div class="form-group"><label for="input1sz"' .
            ' class="col-sm-3 control-label">URL Situs</label><div class="col-sm-9">' .
            '<input type="text" class="form-control" name="site_url" id="input1sz"' .
            ' value="' . __e($set['site_url']) . '"/>' .
            '<p class="help-block">Harus diakhiri garis miring.</p></div></div>';
        echo '<div class="form-group"><label for="input7s"' .
            ' class="col-sm-3 control-label">Zona Waktu</label><div class="col-sm-9">' .
            '<input type="text" class="form-control" name="zona_waktu" id="input7s"' .
            ' value="' . __e($set['zona_waktu']) . '" maxlength="8" required/></div></div>';
        echo '<div class="form-group"><label for="input7sa"' .
            ' class="col-sm-3 control-label">List Per Page</label><div class="col-sm-9">' .
            '<input type="number" class="form-control" name="list_per_page" id="input7sa"' .
            ' value="' . __e($set['list_per_page']) .
            '" maxlength="2" required/></div></div>';
        echo '<div class="form-group">' .
            '<label class="col-sm-3 control-label">Maintenance Status</label>' .
            '<div class="col-sm-9"><select class="form-control" name="mnt_status">' .
            '<option value="on"' . ($set['maintenance'] == 'on' ? ' selected' : '') .
            '>Aktif</option><option value="off" ' . ($set['maintenance'] == 'off' ?
            ' selected' : '') . '>Tidak aktif</option></select>' . '</div></div>';

        echo '<div class="form-group"><label for="input7"' .
            ' class="col-sm-3 control-label">Admin Username</label><div class="col-sm-9">' .
            '<input type="text" class="form-control" name="adm_username" id="input7"' .
            ' value="' . __e($admin->username) . '" required/></div></div>';
        echo '<div class="form-group"><label for="input8"' .
            ' class="col-sm-3 control-label">Admin Password</label><div class="col-sm-9">' .
            '<input type="text" class="form-control" name="adm_password" id="input8"' .
            ' value="' . __e(base64_decode($admin->password)) . '" required/></div></div>';
        echo '<div class="form-group"><div class="col-sm-offset-3 col-sm-9">' .
            '<button type="submit" class="btn btn-primary" name="submit" value="1">Simpan' .
            '</button></div></div>';
        echo '</form>';
        break;
}
