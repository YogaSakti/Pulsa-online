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
    case 'tambah_voucher':
        if ($id || isset($_POST['id']))
        {
            if (isset($_POST['id']))
                $id = abs(intval($_POST['id']));
            $req = $pdo->query("SElECT * FROM operator WHERE op_id = '$id'");
            if (!$req->rowCount())
            {
                include (APP_PATH . '/includes/header.php');
                echo '<div class="alert alert-danger">Provider tidak ditemukan.</div>';
                include (APP_PATH . '/includes/footer.php');
                exit();
            }
            $op = $req->fetch();
        }
        $err = array();
        $kode = isset($_POST['kode']) ? trim(strtoupper($_POST['kode'])) : '';
        $nominal = isset($_POST['nominal']) ? trim($_POST['nominal']) : '';
        $harga = isset($_POST['harga']) ? abs($_POST['harga']) : '';
        $status = isset($_POST['status']) ? trim(strtoupper($_POST['status'])) : '1';
        if (isset($_POST['submit']))
        {
            if (empty($kode))
                $err[] = 'Silakan masukan Kode Voucher.';
            if (empty($nominal))
                $err[] = 'Silakan masukan Nominal Voucher.';
            if (empty($harga))
                $err[] = 'Silakan masukan Harga Voucher.';
            if (!in_array($status, array('0', '1')))
                $err[] = 'Status tidak benar.';
            if (empty($err))
            {
                $q = $pdo->prepare("INSERT INTO voucher (op_id,vo_nominal,vo_harga,vo_kode,vo_status) VALUES (?,?,?,?,?)");
                $q->execute(array(
                    $id,
                    $nominal,
                    $harga,
                    $kode,
                    $status,
                    ));
                header('Location: ' . ADM_URL . '?c=operator&a=view&id=' . $id);
                exit();
            }
        }
        include (APP_PATH . '/includes/header.php');
        echo '<ol class="breadcrumb"><li><a href="' . ADM_URL .
            '">Admin Panel</a></li><li><a href="' . ADM_URL . '?c=operator' . ($id ?
            '&amp;a=view&amp;id=' . $id : '') . '">Provider</a></li><li class="active">' .
            '<span>Tambah Voucher</span></li></ol>';
        if ($err)
            echo '<div class="alert alert-danger"><h3>Kesalahan</h3><ol><li>' . implode('</li><li>',
                $err) . '</li></ol></div>';
        echo '<form method="post" action="' . ADM_URL .
            '?c=operator&amp;a=tambah_voucher">';
        $q = $pdo->query("SELECT * FROM operator ORDER BY op_produk ASC, op_nama ASC");
        $ops = $q->fetchAll();
        echo '<div class="form-group"><label class="control-label">Provider</label>' .
            '<select class="form-control" name="id">';
        $my_produk = array();
        foreach ($ops as $operator)
        {
            if ($produk->{$operator->op_produk}->status == 'off')
                continue;
            if (!array_key_exists($operator->op_produk, $my_produk))
            {
                echo (count($my_produk) == 0 ? '' : '</optgroup>') . PHP_EOL;
                echo '<optgroup label="' . $produk->{$operator->op_produk}->nama .
                    '" id="produk-' . $operator->op_produk . '">' . PHP_EOL;
            }
            $my_produk[$operator->op_produk][$operator->op_id] = $operator->op_nama;
            echo '<option value="' . $operator->op_id . '">' . $operator->op_nama .
                '</option>' . PHP_EOL;
        }
        echo '</select></div>';
        echo '<div class="form-group"><label class="control-label">Kode</label>' .
            '<input class="form-control" name="kode" value="' . __e($kode) .
            '"/></div><div class="form-group">' .
            '<label class="control-label">Nominal</label><input class="form-control" name="nominal" value="' .
            __e($nominal) . '"/>' .
            '</div><div class="form-group"><label class="control-label">Harga</label>' .
            '<input class="form-control" name="harga" value="' . __e($harga) .
            '"/></div><div class="form-group">' .
            '<label class="control-label">Status</label><select class="form-control" name="status">' .
            '<option value="1"' . ($status == 1 ? ' selected' : '') .
            '>Tersedia</option><option value="0" ' . ($status == 0 ? ' selected' : '') .
            '>Tidak Tersedia</option></select>' .
            '</div><div class="form-group"><button class="btn btn-primary" type="submit" name="submit" value="1">Membuat</button>' .
            '</div></form>';
        break;

    case 'edit_voucher':
        $req = $pdo->query("SElECT * FROM voucher WHERE vo_id = '$id'");
        if (!$req->rowCount())
        {
            include (APP_PATH . '/includes/header.php');
            echo '<div class="alert alert-danger">Voucher tidak ditemukan.</div>';
            include (APP_PATH . '/includes/footer.php');
            exit();
        }
        $vo = $req->fetch();
        $err = array();
        $kode = isset($_POST['kode']) ? trim(strtoupper($_POST['kode'])) : $vo->vo_kode;
        $nominal = isset($_POST['nominal']) ? trim($_POST['nominal']) : $vo->vo_nominal;
        $harga = isset($_POST['harga']) ? abs($_POST['harga']) : $vo->vo_harga;
        $status = isset($_POST['status']) ? trim(strtoupper($_POST['status'])) : $vo->
            vo_status;
        if (isset($_POST['submit']))
        {
            if (empty($kode))
                $err[] = 'Silakan masukan Kode Voucher.';
            elseif (strlen($kode) > 6)
                $err[] = 'Kode Voucher maksimal 6 karakter.';
            if (empty($nominal))
                $err[] = 'Silakan masukan Nominal Voucher.';
            if (empty($harga))
                $err[] = 'Silakan masukan Harga Voucher.';
            if (!in_array($status, array('0', '1')))
                $err[] = 'Status tidak benar.';
            if (empty($err))
            {
                $q = $pdo->prepare("UPDATE voucher SET vo_nominal = ?, vo_harga = ?, vo_kode = ?, " .
                    "vo_status = ? WHERE vo_id = ?");
                $q->execute(array(
                    $nominal,
                    $harga,
                    $kode,
                    $status,
                    $id,
                    ));
                header('Location: ' . ADM_URL . '?c=operator&a=view&id=' . $vo->op_id);
                exit();
            }
        }
        include (APP_PATH . '/includes/header.php');
        if ($err)
            echo '<div class="alert alert-danger"><h3>Kesalahan</h3><ol><li>' . implode('</li><li>',
                $err) . '</li></ol></div>';
        echo '<form method="post" action="' . ADM_URL .
            '?c=operator&amp;a=edit_voucher&amp;id=' . $id . '">';
        echo '<div class="form-group"><label class="control-label">Kode</label>' .
            '<input class="form-control" name="kode" value="' . __e($kode) .
            '"/></div><div class="form-group">' .
            '<label class="control-label">Nominal</label><input class="form-control" name="nominal" value="' .
            __e($nominal) . '"/>' .
            '</div><div class="form-group"><label class="control-label">Harga</label>' .
            '<input class="form-control" name="harga" value="' . __e($harga) .
            '"/></div><div class="form-group">' .
            '<label class="control-label">Status</label><select class="form-control" name="status">' .
            '<option value="1"' . ($status == 1 ? ' selected' : '') .
            '>Tersedia</option><option value="0" ' . ($status == 0 ? ' selected' : '') .
            '>Tidak Tersedia</option></select>' .
            '</div><div class="form-group"><button class="btn btn-primary" type="submit" name="submit" value="1">Simpan</button>' .
            '</div></form>';
        break;

    case 'edit':
        $query = $pdo->query("SELECT * FROM operator WHERE op_id = '$id'");
        if (!$query->rowCount())
        {
            include (APP_PATH . '/includes/header.php');
            echo '<div class="alert alert-danger">Provider tidak ditemukan.</div>';
            include (APP_PATH . '/includes/header.php');
            exit();
        }
        $op = $query->fetch();
        $err = array();
        $op_name = isset($_POST['op_name']) ? trim(@$_POST['op_name']) : $op->op_nama;
        $op_produk = isset($_POST['op_produk']) ? trim(@$_POST['op_produk']) : $op->
            op_produk;
        if (isset($_POST['submit']))
        {
            if (empty($op_name))
                $err[] = 'Silakan masukan Nama Provider.';
            elseif (strlen($op_name) > 32)
                $err[] = 'Nama Provider maksimal 32 karakter.';
            if (empty($err))
            {
                $q = $pdo->prepare("UPDATE operator SET  op_nama = ?, op_produk = ? WHERE op_id = ?");
                $q->execute(array(
                    $op_name,
                    $op_produk,
                    $id,
                    ));
                header('Location: ' . ADM_URL . '?c=operator&a=view&id=' . $op->op_id);
                exit();
            }
        }
        include (APP_PATH . '/includes/header.php');
        echo '<ol class="breadcrumb"><li><a href="' . ADM_URL . '">Admin Panel</a></li>' .
            '<li><a href="' . ADM_URL . '?c=operator">Provider</a></li><li><a href="' .
            ADM_URL . '?c=operator&amp;a=view&amp;id=' . $id . '">' . __e($op->op_nama) .
            '</a></li><li class="active">' . '<span>Edit</span></li></ol>';
        if ($err)
            echo '<div class="alert alert-danger"><h3>Kesalahan</h3><ol><li>' . implode('</li><li>',
                $err) . '</li></ol></div>';
        echo '<form method="post" action="' . ADM_URL . '?c=operator&amp;a=edit&amp;id=' .
            $id . '">' . '<div class="form-group"><label class="control-label">Nama Provider</label>' .
            '<input class="form-control" name="op_name" value="' . __e($op_name) .
            '"/></div><div class="form-group">' .
            '<label class="control-label">Jenis Produk</label><select class="form-control" name="op_produk">';
        foreach ($produk as $pr_k => $pr_v)
        {
            echo '<option value="' . $pr_k . '"' . ($op->op_produk == $pr_k ?
                ' selected="selected"' : '') . '>' . $pr_v->nama . '</option>';
        }
        echo '</select></div><div class="form-group"><button class="btn btn-primary" type="submit" name="submit" value="1">Simpan</button>' .
            '</div></form>';
        break;

    case 'tambah':
        $err = array();
        $op_name = trim(@$_POST['op_name']);
        $op_produk = @$_POST['op_produk'];
        if (isset($_POST['submit']))
        {
            if (empty($op_name))
                $err[] = 'Silakan masukan Nama Provider.';
            elseif (strlen($op_name) > 32)
                $err[] = 'Nama Provider maksimal 32 karakter.';
            if (!array_key_exists($op_produk, $produk))
                $err[] = 'Silakan masukan Jenis Produk.';
            elseif (strlen($op_produk) > 32)
                $err[] = 'Jenis Produk maksimal 32 karakter.';
            if (empty($err))
            {
                $q = $pdo->prepare("INSERT INTO operator (op_produk,op_nama) VALUES (?, ?)");
                $q->execute(array($op_produk, $op_name));
                header('Location: ' . ADM_URL . '?c=operator&a=view&id=' . $pdo->lastInsertId());
                exit();
            }
        }
        include (APP_PATH . '/includes/header.php');
        echo '<ol class="breadcrumb"><li><a href="' . ADM_URL .
            '">Admin Panel</a></li><li><a href="' . ADM_URL .
            '?c=operator">Provider</a></li><li class="active">' .
            '<span>Tambah</span></li></ol>';
        if ($err)
            echo '<div class="alert alert-danger"><h3>Kesalahan</h3><ol><li>' . implode('</li><li>',
                $err) . '</li></ol></div>';
        echo '<form method="post" action="' . ADM_URL . '?c=operator&amp;a=tambah">' .
            '<div class="form-group"><label class="control-label">Nama Provider</label>' .
            '<input class="form-control" name="op_name" value="' . __e($op_name) .
            '"/></div><div class="form-group">' .
            '<label class="control-label">Jenis Produk</label><select class="form-control"  value=" name="op_produk">';
        foreach ($produk as $pr_k => $pr_v)
        {
            echo '<option value="' . $pr_k . '">' . $pr_v->nama . '</option>';
        }
        echo '</select></div><div class="form-group"><button class="btn btn-primary" type="submit" name="submit" value="1">Membuat</button>' .
            '</div></form>';
        break;

    case 'hapus':
        if (isset($_POST['submit']))
        {
            $pdo->query("DELETE FROM operator WHERE op_id = $id");
            $pdo->query("DELETE FROM voucher WHERE op_id = $id");
            header('Location: ' . ADM_URL . '?c=operator');
            exit();
        }
        include (APP_PATH . '/includes/header.php');
        echo '<form method="POST" action="' . ADM_URL .
            '?c=operator&amp;a=hapus&amp;id=' . $id .
            '"><div class="alert alert-warning">Apakah kamu ingin menghapus Provider ini?</div>' .
            '<button class="btn btn-default" type="button" onclick="location.back()" data-dismiss="modal">Tidak</button>' .
            '&nbsp;<button class="btn btn-primary" type="submit" name="submit" value="1">Ya</button></form>';
        break;

    case 'voucher_actions':
        if (isset($_POST['vo_id']))
        {
            $vid = array();
            foreach ($_POST['vo_id'] as $_vid)
            {
                if (ctype_digit($_vid))
                    $vid[] = $_vid;
            }
            if ($_POST['action'] == 'delete')
                $pdo->query("DELETE FROM voucher WHERE vo_id IN (" . implode(', ', $vid) .
                    ") AND op_id = '$id'");
            elseif ($_POST['action'] == 'sts0')
                $pdo->query("UPDATE voucher SET vo_status = '0' WHERE vo_id IN (" . implode(', ',
                    $vid) . ") AND op_id = '$id'");
            elseif ($_POST['action'] == 'sts1')
                $pdo->query("UPDATE voucher SET vo_status = '1' WHERE vo_id IN (" . implode(', ',
                    $vid) . ") AND op_id = '$id'");
        }
        header('Location: ' . ADM_URL . '?c=operator&a=view&id=' . $id);
        exit();
        break;

    case 'hapus_voucher':
        $q = $pdo->query("SELECT * FROM voucher WHERE vo_id = '$id'");
        if (!$q->rowCount())
        {
            include (APP_PATH . '/includes/header.php');
            echo '<div class="alert alert-danger">Voucher tidak ditemukan.</div>';
            include (APP_PATH . '/includes/footer.php');
            exit();
        }
        $op = $q->fetch();
        if (isset($_POST['submit']))
        {
            $pdo->query("DELETE FROM voucher WHERE vo_id = $id");
            header('Location: ' . ADM_URL . '?c=operator&a=view&id=' . $op->op_id);
            exit();
        }
        include (APP_PATH . '/includes/header.php');
        echo '<form method="POST" action="' . ADM_URL .
            '?c=operator&amp;a=hapus_voucher&amp;id=' . $id .
            '"><div class="alert alert-warning">Apakah kamu ingin menghapus Voucher ini?</div>' .
            '<button class="btn btn-default" type="button" onclick="location.back()" data-dismiss="modal">Tidak</button>' .
            '&nbsp;<button class="btn btn-primary" type="submit" name="submit" value="1">Ya</button></form>';
        break;

    case 'view':
        include (APP_PATH . '/includes/header.php');
        $q = $pdo->prepare("SELECT * FROM operator WHERE op_id = ?");
        $q->execute(array($id));
        if ($q->rowCount() == 0)
        {
            echo '<div class="alert alert-danger">Provider tidak ditemukan.</div>';
            include (APP_PATH . '/includes/footer.php');
            exit();
        }
        $op = $q->fetch();
        echo '<ol class="breadcrumb"><li><a href="' . ADM_URL . '">Admin Panel</a></li>' .
            '<li><a href="' . ADM_URL . '?c=operator">Provider</a></li><li class="active">' .
            '<span>' . __e($op->op_nama) . '</span></li></ol>';
        $query = $pdo->query("SELECT * FROM voucher WHERE op_id = $op->op_id ORDER by vo_harga ASC");
        if ($query->rowCount())
            $vcrs = $query->fetchAll();
        else
            $vcrs = array();
        echo '<div class="row"><div class="col-sm-8"><div class="panel panel-default">' .
            '<div class="panel-heading"><strong>Daftar Voucher ' . __e($op->op_nama) .
            '</strong>' . '</div>';
        echo '<form id="formAct" method="post" ' . 'action="' . ADM_URL .
            '?c=operator&amp;a=voucher_actions&amp;id=' . $op->op_id .
            '"><div class="table-responsive"><table class="table table-bordered">' .
            '<thead><tr><th class="text-center"><input type="checkbox" id="selectAll"/></th>' .
            '<th class="text-center">Kode</th><th class="text-center">Nominal</th>' .
            '<th class="text-center">Harga</th><th class="text-center">Status</th>' .
            '<th class="text-center">Aksi</th>' . '</tr></thead><tbody>';
        foreach ($vcrs as $vcr)
        {
            echo '<tr><td class="text-center"><input class="checkbox1" type="checkbox" name="vo_id[]" ' .
                'value="' . $vcr->vo_id . '"/></td><td>' . $vcr->vo_kode . '</td><td>' . __e($vcr->
                vo_nominal) . '</td><td>Rp. ' . format_uang($vcr->vo_harga) . '</td><td>' . ($vcr->
                vo_status ? 'Tersedia' : 'Tidak Tersedia') .
                '</td><td class="text-center" title="Edit"><a data-toggle="modal" ' .
                'data-target="#myModal" href="' . ADM_URL .
                '?c=operator&amp;a=edit_voucher&amp;id=' . $vcr->vo_id .
                '">[E]</a> &bull; <a data-toggle="modal" data-target="#myModal" ' . 'href="' .
                ADM_URL . '?c=operator&amp;a=hapus_voucher&amp;id=' . $vcr->vo_id .
                '" title="Hapus">[D]</a></td></tr>';
        }
        echo '</tbody></table></div>';
        echo '<div class="panel-body" style="border-top:0;padding-top:0"><select name="action" class="form-control"' .
            ' style="width:auto" onchange="formAct.submit()"><option value="">-- Yang ditandai --</option>' .
            '<option value="delete">Hapus</option><option value="sts1">Status Tersedia</option>' .
            '<option value="sts0">Status Tidak tersedia</option></select></div>';
        echo '</form></div></div><div class="col-sm-4">';
        echo '<div class="panel panel-default"><div class="panel-heading"><strong>Tambah Voucher</strong>' .
            '</div><div class="panel-body"><form method="post" action="' . ADM_URL .
            '?c=operator&amp;a=tambah_voucher&amp;id=' . $op->op_id . '">' .
            '<div class="form-group"><label class="control-label">Kode</label>' .
            '<input class="form-control" name="kode""/></div><div class="form-group">' .
            '<label class="control-label">Nominal</label><input class="form-control" name="nominal"/>' .
            '</div><div class="form-group"><label class="control-label">Harga</label>' .
            '<input class="form-control" name="harga"/></div><div class="form-group">' .
            '<label class="control-label">Status</label><select class="form-control" name="status">' .
            '<option value="1">Tersedia</option><option value="0">Tidak Tersedia</option></select>' .
            '</div><div class="form-group"><button class="btn btn-primary" type="submit" name="submit" value="1">Membuat</button>' .
            '</div></form></div></div>';
        echo '</div></div>';
        $foot = '<script>$(document).ready(function() {$(\'#selectAll\').click(function(event) {' .
            'if(this.checked) {$(\'.checkbox1\').each(function() {this.checked = true;});}else{' .
            '$(\'.checkbox1\').each(function() {this.checked = false;});}});});</script>';
        break;

    default:
        include (APP_PATH . '/includes/header.php');
        echo '<ol class="breadcrumb"><li><a href="' . ADM_URL .
            '">Admin Panel</a></li><li class="active">' . '<span>Provider</span></li></ol>';
        $query = $pdo->query("SELECT * FROM operator ORDER BY op_produk ASC, op_nama ASC");
        if ($query->rowCount())
            $operators = $query->fetchAll();
        else
            $operators = array();
        echo '<div class="row"><div class="col-sm-8"><div class="panel panel-default">' .
            '<div class="panel-heading"><strong><i class="glyphicon glyphicon-book"></i> Provider</strong>' .
            '</div>';
        echo '<div class="table-responsive"><table class="table table-bordered">' .
            '<thead><tr><th class="text-center">Produk</th><th class="text-center">Nama</th>' .
            '<th class="text-center">Tindakan</th>' . '</tr></thead><tbody>';
        $pr = array();
        foreach ($operators as $op)
        {
            echo '<tr' . (!in_array($op->op_produk, $pr) ? ' id="produk-' . $op->op_produk .
                '"' : '') . '><td>' . $produk->{$op->op_produk}->nama . '</td><td><a href="' .
                ADM_URL . '?c=operator&amp;a=view&amp;id=' . $op->op_id . '">' . __e($op->
                op_nama) . '</a></td><td class="text-center" title="Edit"><a data-toggle="modal" data-target="#myModal" href="' .
                ADM_URL . '?c=operator&amp;a=edit&amp;id=' . $op->op_id .
                '">[E]</a> &bull; <a data-toggle="modal" data-target="#myModal" href="' .
                ADM_URL . '?c=operator&amp;a=hapus&amp;id=' . $op->op_id .
                '" title="Hapus">[D]</a></td></tr>';
            $pr[] = $op->op_produk;
        }
        echo '</tbody></table></div>';
        echo '</div></div><div class="col-sm-4">';
        echo '<div class="panel panel-default"><div class="panel-heading"><strong>Tambah Provider</strong>' .
            '</div><div class="panel-body"><form method="post" action="' . ADM_URL .
            '?c=operator&amp;a=tambah">' .
            '<div class="form-group"><label class="control-label">Nama Provider</label>' .
            '<input class="form-control" name="op_name"/></div><div class="form-group">' .
            '<label class="control-label">Jenis Produk</label><select class="form-control" name="op_produk">';
        foreach ($produk as $pr_k => $pr_v)
        {
            echo '<option value="' . $pr_k . '">' . $pr_v->nama . '</option>';
        }
        echo '</select></div><div class="form-group"><button class="btn btn-primary" type="submit" name="submit" value="1">Membuat</button>' .
            '</div></form></div></div>';
        echo '<div class="panel panel-default"><div class="panel-heading"><strong>Tambah Voucher</strong>' .
            '</div><div class="panel-body"><form method="post" action="' . ADM_URL .
            '?c=operator&amp;a=tambah_voucher">' .
            '<div class="form-group"><label class="control-label">Provider</label><select class="form-control" name="id">';
        echo '<option value=""></option>';
        $my_produk = array();
        foreach ($operators as $operator)
        {
            if ($produk->{$operator->op_produk}->status == 'off')
                continue;
            if (!array_key_exists($operator->op_produk, $my_produk))
            {
                echo (count($my_produk) == 0 ? '' : '</optgroup>') . PHP_EOL;
                echo '<optgroup label="' . $produk->{$operator->op_produk}->nama .
                    '" id="produk-' . $operator->op_produk . '">' . PHP_EOL;
            }
            $my_produk[$operator->op_produk][$operator->op_id] = $operator->op_nama;
            echo '<option value="' . $operator->op_id . '">' . $operator->op_nama .
                '</option>' . PHP_EOL;
        }
        echo '</select></div><div class="form-group"><label class="control-label">Kode</label>' .
            '<input class="form-control" name="kode"/></div><div class="form-group">' .
            '<label class="control-label">Nominal</label><input class="form-control" name="nominal"/>' .
            '</div><div class="form-group"><label class="control-label">Harga</label>' .
            '<input class="form-control" name="harga"/></div><div class="form-group">' .
            '<label class="control-label">Status</label><select class="form-control" name="status">' .
            '<option value="1">Tersedia</option><option value="0">Tidak Tersedia</option></select>' .
            '</div><div class="form-group"><button class="btn btn-primary" type="submit" name="submit" value="1">Membuat</button>' .
            '</div></form></div></div>';
        echo '</div></div>';
        break;
}
