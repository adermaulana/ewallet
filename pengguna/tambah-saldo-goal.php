<?php

include '../koneksi.php';

session_start();

$id_pengguna = $_SESSION['id_pengguna'];

if ($_SESSION['status'] != 'login') {
    session_unset();
    session_destroy();

    header('location:../');
}


// Ambil ID goal dari parameter URL
$id_goal = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query untuk mendapatkan detail goal
$goal_query = mysqli_query($koneksi, "SELECT * FROM savings_goals WHERE id_goal = '$id_goal' AND id_pengguna = '$id_pengguna'");
$goal = mysqli_fetch_assoc($goal_query);

// Jika goal tidak ditemukan
if (!$goal) {
    header("Location: savings-goals.php");
    exit();
}

// Proses form tambah saldo
if (isset($_POST['tambah_saldo'])) {
    $jumlah = mysqli_real_escape_string($koneksi, $_POST['jumlah']);
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);
    $tanggal = date('Y-m-d H:i:s');
    
    // Validasi jumlah
    if ($jumlah <= 0) {
        $error = "Jumlah harus lebih dari 0";
    } else {
        // Mulai transaksi
        mysqli_begin_transaction($koneksi);
        
        try {
            // 1. Cek saldo pengguna
            $user_query = mysqli_query($koneksi, "SELECT saldo FROM pengguna WHERE id_pengguna = '$id_pengguna' FOR UPDATE");
            $user = mysqli_fetch_assoc($user_query);
            
            if ($user['saldo'] < $jumlah) {
                throw new Exception("Saldo tidak mencukupi untuk melakukan transaksi ini");
            }
            
            // 2. Kurangi saldo pengguna
            $update_saldo = mysqli_query($koneksi, 
                "UPDATE pengguna 
                 SET saldo = saldo - $jumlah 
                 WHERE id_pengguna = '$id_pengguna'");
            
            if (!$update_saldo) {
                throw new Exception("Gagal mengurangi saldo pengguna: " . mysqli_error($koneksi));
            }
            
            // 3. Update jumlah terkumpul di savings_goals
            $update_goal = mysqli_query($koneksi, 
                "UPDATE savings_goals 
                 SET jumlah_terkumpul = jumlah_terkumpul + $jumlah 
                 WHERE id_goal = $id_goal");
            
            if (!$update_goal) {
                throw new Exception("Gagal update goal: " . mysqli_error($koneksi));
            }
            
            // 4. Cek apakah goal sudah tercapai
            $new_amount = $goal['jumlah_terkumpul'] + $jumlah;
            if ($new_amount >= $goal['target_jumlah'] && $goal['status'] == 'aktif') {
                $update_status = mysqli_query($koneksi, 
                    "UPDATE savings_goals 
                     SET status = 'tercapai', tanggal_tercapai = NOW() 
                     WHERE id_goal = $id_goal");
                
                if (!$update_status) {
                    throw new Exception("Gagal update status: " . mysqli_error($koneksi));
                }
            }
            
            // Commit transaksi
            mysqli_commit($koneksi);
            
            $_SESSION['success'] = "Saldo berhasil ditambahkan ke tabungan!";
            header("Location: tabungan.php");
            exit();
            
        } catch (Exception $e) {
            // Rollback jika ada error
            mysqli_rollback($koneksi);
            $error = $e->getMessage();
        }
    }
}

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
	<link href="../assets/plugins/datatable/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
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
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class="bx bx-dollar"></i>
						</div>
						<div class="menu-title">Tabungan</div>
					</a>
					<ul>
						<li> <a href="tabungan.php"><i class='bx bx-radio-circle'></i>Lihat Tabungan</a>
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
								<p class="user-name mb-0"><?= $_SESSION['nama_pengguna'] ?></p>
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

        <div class="page-wrapper">
            <div class="page-content">
                <!--breadcrumb-->
                <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                    <div class="breadcrumb-title pe-3">Savings Goals</div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item"><a href="savings-goals.php"><i class="bx bx-home-alt"></i></a>
                                </li>
                                <li class="breadcrumb-item"><a href="detail-savings-goal.php?id=<?= $id_goal ?>">Detail Goal</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Tambah Saldo</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <!--end breadcrumb-->
                
                <hr/>
                
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="mb-4">Tambah Saldo untuk Goal</h4>
                                
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?= $error ?></div>
                                <?php endif; ?>
                                
                                <form method="post">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Goal</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($goal['nama_goal']) ?>" readonly>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Saldo Saat Ini</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" class="form-control" value="<?= number_format($goal['jumlah_terkumpul'], 0, ',', '.') ?>" readonly>
                                            <span class="input-group-text">dari Rp <?= number_format($goal['target_jumlah'], 0, ',', '.') ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="jumlah" class="form-label">Jumlah Tambahan <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" name="jumlah" id="jumlah" required min="1000">
                                        </div>
                                        <small class="text-muted">Minimal Rp1.000</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="catatan" class="form-label">Catatan (Opsional)</label>
                                        <textarea class="form-control" name="catatan" id="catatan" rows="2" placeholder="Misal: Setoran gaji bulan Januari"></textarea>
                                    </div>
                                    
                                    <div class="d-md-flex d-grid align-items-center gap-3">
                                        <button type="submit" name="tambah_saldo" class="btn btn-primary px-4">Simpan</button>
                                        <a href="detail-savings-goal.php?id=<?= $id_goal ?>" class="btn btn-light px-4">Batal</a>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border shadow-none">
                                    <div class="card-body">
                                        <h5 class="card-title">Info Goal</h5>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="me-3" style="font-size: 2rem;">
                                                <?= $goal['ikon'] ?: '💰' ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($goal['nama_goal']) ?></h6>
                                                <p class="mb-0 text-muted">Target: Rp <?= number_format($goal['target_jumlah'], 0, ',', '.') ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="progress mb-3" style="height: 10px;">
                                            <?php
                                            $progress = ($goal['jumlah_terkumpul'] / $goal['target_jumlah']) * 100;
                                            $progress_class = $progress >= 100 ? 'bg-success' : ($progress >= 75 ? 'bg-info' : ($progress >= 50 ? 'bg-warning' : 'bg-danger'));
                                            ?>
                                            <div class="progress-bar <?= $progress_class ?>" role="progressbar" style="width: <?= min($progress, 100) ?>%"></div>
                                        </div>
                                        <p class="text-center mb-3"><?= number_format($progress, 1) ?>% Tercapai</p>
                                        
                                        <?php if ($goal['target_tanggal']): ?>
                                            <div class="mb-3">
                                                <h6>Target Tanggal:</h6>
                                                <p><?= date('d F Y', strtotime($goal['target_tanggal'])) ?></p>
                                                <?php
                                                $today = new DateTime();
                                                $target_date = new DateTime($goal['target_tanggal']);
                                                $interval = $today->diff($target_date);
                                                ?>
                                                <p class="<?= $target_date < $today ? 'text-danger' : 'text-success' ?>">
                                                    <i class="bx bx-time"></i> 
                                                    <?= $target_date < $today ? 'Terlambat ' : 'Tersisa ' ?>
                                                    <?= $interval->format('%a hari') ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($goal['deskripsi']): ?>
                                            <div class="mb-3">
                                                <h6>Deskripsi:</h6>
                                                <p><?= htmlspecialchars($goal['deskripsi']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
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




	<!--start switcher-->
	<div class="switcher-wrapper">
		<div class="switcher-btn"> <i class='bx bx-cog bx-spin'></i>
		</div>
		<div class="switcher-body">
			<div class="d-flex align-items-center">
				<h5 class="mb-0 text-uppercase">Theme Customizer</h5>
				<button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
			</div>
			<hr/>
			<h6 class="mb-0">Theme Styles</h6>
			<hr/>
			<div class="d-flex align-items-center justify-content-between">
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="lightmode" checked>
					<label class="form-check-label" for="lightmode">Light</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="darkmode">
					<label class="form-check-label" for="darkmode">Dark</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="semidark">
					<label class="form-check-label" for="semidark">Semi Dark</label>
				</div>
			</div>
			<hr/>
			<div class="form-check">
				<input class="form-check-input" type="radio" id="minimaltheme" name="flexRadioDefault">
				<label class="form-check-label" for="minimaltheme">Minimal Theme</label>
			</div>
			<hr/>
			<h6 class="mb-0">Header Colors</h6>
			<hr/>
			<div class="header-colors-indigators">
				<div class="row row-cols-auto g-3">
					<div class="col">
						<div class="indigator headercolor1" id="headercolor1"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor2" id="headercolor2"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor3" id="headercolor3"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor4" id="headercolor4"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor5" id="headercolor5"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor6" id="headercolor6"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor7" id="headercolor7"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor8" id="headercolor8"></div>
					</div>
				</div>
			</div>
			<hr/>
			<h6 class="mb-0">Sidebar Colors</h6>
			<hr/>
			<div class="header-colors-indigators">
				<div class="row row-cols-auto g-3">
					<div class="col">
						<div class="indigator sidebarcolor1" id="sidebarcolor1"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor2" id="sidebarcolor2"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor3" id="sidebarcolor3"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor4" id="sidebarcolor4"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor5" id="sidebarcolor5"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor6" id="sidebarcolor6"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor7" id="sidebarcolor7"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor8" id="sidebarcolor8"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!--end switcher-->
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
	<script src="../assets/plugins/datatable/js/jquery.dataTables.min.js"></script>
	<script src="../assets/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
	<!--app JS-->


	<script>
		$(document).ready(function() {
			$('#example').DataTable();
		  } );
	</script>
	<script>
		$(document).ready(function() {
			var table = $('#example2').DataTable( {
				lengthChange: false,
				buttons: [ 'copy', 'excel', 'pdf', 'print']
			} );
		 
			table.buttons().container()
				.appendTo( '#example2_wrapper .col-md-6:eq(0)' );
		} );
	</script>


	<script src="../assets/js/app.js"></script>
	<script>
		new PerfectScrollbar(".app-container")
	</script>


</body>

</html>