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
$page_title = 'Berita | ' . $set['site_name'];
$active_page = 'berita';
$id = isset($_GET['id']) ? intval($_GET['id']) : false;
if ($id)
{
    $query = $pdo->prepare("SELECT * FROM berita WHERE id = ?");
    $query->execute(array($id));
    if (!$query->rowCount())
    {
        include ('includes/header.php');
        echo '<div class="alert alert-danger">Berita tidak ditemukan atau telah dihapus</div>';
    }
    else
    {
        $berita = $query->fetch();
        $page_title = $berita->judul . ' | ' . $set['site_name'];
        $page_description = mb_substr(remove_tags($berita->deskripsi), 0, 150);
        $image = get_thumb($berita->deskripsi, '');
        if ($image)
        {
            $page_image = $image;
        }
        include ('includes/header.php');
        echo '<ol class="breadcrumb"><li><a href="' . SITE_URL .
            'index.php">Home</a></li><li><a href="' . SITE_URL .
            'berita.php">Berita</a></li>' . '<li class="active"><span>' . __e($berita->
            judul) . '</span></li></ol>';
        echo '<h1>' . __e($berita->judul) .
            ' </h1><p><i class="glyphicon glyphicon-time"></i> ' . format_tanggal($berita->
            tanggal, true) . '</p><p>' . ($berita->deskripsi) . '</p>';
    }
}
else
{
    $css = '.media-left img {width:120px;height:120px;}.media a{color:#1a0dab}.media a:visited{color:#609}';
    $css .= '@media (max-width:769px) {.media-left img {width:80px;height:80px;}}';
    $head = '<style type="text/css">' . $css . '</style>';
    include ('includes/header.php');
    echo '<ol class="breadcrumb"><li><a href="' . SITE_URL .
        'index.php">Home</a></li><li class="active"><span>Berita</span></li></ol>';
    $query = $pdo->query("SELECT COUNT(*) FROM berita");
    $total = $query->fetchColumn();
    if ($total)
    {
        echo '<ul class="media-list">';
        $query = $pdo->query("SELECT * FROM berita ORDER BY tanggal DESC LIMIT $start, {$set['list_per_page']}");
        foreach ($query->fetchAll() as $berita)
        {
            $link = SITE_URL . 'berita.php/' . date('Y/m/d', $berita->tanggal) . '/' . str_link($berita->
                judul) . '?id=' . $berita->id;
            echo '<li class="media"><div class="media-left"><a href="' . $link . '" title="' .
                __e($berita->judul) . '"><img class="media-object" src="' . get_thumb($berita->
                deskripsi, SITE_URL . 'assets/img1.png') . '" alt="' . __e($berita->judul) .
                '"></a></div><div class="media-body">' . '<h3 class="media-heading"><a href="' .
                $link . '" title="' . __e($berita->judul) .
                '"></h3><h4 style="margin-bottom:0">' . __e($berita->judul) .
                '</h3><span class="small" style="color:#006621;"><i class="glyphicon glyphicon-time"></i> ' .
                format_tanggal($berita->tanggal) . '</span></a></h4><p>' . mb_substr(remove_tags
                ($berita->deskripsi), 0, 250) . '... <span><a href="' . $link .
                '">Baca selengkapnya &raquo;</a></span></p></li>';
        }
        echo '</ul>';
        echo '<div style="margin: 0 auto;text-align:center;">' . pagination(SITE_URL .
            'berita.php?', $start, $total, $set['list_per_page']) . '</div>';
    }
    else
    {
        echo '<div class="alert alert-info">Belum ada berita.</div>';
    }
}
include ('includes/footer.php');
