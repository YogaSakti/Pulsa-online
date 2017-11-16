DILARANG MENJUAL BELIKAN SCRIPT INI JIKA ANDA NEKAT MENJUAL BELIKAN KEPADA ORANG SEMOGA RAJEKI SERET 7 TURUNAN, SHARING IS CARING.
# Persyaratan Utama
* Anda telah menjadi distributor pulsa multichip
* Handphone Android sebagai SMS gateway dengan menggunakan aplikasi EnvayaSMS (Gratis)

# Persyaratan Lainnya
* Paypal API Credential (opsional jika pembayaran via paypal diaktifkan)
* Akun internet Banking BCA / Mandiri (opsioanal jika pembayaran via bank diaktifkan)

# Langkah - Langkah Instalasi
* Ekstrak Arsip zip ke Webhosting
* Membuat Database di phpMyAdmin dan import mysql.sql ke Database yang dibuat tadi
* Ubah nama file includes/config-sample.php menjadi includes/config.php dan edit file tersebut lalu masukan data-data Database tadi
* Membuat Paypal API Credential
    - Akun paypal harus akun bisnis.
    - Masuk ke My Account > Profile > Get API Credentials dan ikuti petunjuk selanjutnya dan anda akan mendapatkan API Username, API Password dan API Signature
    - Simpan / salin data-data API tersebut
* Masuk / kunjungi Admin panel (admin.php) dan masukan Username dan Password Administrator (Default: username = okepulsa, password = mahadewa)
    - Admin Panel > Pengaturan::Metode Pembayaran dan masukan data - data API Paypal yang didapatkan tadi
    - Admin Panel > Pengaturan::Produk & Format Transaksi dan masukan format transaksi pengisian pulsa (NB: Format transaksi dari Agen Pulsa)
    - Admin Panel > Pengaturan::EnvayaSMS dan measukan kata sandi untuk memvalidasi aplikasi EnvayaSMS di android
* Download Aplikasi EnvayaSMS, setelah itu buka aplikasi tersebut dan ikuti langkah-langkah pengaturannya dibawah ini
    - Server URL : Isi dengan URL dimana script ini diinstal, contoh: http://domain.com/EnvayaSMS.php atau http://domain.com/folder/EnvayaSMS.php
    - Your phone number : Isi dengan nomor SMS Center pengisian pulsa dan nomor harus diwali dengan angka 0, contoh: 081811111111
    - Password : Silakan masukan kata sandi yang telah dibuat di admin panel
    - Poll Interval : Silakan pilih (Rekomendasi 30 detik s/d 1 menit)
    - Keep new messages : Tandai
    - Call notifications : Tandai
    - Enable EnvayaSMS : Tandai
    
Thanks to: SGB TEAM, ALAM DWI GUNAWAN
