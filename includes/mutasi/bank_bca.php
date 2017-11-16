<?php

/**
 * @package Script Pulsa Online
 * @version 1
 * @author Engky Datz
 * @link http://okepulsa.id
 * @link http://facebook.com/Engky09
 * @link http://okepulsa.id * @link https://www.bukalapak.com/engky09
 * @copyright 2015 -2016
 */
if (!function_exists('fix_angka'))
{
    function fix_angka($string)
    {
        $string = str_replace(',', '', $string);
        $string = strtok($string, '.');
        return $string;
    }
}
function grab_bca($user, $pass)
{
    $user_ip = '202.62.16.186';
    $ua = "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) '.
    Chrome/44.0.2403.89 Safari/537.36";
    $cookie = APP_PATH . '/cache/bca-cookie.txt';

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com');
    $info = curl_exec($ch);

    $a = strstr($info, 'var s = document.createElement(\'script\'), attrs = { src: (window.location.protocol ==',
        1);
    $a = strstr($a, 'function getCurNum(){');

    $b = array(
        'return "',
        'function getCurNum(){',
        '";',
        '}',
        '{',
        '(function()',
        );

    $b = str_replace($b, '', $a);
    $curnum = trim($b);
    $params = 'value%28actions%29=login&value%28user_id%29=' . $user .
        '&value%28CurNum%29=' . $curnum . '&value%28user_ip%29=' . $user_ip .
        '&value%28browser_info%29=' . $ua . '&value%28mobile%29=false&value%28pswd%29=' .
        $pass . '&value%28Submit%29=LOGIN';
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, 'https://ibank.klikbca.com/authentication.do');
    curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_POST, 1);
    $info = curl_exec($ch);

    // Buka menu
    curl_setopt($ch, CURLOPT_URL,
        'https://ibank.klikbca.com/nav_bar_indo/menu_bar.htm');
    curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/authentication.do');
    $info = curl_exec($ch);

    // Buka Informasi Rekening
    curl_setopt($ch, CURLOPT_URL,
        'https://ibank.klikbca.com/nav_bar_indo/account_information_menu.htm');
    curl_setopt($ch, CURLOPT_REFERER, 'https://ibank.klikbca.com/authentication.do');
    $info = curl_exec($ch);

    // Buka Mutasi Rekening
    curl_setopt($ch, CURLOPT_URL,
        'https://ibank.klikbca.com/accountstmt.do?value(actions)=acct_stmt');
    curl_setopt($ch, CURLOPT_REFERER,
        'https://ibank.klikbca.com/nav_bar_indo/account_information_menu.htm');
    curl_setopt($ch, CURLOPT_POST, 1);
    $info = curl_exec($ch);

    // Parameter untuk Lihat Mutasi Rekening
    $params = array();

    $jkt_time = time() + (3600 * 7);
    $t1 = explode('-', date('Y-m-d', $jkt_time));
    $t0 = explode('-', date('Y-m-d', $jkt_time - (3600 * 24)));

    $params[] = 'value%28startDt%29=' . $t0[2];
    $params[] = 'value%28startMt%29=' . $t0[1];
    $params[] = 'value%28startYr%29=' . $t0[0];
    $params[] = 'value%28endDt%29=' . $t1[2];
    $params[] = 'value%28endMt%29=' . $t1[1];
    $params[] = 'value%28endYr%29=' . $t1[0];
    $params[] = 'value%28D1%29=0';
    $params[] = 'value%28r1%29=1';
    $params[] = 'value%28fDt%29=';
    $params[] = 'value%28tDt%29=';
    $params[] = 'value%28submit1%29=Lihat+Mutasi+Rekening';

    $params = implode('&', $params);

    // Buka Lihat Mutasi Rekening & simpan hasilnya di $source
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL,
        'https://ibank.klikbca.com/accountstmt.do?value(actions)=acctstmtview');
    curl_setopt($ch, CURLOPT_REFERER,
        'https://ibank.klikbca.com/nav_bar_indo/account_information_menu.htm');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_POST, 1);

    $source = curl_exec($ch);

    // Logout, cURL close, hapus cookies
    curl_setopt($ch, CURLOPT_URL,
        'https://ibank.klikbca.com/authentication.do?value(actions)=logout');
    curl_setopt($ch, CURLOPT_REFERER,
        'https://ibank.klikbca.com/nav_bar_indo/account_information_menu.htm');
    $info = curl_exec($ch);
    curl_close($ch);
    @unlink($cookie);
    return $source;
}

function bank_bca($config, $transactions)
{
    global $pdo;

    $source = grab_bca($config['api']['username'], $config['api']['password']);
    $exp = explode('<b>Saldo</b></font></div></td>', $source);

    $invoices = array();
    $lunas = array();
    $jkt_time = time() + (3600 * 7);
    $tahun = date('Y', $jkt_time);

    if (isset($exp[1]))
    {
        $table = explode("</table>", $exp[1]);
        $tr = explode("<tr>", $table[0]);
        for ($i = 1; $i < count($tr); $i++)
        {
            $str = str_ireplace('</font>', '#~#~#</font>', $tr[$i]);
            $str = str_ireplace('<br>', '<br> ', $str);
            $str = preg_replace('!\s+!', ' ', trim(strip_tags($str)));

            $arr = array_map('trim', explode("#~#~#", $str));

            if ($arr[4] == 'DB')
            {
                continue;
            }
            if ($arr[0] == 'PEND')
                $arr[0] = date('d/m', $jkt_time);
            $tgl = $arr[0] . '/' . $tahun;
            $keterangan = $arr[1];
            $kredit = fix_angka($arr[3]);
            if (!array_key_exists($kredit, $transactions))
                continue;

            $hash = hash('sha1', $tgl . $keterangan . $kredit);
            if (isset($invoices[$hash]))
                continue;
            $invoices[$hash] = 1;
            $q = $pdo->prepare("SELECT COUNT(*) FROM pembayaran WHERE bank = ? AND hash = ?");
            $q->execute(array('bank_bca', $hash));
            if ($q->fetchColumn() == 0)
            {
                $lunas[] = $transactions[$kredit]['tr_id'];

                $insert = $pdo->prepare("INSERT INTO pembayaran (trx_id, bank, kredit, keterangan, hash, tanggal) VALUES(?, ?, ?, ?, ?, ?)");
                $insert->execute(array(
                    $transactions[$kredit]['tr_id'],
                    'bank_bca',
                    $kredit,
                    $keterangan,
                    $hash,
                    $tgl,
                    ));
            }
        }
    }
    if ($lunas)
    {
        $pdo->query("UPDATE transaksi SET tr_status_pembayaran = 'success' WHERE tr_id IN(" .
            implode(',', $lunas) . ")");
    }
    return $lunas;
}
