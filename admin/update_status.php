<?php
include '../koneksi.php'; // Sesuaikan dengan nama file koneksi Anda

$id_transaksi = $_POST['id_transaksi'];
$status = $_POST['status'];

$query = "UPDATE transaksi SET status = '$status' WHERE id_transaksi = '$id_transaksi'";
$result = mysqli_query($koneksi, $query);

if ($result) {
    echo "Success";
} else {
    echo "Error: " . mysqli_error($koneksi);
}
?>