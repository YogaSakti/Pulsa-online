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

if (!function_exists('mb_substr'))
{
    function mb_substr($str, $start = null, $length = null)
    {
        return substr($str, $start, $length);
    }
}

if (!function_exists('mb_strlen'))
{
    function mb_strlen($str)
    {
        return strlen($str);
    }
}

if (!function_exists('get_thumb'))
{
    function get_thumb($str, $default_image = '')
    {
        preg_match('/\<img(.*?)src=\"(.*?)\"(.*?)\>/i', $str, $matches,
            PREG_OFFSET_CAPTURE);
        if ($matches)
        {
            return strip_tags($matches[2][0]);
        }
        return $default_image;
    }
}

if (!function_exists('remove_tags'))
{
    function remove_tags($str)
    {
        $str = str_replace('<', ' <', $str);
        $str = strip_tags($str);
        $str = preg_replace('!\s+!', ' ', $str);
        return trim($str);
    }
}

if (!function_exists('str_link'))
{
    function str_link($str)
    {
        setlocale(LC_ALL, 'en_US.UTF8');
        $plink = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $plink = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $plink);
        $plink = strtolower(trim($plink, '-'));
        $plink = preg_replace("/[\/_| -]+/", '-', $plink);

        return $plink;
    }
}

if (!function_exists('format_uang'))
{
    function format_uang($nilai)
    {
        return number_format($nilai, 0, '', '.');
    }
}

if (!function_exists('pagination'))
{
    function pagination($url, $start, $total, $kmess)
    {
        if ($total <= $kmess)
            return;
        $page_str = 'page=%d';
        $neighbors = 5;
        if ($start >= $total)
            $start = max(0, $total - (($total % $kmess) == 0 ? $kmess : ($total % $kmess)));
        else
            $start = max(0, (int)$start - ((int)$start % (int)$kmess));
        $base_link = '<li><a href="' . strtr($url, array('%' => '%%')) . $page_str .
            '">%s</a></li>';
        $out[] = $start == 0 ?
            '<li class="disabled"><span><span aria-hidden="true">&laquo; Prev</span></span></li>' :
            sprintf('<li><a href="' . strtr($url, array('%' => '%%')) . $page_str .
            '">%s</a></li>', $start / $kmess, '&laquo; Prev');
        if ($start > $kmess * $neighbors)
            $out[] = sprintf($base_link, 1, '1');
        if ($start > $kmess * ($neighbors + 1))
        {
            $out[] = '<li class="disabled"><span><span aria-hidden="true">...</span></span></li>';
        }
        for ($nCont = $neighbors; $nCont >= 1; $nCont--)
            if ($start >= $kmess * $nCont)
            {
                $tmpStart = $start - $kmess * $nCont;
                $out[] = sprintf($base_link, $tmpStart / $kmess + 1, $tmpStart / $kmess + 1);
            }
        $out[] = '<li class="active"><a href="#">' . ($start / $kmess + 1) . '</a></li>';
        $tmpMaxPages = (int)(($total - 1) / $kmess) * $kmess;
        for ($nCont = 1; $nCont <= $neighbors; $nCont++)
            if ($start + $kmess * $nCont <= $tmpMaxPages)
            {
                $tmpStart = $start + $kmess * $nCont;
                $out[] = sprintf($base_link, $tmpStart / $kmess + 1, $tmpStart / $kmess + 1);
            }
        if ($start + $kmess * ($neighbors + 1) < $tmpMaxPages)
        {
            $out[] = '<li class="disabled"><span><span aria-hidden="true">...</span></span></li>';
        }
        if ($start + $kmess * $neighbors < $tmpMaxPages)
            $out[] = sprintf($base_link, $tmpMaxPages / $kmess + 1, $tmpMaxPages / $kmess +
                1);
        if ($start + $kmess < $total)
        {
            $display_page = ($start + $kmess) > $total ? $total : ($start / $kmess + 2);
            $out[] = sprintf('<li><a href="' . strtr($url, array('%' => '%%')) . $page_str .
                '">%s</a></li>', $display_page, 'Next &raquo;');
        }
        else
        {
            $out[] = '<li class="disabled"><span><span aria-hidden="true">Next &raquo;</span></span></li>';
        }

        $html = '<nav><ul class="pagination">' . implode('', $out) . '</ul></nav>';

        return $html;
    }
}

if (!function_exists('format_tanggal'))
{
    function format_tanggal($var, $full = false)
    {
        global $set;
        $shift = $set['zona_waktu'] * 3600;
        if ($full != false)
        {
            $tanggal = strtr(date("-N, j #m Y H:i", $var + $shift), array(
                '-1' => 'Senin',
                '-2' => 'Selsa',
                '-3' => 'Rabu',
                '-4' => 'Kamis',
                '-5' => 'Jum&quot;at',
                '-6' => 'Sabtu',
                '-7' => 'Minggu',
                '#01' => 'Januari',
                '#02' => 'Februari',
                '#03' => 'Maret',
                '#04' => 'April',
                '#05' => 'Mei',
                '#06' => 'Juni',
                '#07' => 'Juli',
                '#08' => 'Agustus',
                '#09' => 'September',
                '#10' => 'Oktober',
                '#11' => 'November',
                '#12' => 'Desember',
                ));
            return $tanggal . ' WIB';
        }
        return date("d/m/Y H:i", $var + $shift);
    }
}

if (!function_exists('__e'))
{
    function __e($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('get_set'))
{
    function get_set($key = null)
    {
        global $pdo;
        if ($key == null)
        {
            $result = array();
            $query = $pdo->query("SELECT * FROM setelan WHERE set_autoload = 'yes'");
            foreach ($query->fetchAll() as $set)
            {
                $result[$set->set_key] = $set->set_val;
            }
        }
        elseif (is_array($key))
        {
            $result = array();
            $query = $pdo->query("SELECT * FROM setelan WHERE set_key IN ('" . implode("', '",
                $key) . "')");
            foreach ($query->fetchAll() as $set)
            {
                $result[$set->set_key] = $set->set_val;
            }
        }
        else
        {
            $query = $pdo->prepare("SELECT * FROM setelan WHERE set_key = ?");
            $query->execute(array($key));
            if ($query->rowCount())
            {
                $res = $query->fetch();
                $result = $res->set_val;
            }
            else
            {
                $result = false;
            }
        }
        return $result;
    }
}
