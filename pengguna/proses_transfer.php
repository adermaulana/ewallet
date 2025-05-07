<?php
include '../koneksi.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] != 'POST' || !isset($_SESSION['id_pengguna'])) {
    header('Location: transfer.php');
    exit;
}

$id_pengirim = $_SESSION['id_pengguna'];
$id_penerima = $_POST['id_penerima'];
$jumlah = $_POST['jumlah'];
$catatan = $_POST['catatan'] ?? '';
$tanggal_transfer = $_POST['tanggal_transfer'];
$status = 'pending'; // Atau 'success' jika langsung diproses

// Validasi data
if (empty($id_penerima) || empty($jumlah)) {
    $_SESSION['transfer_error'] = 'Data tidak lengkap';
    header('Location: transfer.php');
    exit;
}

// Cek apakah penerima ada
$query = mysqli_query($koneksi, "SELECT id_pengguna FROM pengguna WHERE id_pengguna = '$id_penerima'");
if (mysqli_num_rows($query) == 0) {
    $_SESSION['transfer_error'] = 'Penerima tidak ditemukan';
    header('Location: transfer.php');
    exit;
}

// Cek saldo pengirim (jika diperlukan)
$saldo_pengirim = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT saldo FROM pengguna WHERE id_pengguna = '$id_pengirim'"))['saldo'];
if ($saldo_pengirim < $jumlah) {
    $_SESSION['transfer_error'] = 'Saldo tidak mencukupi';
    header('Location: transfer.php');
    exit;
}

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    // Kurangi saldo pengirim
    mysqli_query($koneksi, "UPDATE pengguna SET saldo = saldo - $jumlah WHERE id_pengguna = '$id_pengirim'");
    
    // Tambah saldo penerima
    mysqli_query($koneksi, "UPDATE pengguna SET saldo = saldo + $jumlah WHERE id_pengguna = '$id_penerima'");
    
    // Catat di riwayat transfer
    $insert_transfer = mysqli_query($koneksi, "INSERT INTO riwayat_transfer 
        (id_pengirim, id_penerima, jumlah, deskripsi, status, tanggal_transfer) 
        VALUES ('$id_pengirim', '$id_penerima', '$jumlah', '$catatan', '$status', '$tanggal_transfer')");
    
    // Catat di tabel transaksi pengirim
    $insert_transaksi_keluar = mysqli_query($koneksi, "INSERT INTO transaksi 
        (id_pengguna, jenis_transaksi, jumlah, id_penerima, deskripsi, status, tanggal_transaksi) 
        VALUES ('$id_pengirim', 'transfer_keluar', '$jumlah', '$id_penerima', '$catatan', '$status', '$tanggal_transfer')");
    
    // Catat di tabel transaksi penerima
    $insert_transaksi_masuk = mysqli_query($koneksi, "INSERT INTO transaksi 
        (id_pengguna, jenis_transaksi, jumlah, id_penerima, deskripsi, status, tanggal_transaksi) 
        VALUES ('$id_penerima', 'transfer_masuk', '$jumlah', '$id_pengirim', '$catatan', '$status', '$tanggal_transfer')");
    
    // Commit transaksi jika semua berhasil
    mysqli_commit($koneksi);
    
    $_SESSION['transfer_success'] = 'Transfer berhasil dilakukan';
    header('Location: laporan.php');
    
} catch (Exception $e) {
    // Rollback jika ada error
    mysqli_rollback($koneksi);
    $_SESSION['transfer_error'] = 'Terjadi kesalahan: ' . $e->getMessage();
    header('Location: transfer.php');
}
?>