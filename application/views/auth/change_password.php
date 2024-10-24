<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title>LG Peru</title>
	<meta content="" name="description">
	<meta content="" name="keywords">
	<link href="<?= base_url() ?>assets/img/favicon.png" rel="icon">
	<link href="<?= base_url() ?>assets/img/apple-touch-icon.png" rel="apple-touch-icon">
	<link href="https://fonts.gstatic.com" rel="preconnect">
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
	<link href="<?= base_url() ?>assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?= base_url() ?>assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
	<link href="<?= base_url() ?>assets/vendor/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
	<link href="<?= base_url() ?>assets/vendor/simple-datatables/style.css" rel="stylesheet">
	<link href="<?= base_url() ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
	<main>
		<div class="container">
			<section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
				<div class="container">
					<div class="row justify-content-center">
						<div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
							<div class="d-flex justify-content-center py-4">
								<a href="<?= base_url() ?>dashboard" class="logo d-flex align-items-center w-auto">
									<span class="d-none d-lg-block"><i class="bi bi-arrow-left me-3"></i> Back to Dashboard</span>
								</a>
							</div><!-- End Logo -->
							<div class="card mb-3">
								<div class="card-body">
									<div class="pt-4 pb-2">
										<h5 class="card-title text-center pb-0 fs-4">Change Password</h5>
										<p class="text-center small">Enter actual password & new password</p>
									</div>
									<form class="row g-3" id="form_change_password">
										<div class="col-12">
											<label class="form-label">Password</label>
											<input type="password" class="form-control" name="password" required>
										</div>
										<div class="col-12">
											<label class="form-label">New Password</label>
											<input type="password" class="form-control" name="password_n" required>
										</div>
										<div class="col-12">
											<label class="form-label">Confirm Password</label>
											<input type="password" class="form-control" name="password_c" required>
										</div>
										<div class="col-12">
											<button class="btn btn-primary w-100" type="submit">Change Password</button>
										</div>
										<div class="col-12 pt-5">
											<p class="small mb-0">Contact with PI Team if you need help.</p>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
	</main><!-- End #main -->

	<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
	
	<script src="<?= base_url() ?>assets/vendor/jquery-3.7.0.min.js"></script>
	<script src="<?= base_url() ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="<?= base_url() ?>assets/vendor/echarts/echarts.min.js"></script>
	<script src="<?= base_url() ?>assets/vendor/sweetalert2/dist/sweetalert2.all.min.js"></script>
	<script src="<?= base_url() ?>assets/vendor/simple-datatables/simple-datatables.js"></script>
	<script src="<?= base_url() ?>assets/js/main.js"></script>
	<script src="<?= base_url() ?>assets/js/func.js"></script>
	<script>
	document.addEventListener("DOMContentLoaded", () => {
		$("#form_change_password input").keydown(function(event) {
			if (event.keyCode === 13) { // 13 is the key code for Enter
				event.preventDefault(); // Prevent default form submission
				$("#form_change_password").submit(); // Submit the form
			}
		});
		
		$("#form_change_password").submit(function(e) {
			e.preventDefault();
			$("#form_change_password .sys_msg").html("");
			ajax_form_warning(this, "auth/change_password_process", "You must log in again after changing your password.").done(function(res) {
				swal_redirection(res.type, res.msg, res.url);
				
				
				//if (res.type == "success") window.location.href = res.url;
				//else swal(res.type, res.msg);
			});
		});
	});
	</script>
</body>

</html>