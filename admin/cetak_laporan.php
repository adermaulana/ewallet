<?php
include '../koneksi.php';
session_start();

if(!isset($_SESSION['id_admin'])) {
    header('Location:../');
    exit();
}

// Ambil parameter filter
$filter_type = $_GET['filter_type'] ?? 'bulanan';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');

// Sesuaikan tanggal berdasarkan tipe filter
if ($filter_type == 'harian') {
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d');
} elseif ($filter_type == 'mingguan') {
    $start_date = date('Y-m-d', strtotime('monday this week'));
    $end_date = date('Y-m-d', strtotime('sunday this week'));
} elseif ($filter_type == 'bulanan') {
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
}

// Query untuk mendapatkan data laporan
$query = "SELECT t.*, p1.nama_lengkap as nama_pengguna, p2.nama_lengkap as nama_penerima 
          FROM transaksi t
          LEFT JOIN pengguna p1 ON t.id_pengguna = p1.id_pengguna
          LEFT JOIN pengguna p2 ON t.id_penerima = p2.id_pengguna
          WHERE DATE(t.tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'";
$result = mysqli_query($koneksi, $query);

// Hitung total transaksi
$total_query = "SELECT 
                SUM(CASE WHEN jenis_transaksi = 'top_up' THEN jumlah ELSE 0 END) as total_topup,
                SUM(CASE WHEN jenis_transaksi = 'transfer' THEN jumlah ELSE 0 END) as total_transfer,
                SUM(CASE WHEN jenis_transaksi = 'tarik' THEN jumlah ELSE 0 END) as total_tarik
                FROM transaksi
                WHERE DATE(tanggal_transaksi) BETWEEN '$start_date' AND '$end_date'";
$total_result = mysqli_query($koneksi, $total_query);
$total_data = mysqli_fetch_assoc($total_result);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cetak Laporan Transaksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin-bottom: 5px;
        }
        .header p {
            margin-top: 0;
        }
        .periode {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .summary {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .summary-box {
            border: 1px solid #ddd;
            padding: 10px;
            width: 50%;
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Laporan Transaksi</h2>
        <p>Sistem Manajemen Dompet Digital</p>
    </div>
    
    <div class="periode">
        <p><strong>Periode:</strong> 
            <?= date('d/m/Y', strtotime($start_date)) ?> - <?= date('d/m/Y', strtotime($end_date)) ?>
        </p>
    </div>
    
    <div class="summary">
        <div class="summary-box">
            <h3>Total Top Up</h3>
            <p><?= number_format($total_data['total_topup'], 0, ',', '.') ?></p>
        </div>
        <div class="summary-box">
            <h3>Total Transfer</h3>
            <p><?= number_format($total_data['total_transfer'], 0, ',', '.') ?></p>
        </div>

    </div>
    
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Pengguna</th>
                <th>Jumlah</th>
                <th>Penerima</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            while($data = mysqli_fetch_array($result)):
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= date('d/m/Y H:i', strtotime($data['tanggal_transaksi'])) ?></td>
                <td><?= ucfirst($data['jenis_transaksi']) ?></td>
                <td><?= $data['nama_pengguna'] ?></td>
                <td><?= number_format($data['jumlah'], 0, ',', '.') ?></td>
                <td><?= $data['nama_penerima'] ?: '-' ?></td>
                <td><?= ucfirst($data['status']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Dicetak pada: <?= date('d/m/Y H:i:s') ?></p>
        <p>Oleh: <?= $_SESSION['nama_admin'] ?></p>
    </div>
    
    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()">Cetak Laporan</button>
        <button onclick="window.close()">Tutup</button>
    </div>
    
    <script>
        // Auto print saat halaman selesai load
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>