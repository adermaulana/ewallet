<?php
// Include the database connection and necessary functions
include '../koneksi.php';

// Get the start and end dates from the query parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Construct the SQL query with date filter if provided
$query = "
  SELECT catatan.*, kategori.nama AS kategori_nama, pengguna.nama AS pengguna_nama
  FROM catatan
  JOIN kategori ON catatan.id_kategori = kategori.id
  JOIN pengguna ON catatan.id_pengguna = pengguna.id
";

if (!empty($start_date) && !empty($end_date)) {
  $query .= " WHERE tanggal BETWEEN '$start_date' AND '$end_date'";
}

$result = mysqli_query($koneksi, $query);

// Start output buffering to capture the HTML content
ob_start();
?>

<!DOCTYPE html>
<html>
<head>
  <title>Report</title>
  <style>
    @media print {
      /* Add any necessary styling for the print report */
      body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
      }
      table {
        width: 100%;
        border-collapse: collapse;
      }
      th, td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
      }
      th {
        background-color: #f2f2f2;
      }
      /* Hide non-essential elements for printing */
      .no-print {
        display: none;
      }
    }
  </style>
</head>
<body onload="window.print()">
  <div class="no-print">
    <h1>Report</h1>
  </div>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Pengguna</th>
        <th>Tanggal Catatan</th>
        <th>Kategori</th>
        <th>Jumlah</th>
        <th>Deskripsi</th>
      </tr>
    </thead>
    <tbody>
      <?php
        $no = 1;
        while ($data = mysqli_fetch_array($result)) {
      ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= $data['pengguna_nama'] ?></td>
        <td><?= $data['tanggal'] ?></td>
        <td><?= $data['kategori_nama'] ?></td>
        <td>Rp. <?= number_format($data['jumlah'], 0, ',', '.') ?></td>
        <td><?= $data['deskripsi'] ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</body>
</html>

<?php
// Get the HTML content and output it
$html_content = ob_get_clean();
echo $html_content;
?>