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

class Mandiri
{
    private $ch;
    private $cookieFile;
    private $isLoggedIn = false;
    private $accountId = null;

    public function __construct($cookie_dir = false)
    {
        $this->ch = curl_init();
        if ($cookie_dir == false)
        {
            $this->cookieFile = dirname(__file__) . '/mandiri_cookie.txt';
        }
        else
        {
            $this->cookieFile = $cookie_dir . '/mandiri_cookie.txt';
        }
        curl_setopt($this->ch, CURLOPT_URL,
            'https://ib.bankmandiri.co.id/retail/Login.do?action=form&lang=in_ID');
        curl_setopt($this->ch, CURLOPT_NOBODY, true);
        $this->curlExec();
    }

    public function logIn($username, $pin)
    {
        $params = implode('&', array(
            'action=result',
            'userID=' . $username,
            'password=' . $pin,
            'image.x=0',
            'image.y=0',
            ));
        curl_setopt($this->ch, CURLOPT_URL,
            'https://ib.bankmandiri.co.id/retail/Login.do');
        curl_setopt($this->ch, CURLOPT_REFERER,
            'https://ib.bankmandiri.co.id/retail/Login.do?action=form&lang=in_ID');

        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($this->ch, CURLOPT_HEADER, 1);
        curl_setopt($this->ch, CURLOPT_NOBODY, false);
        $result = $this->curlExec();
        if (stripos($result,
            'https://ib.bankmandiri.co.id/retail/Redirect.do?action=forward') === false)
            return false;

        return $this->isLoggedIn = true;
    }

    public function logOut()
    {
        if ($this->isLoggedIn == false)
            return false;

        curl_setopt($this->ch, CURLOPT_URL,
            'https://ib.bankmandiri.co.id/retail/Logout.do?action=result');
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($this->ch, CURLOPT_NOBODY, true);
        $this->curlExec();
        @unlink($this->cookieFile);
    }

    private function curlExec()
    {
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_USERAGENT,
            'Mozilla/5.0 (Linux; U; Android 2.3.7; en-us; Nexus One Build/GRK39F) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1');
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->cookieFile);

        return curl_exec($this->ch);
    }

    public function setAccountId($id)
    {
        $this->accountId = $id;
    }

    protected function getTableMutation($no_rek, $hari)
    {
        if (!$this->accountId)
            $accountId = $this->getAccountId($no_rek);
        else
            $accountId = $this->accountId;

        $time = time() + (3600 * 7);
        $from_time = $time - (3600 * 24 * $hari);
        $params = implode('&', array(
            'action=result',
            'fromAccountID=' . $accountId,
            'searchType=R',
            'fromDay=' . date('j', $from_time),
            'fromMonth=' . date('n', $from_time),
            'fromYear=' . date('Y', $from_time),
            'toDay=' . date('j', $time),
            'toMonth=' . date('n', $time),
            'toYear=' . date('Y', $time),
            ));
        curl_setopt($this->ch, CURLOPT_URL,
            'https://ib.bankmandiri.co.id/retail/TrxHistoryInq.do');
        curl_setopt($this->ch, CURLOPT_REFERER,
            'https://ib.bankmandiri.co.id/retail/TrxHistoryInq.do?action=form');
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($this->ch, CURLOPT_HEADER, 0);
        $result = $this->curlExec();
        $exp = explode('<table border="0" cellpadding="2" cellspacing="1" width="100%">',
            $result);
        $table = '<table border="0" cellpadding="2" cellspacing="1" width="100%">' . $exp[1];
        return $table;
    }

    public function getTransactions($no_rek = false, $hari = 1, $transactions)
    {
        global $pdo;
        
        $table = $this->getTableMutation($no_rek, $hari);

        $tr = explode('<tr height="25">', $table);
        $invoices = array();
        $lunas = array();

        for ($i = 1; $i < count($tr); $i++)
        {
            $str = str_ireplace('<td height="25"', '#~#~#<td height="25"', $tr[$i]);
            $str = substr(trim(strip_tags($str)), 5);
            $str = preg_replace('!\s+!', ' ', $str);
            $exp = explode("#~#~#", $str);
            $keterangan = trim($exp[1]);
            $kredit = str_replace('.', '', substr(trim($exp[3]), 0, -3));
            $tgl = trim($exp[0]);

            if ($kredit == '0')
                continue;

            if (!array_key_exists($kredit, $transactions))
                continue;

            $hash = hash('sha1', $tgl . $keterangan . $kredit);
            if (isset($invoices[$hash]))
                continue;
            $invoices[$hash] = 1;

            $q = $pdo->prepare("SELECT COUNT(*) FROM pembayaran WHERE bank = ? AND hash = ?");
            $q->execute(array('bank_mandiri', $hash));
            if ($q->fetchColumn() == 0)
            {
                $lunas[] = $transactions[$kredit]['tr_id'];

                $insert = $pdo->prepare("INSERT INTO pembayaran (trx_id, bank, kredit, keterangan, hash, tanggal) VALUES(?, ?, ?, ?, ?, ?)");
                $insert->execute(array(
                    $transactions[$kredit]['tr_id'],
                    'bank_mandiri',
                    $kredit,
                    $keterangan,
                    $hash,
                    $tgl,
                    ));
            }
        }
        if ($lunas)
        {
            $pdo->query("UPDATE transaksi SET tr_status_pembayaran = 'success' WHERE tr_id IN(" .
                implode(',', $lunas) . ")");
        }
        return $lunas;
    }

    public function getAccountId($no_rek = false)
    {
        if (!$this->isLoggedIn)
            return false;

        curl_setopt($this->ch, CURLOPT_URL,
            'https://ib.bankmandiri.co.id/retail/TrxHistoryInq.do?action=form');
        curl_setopt($this->ch, CURLOPT_REFERER,
            'https://ib.bankmandiri.co.id/retail/Redirect.do?action=forward');

        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        curl_setopt($this->ch, CURLOPT_NOBODY, false);
        $result = $this->curlExec();
        if ($no_rek)
            preg_match('/\<option value\=\"([0-9{8,32}]+)\"\>' . $no_rek . '(.*?)\<\/option\>/i',
                $result, $matches);
        else
            preg_match('/\<option value\=\"([0-9{8,32}]+)\"\>(.*)\<\/option\>/i', $result, $matches);
        if ($matches)
            return $matches[1];
        else
            return false;
    }

    public function curlClose()
    {
        curl_close($this->ch);
    }
}

function bank_mandiri($config, $transactions)
{
    $history = array();
    $mandiri = new Mandiri(APP_PATH . '/cache');
    if ($mandiri->logIn($config['api']['username'], $config['api']['password']))
    {
        $history = $mandiri->getTransactions($config['nomor_rekening'], 2, $transactions);
        $mandiri->logOut();
    }
    $mandiri->curlClose();
    return $history;
}
