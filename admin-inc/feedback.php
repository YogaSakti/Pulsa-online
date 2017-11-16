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

if ($a == 'hapus')
{
    $pdo->query("DELETE FROM feedback WHERE id = '$id'");
    header("Location: " . ADM_URL . "?c=feedback&a=" . urlencode(@$_GET['t']) .
        "&page=" . $page);
    exit();
}
elseif ($a == 'act')
{
    if (isset($_POST['msg_id']))
    {
        $tid = array();
        foreach ($_POST['msg_id'] as $mid)
        {
            $tid[] = abs(intval($mid));
        }
        if ($_POST['act'] == 'del')
            $pdo->query("DELETE FROM feedback WHERE id IN (" . implode(', ', $tid) . ")");
        elseif ($_POST['act'] == 'acc')
            $pdo->query("UPDATE feedback SET baca = '1' WHERE id IN (" . implode(', ', $tid) .
                ")");
    }
    header("Location: " . ADM_URL . "?c=feedback&a=" . urlencode(@$_GET['t']) .
        "&page=" . $page);
    exit();
}
include (APP_PATH . '/includes/header.php');
echo '<ol class="breadcrumb"><li class=""><a href="' . ADM_URL .
    '">Admin Panel</a></li><li class="active">' .
    '<span>Umpan Balik</span></li></ol>';
echo '<p><div class="input-group"><span class="input-group-addon" data-toggle="tooltip" ' .
    'data-title="Tandai semua"><input type="checkbox" id="selectAll"/></span><form action="" method="get" name="form1"><input type="hidden" name="c" value="feedback"/>' .
    '<select name="a" class="form-control" onchange="form1.submit()" style="width:auto">' .
    '<option value="semua"' . ($a != 'belum_dibaca' && $a != 'sudah_dibaca' ?
    ' selected="selected"' : '') .
    '>Semua Pesan</option><option value="belum_dibaca"' . ($a == 'belum_dibaca' ?
    ' selected="selected"' : '') . '>Belum Dibaca</option>' .
    '<option value="sudah_dibaca"' . ($a == 'sudah_dibaca' ? ' selected="selected"' :
    '') . '>Sudah Dibaca</option></select></form></div></p>';
echo '<form name="myForm" method="post" action="' . ADM_URL .
    '?c=feedback&amp;a=act&amp;t=' . urlencode($a) . '&amp;page=' . $page . '">';

echo '<ul class="list-group">';
if ($a == 'belum_dibaca')
    $qw = "WHERE baca = '0'";
elseif ($a == 'sudah_dibaca')
    $qw = "WHERE baca = '1'";
else
    $qw = "";
$q = $pdo->query("SELECT COUNT(*) FROM feedback $qw");
$total = $q->fetchColumn();
if ($total)
{
    $msg_id = array();
    $q = $pdo->query("SELECT * FROM feedback $qw ORDER BY tanggal DESC LIMIT $start, {$set['list_per_page']}");
    foreach ($q->fetchAll() as $msg)
    {
        if ($msg->baca == 0)
            $msg_id[] = $msg->id;
        echo '<li class="list-group-item" style="' . ($msg->baca == 0 ?
            'background-color:#f9f9f9;' : '') . '"><input type="checkbox" ' .
            'class="tandai" name="msg_id[]" value="' . $msg->id .
            '" style="display:none;"/><div class="list-group-item-heading">' .
            '<span class="text-muted pull-right"><small>' . format_tanggal($msg->tanggal) .
            '</small></span><strong>' . __e($msg->nama) .
            '</strong>&nbsp;(<a class="small" href="' . ADM_URL .
            '?c=sms&amp;a=kirim&amp;phone=' . urlencode($msg->no_hp) .
            '" data-toggle="modal" data-target="#myModal">' . __e($msg->no_hp) .
            '</a>)</div><div class="list-group-item-text">';
        if ($msg->inv_id != '')
        {
            echo '* Komplain: <a href="' . ADM_URL . '?c=transaksi&amp;inv_id=' . $msg->
                inv_id . '">' . $msg->inv_id . '</a><br/>';
        }
        echo nl2br(__e($msg->pesan)) .
            '</div><div style="margin-top:5px;padding-top:5px;border-top:1px ' .
            'dashed #ddd;"><a href="' . ADM_URL . '?c=feedback&amp;a=hapus&amp;id=' . $msg->
            id . '&amp;t=' . urlencode($a) . '&amp;page=' . $page .
            '" onclick="return confirm(\'Apakah ' . 'kamu yakin ingin menghapus ini?\')">Hapus</a></div></li>';
    }
    if ($msg_id)
        $pdo->query("UPDATE feedback SET baca = 1 WHERE id IN (" . implode(', ', $msg_id) .
            ")");

    $foot = '<script>$(document).ready(function(){
                $(\'#selectAll\').click(function(event) {' . 'if(this.checked) {
                        $(\'.tandai\').each(function() {
                            $(this).attr("checked",true);
                            $(this).parent().addClass("active");
                        });
                        }
                        else {' . '$(\'.tandai\').each(function() {
                        $(this).attr("checked",false);
                        $(this).parent().removeClass("active");
                    });
                    }});
                $(".list-group-item").click(function(){
                    if ($(this).hasClass("active")){
                    $(this).removeClass("active");
                    $(this).children(".tandai").attr("checked",false);
                    }
                    else{
                        $(this).addClass("active");
                        $(this).children(".tandai").attr("checked",true);
                    }
                });
                });
                $(document.body).on("click",".phone_number",function(){
                            $("#phone").val($(this).data("phone"));
                    });</script>';
}
else
{
    echo '<li class="list-group-item list-group-item-info">Tidak ada pesan</li>';
}
echo '</ul>';
echo '<div><select class="form-control" style="width:auto" onchange="myForm.submit()" name="act"><option value="">' .
    '-- Yang ditandai --</option><option value="del">Hapus</option></select></div>';
echo '</form>';
echo pagination(ADM_URL . '?c=feedback&amp;a=' . urlencode($a) . '&amp;', $start,
    $total, $set['list_per_page']);
