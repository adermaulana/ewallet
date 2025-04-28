<?php
// File: proses_upload_bukti.php
session_start();
include '../koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['id_pengguna'])) {
    echo "<script>alert('Silakan login terlebih dahulu!'); window.location='login.php';</script>";
    exit;
}

$id_pengguna = $_SESSION['id_pengguna'];

// Memastikan ada request POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_top_up = $_POST['id_top_up'];
    $catatan = isset($_POST['catatan']) ? $_POST['catatan'] : '';
    
    // Validasi ID top up
    if (empty($id_top_up)) {
        echo "<script>alert('ID Top Up tidak valid!'); window.location='riwayat_topup.php';</script>";
        exit;
    }
    
    // Dapatkan informasi top up untuk mendapatkan data yang diperlukan
    $query_get_topup = "SELECT * FROM top_up WHERE id_top_up = '$id_top_up' AND id_pengguna = '$id_pengguna'";
    $result_topup = mysqli_query($koneksi, $query_get_topup);
    
    if (!$result_topup || mysqli_num_rows($result_topup) == 0) {
        echo "<script>alert('Data top up tidak ditemukan!'); window.location='riwayat_topup.php';</script>";
        exit;
    }
    
    $data_topup = mysqli_fetch_assoc($result_topup);
    $tanggal_topup = $data_topup['tanggal_top_up'];
    
    // Cek apakah file diunggah
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        $file_type = $_FILES['bukti_pembayaran']['type'];
        $file_size = $_FILES['bukti_pembayaran']['size'];
        
        // Validasi tipe file
        if (!in_array($file_type, $allowed_types)) {
            echo "<script>alert('Format file tidak didukung. Gunakan JPG atau PNG!'); window.location='riwayat_topup.php';</script>";
            exit;
        }
        
        // Validasi ukuran file
        if ($file_size > $max_size) {
            echo "<script>alert('Ukuran file terlalu besar. Maksimal 2MB!'); window.location='riwayat_topup.php';</script>";
            exit;
        }
        
        // Buat direktori jika belum ada
        $upload_dir = 'bukti_pembayaran/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate nama file unik
        $file_extension = pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION);
        $file_name = 'bukti_topup_' . $id_top_up . '_' . date('YmdHis') . '.' . $file_extension;
        $target_file = $upload_dir . $file_name;
        
        // Upload file
        if (move_uploaded_file($_FILES['bukti_pembayaran']['tmp_name'], $target_file)) {
            // Update tabel top_up
            $query_topup = "UPDATE top_up SET 
                          bukti_pembayaran = '$file_name'
                          WHERE id_top_up = '$id_top_up' AND id_pengguna = '$id_pengguna'";
            
            $update_topup = mysqli_query($koneksi, $query_topup);
            
            // Cari transaksi terkait dengan top-up ini berdasarkan ID pengguna dan tanggal
            // Asumsi: transaksi dibuat pada tanggal yang sama dengan top-up
            $query_transaksi = "UPDATE transaksi SET 
                              bukti_pembayaran = '$file_name'
                              WHERE jenis_transaksi = 'top_up' 
                              AND id_pengguna = '$id_pengguna'
                              AND tanggal_transaksi LIKE '" . date('Y-m-d', strtotime($tanggal_topup)) . "%'
                              AND jumlah = '" . $data_topup['jumlah'] . "'";
            
            $update_transaksi = mysqli_query($koneksi, $query_transaksi);
            
            if ($update_topup) {
                echo "<script>alert('Bukti pembayaran berhasil diunggah!'); window.location='topup.php';</script>";
            } else {
                echo "<script>alert('Gagal mengupdate database: " . mysqli_error($koneksi) . "'); window.location='topup.php';</script>";
            }
        } else {
            echo "<script>alert('Gagal mengunggah file!'); window.location='topup.php';</script>";
        }
    } else {
        echo "<script>alert('Silakan pilih file terlebih dahulu!'); window.location='topup.php';</script>";
    }
} else {
    // Jika bukan POST request, redirect ke halaman transaksi
    header('Location: topup.php');
    exit;
}
?>