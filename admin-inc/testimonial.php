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
    case 'menyetujui':
        $pdo->query("UPDATE testimonial SET moderasi = '0' WHERE id = '$id'");
        header("Location: " . ADM_URL . "?c=testimonial&a=" . urlencode(@$_GET['t']) .
            "&page=" . $page . "#id" . $id);
        exit();
        break;

    case 'hapus':
        $pdo->query("DELETE FROM testimonial WHERE id = '$id'");
        header("Location: " . ADM_URL . "?c=testimonial&a=" . urlencode(@$_GET['t']) .
            "&page=" . $page);
        exit();
        break;
    case 'act':
        if (isset($_POST['msg_id']))
        {
            $tid = array();
            foreach ($_POST['msg_id'] as $mid)
            {
                $tid[] = abs(intval($mid));
            }
            if ($_POST['act'] == 'del')
                $pdo->query("DELETE FROM testimonial WHERE id IN (" . implode(', ', $tid) . ")");
            elseif ($_POST['act'] == 'acc')
                $pdo->query("UPDATE testimonial SET moderasi = '0' WHERE id IN (" . implode(', ',
                    $tid) . ")");
        }
        header("Location: " . ADM_URL . "?c=testimonial&a=" . urlencode(@$_GET['t']) .
            "&page=" . $page);
        exit();
        break;
}
include (APP_PATH . '/includes/header.php');
echo '<ol class="breadcrumb"><li class=""><a href="' . ADM_URL .
    '">Admin Panel</a></li><li class="active">' .
    '<span>Testimonial</span></li></ol>';
echo '<p><div class="input-group"><span class="input-group-addon" data-toggle="tooltip" ' .
    'data-title="Tandai semua"><input type="checkbox" id="selectAll"/></span><form action="" ' .
    'method="get" name="form1"><input type="hidden" name="c" value="testimonial"/>' .
    '<select name="a" class="form-control" onchange="form1.submit()" style="width:auto">' .
    '<option value="semua"' . ($a != 'moderasi' ? ' selected="selected"' : '') .
    '>Semua</option><option value="moderasi"' . ($a == 'moderasi' ?
    ' selected="selected"' : '') . '>Moderasi</option></select></form></div></p>';
echo '<form name="myForm" method="post" action="' . ADM_URL .
    '?c=testimonial&amp;a=act&amp;t=' . urlencode($a) . '&amp;page=' . $page . '">';
echo '<ul class="list-group">';
if ($a == 'moderasi')
    $qw = "WHERE moderasi = '1'";
else
    $qw = "";
$q = $pdo->query("SELECT COUNT(*) FROM testimonial $qw");
$total = $q->fetchColumn();
if ($total)
{
    $q = $pdo->query("SELECT * FROM testimonial $qw ORDER BY tanggal DESC LIMIT $start, {$set['list_per_page']}");
    foreach ($q->fetchAll() as $msg)
    {
        echo '<li id="id' . $msg->id . '" class="list-group-item" style="' . ($msg->
            moderasi == 1 ? 'background-color:#f9f9f9;' : '') . '"><input type="checkbox" ' .
            'class="tandai" name="msg_id[]" value="' . $msg->id .
            '" style="display:none;"/><div class="list-group-item-heading">' .
            '<span class="text-muted pull-right"><small>' . format_tanggal($msg->tanggal) .
            '</small></span><strong>' . __e($msg->nama) .
            '</strong>&nbsp;(<a class="small" href="' . ADM_URL .
            '?c=sms&amp;a=kirim&amp;phone=' . urlencode($msg->no_hp) .
            '" data-toggle="modal" data-target="#myModal">' . __e($msg->no_hp) .
            '</a>)</div><div class="list-group-item-text">' . nl2br(__e($msg->pesan)) .
            '</div><div style="margin-top:5px;padding-top:5px;border-top:1px ' .
            'dashed #ddd;">';
        if ($msg->moderasi == 1)
            echo '<a href="' . ADM_URL . '?c=testimonial&amp;a=menyetujui&amp;id=' . $msg->
                id . '&amp;t=' . urlencode($a) . '&amp;page=' . $page .
                '">Menyetujui</a> &bull; ';
        echo '<a href="' . ADM_URL . '?c=testimonial&amp;a=hapus&amp;id=' . $msg->id .
            '&amp;t=' . urlencode($a) . '&amp;page=' . $page . '" onclick="return confirm(\'Apakah ' .
            'kamu yakin ingin menghapus ini?\')">Hapus</a>';
        echo '</div></li>';
    }
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
    '-- Yang ditandai --</option><option value="del">Hapus</option><option value="acc">Menyetujui' .
    '</option></select></div>';
echo '</form>';
echo pagination(ADM_URL . '?c=testimonial&amp;a=' . urlencode($a) . '&amp;', $start,
    $total, $set['list_per_page']);
