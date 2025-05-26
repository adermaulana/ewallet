<?php
include '../koneksi.php';

session_start();

$id_pengguna = $_SESSION['id_pengguna'];

if ($_SESSION['status'] != 'login') {
    session_unset();
    session_destroy();
    header('location:../');
}

// Get user data
$query = mysqli_query($koneksi, "SELECT saldo, nomor_rekening, nama_lengkap FROM pengguna WHERE id_pengguna = '$id_pengguna'");
$data_pengguna = mysqli_fetch_assoc($query);
$saldo = $data_pengguna['saldo'] ?? 0;
$no_rekening = $data_pengguna['nomor_rekening'] ?? 'Belum terdaftar';
$nama_pengguna = $data_pengguna['nama_lengkap'] ?? 'Pengguna';

// Get transaction stats
$query_transaksi = mysqli_query($koneksi, "SELECT 
    COUNT(*) as total_transaksi, 
    SUM(jumlah) as total_jumlah 
    FROM transaksi 
    WHERE id_pengguna = '$id_pengguna'");
$stats_transaksi = mysqli_fetch_assoc($query_transaksi);
$total_transaksi = $stats_transaksi['total_transaksi'] ?? 0;
$total_jumlah = $stats_transaksi['total_jumlah'] ?? 0;

// Get recent transactions (combining transfers and top-ups)
$query_recent = mysqli_query($koneksi, "(
    SELECT 
        'transfer' as jenis,
        t.id_transfer as id,
        t.tanggal_transfer as tanggal,
        t.jumlah,
        t.status,
        p.nama_lengkap as pihak_lain,
        'Transfer ke ' as keterangan
    FROM riwayat_transfer t
    JOIN pengguna p ON t.id_penerima = p.id_pengguna
    WHERE t.id_pengirim = '$id_pengguna'
    ORDER BY t.tanggal_transfer DESC
    LIMIT 5
) UNION ALL (
    SELECT 
        'topup' as jenis,
        tu.id_top_up as id,
        tu.tanggal_top_up as tanggal,
        tu.jumlah,
        tu.status,
        '' as pihak_lain,
        'Top Up ' as keterangan
    FROM top_up tu
    WHERE tu.id_pengguna = '$id_pengguna'
    ORDER BY tu.tanggal_top_up DESC
    LIMIT 5
) ORDER BY tanggal DESC LIMIT 5");

// Format saldo ke Rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Format date to relative time
function formatWaktu($date) {
    $now = new DateTime();
    $date = new DateTime($date);
    $diff = $now->diff($date);
    
    if ($diff->y > 0) return $date->format('d M Y');
    if ($diff->m > 0) return $date->format('d M');
    if ($diff->d > 0) return $diff->d . ' hari lalu';
    if ($diff->h > 0) return $diff->h . ' jam lalu';
    if ($diff->i > 0) return $diff->i . ' menit lalu';
    return 'Baru saja';
}



$query_chart = mysqli_query($koneksi, "(
    SELECT 
        DATE(tanggal_transfer) as tanggal,
        SUM(jumlah) as jumlah,
        'transfer' as jenis
    FROM riwayat_transfer 
    WHERE id_pengirim = '$id_pengguna'
    AND tanggal_transfer >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(tanggal_transfer)
) UNION ALL (
    SELECT 
        DATE(tanggal_top_up) as tanggal,
        SUM(jumlah) as jumlah,
        'topup' as jenis
    FROM top_up 
    WHERE id_pengguna = '$id_pengguna'
    AND tanggal_top_up >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(tanggal_top_up))
ORDER BY tanggal");

$chart_labels = [];
$chart_transfer = [];
$chart_topup = [];

// Initialize arrays with 7 days
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chart_labels[] = date('d M', strtotime($date));
    $chart_transfer[$date] = 0;
    $chart_topup[$date] = 0;
}

// Process chart data
while ($row = mysqli_fetch_assoc($query_chart)) {
    $date = $row['tanggal'];
    if ($row['jenis'] == 'transfer') {
        $chart_transfer[$date] = (float)$row['jumlah'];
    } else {
        $chart_topup[$date] = (float)$row['jumlah'];
    }
}

// Convert to arrays in order
$chart_transfer_data = array_values($chart_transfer);
$chart_topup_data = array_values($chart_topup);

?>
<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="../assets/images/favicon-32x32.png" type="image/png"/>
	<!--plugins-->
	<link href="../assets/plugins/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet"/>
	<link href="../assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
	<link href="../assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
	<link href="../assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet"/>
	<!-- loader-->
	<link href="../assets/css/pace.min.css" rel="stylesheet"/>
	<script src="../assets/js/pace.min.js"></script>
	<!-- Bootstrap CSS -->
	<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="../assets/css/bootstrap-extended.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="../assets/css/app.css" rel="stylesheet">
	<link href="../assets/css/icons.css" rel="stylesheet">
	<!-- Theme Style CSS -->
	<link rel="stylesheet" href="../assets/css/dark-theme.css"/>
	<link rel="stylesheet" href="../assets/css/semi-dark.css"/>
	<link rel="stylesheet" href="../assets/css/header-colors.css"/>
	<title>Dashboard Pengguna</title>
</head>

<body>
	<!--wrapper-->
	<div class="wrapper">
		<!--sidebar wrapper -->
		<div class="sidebar-wrapper" data-simplebar="true">
			<div class="sidebar-header">
				<div>
					<img src="../assets/images/logo-icon.png" class="logo-icon" alt="logo icon">
				</div>
				<div>
					<h4 class="logo-text">Pengguna</h4>
				</div>
				<div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i>
				</div>
			 </div>
			<!--navigation-->
			<ul class="metismenu" id="menu">
				<li>
					<a href="index.php">
						<div class="parent-icon"><i class='bx bx-home-alt'></i>
						</div>
						<div class="menu-title">Dashboard</div>
					</a>
				</li>
				<li class="menu-label">Fitur</li>
				<li>
					<a href="javascript:;" class="has-arrow">
						<div class="parent-icon"><i class='bx bx-user'></i>
						</div>
						<div class="menu-title">Kelola Top Up</div>
					</a>
					<ul>
						<li> <a href="topup.php"><i class='bx bx-radio-circle'></i>Riwayat Top Up</a>
						</li>
						<li> <a href="tambahtopup.php"><i class='bx bx-radio-circle'></i>Top Up</a>
						</li>
					</ul>
				</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-bookmark-heart'></i>
						</div>
						<div class="menu-title">Transfer</div>
					</a>
					<ul>
						<li> <a href="transfer.php"><i class='bx bx-radio-circle'></i>Transfer Uang</a>
						</li>
					</ul>
				</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class="bx bx-repeat"></i>
						</div>
						<div class="menu-title">Riwayat Transfer</div>
					</a>
					<ul>
						<li> <a href="laporan.php"><i class='bx bx-radio-circle'></i>Lihat Riwayat Transfer</a>
						</li>
					</ul>
				</li>
			</ul>
			<!--end navigation-->
		</div>
		<!--end sidebar wrapper -->
		<!--start header -->
		<header>
			<div class="topbar d-flex align-items-center">
				<nav class="navbar navbar-expand gap-3">
					<div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
					</div>
					  <div class="top-menu ms-auto">
						<ul class="navbar-nav align-items-center gap-1">
							<li class="nav-item mobile-search-icon d-flex d-lg-none" data-bs-toggle="modal" data-bs-target="#SearchModal">
								<a class="nav-link" href="avascript:;"><i class='bx bx-search'></i>
								</a>
							</li>
							<li class="nav-item dark-mode d-none d-sm-flex">
								<a class="nav-link dark-mode-icon" href="javascript:;"><i class='bx bx-moon'></i>
								</a>
							</li>

							<li class="nav-item dropdown dropdown-app">
								<div class="dropdown-menu dropdown-menu-end p-0">
									<div class="app-container p-2 my-2">
									  <div class="row gx-0 gy-2 row-cols-3 justify-content-center p-2">
										 <div class="col">
										 </div>
									  </div><!--end row-->
									</div>
								</div>
							</li>

							<li class="nav-item dropdown dropdown-large">
								<div class="dropdown-menu dropdown-menu-end">
									<div class="header-notifications-list">
									</div>
								</div>
							</li>
							<li class="nav-item dropdown dropdown-large">
								<div class="dropdown-menu dropdown-menu-end">
									<div class="header-message-list">
									</div>
								</div>
							</li>
						</ul>
					</div>
					<div class="user-box dropdown px-3">
						<a class="d-flex align-items-center nav-link dropdown-toggle gap-3 dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<img src="../assets/images/avatars/avatar-2.png" class="user-img" alt="user avatar">
							<div class="user-info">
								<p class="user-name mb-0"><?= $nama_pengguna ?></p>
							</div>
						</a>
						<ul class="dropdown-menu dropdown-menu-end">
							<li><a class="dropdown-item d-flex align-items-center" href="logout.php"><i class="bx bx-log-out-circle"></i><span>Logout</span></a>
							</li>
						</ul>
					</div>
				</nav>
			</div>
		</header>
		<!--end header -->
		<!--start page wrapper -->
		<div class="page-wrapper">
            <div class="page-content">
                <!-- Dashboard Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold text-primary">Hello, <?= $nama_pengguna ?></h2>
                </div>

                <!-- Wallet Balance Card -->
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="mb-1">Saldo</p>
                                <h2 class="fw-bold mb-2 text-dark"><?= formatRupiah($saldo) ?></h2>
                                <p class="small mb-0">Nomor Rekening: <?= $no_rekening ?></p>
                            </div>
                            <div class="text-end">
                                <a href="tambahtopup.php" class="btn btn-light me-2">Top Up</a>
                                <a href="transfer.php" class="btn btn-outline-light">Transfer</a>
                            </div>
                        </div>
                    </div>
                </div>


				<div class="row mb-4">
					<div class="col-12">
						<div class="card border-0 shadow-sm">
							<div class="card-header bg-white border-0">
								<h5 class="fw-bold mb-0">Aktivitas Transaksi 7 Hari Terakhir</h5>
							</div>
							<div class="card-body">
								<div>
									<canvas id="transactionChart" height="300"></canvas>
								</div>
							</div>
						</div>
					</div>
				</div>

                <!-- Stats Cards -->
                <div class="row row-cols-1 row-cols-md-2 g-4 mb-4">
                    <div class="col">
                        <div class="card card-hover border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-gradient-danger bg-opacity-10 p-3 rounded me-3">
                                        <i class='bx bxs-wallet text-danger' style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <p class="mb-1 text-secondary">Total Transaksi</p>
                                        <h4 class="mb-0 fw-bold"><?= $total_transaksi ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card card-hover border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-gradient-success bg-opacity-10 p-3 rounded me-3">
                                        <i class='bx bx-transfer text-success' style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <p class="mb-1 text-secondary">Total Nominal</p>
                                        <h4 class="mb-0 fw-bold"><?= formatRupiah($total_jumlah) ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Transactions and Quick Actions -->
                <div class="row">
                    <div class="col-lg-12 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0">
                                <h5 class="fw-bold mb-0">Transaksi Terakhir</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Deskripsi</th>
                                                <th>Jumlah</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(mysqli_num_rows($query_recent) > 0): ?>
                                                <?php while($transaksi = mysqli_fetch_assoc($query_recent)): ?>
                                                    <tr>
                                                        <td><?= formatWaktu($transaksi['tanggal']) ?></td>
                                                        <td>
                                                            <?= $transaksi['keterangan'] ?>
                                                            <?= $transaksi['jenis'] == 'transfer' ? $transaksi['pihak_lain'] : '' ?>
                                                        </td>
                                                        <td class="<?= $transaksi['jenis'] == 'topup' ? 'text-success' : 'text-danger' ?>">
                                                            <?= $transaksi['jenis'] == 'topup' ? '+' : '-' ?>
                                                            <?= formatRupiah($transaksi['jumlah']) ?>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?= $transaksi['status'] == 'selesai' ? 'success' : 'warning' ?>">
                                                                <?= ucfirst($transaksi['status']) ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">Belum ada transaksi</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <a href="laporan.php" class="btn btn-outline-primary w-100 mt-2">Lihat Semua Transaksi</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
		<!--end page wrapper -->
		<!--start overlay-->
		 <div class="overlay toggle-icon"></div>
		<!--end overlay-->
		<!--Start Back To Top Button-->
		  <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
		<!--End Back To Top Button-->
		<footer class="page-footer">
			<p class="mb-0">Copyright © 2022. All right reserved.</p>
		</footer>
	</div>
	<!--end wrapper-->


	<!-- search modal -->
    <div class="modal" id="SearchModal" tabindex="-1">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-md-down">
		  <div class="modal-content">
			<div class="modal-header gap-2">
			  <div class="position-relative popup-search w-100">
				<input class="form-control form-control-lg ps-5 border border-3 border-primary" type="search" placeholder="Search">
				<span class="position-absolute top-50 search-show ms-3 translate-middle-y start-0 top-50 fs-4"><i class='bx bx-search'></i></span>
			  </div>
			  <button type="button" class="btn-close d-md-none" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="search-list">
				   <p class="mb-1">Html Templates</p>
				   <div class="list-group">
					  <a href="javascript:;" class="list-group-item list-group-item-action active align-items-center d-flex gap-2 py-1"><i class='bx bxl-angular fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-vuejs fs-4'></i>Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-magento fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-shopify fs-4'></i>eCommerce Html Templates</a>
				   </div>
				   <p class="mb-1 mt-3">Web Designe Company</p>
				   <div class="list-group">
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-windows fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-dropbox fs-4' ></i>Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-opera fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-wordpress fs-4'></i>eCommerce Html Templates</a>
				   </div>
				   <p class="mb-1 mt-3">Software Development</p>
				   <div class="list-group">
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-mailchimp fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-zoom fs-4'></i>Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-sass fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-vk fs-4'></i>eCommerce Html Templates</a>
				   </div>
				   <p class="mb-1 mt-3">Online Shoping Portals</p>
				   <div class="list-group">
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-slack fs-4'></i>Best Html Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-skype fs-4'></i>Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-twitter fs-4'></i>Responsive Html5 Templates</a>
					  <a href="javascript:;" class="list-group-item list-group-item-action align-items-center d-flex gap-2 py-1"><i class='bx bxl-vimeo fs-4'></i>eCommerce Html Templates</a>
				   </div>
				</div>
			</div>
		  </div>
		</div>
	  </div>
    <!-- end search modal -->

	<!-- Bootstrap JS -->
	<script src="../assets/js/bootstrap.bundle.min.js"></script>
	<!--plugins-->
	<script src="../assets/js/jquery.min.js"></script>
	<script src="../assets/plugins/simplebar/js/simplebar.min.js"></script>
	<script src="../assets/plugins/metismenu/js/metisMenu.min.js"></script>
	<script src="../assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
	<script src="../assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js"></script>
    <script src="../assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js"></script>
	<script src="../assets/plugins/chartjs/js/chart.js"></script>
	<script src="../assets/js/index.js"></script>
	<!--app JS-->
	<script src="../assets/js/app.js"></script>
	<script>
		new PerfectScrollbar(".app-container")
	</script>


<!-- Chart JS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('transactionChart').getContext('2d');
        const transactionChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_labels) ?>,
                datasets: [
                    {
                        label: 'Transfer Keluar',
                        data: <?= json_encode($chart_transfer_data) ?>,
                        backgroundColor: 'rgb(255, 0, 55)',
                        borderColor: 'rgb(255, 0, 55)',
                        borderWidth: 1
                    },
                    {
                        label: 'Top Up',
                        data: <?= json_encode($chart_topup_data) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR',
                                        minimumFractionDigits: 0
                                    }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    },
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    });
</script>

</body>
</html>