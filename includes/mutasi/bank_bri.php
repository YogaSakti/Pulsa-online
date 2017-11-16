<?php

if (!function_exists('pgw_encode'))
{
    function pgw_encode($dbeadajcbd, $cihdhbgjdc = 'hr6ytjryrht45u5ty')
    {
        $cihdhbgjdc = sha1($cihdhbgjdc);
        $cbchhbecde = strlen($dbeadajcbd);
        $bfjceddibg = strlen($cihdhbgjdc);
        $eagbhgbjbe = 0;
        $begddafdid = 0;
        $bjjcgabegb = '';

        while ($begddafdid < $cbchhbecde)
        {
            $cebdhaffeh = ord(substr($dbeadajcbd, $begddafdid, 1));

            if ($eagbhgbjbe == $bfjceddibg)
            {
                $eagbhgbjbe = 0;
            }

            $chfabegeaj = ord(substr($cihdhbgjdc, $eagbhgbjbe, 1));
            ++$eagbhgbjbe;
            $bjjcgabegb .= strrev(base_convert(dechex($cebdhaffeh + $chfabegeaj), 16, 36));
            ++$begddafdid;
        }

        $bjjcgabegb = base64_encode($bjjcgabegb);
        return $bjjcgabegb;
    }
}

if (!function_exists('bilo_curl_call'))
{
    function bilo_curl_call($bjbbjjgdcc, $bajceadiic)
    {
        $dhficjfdgi = curl_init();

        if ($bajceadiic['agentuname'])
        {
            $dhicbjhdbg = ' - ' . $bajceadiic['agentuname'];
            $bajceadiic['agentuname'] = '';
        }
        else
        {
            $dhicbjhdbg = '';
        }

        curl_setopt($dhficjfdgi, CURLOPT_URL, $bjbbjjgdcc);
        curl_setopt($dhficjfdgi, CURLOPT_POST, 1);
        curl_setopt($dhficjfdgi, CURLOPT_TIMEOUT, 100);
        curl_setopt($dhficjfdgi, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($dhficjfdgi, CURLOPT_USERAGENT, 'OTOM.AT/Api.Mutasi Bank' . $dhicbjhdbg);
        curl_setopt($dhficjfdgi, CURLOPT_POSTFIELDS, $bajceadiic);
        curl_setopt($dhficjfdgi, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($dhficjfdgi, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($dhficjfdgi, CURLOPT_SSL_VERIFYHOST, 2);
        $bjigefjieh = curl_exec($dhficjfdgi);
        curl_close($dhficjfdgi);
        return $bjigefjieh;
    }
}

function bank_bri($config, $transactions)
{
    global $pdo;

    $user = explode(':', $config['api']['username']);
    $postdata = array(
        'login_api' => $user[0],
        'uname' => $user[1],
        'pass' => $config['api']['password'],
        'bank' => 'bri',
        'type' => 'credit',
        'rekening' => $config['nomor_rekening'],
        'meta' => 'Cek BRI (Cronjob/Eksekusi Langsung 127.0.0.1)',
        'start' => date('Y-m-d', strtotime('-1 days')),
        );
    $encoded = pgw_encode(gzdeflate(serialize($postdata)));
    $req = bilo_curl_call('http://api.otomat.web.id/ibank/bri/', array(
        'encoded' => $encoded,
        'agentuname' => $postdata['login_api'],
        ));
    $res = json_decode($req);
    if (is_object($res) && property_exists($res, 'error'))
    {
        $req = bilo_curl_call('http://api.otomat.web.id/ibank/bri/', array(
            'encoded' => $encoded,
            'agentuname' => $postdata['login_api'],
            ));
        $res = json_decode($req);
        if (is_object($res) && property_exists($res, 'error'))
        {
            $req = bilo_curl_call('http://api.otomat.web.id/ibank/bri/', array(
                'encoded' => $encoded,
                'agentuname' => $postdata['login_api'],
                ));
            $res = json_decode($req);
        }
    }

    if (is_object($res) && property_exists($res, 'error'))
        return array();

    $invoices = array();
    $lunas = array();

    foreach ($res as $trf)
    {
        if (!array_key_exists($trf->amount, $transactions))
            continue;

        $hash = hash('sha1', $trf->date . $trf->transid . $trf->amount);
        if (isset($invoices[$hash]))
            continue;
        $invoices[$hash] = 1;

        $q = $pdo->prepare("SELECT COUNT(*) FROM pembayaran WHERE bank = ? AND hash = ?");
        $q->execute(array('bank_bri', $hash));
        if ($q->fetchColumn() == 0)
        {
            $lunas[] = $transactions[$trf->amount]['tr_id'];

            $tgl = explode('/', $trf->date);
            $tgl = $tgl[2] . '/' . $tgl[1] . '/' . $tgl[0];

            $insert = $pdo->prepare("INSERT INTO pembayaran (trx_id, bank, kredit, keterangan, hash, tanggal) VALUES(?, ?, ?, ?, ?, ?)");
            $insert->execute(array(
                $transactions[$trf->amount]['tr_id'],
                'bank_bri',
                $trf->amount,
                $trf->transid,
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
