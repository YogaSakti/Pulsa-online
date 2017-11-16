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

$foot = '<script>$(document.body).on("click",".phone_number",function(){
                            $("#phone").val($(this).data("phone"));
                    });</script>';
switch ($a)
{
    case 'hapus_semua':
        $t = isset($_GET['t']) ? $_GET['t'] : '';
        if (isset($_POST['submit']))
        {
            if ($t == 'sms_masuk')
                $pdo->query("TRUNCATE TABLE sms_masuk");
            elseif ($t == 'sms_keluar')
                $pdo->query("TRUNCATE TABLE sms_keluar");
            header('Location: ' . ADM_URL . '?c=sms&a=' . urlencode($t));
            exit();
        }
        include (APP_PATH . '/includes/header.php');
        echo '<form method="POST" action="' . ADM_URL .
            '?c=sms&amp;a=hapus_semua&amp;t=' . __e($t) .
            '"><div class="alert alert-warning">Apakah kamu ingin menghapus menghapus semua pesan?</div>' .
            '<button class="btn btn-default" type="button" onclick="location.back()" ' .
            'data-dismiss="modal">Tidak</button>' .
            '&nbsp;<button class="btn btn-primary" type="submit" name="submit" value="1">Ya</button></form>';
        break;


    case 'hapus':
        $t = isset($_GET['t']) ? $_GET['t'] : '';
        if (isset($_POST['submit']))
        {
            if ($t == 'sms_masuk')
                $pdo->query("DELETE FROM sms_masuk WHERE in_id = $id");
            elseif ($t == 'sms_keluar')
                $pdo->query("DELETE FROM sms_keluar WHERE out_id = $id");
            header('Location: ' . ADM_URL . '?c=sms&a=' . urlencode($t));
            exit();
        }
        include (APP_PATH . '/includes/header.php');
        echo '<form method="POST" action="' . ADM_URL . '?c=sms&amp;a=hapus&amp;id=' . $id .
            '&amp;t=' . __e($t) .
            '"><div class="alert alert-warning">Apakah kamu ingin menghapus Pesan ini?</div>' .
            '<button class="btn btn-default" type="button" onclick="location.back()" data-dismiss="modal">Tidak</button>' .
            '&nbsp;<button class="btn btn-primary" type="submit" name="submit" value="1">Ya</button></form>';
        break;


    case 'kirim':
        $phone = isset($_REQUEST['phone']) ? trim(urldecode($_REQUEST['phone'])) : '';
        $pesan = isset($_REQUEST['message']) ? trim(urldecode($_REQUEST['message'])) :
            '';
        if (isset($_POST['submit']))
        {
            $q = $pdo->prepare("INSERT INTO sms_keluar (out_to, out_message, out_status, " .
                "out_submit_date, out_send_date) VALUES (?, ?, ?, ?, ?)");
            $q->execute(array(
                $phone,
                $pesan,
                '',
                time(),
                '0',
                ));
            header("Location: " . ADM_URL . "?c=sms&a=sms_keluar&ok=Pesan+dikirim.");
            exit();
        }
        $sms_center = get_set('sms_center');
        include (APP_PATH . '/includes/header.php');
        echo '<ol class="breadcrumb"><li><a href="' . ADM_URL . '">Admin Panel</a></li>' .
            '<li><a href="' . ADM_URL . '?c=sms">SMS</a></li><li class="active">' .
            '<span>Mengirim</span></li></ol>';
        echo '<form class="form-horizontal" action="' . ADM_URL . '?c=sms&amp;a=kirim"' .
            ' method="post">';
        echo '<div class="form-group"><label for="phone"' .
            ' class="col-sm-3 control-label">Nomor Handphone</label><div class="col-sm-9">' .
            '<input type="text" class="form-control" name="phone" id="phone"' .
            ' maxlength="16" placeholder="+628xxxxxxxxxx" value="' . __e($phone) .
            '" required/></div></div>';
        echo '<div class="form-group">' .
            '<label for="message" class="col-sm-3 control-label">Pesan</label>' .
            '<div class="col-sm-9"><textarea class="form-control" name="message" id="message" rows="8" maxlength="160" required>' .
            __e($pesan) . '</textarea></div></div><div class="form-group">' .
            '<div class="col-sm-offset-3 col-sm-9"><button type="submit" name="submit" ' .
            'class="btn btn-primary" value="1">Kirim</button></div></div></form>';
        break;

    case 'sms_keluar':
        include (APP_PATH . '/includes/header.php');
        echo '<ol class="breadcrumb"><li><a href="' . ADM_URL .
            '">Admin Panel</a></li><li class="active">' . '<span>SMS</span></li></ol>';
        echo (isset($_GET['ok'])) ? '<div class="alert alert-success">' . __e(urldecode
            ($_GET['ok'])) . '</div>' : '';
        echo '<ul class="nav nav-tabs"><li class=""><a href="' . ADM_URL .
            '?c=sms&amp;a=sms_masuk">SMS Masuk</a></li>' .
            '<li class="active"><a href="#">SMS Keluar</a></li>' .
            '<li class="pull-right"><a href="' . ADM_URL .
            '?c=sms&amp;a=kirim" data-toggle="modal" data-target="#myModal">Menulis</a></li></ul><div class="tab-content">' .
            '<div class="tab-pane active" style="padding: 15px 0;">';
        echo '<ul class="list-group">';
        $q = $pdo->query("SELECT COUNT(*) FROM sms_keluar");
        $total = $q->fetchColumn();
        $icons = array(
            'queued' => 'glyphicon glyphicon-time',
            'failed' => 'glyphicon glyphicon-remove',
            'cancelled' => 'glyphicon glyphicon-eject',
            'sent' => 'glyphicon glyphicon-ok',
            );
        if ($total)
        {
            echo '<li class="list-group-item text-right"><a class="btn btn-danger" href="' .
                ADM_URL . '?c=sms&amp;a=hapus_semua&amp;t=sms_keluar" data-toggle="modal" data-target="#myModal">Hapus semua pesan</a></li>';
            $q = $pdo->query("SELECT * FROM sms_keluar ORDER BY out_submit_date DESC LIMIT $start, {$set['list_per_page']}");
            foreach ($q->fetchAll() as $sms)
            {
                echo '<li class="list-group-item"><div class="list-group-item-heading">' .
                    '<span class="text-muted pull-right"><small>';
                if (array_key_exists($sms->out_status, $icons))
                    echo '<i class="' . $icons[$sms->out_status] .
                        '" data-toggle="tooltip" data-title="' . ucfirst($sms->out_status) . ' &bull; ' .
                        format_tanggal($sms->out_send_date) . '"></i>&nbsp;';
                echo format_tanggal($sms->out_submit_date) . '</small></span><strong>' . $sms->
                    out_to . '</strong></div><div class="list-group-item-text">' . nl2br(__e($sms->
                    out_message)) . '</div><div style="margin-top:5px;padding-top:5px;border-top:1px ' .
                    'dashed #ddd;"><a href="' . ADM_URL . '?c=sms&amp;a=hapus&amp;id=' . $sms->
                    out_id . '&amp;t=sms_keluar" data-toggle="modal" data-target="#myModal">Hapus</a></div></li>';
            }
        }
        else
        {
            echo '<li class="list-group-item">Tidak ada pesan</li>';
        }
        echo '</ul>';
        echo pagination(ADM_URL . '?c=sms&amp;a=' . urlencode($a) . '&amp;', $start, $total,
            $set['list_per_page']);
        echo '</div></div>';
        break;

    default:
    case 'sms_masuk':
        include (APP_PATH . '/includes/header.php');
        echo '<ol class="breadcrumb"><li class=""><a href="' . ADM_URL .
            '">Admin Panel</a></li><li class="active">' . '<span>SMS</span></li></ol>';
        echo '<ul class="nav nav-tabs"><li class="active"><a href="#">SMS Masuk</a></li>' .
            '<li class=""><a href="' . ADM_URL .
            '?c=sms&amp;a=sms_keluar">SMS Keluar</a></li>' .
            '<li class="pull-right"><a href="' . ADM_URL .
            '?c=sms&amp;a=kirim" data-toggle="modal" data-target="#myModal">Menulis</a></li></ul>' .
            '<div class="tab-content">' .
            '<div class="tab-pane active" style="padding: 15px 0;">';
        echo '<ul class="list-group">';
        $q = $pdo->query("SELECT COUNT(*) FROM sms_masuk");
        $total = $q->fetchColumn();
        if ($total)
        {
            echo '<li class="list-group-item text-right"><a class="btn btn-danger" href="' .
                ADM_URL . '?c=sms&amp;a=hapus_semua&amp;t=sms_masuk" data-toggle="modal" data-target="#myModal">Hapus semua pesan</a></li>';
            $q = $pdo->query("SELECT * FROM sms_masuk ORDER BY in_timestamp DESC LIMIT $start, {$set['list_per_page']}");
            foreach ($q->fetchAll() as $sms)
            {
                echo '<li class="list-group-item"><div class="list-group-item-heading">' .
                    '<span class="text-muted pull-right"><small>' . format_tanggal($sms->
                    in_timestamp) . '</small></span><strong>' . $sms->in_from .
                    '</strong></div><div class="list-group-item-text">' . nl2br(__e($sms->
                    in_message)) . '</div><div style="margin-top:5px;padding-top:5px;border-top:1px ' .
                    'dashed #ddd;"><a href="' . ADM_URL . '?c=sms&amp;a=kirim&amp;phone=' .
                    rawurlencode($sms->in_from) .
                    '" data-toggle="modal" data-target="#myModal">Balas</a> &bull; <a href="' .
                    ADM_URL . '?c=sms&amp;a=kirim&amp;message=' . urlencode($sms->in_message) .
                    '" data-toggle="modal" data-target="#myModal">Meneruskan</a> &bull; <a href="' .
                    ADM_URL . '?c=sms&amp;a=hapus&amp;id=' . $sms->in_id .
                    '&amp;t=sms_masuk" data-toggle="modal" data-target="#myModal">Hapus</a></div></li>';
            }
        }
        else
        {
            echo '<li class="list-group-item">Tidak ada pesan</li>';
        }
        echo '</ul>';
        echo pagination(ADM_URL . '?c=sms&amp;a=' . urlencode($a) . '&amp;', $start, $total,
            $set['list_per_page']);
        echo '</div></div>';
        break;
}
