<?php
include '../koneksi.php';

header('Content-Type: application/json');

if (!isset($_GET['id_penerima'])) {
    echo json_encode(['success' => false, 'message' => 'ID Penerima tidak valid']);
    exit;
}

$id_penerima = $_GET['id_penerima'];

// Cek apakah ID penerima sama dengan pengirim (tidak boleh transfer ke diri sendiri)
if (isset($_SESSION['id_pengguna']) && $id_penerima == $_SESSION['id_pengguna']) {
    echo json_encode(['success' => false, 'message' => 'Tidak bisa transfer ke diri sendiri']);
    exit;
}

$query = mysqli_query($koneksi, "SELECT id_pengguna, nama_lengkap, email, nomor_rekening FROM pengguna WHERE id_pengguna = '$id_penerima'");

if (mysqli_num_rows($query) > 0) {
    $data = mysqli_fetch_assoc($query);
    echo json_encode(['success' => true, 'data' => $data]);
} else {
    echo json_encode(['success' => false, 'message' => 'Penerima tidak ditemukan']);
}
?>