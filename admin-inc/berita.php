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
    case 'hapus':
        $pdo->query("DELETE FROM berita WHERE id = '$id'");
        header("Location: " . ADM_URL . "?c=berita&ok=del&page=" . $page);
        exit();
        break;
    case 'tambah':
        $err = array();
        if (isset($_POST['submit']))
        {
            $judul = trim(@$_POST['judul']);
            $deskripsi = trim(@$_POST['deskripsi']);
            if (strlen($judul) < 2 || strlen($judul) > 100)
                $err[] = 'Judul harus 2 s/d 100 karakter.';
            elseif (empty($deskripsi))
                $err[] = 'Deskripsi tidak boleh dikosongkan.';
            if (empty($err))
            {
                $q = $pdo->prepare("INSERT INTO berita (judul, deskripsi, tanggal) VALUES (?, ?, ?)");
                $q->execute(array(
                    $judul,
                    $deskripsi,
                    time(),
                    ));
                header("Location: " . ADM_URL . "?c=berita&ok=add#berita-" . $pdo->lastInsertId
                    ());
                exit();
            }
            else
            {
                $error = '<div class="alert alert-danger"><ol><li>' . implode('</li><li>', $err) .
                    '</li></ol></div>';
            }
        }
    case 'edit':
        $q = $pdo->query("SELECT * FROM berita WHERE id = '$id'");
        if ($q->rowCount())
        {
            $br = $q->fetch();
            $judul = $br->judul;
            $deskripsi = $br->deskripsi;
            $edit = $id;
            $err = array();
            if (isset($_POST['submit']))
            {
                $judul = trim(@$_POST['judul']);
                $deskripsi = trim(@$_POST['deskripsi']);
                $uptime = isset($_POST['uptime']) ? abs(intval($_POST['uptime'])) : $br->
                    tanggal;
                if (strlen($judul) < 2 || strlen($judul) > 100)
                    $err[] = 'Judul harus 2 s/d 100 karakter.';
                elseif (empty($deskripsi))
                    $err[] = 'Deskripsi tidak boleh dikosongkan.';
                if (empty($err))
                {
                    $q = $pdo->prepare("UPDATE berita SET judul = ?, deskripsi = ?, tanggal = ? WHERE id = ?");
                    $q->execute(array(
                        $judul,
                        $deskripsi,
                        $uptime,
                        $br->id,
                        ));
                    header("Location: " . ADM_URL . "?c=berita&ok=upd&page=$page#berita-" . $br->id);
                    exit();
                }
                else
                {
                    $error = '<div class="alert alert-danger"><ol><li>' . implode('</li><li>', $err) .
                        '</li></ol></div>';
                }
            }
        }
    default:
        include (APP_PATH . '/includes/header.php');
        echo '<ol class="breadcrumb"><li class=""><a href="' . ADM_URL .
            '">Admin Panel</a></li><li class="active">' . '<span>Berita</span></li></ol>';
        echo '<div class="row"><div class="col-sm-6" id="t1">';
        if (@$_GET['ok'] == 'add')
            echo '<div class="alert alert-success">Berita berhasil ditambahkan.</div>';
        elseif (@$_GET['ok'] == 'del')
            echo '<div class="alert alert-success">Berita berhasil dihapus.</div>';
        elseif (@$_GET['ok'] == 'upd')
            echo '<div class="alert alert-success">Berita berhasil diperbarui.</div>';
        $q = $pdo->query("SELECT COUNT(*) FROM berita");
        $total = $q->fetchColumn();
        if ($total)
        {
            $query = $pdo->query("SELECT * FROM berita ORDER BY tanggal DESC LIMIT $start, {$set['list_per_page']}");
            echo '<ul class="list-group">';
            foreach ($query->fetchAll() as $berita)
            {
                echo '<li class="list-group-item" id="b-' . $berita->id .
                    '"><div class="list-group-item-heading"><span class="text-muted pull-right"><small>' .
                    format_tanggal($berita->tanggal) . '</small></span><strong>' . __e($berita->
                    judul) . '</strong></div><div class="list-group-item-text">' . substr(remove_tags
                    ($berita->deskripsi), 0, 150) .
                    '</div><div style="margin-top:5px;padding-top:5px;border-top:1px ' .
                    'dashed #ddd;"><a href="' . SITE_URL . 'berita.php?id=' . $berita->id .
                    '">Lihat</a> &bull; <a href="' . ADM_URL . '?c=berita&amp;a=edit&amp;id=' . $berita->
                    id . '&amp;page=' . $page . '#formAdd">Edit</a> &bull; <a href="' . ADM_URL .
                    '?c=berita&amp;a=hapus&amp;id=' . $berita->id . '&amp;page=' . $page .
                    '" onclick="return confirm(\'Apakah ' . 'kamu yakin ingin menghapus ini?\')">Hapus</a></div></li>';
            }
            echo '</ul>';
            echo pagination(ADM_URL . '?c=berita&amp;', $start, $total, $set['list_per_page']);
        }
        else
        {
            echo '<div class="alert alert-danger">Belum ada berita</div>';
        }
        echo '</div><div class="col-sm-6" id="t2">';
        echo '<div class="panel panel-default" id="formAdd"><div class="panel-heading">' .
            '<strong>' . (isset($edit) ? 'Mengedit' : 'Menambahkan') . ' Berita</strong>' .
            '</div><div class="panel-body" style="background-color:#fbfbfb;">' . (isset($error) ?
            $error : '') . '<form action="' . ADM_URL . '?c=berita&amp;a=' . (isset($edit) ?
            'edit&amp;id=' . $id : 'tambah') . '&amp;page=' . $page .
            '#formAdd" method="post">';
        echo '<div class="form-group"><label class="control-label">Judul</label>' .
            '<input type="text" class="form-control" name="judul" value="' . __e(@$judul) .
            '" maxlength="100" required/></div>';
        echo '<div class="form-group"><label class="control-label">Deskripsi</label>' .
            '<textarea class="form-control" name="deskripsi" rows="8" required>' . __e(@$deskripsi) .
            '</textarea></div>';
        if (isset($edit))
            echo '<div class="form-group"><div class="checkbox"><label><input type="checkbox" name="uptime" value="' .
                time() . '"/> <span>Perbarui waktu</span></label></div></div>';
        echo '<div class="form-group"><button class="btn btn-primary" type="submit" name="submit" value="1">' . (isset
            ($edit) ? 'Simpan' : 'Membuat') . '</button></div>';
        echo '</form>';
        echo '</div></div></div></div>';
        break;
}
