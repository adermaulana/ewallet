
<?php

    include 'koneksi.php';

    session_start();

    if(isset($_SESSION['status']) == 'login'){

        header("location:admin");
        
    }

    if (isset($_POST['registrasi'])) {
        $password = md5($_POST['password']);
        $username = $_POST['username'];

        // Check if the username already exists
        $checkUsername = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE username='$username'");
        if (mysqli_num_rows($checkUsername) > 0) {
            echo "<script>
                    alert('Username sudah digunakan, pilih Username lain.');
                    document.location='registrasi.php';
                </script>";
            exit; // Stop further execution
        }

        // If the username is not taken, proceed with the registration
        $simpan = mysqli_query($koneksi, "INSERT INTO pengguna (nama_lengkap,email,nomor_telepon, username,  password) VALUES ('$_POST[nama_lengkap]','$_POST[email]','$_POST[nomor_telepon]','$_POST[username]','$password')");

        if ($simpan) {
            echo "<script>
                    alert('Berhasil Registrasi!');
                    document.location='index.php';
                </script>";
        } else {
            echo "<script>
                    alert('Gagal!');
                    document.location='registrasi.php';
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
	<link rel="icon" href="assets/images/favicon-32x32.png" type="image/png" />
	<!--plugins-->
	<link href="assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
	<link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
	<link href="assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet" />
	<!-- loader-->
	<link href="assets/css/pace.min.css" rel="stylesheet" />
	<script src="assets/js/pace.min.js"></script>
	<!-- Bootstrap CSS -->
	<link href="assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="assets/css/bootstrap-extended.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link href="assets/css/app.css" rel="stylesheet">
	<link href="assets/css/icons.css" rel="stylesheet">
	<title>Registrasi</title>
</head>

<body class="">
	<!--wrapper-->
	<div class="wrapper">
		<div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
			<div class="container">
				<div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
					<div class="col mx-auto">
						<div class="card mb-0">
							<div class="card-body">
								<div class="p-4">
									<div class="mb-3 text-center">
										<img src="assets/images/ewallet.jpg" width="60" alt="" />
									</div>
									<div class="text-center mb-4">
										<h5 class="">E Wallet</h5>
										<p class="mb-0">Create an E-Wallet account</p>
									</div>
									<div class="form-body">
										<form class="row g-3" method="POST">
											<div class="col-12">
												<label for="nama" class="form-label">Nama Lengkap</label>
												<input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap"  required>
											</div>
											<div class="col-12">
												<label for="email" class="form-label">Email</label>
												<input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
											</div>
											<div class="col-12">
												<label for="telepon" class="form-label">No. Telepon</label>
												<input type="text" class="form-control" id="nomor_telepon" name="nomor_telepon" placeholder="No. Telepon" required>
											</div>
											<div class="col-12">
												<label for="username" class="form-label">Username</label>
												<input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
											</div>
											<div class="col-12">
												<label for="inputChoosePassword" class="form-label">Password</label>
												<div class="input-group" id="show_hide_password">
													<input type="password" name="password" class="form-control border-end-0" id="inputChoosePassword" placeholder="Enter Password" required> <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
												</div>
											</div>
											<div class="col-12">
												<div class="d-grid">
													<button type="submit" name="registrasi" class="btn btn-primary">Registrasi</button>
												</div>
											</div>
											<div class="col-12">
												<div class="text-center ">
													<p class="mb-0">Sudah Punya Akun? <a href="index.php">Login</a></p>
												</div>
											</div>
										</form>
									</div>

								</div>
							</div>
						</div>
					</div>
				</div>
				<!--end row-->
			</div>
		</div>
	</div>
	<!--end wrapper-->
	<!-- Bootstrap JS -->
	<script src="assets/js/bootstrap.bundle.min.js"></script>
	<!--plugins-->
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
	<script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
	<script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
	<!--Password show & hide js -->
	<script>
		$(document).ready(function () {
			$("#show_hide_password a").on('click', function (event) {
				event.preventDefault();
				if ($('#show_hide_password input').attr("type") == "text") {
					$('#show_hide_password input').attr('type', 'password');
					$('#show_hide_password i').addClass("bx-hide");
					$('#show_hide_password i').removeClass("bx-show");
				} else if ($('#show_hide_password input').attr("type") == "password") {
					$('#show_hide_password input').attr('type', 'text');
					$('#show_hide_password i').removeClass("bx-hide");
					$('#show_hide_password i').addClass("bx-show");
				}
			});
		});
	</script>
	<!--app JS-->
	<script src="assets/js/app.js"></script>
</body>

</html>