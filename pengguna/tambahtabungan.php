<?php

include '../koneksi.php';

session_start();

$id_pengguna = $_SESSION['id_pengguna'];

if ($_SESSION['status'] != 'login') {
    session_unset();
    session_destroy();

    header('location:../');
}


if (isset($_POST['simpan_tabungan'])) {
    // Ambil data dari form
    $id_pengguna = mysqli_real_escape_string($koneksi, $_POST['id_pengguna']);
    $nama_goal = mysqli_real_escape_string($koneksi, $_POST['nama_goal']);
    $target_jumlah = mysqli_real_escape_string($koneksi, $_POST['target_jumlah']);
    $target_tanggal = isset($_POST['target_tanggal']) ? mysqli_real_escape_string($koneksi, $_POST['target_tanggal']) : null;
    $ikon = mysqli_real_escape_string($koneksi, $_POST['ikon']);
    $deskripsi = isset($_POST['deskripsi']) ? mysqli_real_escape_string($koneksi, $_POST['deskripsi']) : null;
    $status = 'aktif';
    $tanggal_dibuat = mysqli_real_escape_string($koneksi, $_POST['tanggal_dibuat']);
    
    // Handle auto save fields
    $auto_save_aktif = isset($_POST['auto_save_aktif']) ? 1 : 0;
    $jumlah_auto_save = $auto_save_aktif ? mysqli_real_escape_string($koneksi, $_POST['jumlah_auto_save']) : 0;
    $frekuensi_auto_save = $auto_save_aktif ? mysqli_real_escape_string($koneksi, $_POST['frekuensi_auto_save']) : null;
    $tanggal_auto_save_terakhir = null;
    
    // Set tanggal auto save untuk frekuensi bulanan
    if ($auto_save_aktif && $frekuensi_auto_save == 'bulan') {
        $tanggal_auto_save = mysqli_real_escape_string($koneksi, $_POST['tanggal_auto_save']);
    } else {
        $tanggal_auto_save = null;
    }
    
    // Validasi data
    if (empty($nama_goal) || empty($target_jumlah)) {
        echo "<script>
                alert('Nama goal dan target jumlah harus diisi!');
                document.location='tambahtabungan.php';
            </script>";
        exit;
    }
    
    if ($target_jumlah < 10000) {
        echo "<script>
                alert('Target jumlah minimal Rp10.000!');
                document.location='tambahtabungan.php';
            </script>";
        exit;
    }
    
    if ($auto_save_aktif && $jumlah_auto_save < 1000) {
        echo "<script>
                alert('Jumlah auto save minimal Rp1.000!');
                document.location='tambahtabungan.php';
            </script>";
        exit;
    }

    // Insert data ke database
    $simpan = mysqli_query($koneksi, "INSERT INTO savings_goals 
                        (id_pengguna, nama_goal, target_jumlah, jumlah_terkumpul, 
                         target_tanggal, auto_save_aktif, jumlah_auto_save, 
                         frekuensi_auto_save, tanggal_auto_save_terakhir, 
                         ikon, deskripsi, status, tanggal_dibuat) 
                        VALUES 
                        ('$id_pengguna', '$nama_goal', '$target_jumlah', 0,
                         " . ($target_tanggal ? "'$target_tanggal'" : "NULL") . ", 
                         '$auto_save_aktif', '$jumlah_auto_save',
                         " . ($frekuensi_auto_save ? "'$frekuensi_auto_save'" : "NULL") . ", 
                         NULL,
                         '$ikon', 
                         " . ($deskripsi ? "'$deskripsi'" : "NULL") . ", 
                         '$status', '$tanggal_dibuat')");

    if ($simpan) {
        echo "<script>
                alert('Tabungan baru berhasil dibuat!');
                document.location='tabungan.php';
            </script>";
    } else {
        echo "<script>
                alert('Gagal membuat tabungan: ".mysqli_error($koneksi)."');
                document.location='tambahtabungan.php';
            </script>";
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
                                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">Tambah Tabungan Baru</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <!--end breadcrumb-->
                <hr/>
                <div class="card">
                    <div class="card-body">
                        <form method="post" action="">
                            <input type="hidden" name="id_pengguna" value="<?= $id_pengguna ?>">
                            <input type="hidden" name="status" value="aktif">
                            <input type="hidden" name="tanggal_dibuat" value="<?= date('Y-m-d H:i:s') ?>">
                            
                            <div class="row">
                                <!-- Nama Goal -->
                                <div class="col-md-6 mb-3">
                                    <label for="nama_goal" class="form-label">Nama Tabungan/Tujuan</label>
                                    <input type="text" class="form-control" name="nama_goal" id="nama_goal" required placeholder="Contoh: Liburan ke Bali, Beli Laptop Baru">
                                </div>
                                
                                <!-- Ikon -->
                                <div class="col-md-6 mb-3">
                                    <label for="ikon" class="form-label">Ikon</label>
                                    <select class="form-select" name="ikon" id="ikon">
                                        <option value="💰">💰 Tabung</option>
                                        <option value="🏠">🏠 Rumah</option>
                                        <option value="🚗">🚗 Mobil</option>
                                        <option value="✈️">✈️ Liburan</option>
                                        <option value="🎓">🎓 Pendidikan</option>
                                        <option value="💍">💍 Pernikahan</option>
                                        <option value="📱">📱 Gadget</option>
                                        <option value="🛒">🛒 Belanja</option>
                                    </select>
                                </div>
                                
                                <!-- Deskripsi -->
                                <div class="col-12 mb-3">
                                    <label for="deskripsi" class="form-label">Deskripsi (Opsional)</label>
                                    <textarea class="form-control" name="deskripsi" id="deskripsi" rows="2" placeholder="Tambahkan deskripsi tentang tujuan tabungan ini"></textarea>
                                </div>
                                
                                <!-- Target Jumlah -->
                                <div class="col-md-6 mb-3">
                                    <label for="target_jumlah" class="form-label">Target Jumlah</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="target_jumlah" id="target_jumlah" required min="10000">
                                    </div>
                                    <small class="text-muted">Minimal Rp10.000</small>
                                </div>
                                
                                <!-- Target Tanggal -->
                                <div class="col-md-6 mb-3">
                                    <label for="target_tanggal" class="form-label">Target Tanggal (Opsional)</label>
                                    <input type="date" class="form-control" name="target_tanggal" id="target_tanggal" min="<?= date('Y-m-d') ?>">
                                </div>
                                
                                <div id="auto_save_fields" style="display:none;">
                                    <div class="row">
                                        <!-- Jumlah Auto Save -->
                                        <div class="col-md-4 mb-3">
                                            <label for="jumlah_auto_save" class="form-label">Jumlah Auto Save</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" class="form-control" name="jumlah_auto_save" id="jumlah_auto_save" min="1000">
                                            </div>
                                            <small class="text-muted">Minimal Rp1.000</small>
                                        </div>
                                        
                                        <!-- Frekuensi Auto Save -->
                                        <div class="col-md-4 mb-3">
                                            <label for="frekuensi_auto_save" class="form-label">Frekuensi</label>
                                            <select class="form-select" name="frekuensi_auto_save" id="frekuensi_auto_save">
                                                <option value="hari">Harian</option>
                                                <option value="minggu">Mingguan</option>
                                                <option value="bulan" selected>Bulanan</option>
                                            </select>
                                        </div>
                                        
                                        <!-- Tanggal Auto Save -->
                                        <div class="col-md-4 mb-3" id="tanggal_auto_save_field">
                                            <label for="tanggal_auto_save" class="form-label">Tanggal</label>
                                            <select class="form-select" name="tanggal_auto_save" id="tanggal_auto_save">
                                                <?php for ($i = 1; $i <= 28; $i++): ?>
                                                    <option value="<?= $i ?>" <?= $i == date('j') ? 'selected' : '' ?>>
                                                        <?= $i ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Tombol Submit -->
                                <div class="col-12">
                                    <div class="d-md-flex d-grid align-items-center gap-3">
                                        <button type="submit" name="simpan_tabungan" class="btn btn-primary px-4">Simpan Tabungan</button>
                                        <button type="reset" class="btn btn-light px-4">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </form>
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

    <script>
    // Show/hide auto save fields
    document.getElementById('auto_save_aktif').addEventListener('change', function() {
        const autoSaveFields = document.getElementById('auto_save_fields');
        autoSaveFields.style.display = this.checked ? 'block' : 'none';
        
        // Set required attribute if checked
        const requiredFields = autoSaveFields.querySelectorAll('[name="jumlah_auto_save"], [name="frekuensi_auto_save"]');
        requiredFields.forEach(field => {
            field.required = this.checked;
        });
    });

    // Show/hide tanggal field based on frequency
    document.getElementById('frekuensi_auto_save').addEventListener('change', function() {
        const tanggalField = document.getElementById('tanggal_auto_save_field');
        tanggalField.style.display = this.value === 'bulan' ? 'block' : 'none';
    });
    </script>

	<script src="../assets/js/app.js"></script>
	<script>
		new PerfectScrollbar(".app-container")
	</script>


</body>

</html>