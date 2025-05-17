<?php
include '../koneksi.php';
session_start();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifikasi session
    if (!isset($_SESSION['id_pengguna'])) {
        $response['message'] = 'Anda harus login untuk melakukan transfer';
        echo json_encode($response);
        exit;
    }

    $id_pengirim = $_POST['id_pengirim'];
    $id_penerima = $_POST['id_penerima'];
    $jumlah = $_POST['jumlah'];
    $catatan = $_POST['catatan'] ?? '';
    $password = $_POST['password']; // Get raw password from POST

    // Verifikasi data
    if ($id_pengirim != $_SESSION['id_pengguna']) {
        $response['message'] = 'Akses tidak sah';
        echo json_encode($response);
        exit;
    }

    // Verifikasi password
    $query = mysqli_query($koneksi, "SELECT password, saldo FROM pengguna WHERE id_pengguna = '$id_pengirim'");
    if (mysqli_num_rows($query) == 0) {
        $response['message'] = 'Pengguna tidak ditemukan';
        echo json_encode($response);
        exit;
    }

    $data = mysqli_fetch_assoc($query);
    if (md5($password) != $data['password']) {
        $response['message'] = 'Password salah';
        echo json_encode($response);
        exit;
    }

    // Verifikasi saldo
    if ($data['saldo'] < $jumlah) {
        $response['message'] = 'Saldo tidak mencukupi';
        echo json_encode($response);
        exit;
    }

    // Mulai transaksi
    mysqli_begin_transaction($koneksi);

    try {
        // Kurangi saldo pengirim
        mysqli_query($koneksi, "UPDATE pengguna SET saldo = saldo - $jumlah WHERE id_pengguna = '$id_pengirim'");
        
        // Tambah saldo penerima
        mysqli_query($koneksi, "UPDATE pengguna SET saldo = saldo + $jumlah WHERE id_pengguna = '$id_penerima'");
        
        // Catat transaksi
        $tanggal_transfer = date('Y-m-d H:i:s');
        $query_transfer = mysqli_query($koneksi, "INSERT INTO riwayat_transfer 
            (id_pengirim, id_penerima, jumlah, deskripsi, status, tanggal_transfer) 
            VALUES ('$id_pengirim', '$id_penerima', '$jumlah', '$catatan', 'sukses', '$tanggal_transfer')");
        
        if ($query_transfer) {
            mysqli_commit($koneksi);
            $response['success'] = true;
            $response['message'] = 'Transfer berhasil dilakukan';
        } else {
            throw new Exception('Gagal mencatat transaksi');
        }
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        $response['message'] = 'Terjadi kesalahan: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Metode request tidak valid';
}

echo json_encode($response);
?>