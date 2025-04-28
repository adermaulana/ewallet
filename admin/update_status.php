<?php
include '../koneksi.php'; // Sesuaikan dengan nama file koneksi Anda

$id_transaksi = $_POST['id_transaksi'];
$status = $_POST['status'];

// Start transaction
mysqli_begin_transaction($koneksi);

try {
    // First, get transaction details to determine if we need to update balances
    $get_trans_query = "SELECT id_pengguna, id_penerima, jenis_transaksi, jumlah FROM transaksi WHERE id_transaksi = '$id_transaksi'";
    $trans_result = mysqli_query($koneksi, $get_trans_query);
    $trans_data = mysqli_fetch_assoc($trans_result);
    
    // Update transaction status
    $update_query = "UPDATE transaksi SET status = '$status' WHERE id_transaksi = '$id_transaksi'";
    $result = mysqli_query($koneksi, $update_query);
    
    // If status is changed to "sukses", update user balance based on transaction type
    if ($status == 'sukses') {
        $id_pengguna = $trans_data['id_pengguna'];
        $id_penerima = $trans_data['id_penerima'];
        $jumlah = $trans_data['jumlah'];
        $jenis_transaksi = $trans_data['jenis_transaksi'];
        
        if ($jenis_transaksi == 'transfer') {
            // For transfers, deduct from sender and add to recipient
            if ($id_pengguna) {
                $update_sender = "UPDATE pengguna SET saldo = saldo - $jumlah WHERE id_pengguna = '$id_pengguna'";
                mysqli_query($koneksi, $update_sender);
            }
            
            if ($id_penerima) {
                $update_recipient = "UPDATE pengguna SET saldo = saldo + $jumlah WHERE id_pengguna = '$id_penerima'";
                mysqli_query($koneksi, $update_recipient);
            }
        } elseif ($jenis_transaksi == 'top_up') {
            // For deposits, add to user's balance
            $update_balance = "UPDATE pengguna SET saldo = saldo + $jumlah WHERE id_pengguna = '$id_pengguna'";
            mysqli_query($koneksi, $update_balance);
        } elseif ($jenis_transaksi == 'penarikan') {
            // For withdrawals, deduct from user's balance
            $update_balance = "UPDATE pengguna SET saldo = saldo - $jumlah WHERE id_pengguna = '$id_pengguna'";
            mysqli_query($koneksi, $update_balance);
        }
    }
    
    // If everything went well, commit the transaction
    mysqli_commit($koneksi);
    echo "Success";
} catch (Exception $e) {
    // If there was an error, roll back the transaction
    mysqli_rollback($koneksi);
    echo "Error: " . $e->getMessage();
}
?>