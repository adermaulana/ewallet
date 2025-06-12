<?php
include '../koneksi.php';
session_start();

// Cek apakah pengguna sudah login
if ($_SESSION['status'] != 'login') {
    session_unset();
    session_destroy();
    header('location:../');
    exit();
}

$id_pengguna = $_SESSION['id_pengguna'];

// Cek apakah parameter id ada
if(!isset($_GET['id'])) {
    echo "<script>
    alert('ID Goal tidak valid!');
    document.location='tabungan.php';
    </script>";
    exit();
}

$id_goal = $_GET['id'];

// Ambil data goal
$query = mysqli_query($koneksi, "SELECT jumlah_terkumpul FROM savings_goals 
                                WHERE id_goal = '$id_goal' 
                                AND id_pengguna = '$id_pengguna' 
                                AND status = 'tercapai'");
if(mysqli_num_rows($query) == 0) {
    echo "<script>
    alert('Goal tidak ditemukan atau tidak dalam status tercapai!');
    document.location='tabungan.php';
    </script>";
    exit();
}

$data = mysqli_fetch_array($query);
$jumlah_terkumpul = $data['jumlah_terkumpul'];

// Mulai transaksi
mysqli_begin_transaction($koneksi);

try {
    // Update saldo pengguna
    mysqli_query($koneksi, "UPDATE pengguna SET saldo = saldo + $jumlah_terkumpul 
                           WHERE id_pengguna = '$id_pengguna'");
    
    // Update status goal menjadi dibatalkan
    mysqli_query($koneksi, "UPDATE savings_goals 
                           SET status = 'selesai'
                           WHERE id_goal = '$id_goal'");
    
    // Commit transaksi jika semua query berhasil
    mysqli_commit($koneksi);
    
    echo "<script>
    alert('Saldo sebesar Rp ".number_format($jumlah_terkumpul, 0, ',', '.')." telah dikembalikan ke akun Anda.');
    document.location='tabungan.php';
    </script>";
} catch (Exception $e) {
    // Rollback jika ada error
    mysqli_rollback($koneksi);
    
    echo "<script>
    alert('Gagal mengembalikan saldo: ".addslashes($e->getMessage())."');
    document.location='tabungan.php';
    </script>";
}
?>