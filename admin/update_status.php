<?php
include '../koneksi.php'; // Sesuaikan dengan nama file koneksi Anda

$id_transaksi = $_POST['id_transaksi'];
$status = $_POST['status'];

// Start transaction
mysqli_begin_transaction($koneksi);

try {
    // First, get transaction details to determine if we need to update balances
    $get_trans_query = "SELECT id_pengguna, id_penerima, jenis_transaksi, jumlah, tanggal_transaksi, bukti_pembayaran FROM transaksi WHERE id_transaksi = '$id_transaksi'";
    $trans_result = mysqli_query($koneksi, $get_trans_query);
    $trans_data = mysqli_fetch_assoc($trans_result);
    
    // Update transaction status
    $update_query = "UPDATE transaksi SET status = '$status' WHERE id_transaksi = '$id_transaksi'";
    $result = mysqli_query($koneksi, $update_query);
    
    // If status is changed, update user balance and top_up status if applicable
    if ($result) {
        $id_pengguna = $trans_data['id_pengguna'];
        $id_penerima = $trans_data['id_penerima'];
        $jumlah = $trans_data['jumlah'];
        $jenis_transaksi = $trans_data['jenis_transaksi'];
        $tanggal_transaksi = $trans_data['tanggal_transaksi'];
        $bukti_pembayaran = $trans_data['bukti_pembayaran'];
        
        // Handle balance updates if status is successful
        if ($status == 'sukses') {
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
        
        // If it's a top_up transaction, update the corresponding top_up record
        if ($jenis_transaksi == 'top_up') {
            // Try to match by: id_pengguna, jumlah, and close date (same date or a day before/after)
            // Also try to match by bukti_pembayaran if it exists
            $bukti_condition = $bukti_pembayaran ? "OR bukti_pembayaran = '$bukti_pembayaran'" : "";
            
            // We can use DATE() to compare just the date part if the format is datetime
            $update_topup = "UPDATE top_up 
                           SET status = '$status' 
                           WHERE id_pengguna = '$id_pengguna' 
                           AND jumlah = $jumlah
                           AND (
                               DATE(tanggal_top_up) = DATE('$tanggal_transaksi') 
                               OR DATE(tanggal_top_up) = DATE_ADD(DATE('$tanggal_transaksi'), INTERVAL -1 DAY)
                               OR DATE(tanggal_top_up) = DATE_ADD(DATE('$tanggal_transaksi'), INTERVAL 1 DAY)
                               $bukti_condition
                           )
                           AND status != '$status'
                           ORDER BY tanggal_top_up DESC 
                           LIMIT 1";
            mysqli_query($koneksi, $update_topup);
            
            // Optional: check if any rows were affected
            $affected_rows = mysqli_affected_rows($koneksi);
            if ($affected_rows == 0) {
                // Log or handle cases where no top_up record was found
                // echo "No matching top_up record found.";
            }
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