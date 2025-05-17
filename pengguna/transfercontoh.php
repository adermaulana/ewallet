
<?php

include '../koneksi.php';

session_start();

$id_pengguna = $_SESSION['id_pengguna'];

if ($_SESSION['status'] != 'login') {
    session_unset();
    session_destroy();

    header('location:../');
}

$query = mysqli_query($koneksi, "SELECT saldo, nomor_rekening FROM pengguna WHERE id_pengguna = '$id_pengguna'");
$data_pengguna = mysqli_fetch_assoc($query);
$current_balance = $data_pengguna['saldo'] ?? 0;
$no_rekening = $data_pengguna['no_rekening'] ?? 'Belum terdaftar';

// Format saldo ke Rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
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
                    <div class="breadcrumb-title pe-3">Transaksi</div>
                    <div class="ps-3">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0 p-0">
                                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                                <li class="breadcrumb-item active" aria-current="page">Transfer</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <!--end breadcrumb-->
                <hr/>
                <div class="card">
                    <div class="card-body">

						<?php if(isset($_SESSION['transfer_error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bx bx-error-circle me-1"></i> <?= $_SESSION['transfer_error'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['transfer_error']); ?>
                        <?php endif; ?>
                        
                        <!-- Display Success Messages -->
                        <?php if(isset($_SESSION['transfer_success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bx bx-check-circle me-1"></i> <?= $_SESSION['transfer_success'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['transfer_success']); ?>
                        <?php endif; ?>

                        <div class="row">
                            <!-- Current Balance -->
                            <div class="col-md-12 mb-4">
                                <h5>TOTAL SALDO : <?= formatRupiah($current_balance) ?></h5>
                            </div>
                            
                            <!-- QR Code Section -->
							<div class="col-md-6 mb-4">
								<div class="text-center">
									<h6 class="mb-3">Transfer ke Pengguna</h6>
									<?php
									// Generate QR code data - combine user ID and email
									$userData = $id_pengguna . '|' . $_SESSION['email'];
									
									// Include QR code library
									include('../assets/phpqrcode/qrlib.php');
									
									// Path to save QR code image
									$qrPath = '../uploads/qrcodes/';
									if (!file_exists($qrPath)) {
										mkdir($qrPath, 0755, true);
									}
									
									$qrFile = $qrPath . 'user_' . $id_pengguna . '.png';
									$qrRelativePath = 'uploads/qrcodes/user_' . $id_pengguna . '.png';
									
									// Generate QR code
									QRcode::png($userData, $qrFile, QR_ECLEVEL_L, 10);
									
									// Display QR code
									echo '<img src="' . $qrFile . '" class="img-fluid" alt="QR Code" style="max-width: 250px;">';
									?>
									<div class="mt-2">
										<a href="<?php echo $qrFile; ?>" download="transfer_qrcode.png" class="btn btn-primary">Download QR Code</a>
									</div>
								</div>
							</div>
                            
                            <!-- Transfer Form -->
                            <div class="col-md-6">
                                <div class="text-center p-4">
                                    <h6 class="mb-3">Transfer dengan QR Code</h6>
                                    <div class="d-flex justify-content-center gap-3">
                                        <button class="btn btn-primary mb-3" type="button" id="scanQRBtn">
                                            <i class="bx bx-camera"></i> Scan Kamera
                                        </button>
                                        <button class="btn btn-secondary mb-3" type="button" id="uploadQRBtn">
                                            <i class="bx bx-upload"></i> Upload QR
                                        </button>
                                    </div>
                                    
                                    <!-- Camera preview placeholder (initially hidden) -->
                                    <div id="cameraPreviewBox" style="display: none; max-width: 250px; margin: 0 auto;">
                                       
                                    </div>
                                    
                                    <!-- Transfer Form (hidden by default) -->
                                    <div id="transferForm" style="display: none;">
                                        <form method="post" action="">
                                            <div class="mb-3">
                                                <label for="id_penerima" class="form-label">ID Penerima</label>
                                                <input type="text" class="form-control" name="id_penerima" id="id_penerima" required readonly>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="jumlah" class="form-label">Jumlah Transfer</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">Rp</span>
                                                    <input type="number" class="form-control" name="jumlah" id="jumlah" required min="10000">
                                                </div>
                                                <small class="text-muted">Minimal Rp10.000</small>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="catatan" class="form-label">Catatan (Opsional)</label>
                                                <textarea class="form-control" name="catatan" id="catatan" rows="2"></textarea>
                                            </div>
                                            
                                            <!-- Hidden fields -->
                                            <input type="hidden" name="id_pengirim" value="<?= $id_pengguna ?>">
                                            <input type="hidden" name="tanggal_transfer" value="<?= date('Y-m-d H:i:s') ?>">
                                            <input type="hidden" name="status" value="pending">
                                            
                                            <div class="d-grid gap-2">
                                                <button type="submit" name="transfer" class="btn btn-primary">Transfer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

		<!-- QR Code Scanner Modal -->
        <div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="qrScannerModalLabel">Scan QR Code</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs" id="qrTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="scan-tab" data-bs-toggle="tab" data-bs-target="#scan-pane" type="button" role="tab">Scan Kamera</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-pane" type="button" role="tab">Upload QR Code</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="qrTabContent">
                            <div class="tab-pane fade show active" id="scan-pane" role="tabpanel">
                                <div class="text-center mt-3">
                                    <video id="qrScanner" width="100%" style="border: 1px solid #ddd;"></video>
                                    <div id="scanResult" class="mt-3"></div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="upload-pane" role="tabpanel">
                                <div class="text-center mt-3">
                                    <div class="mb-3">
                                        <label for="qrUpload" class="form-label">Pilih File QR Code</label>
                                        <input class="form-control" type="file" id="qrUpload" accept="image/*">
                                    </div>
                                    <div id="qrPreview" class="mb-3"></div>
                                    <button id="processQRBtn" class="btn btn-primary" disabled>Proses QR Code</button>
                                    <div id="uploadResult" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

		<!-- Recipient Info Modal -->
		<div class="modal fade" id="recipientModal" tabindex="-1" aria-labelledby="recipientModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="recipientModalLabel">Detail Penerima</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
                    <!-- Di recipientModal, ganti form menjadi div biasa -->
                    <div class="modal-body">
                        <div id="recipientInfo">
                            <!-- Data penerima akan dimuat di sini -->
                        </div>
                        <div class="mb-3">
                            <label for="transferAmount" class="form-label">Jumlah Transfer</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="transferAmount" required min="10000">
                            </div>
                            <small class="text-muted">Minimal Rp10.000</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="transferNote" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control" id="transferNote" rows="2"></textarea>
                        </div>
                        
                        <!-- Hidden fields -->
                        <input type="hidden" id="modalRecipientId">
                        
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-primary" id="confirmTransferBtn">Transfer Sekarang</button>
                        </div>
                    </div>
				</div>
			</div>
		</div>


        <!-- Password Verification Modal -->
        <div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="passwordModalLabel">Verifikasi Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="passwordForm">
                            <div class="mb-3">
                                <label for="userPassword" class="form-label">Masukkan Password Anda</label>
                                <input type="password" class="form-control" id="userPassword" required>
                                <div id="passwordError" class="text-danger mt-1" style="display: none;"></div>
                            </div>
                            <input type="hidden" id="finalRecipientId">
                            <input type="hidden" id="finalTransferAmount">
                            <input type="hidden" id="finalTransferNote">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-primary" id="verifyPasswordBtn">Verifikasi</button>
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

<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
document.getElementById('scanQRBtn').addEventListener('click', function() {
    const modal = new bootstrap.Modal(document.getElementById('qrScannerModal'));
    modal.show();
    
    document.getElementById('qrScannerModal').addEventListener('shown.bs.modal', function() {
        startQRScanner();
    });
    
    document.getElementById('qrScannerModal').addEventListener('hidden.bs.modal', function() {
        stopQRScanner();
    });
});

let videoStream = null;
let interval = null;

function startQRScanner() {
    const video = document.getElementById('qrScanner');
    const scanResult = document.getElementById('scanResult');
    
    scanResult.innerHTML = '<div class="alert alert-info">Harap berikan izin kamera dan arahkan ke QR Code penerima.</div>';
    
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        scanResult.innerHTML = '<div class="alert alert-danger">Browser tidak mendukung akses kamera.</div>';
        return;
    }
    
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            facingMode: "environment",
            width: { ideal: 1280 },
            height: { ideal: 720 }
        } 
    })
    .then(function(stream) {
        videoStream = stream;
        video.srcObject = stream;
        
        video.onloadedmetadata = function(e) {
            video.play();
            
            interval = setInterval(function() {
                if (video.readyState === video.HAVE_ENOUGH_DATA) {
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    
                    try {
                        const code = jsQR(imageData.data, imageData.width, imageData.height, {
                            inversionAttempts: "dontInvert",
                        });
                        
                        if (code) {
                            const qrData = code.data.split('|');
                            const userId = qrData[0];
                            
                            // Stop scanner immediately when QR detected
                            stopQRScanner();
                            
                            // Hide QR scanner modal
                            bootstrap.Modal.getInstance(document.getElementById('qrScannerModal')).hide();
                            
                            // Load recipient data via AJAX
                            loadRecipientData(userId);
                        }
                    } catch (error) {
                        console.error("Error scanning QR:", error);
                    }
                }
            }, 100);
        };
    })
    .catch(function(err) {
        console.error("Camera error:", err);
        scanResult.innerHTML = '<div class="alert alert-danger">Error: ' + err.message + '</div>';
    });
}

function stopQRScanner() {
    const video = document.getElementById('qrScanner');
    if (videoStream) {
        videoStream.getTracks().forEach(track => track.stop());
        video.srcObject = null;
    }
    if (interval) {
        clearInterval(interval);
    }
}

function loadRecipientData(userId) {
    $.ajax({
        url: 'get_recipient.php',
        type: 'GET',
        data: { id_penerima: userId },
        dataType: 'json',
        beforeSend: function() {
            // Show loading state
            $('#recipientInfo').html('<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
        },
        success: function(response) {
            if (response.success) {
                // Set the recipient ID in the modal form
                $('#modalRecipientId').val(userId);
                
                // Format the recipient info
                const recipientHTML = `
                    <div class="d-flex align-items-center mb-4">
                        <img src="../assets/images/avatars/avatar-2.png" class="rounded-circle" width="60" height="60" alt="User Avatar">
                        <div class="ms-3">
                            <h5>${response.data.nama_lengkap}</h5>

                            <p class="mb-0 text-muted">Email: ${response.data.email}</p>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i> Pastikan data penerima sudah benar sebelum melakukan transfer.
                    </div>`;
                
                $('#recipientInfo').html(recipientHTML);
                
                // Show the recipient modal
                const recipientModal = new bootstrap.Modal(document.getElementById('recipientModal'));
                recipientModal.show();
            } else {
                alert(response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Error loading recipient data: ' + error);
        }
    });
}


$('#uploadQRBtn').click(function() {
    const modal = new bootstrap.Modal(document.getElementById('qrScannerModal'));
    modal.show();
    
    // Aktifkan tab upload
    $('#upload-tab').tab('show');
});

$(document).ready(function() {
    // Handle QR code upload
    $('#qrUpload').change(function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = function(event) {
            $('#qrPreview').html(`<img src="${event.target.result}" class="img-fluid" style="max-width: 250px;">`);
            $('#processQRBtn').prop('disabled', false);
        };
        reader.readAsDataURL(file);
    });
    
    $('#processQRBtn').click(function() {
        const fileInput = document.getElementById('qrUpload');
        if (!fileInput.files || !fileInput.files[0]) {
            $('#uploadResult').html('<div class="alert alert-danger">Silakan pilih file QR Code terlebih dahulu</div>');
            return;
        }
        
        const file = fileInput.files[0];
        const reader = new FileReader();
        
        reader.onload = function(event) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                
                try {
                    const code = jsQR(imageData.data, imageData.width, imageData.height, {
                        inversionAttempts: "dontInvert",
                    });
                    
                    if (code) {
                        const qrData = code.data.split('|');
                        const userId = qrData[0];
                        
                        // Stop scanner jika ada
                        stopQRScanner();
                        
                        // Hide QR scanner modal
                        bootstrap.Modal.getInstance(document.getElementById('qrScannerModal')).hide();
                        
                        // Load recipient data
                        loadRecipientData(userId);
                    } else {
                        $('#uploadResult').html('<div class="alert alert-danger">Tidak dapat membaca QR Code dari gambar</div>');
                    }
                } catch (error) {
                    console.error("Error scanning QR:", error);
                    $('#uploadResult').html('<div class="alert alert-danger">Error: ' + error.message + '</div>');
                }
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });
});


$(document).ready(function() {
    // Ketika tombol transfer di modal penerima diklik
    $(document).on('click', '#confirmTransferBtn', function(e) {
        e.preventDefault();
        
        // Validasi input
        const amount = $('#transferAmount').val();
        if (!amount || amount < 10000) {
            alert('Jumlah transfer minimal Rp10.000');
            return;
        }
        
        // Simpan data transfer
        $('#finalRecipientId').val($('#modalRecipientId').val());
        $('#finalTransferAmount').val(amount);
        $('#finalTransferNote').val($('#transferNote').val());
        
        // Tampilkan modal password
        const passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));
        passwordModal.show();
    });
    
    // Ketika tombol verifikasi password diklik
    $('#verifyPasswordBtn').click(function() {
        const password = $('#userPassword').val();
        
        if (!password) {
            $('#passwordError').text('Password harus diisi').show();
            return;
        }
        
        // Kirim permintaan AJAX untuk verifikasi password
        $.ajax({
            url: 'verify_password.php',
            type: 'POST',
            data: {
                id_pengguna: <?= $id_pengguna ?>,
                password: password
            },
            dataType: 'json',
            beforeSend: function() {
                $('#verifyPasswordBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memverifikasi...');
            },
            success: function(response) {
                if (response.success) {
                    // Password valid, lakukan transfer
                    performTransfer();
                } else {
                    $('#passwordError').text(response.message).show();
                    $('#verifyPasswordBtn').prop('disabled', false).text('Verifikasi');
                }
            },
            error: function() {
                $('#passwordError').text('Terjadi kesalahan saat memverifikasi password').show();
                $('#verifyPasswordBtn').prop('disabled', false).text('Verifikasi');
            }
        });
    });
});

function performTransfer() {
    const formData = {
        id_pengirim: <?= $id_pengguna ?>,
        id_penerima: $('#finalRecipientId').val(),
        jumlah: $('#finalTransferAmount').val(),
        catatan: $('#finalTransferNote').val(),
        tanggal_transfer: '<?= date('Y-m-d H:i:s') ?>',
        status: 'pending',
        password: $('#userPassword').val() // Kirim password juga untuk verifikasi ulang di server
    };
    
    $.ajax({
        url: 'proses_transfer_contoh.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            // Tutup semua modal
            bootstrap.Modal.getInstance(document.getElementById('passwordModal')).hide();
            bootstrap.Modal.getInstance(document.getElementById('recipientModal')).hide();
            
            if (response.success) {
                // Tampilkan pesan sukses dan refresh halaman atau update saldo
                alert(response.message);
                location.reload();
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert('Terjadi kesalahan saat melakukan transfer');
        }
    });
}
</script>

</body>

</html>