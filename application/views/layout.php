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
	<link href="<?= base_url() ?>assets/vendor/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
	<link href="<?= base_url() ?>assets/vendor/sweetalert2/dist/sweetalert2.min.css" rel="stylesheet">
	<link href="<?= base_url() ?>assets/vendor/simple-datatables/style.css" rel="stylesheet">
	<link href="<?= base_url() ?>assets/vendor/toastr/toastr.min.css" rel="stylesheet">
	<link href="<?= base_url() ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
	<header id="header" class="header fixed-top d-flex align-items-center">
		<div class="d-flex align-items-center justify-content-between">
			<a href="<?= base_url() ?>" class="logo d-flex align-items-center">
				<img src="<?= base_url() ?>assets/img/logo-lg-100-44.svg" alt="">
				<!-- span class="d-none d-lg-block">LlamaSys</span -->
			</a>
			<i class="bi bi-list toggle-sidebar-btn"></i>
		</div>
		<nav class="header-nav ms-auto">
			<ul class="d-flex align-items-center">
				<li class="nav-item dropdown pe-3">
					<a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
						<i class="bi bi-person-circle" style="font-size: 2em;"></i><span class="dropdown-toggle ps-2"><?= $this->session->userdata('name') ?></span>
					</a>
					<ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
						<li class="dropdown-header">
							<h6><?= $this->session->userdata('department') ?></h6>
							<span><?= $this->session->userdata('employee_number') ?></span>
						</li>
						<li>
							<hr class="dropdown-divider">
						</li>
						<li>
							<a class="dropdown-item d-flex align-items-center" href="<?= base_url() ?>auth/change_password">
								<i class="bi bi-lock"></i>
								<span>Change Password</span>
							</a>
						</li>
						<li>
							<hr class="dropdown-divider">
						</li>
						<li>
							<a class="dropdown-item d-flex align-items-center" id="btn_logout" href="#">
								<i class="bi bi-box-arrow-right"></i>
								<span>Sign Out</span>
							</a>
						</li>
					</ul>
				</li>
			</ul>
		</nav>
	</header>
	<aside id="sidebar" class="sidebar">
		<?php $nav = $this->session->userdata('nav'); ?>
		<ul class="sidebar-nav" id="sidebar-nav">
			<li class="nav-item">
				<a class="nav-link collapsed" href="<?= base_url() ?>dashboard">
					<i class="bi bi-grid"></i>
					<span>Dashboard</span>
				</a>
			</li>
			<?php if ($nav['module']){ ?>
			<li class="nav-item">
				<a class="nav-link collapsed" data-bs-target="#modules-nav" data-bs-toggle="collapse" href="#">
					<i class="bi bi-menu-button-wide"></i><span>Modules</span><i class="bi bi-chevron-down ms-auto"></i>
				</a>
				<ul id="modules-nav" class="nav-content collapse show" data-bs-parent="#modules-nav">
					<?php foreach ($nav['module'] as $item){ ?>
					<li>
						<a href="<?= base_url() ?><?= $item->type ?>/<?= $item->path ?>">
							<i class="bi bi-circle"></i><span><?= $item->title ?></span>
						</a>
					</li>
					<?php } ?>
				</ul>
			</li>
			<?php } if ($nav['data_upload']){ ?>
			<li class="nav-item">
				<a class="nav-link collapsed" data-bs-target="#data_uploads-nav" data-bs-toggle="collapse" href="#">
					<i class="bi bi-upload"></i><span>Data Uploads</span><i class="bi bi-chevron-down ms-auto"></i>
				</a>
				<ul id="data_uploads-nav" class="nav-content collapse show" data-bs-parent="#data_uploads-nav">
					<?php foreach ($nav['data_upload'] as $item){ ?>
					<li>
						<a href="<?= base_url() ?><?= $item->type ?>/<?= $item->path ?>">
							<i class="bi bi-circle"></i><span><?= $item->title ?></span>
						</a>
					</li>
					<?php } ?>
				</ul>
			</li>
			<?php } if ($nav['page']){ ?>
			<li class="nav-item">
				<a class="nav-link collapsed" data-bs-target="#pages-nav" data-bs-toggle="collapse" href="#">
					<i class="bi bi-file-earmark"></i><span>Pages</span><i class="bi bi-chevron-down ms-auto"></i>
				</a>
				<ul id="pages-nav" class="nav-content collapse show" data-bs-parent="#pages-nav">
					<?php foreach ($nav['page'] as $item){ ?>
					<li>
						<a href="<?= base_url() ?><?= $item->type ?>/<?= $item->path ?>" target="_blank">
							<i class="bi bi-circle"></i><span><?= $item->title ?></span>
						</a>
					</li>
					<?php } ?>
				</ul>
			</li>
			<?php } if ($nav['sys']){ ?>
			<li class="nav-item">
				<a class="nav-link collapsed" data-bs-target="#system-nav" data-bs-toggle="collapse" href="#">
					<i class="bi bi-pc-display-horizontal"></i><span>System</span><i class="bi bi-chevron-down ms-auto"></i>
				</a>
				<ul id="system-nav" class="nav-content collapse show" data-bs-parent="#system-nav">
					<?php foreach ($nav['sys'] as $item){ ?>
					<li>
						<a href="<?= base_url() ?><?= $item->type ?>/<?= $item->path ?>">
							<i class="bi bi-circle"></i><span><?= $item->title ?></span>
						</a>
					</li>
					<?php } ?>
				</ul>
			</li>
			<?php } ?>
		</ul>
	</aside>
	
	<main id="main" class="main">
		<?php $this->load->view($main); ?>
	</main><!-- End #main -->

	<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
	
	<script src="<?= base_url() ?>assets/vendor/jquery-3.7.0.min.js"></script>
	<script src="<?= base_url() ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="<?= base_url() ?>assets/vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
	<script src="<?= base_url() ?>assets/vendor/echarts/echarts.min.js"></script>
	<script src="<?= base_url() ?>assets/vendor/sweetalert2/dist/sweetalert2.all.min.js"></script>
	<script src="<?= base_url() ?>assets/vendor/simple-datatables/simple-datatables.js"></script>
	<script src="<?= base_url() ?>assets/vendor/toastr/toastr.min.js"></script>
	<script src="<?= base_url() ?>assets/vendor/peity/jquery.peity.min.js"></script>
	<script src="<?= base_url() ?>assets/js/main.js"></script>
	<script src="<?= base_url() ?>assets/js/func.js"></script>
	<script>
	document.addEventListener("DOMContentLoaded", () => {
		$('#btn_logout').click(function(){
			swal_warning_redirect("Are you sure to leave?", "auth/logout");
		});
		
		$('.alert .btn-close').click(function(){
			$(".alert").addClass("d-none");
		});
	});
	</script>
</body>
</html>