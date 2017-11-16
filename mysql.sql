-- phpMyAdmin SQL Dump
-- version 4.1.6
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: 08 Jan 2016 pada 16.19
-- Versi Server: 5.5.36
-- PHP Version: 5.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `pulsa_online`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `berita`
--

DROP TABLE IF EXISTS `berita`;
CREATE TABLE `berita` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `judul` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `tanggal` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `berita`
--

INSERT INTO `berita` (`id`, `judul`, `deskripsi`, `tanggal`) VALUES
(1, 'Jadwal Offline Internet Banking', '<h3>Bank BCA</h3>\r\n<dl>\r\n<dt>Senin - Jumat</dt><dd>21.00 - 00.30 WIB</dd>\r\n<dt>Sabtu</dt><dd>18.00 - 20.00 WIB</dd>\r\n<dt>Minggu</dt><dd>00.00 - 06.00 WIB</dd>\r\n</dl>\r\n<h3>Bank Mandiri</h3>\r\n<dl>\r\n<dt>Senin - Jumat</dt><dd>23.00 - 04.00 WIB</dd>\r\n<dt>Sabtu</dt><dd>22.00 - 06.00 WIB</dd>\r\n<dt>Minggu</dt><dd>22.00 - 06.00 WIB</dd>\r\n</dl>\r\n\r\nTransaksi otomatis hanya diproses pada jam-jam online saja, jika anda melakukan transaksi pada jam-jam offline maka transaksi akan diproses ketika jam Internet banking sudah aktif kembali', 1452012085);

-- --------------------------------------------------------

--
-- Struktur dari tabel `feedback`
--

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(12) NOT NULL,
  `no_hp` varchar(16) NOT NULL,
  `inv_id` varchar(32) NOT NULL DEFAULT '',
  `pesan` varchar(160) NOT NULL,
  `baca` tinyint(1) NOT NULL DEFAULT '0',
  `tanggal` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `no_hp` (`no_hp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur dari tabel `operator`
--

DROP TABLE IF EXISTS `operator`;
CREATE TABLE `operator` (
  `op_id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `op_produk` varchar(32) NOT NULL,
  `op_nama` varchar(64) NOT NULL,
  PRIMARY KEY (`op_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `operator`
--

INSERT INTO `operator` (`op_id`, `op_produk`, `op_nama`) VALUES
(1, 'pulsa', 'TELKOMSEL'),
(2, 'pulsa', 'XL AXIATA'),
(3, 'pulsa', 'INDOSAT'),
(4, 'pulsa', 'AXIS'),
(5, 'pulsa', 'TRI'),
(6, 'pulsa', 'SMARTFREN'),
(7, 'pulsa', 'ESIA'),
(8, 'pulsa', 'BOLT'),
(12, 'paket_internet', 'Axis Internet Gaul'),
(11, 'token_pln', 'PLN Prabayar'),
(13, 'paket_internet', 'Indosat Data'),
(14, 'paket_internet', 'Indosat Xtra Data'),
(15, 'paket_internet', 'Telkomsel Data'),
(16, 'voucher_game', 'PlayStation US Card');

-- --------------------------------------------------------

--
-- Struktur dari tabel `paypal_trx`
--

DROP TABLE IF EXISTS `paypal_trx`;
CREATE TABLE `paypal_trx` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trx_date` varchar(10) NOT NULL DEFAULT '0000-00-00',
  `trx_data` longtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `trx_date` (`trx_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

DROP TABLE IF EXISTS `pembayaran`;
CREATE TABLE `pembayaran` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `trx_id` int(10) NOT NULL,
  `bank` varchar(32) NOT NULL,
  `kredit` int(10) NOT NULL,
  `keterangan` varchar(255) NOT NULL DEFAULT '',
  `hash` varchar(255) NOT NULL DEFAULT '',
  `tanggal` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `trx_id` (`trx_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur dari tabel `setelan`
--

DROP TABLE IF EXISTS `setelan`;
CREATE TABLE `setelan` (
  `set_key` varchar(32) NOT NULL,
  `set_val` text NOT NULL,
  `set_autoload` enum('yes','no') NOT NULL DEFAULT 'no',
  PRIMARY KEY (`set_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `setelan`
--

INSERT INTO `setelan` (`set_key`, `set_val`, `set_autoload`) VALUES
('paypal_api', '{"username":"","password":"","signature":""}', 'no'),
('admin', '{"username":"okepulsa","password":"bWFoYWRld2E="}', 'no'),
('list_per_page', '20', 'yes'),
('pin', '1234', 'no'),
('site_name', 'Pulsa Online', 'yes'),
('site_url', '/', 'yes'),
('envaya_sms', '{"password":"12345678"}', 'no'),
('zona_waktu', '+7', 'yes'),
('sms_center', '+6285710250005', 'no'),
('smsg_aktif', '0', 'yes'),
('produk', '{"pulsa":{"nama":"Pulsa","format_trx":"{KODE}.{NO_HP}.{PIN}","status":"on"},"paket_internet":{"nama":"Paket Internet","format_trx":"{KODE}.{NO_HP}.{PIN}","status":"on"},"token_pln":{"nama":"Token PLN","format_trx":"{KODE}.{ID_PLN}.{PIN}.{NO_HP}","status":"on"},"voucher_game":{"nama":"Voucher Game","format_trx":"{KODE}.{NO_HP}.{PIN}","status":"on"}}', 'yes'),
('jam_pembayaran', '8', 'yes'),
('maintenance', 'off', 'yes'),
('metode_pembayaran', '{"bank_bca":{"nama":"Bank BCA","nomor_rekening":"","nama_rekening":"","api":{"username":"","password":""},"mutasi":{"durasi":0,"terakhir":0},"status":"off"},"bank_mandiri":{"nama":"Bank MANDIRI","nomor_rekening":"","nama_rekening":"","api":{"username":"","password":""},"mutasi":{"durasi":0,"terakhir":0},"status":"off"},"bank_bri":{"nama":"Bank BRI","nomor_rekening":"","nama_rekening":"","api":{"username":"usernameDomosquare:usernameBRI","password":"passwordBRI"},"mutasi":{"durasi":0,"terakhir":0},"status":"off"},"bank_bni":{"nama":"Bank BNI","nomor_rekening":"","nama_rekening":"","api":{"username":"usernameDomosquare:usernameBNI","password":"passwordBNI"},"mutasi":{"durasi":0,"terakhir":0},"status":"off"},"paypal":{"nama":"PayPal","nomor_rekening":"","nama_rekening":"","api":{"username":"","password":"","signature":""},"rate":13000,"status":"off"}}', 'yes'),
('paypal_lastdate', '0', 'yes'),
('saldo', '0', 'yes');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sms_keluar`
--

DROP TABLE IF EXISTS `sms_keluar`;
CREATE TABLE `sms_keluar` (
  `out_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `out_to` varchar(16) NOT NULL,
  `out_message` text NOT NULL,
  `out_status` varchar(12) NOT NULL DEFAULT '',
  `out_error` varchar(255) NOT NULL DEFAULT '',
  `out_submit_date` int(10) NOT NULL,
  `out_send_date` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`out_id`),
  KEY `out_to` (`out_to`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sms_masuk`
--

DROP TABLE IF EXISTS `sms_masuk`;
CREATE TABLE `sms_masuk` (
  `in_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `in_from` varchar(16) NOT NULL,
  `in_message` text NOT NULL,
  `in_timestamp` int(13) NOT NULL,
  PRIMARY KEY (`in_id`),
  KEY `in_from` (`in_from`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur dari tabel `testimonial`
--

DROP TABLE IF EXISTS `testimonial`;
CREATE TABLE `testimonial` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(32) NOT NULL,
  `no_hp` varchar(16) NOT NULL,
  `pesan` text NOT NULL,
  `moderasi` enum('0','1') NOT NULL DEFAULT '1',
  `tanggal` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

DROP TABLE IF EXISTS `transaksi`;
CREATE TABLE `transaksi` (
  `tr_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `op_id` int(10) NOT NULL,
  `op_produk` varchar(32) NOT NULL,
  `op_nama` varchar(64) NOT NULL,
  `vo_id` int(10) NOT NULL,
  `vo_kode` varchar(12) NOT NULL,
  `vo_nominal` varchar(64) NOT NULL,
  `tr_id_pln` varchar(32) NOT NULL DEFAULT '',
  `tr_no_hp` varchar(16) NOT NULL,
  `tr_harga` int(12) NOT NULL,
  `tr_harga2` int(12) NOT NULL,
  `tr_rate` int(5) NOT NULL DEFAULT '0',
  `tr_pembayaran` varchar(32) NOT NULL,
  `tr_status_pembayaran` varchar(32) NOT NULL,
  `tr_id_pembayaran` varchar(32) NOT NULL,
  `tr_cek_mutasi` int(10) NOT NULL DEFAULT '0',
  `tr_status` varchar(32) NOT NULL DEFAULT 'pending',
  `tr_tanggal` int(10) NOT NULL,
  `tr_terkirim` int(10) NOT NULL,
  PRIMARY KEY (`tr_id`),
  KEY `op_id` (`op_id`,`vo_kode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Struktur dari tabel `voucher`
--

DROP TABLE IF EXISTS `voucher`;
CREATE TABLE `voucher` (
  `vo_id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `op_id` int(6) NOT NULL,
  `vo_nominal` varchar(64) NOT NULL,
  `vo_harga` int(12) NOT NULL,
  `vo_kode` varchar(12) NOT NULL,
  `vo_status` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`vo_id`),
  KEY `op_id` (`op_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

--
-- Dumping data untuk tabel `voucher`
--

INSERT INTO `voucher` (`vo_id`, `op_id`, `vo_nominal`, `vo_harga`, `vo_kode`, `vo_status`) VALUES
(1, 1, '5.000', 6000, 'S5', '0'),
(2, 1, '10.000', 11000, 'S10', '1'),
(3, 1, '20.000', 21000, 'S20', '1'),
(4, 1, '25.000', 26000, 'S25', '1'),
(5, 1, '50.000', 51000, 'S50', '1'),
(6, 1, '100.000', 99000, 'S100', '1'),
(7, 2, '5.000', 6000, 'X5', '0'),
(8, 2, '10.000', 11000, 'X10', '1'),
(9, 2, '25.000', 26000, 'X25', '1'),
(10, 2, '50.000', 51000, 'X50', '1'),
(11, 2, '100.000', 99000, 'X100', '1'),
(12, 3, '5.000', 6000, 'M5', '0'),
(13, 3, '10.000', 11000, 'M10', '1'),
(14, 3, '25.000', 26000, 'M25', '1'),
(15, 3, '50.000', 51000, 'M50', '1'),
(16, 3, '100.000', 99000, 'M100', '1'),
(17, 4, '5.000', 6000, 'AX5', '0'),
(18, 4, '10.000', 11000, 'AX10', '1'),
(19, 4, '20.000', 21000, 'AX20', '1'),
(20, 4, '25.000', 26000, 'AX25', '1'),
(21, 4, '50.000', 51000, 'AX50', '1'),
(22, 4, '100.000', 99000, 'AX100', '1'),
(23, 5, '5.000', 6000, 'T5', '0'),
(24, 5, '10.000', 11000, 'T10', '1'),
(25, 5, '20.000', 21000, 'T20', '1'),
(26, 5, '50.000', 51000, 'T50', '1'),
(27, 5, '100.000', 99000, 'T100', '1'),
(28, 6, '5.000', 6000, 'SM5', '0'),
(29, 6, '10.000', 11000, 'SM10', '1'),
(30, 6, '20.000', 21000, 'SM20', '1'),
(31, 6, '25.000', 26000, 'SM25', '1'),
(32, 6, '50.000', 51000, 'SM50', '1'),
(33, 6, '100.000', 99000, 'SM100', '1'),
(34, 7, '5.000', 6000, 'E5', '0'),
(35, 7, '10.000', 11000, 'E10', '1'),
(36, 7, '20.000', 21000, 'E20', '1'),
(37, 7, '25.000', 26000, 'E25', '1'),
(38, 7, '50.000', 51000, 'E50', '1'),
(39, 7, '100.000', 99000, 'E100', '1'),
(40, 11, '20.000', 21000, 'PLN20', '1'),
(41, 11, '50.000', 51000, 'PLN50', '1'),
(42, 11, '100.000', 110000, 'PLN100', '1'),
(43, 8, '25.000', 25500, 'B25', '1'),
(44, 8, '50.000', 50000, 'B50', '1'),
(45, 8, '100.000', 99000, 'B100', '1'),
(46, 8, '150.000', 146500, 'B150', '1'),
(47, 8, '200.000', 195800, 'B200', '1'),
(48, 12, 'Inet Gaul Bulanan FUP 1,5GB', 33500, 'AIG35', '1'),
(49, 13, 'ISAT DATA 1GB 30 Hari', 23300, 'ID1', '1'),
(50, 13, 'ISAT DATA 2GB 30 Hari', 40300, 'ID2', '1'),
(51, 13, 'MENTARI DATA 3GB 90 Hari', 50800, 'ID3', '1'),
(52, 13, 'ISAT DATA 4,5GB 30 Hari', 41300, 'ID45', '1'),
(53, 13, 'ISAT DATA 5GB 30 Hari', 86300, 'ID5', '1'),
(54, 13, 'ISAT DATA 9,5GB 30 Hari', 71300, 'ID95', '1'),
(55, 13, 'ISAT BB GAUL BANGET 30 Hari', 61200, 'IDB30', '1'),
(56, 13, 'ISAT BB GAUL BANGET 90 Hari', 90300, 'IDB90', '1'),
(57, 14, 'ISAT XTra 2GB', 36300, 'IDX2', '1'),
(58, 14, 'ISAT XTra 4GB', 56300, 'IDX4', '1'),
(59, 14, 'ISAT XTra 6GB', 71300, 'IDX6', '1'),
(60, 15, 'Data Telkomsel 22MB/7Hari', 6700, 'SD5', '1'),
(61, 15, 'Data Telkomsel 70MB/7Hari', 11600, 'SD10', '1'),
(62, 15, 'Data Telkomsel 250MB/7Hari', 21300, 'SD20', '1'),
(63, 15, 'Data Telkomsel 360MB/30Hari', 26500, 'SD25', '1'),
(64, 15, 'Data Telkomsel 800MB/30 Hari', 50500, 'SD50', '1'),
(65, 15, 'Data Telkomsel 2.5GB/30Hari', 98500, 'SD100', '1'),
(66, 16, 'PlayStation 10$ US Card', 144600, 'PS10', '1'),
(67, 16, 'PlayStation 20$ US Card', 261000, 'PS20', '1'),
(68, 16, 'PlayStation 50$ US Card', 648500, 'PS50', '1');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
