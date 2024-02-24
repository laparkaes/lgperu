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
	<link href="<?= base_url() ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
	<header id="header" class="header fixed-top d-flex align-items-center">
		<div class="d-flex align-items-center justify-content-between">
			<a href="<?= base_url() ?>" class="logo d-flex align-items-center">
				<img src="<?= base_url() ?>assets/img/logo-lg-100-44.svg" alt="">
			</a>
			<i class="bi bi-list toggle-sidebar-btn"></i>
		</div>
		<nav class="header-nav ms-auto">
			<ul class="d-flex align-items-center">
				<li class="nav-item dropdown">
					<a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
						<i class="bi bi-bell"></i>
						<span class="badge bg-primary badge-number">4</span>
					</a>
					<ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
						<li class="dropdown-header">
						  You have 4 new notifications
						  <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
						</li>
						<li>
						  <hr class="dropdown-divider">
						</li>

						<li class="notification-item">
						  <i class="bi bi-exclamation-circle text-warning"></i>
						  <div>
							<h4>Lorem Ipsum</h4>
							<p>Quae dolorem earum veritatis oditseno</p>
							<p>30 min. ago</p>
						  </div>
						</li>

						<li>
						  <hr class="dropdown-divider">
						</li>

						<li class="notification-item">
						  <i class="bi bi-x-circle text-danger"></i>
						  <div>
							<h4>Atque rerum nesciunt</h4>
							<p>Quae dolorem earum veritatis oditseno</p>
							<p>1 hr. ago</p>
						  </div>
						</li>

						<li>
						  <hr class="dropdown-divider">
						</li>

						<li class="notification-item">
						  <i class="bi bi-check-circle text-success"></i>
						  <div>
							<h4>Sit rerum fuga</h4>
							<p>Quae dolorem earum veritatis oditseno</p>
							<p>2 hrs. ago</p>
						  </div>
						</li>

						<li>
						  <hr class="dropdown-divider">
						</li>

						<li class="notification-item">
						  <i class="bi bi-info-circle text-primary"></i>
						  <div>
							<h4>Dicta reprehenderit</h4>
							<p>Quae dolorem earum veritatis oditseno</p>
							<p>4 hrs. ago</p>
						  </div>
						</li>

						<li>
						  <hr class="dropdown-divider">
						</li>
						<li class="dropdown-footer">
						  <a href="#">Show all notifications</a>
						</li>
					</ul>
				</li>
				<li class="nav-item dropdown pe-3">
					<a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
						<span class="dropdown-toggle ps-2">K. Anderson</span>
					</a>
					<ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
						<li class="dropdown-header">
							<h6>Kevin Anderson</h6>
							<span>Web Designer</span>
						</li>
						<li>
							<hr class="dropdown-divider">
						</li>
						<li>
							<a class="dropdown-item d-flex align-items-center" href="users-profile.html">
								<i class="bi bi-person"></i>
								<span>My Profile</span>
							</a>
						</li>
						<li>
							<hr class="dropdown-divider">
						</li>
						<li>
							<a class="dropdown-item d-flex align-items-center" href="users-profile.html">
								<i class="bi bi-gear"></i>
								<span>Account Settings</span>
							</a>
						</li>
						<li>
							<hr class="dropdown-divider">
						</li>
						<li>
							<a class="dropdown-item d-flex align-items-center" href="pages-faq.html">
								<i class="bi bi-question-circle"></i>
								<span>Need Help?</span>
							</a>
						</li>
						<li>
							<hr class="dropdown-divider">
						</li>
						<li>
							<a class="dropdown-item d-flex align-items-center" href="#">
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
		<ul class="sidebar-nav" id="sidebar-nav">
			<li class="nav-item">
				<a class="nav-link <?= ($this->nav_menu[0] === "dashboard") ? "" : "collapsed" ?>" href="<?= base_url() ?>dashboard">
					<i class="bi bi-grid"></i>
					<span>Dashboard</span>
				</a>
			</li>
			
			<li class="nav-item">
				<a class="nav-link <?= ($this->nav_menu[0] === "hr") ? "" : "collapsed" ?>" data-bs-target="#hr-nav" data-bs-toggle="collapse" href="#" aria-expanded="<?= ($this->nav_menu[0] === "hr") ? "true" : "false" ?>">
					<i class="bi bi-person-rolodex"></i><span>HR</span><i class="bi bi-chevron-down ms-auto"></i>
				</a>
				<ul id="hr-nav" class="nav-content collapse <?= ($this->nav_menu[0] === "hr") ? "show" : "" ?>" data-bs-parent="#sidebar-nav">
					<li>
						<a href="<?= base_url() ?>hr/attendance" class="<?= ($this->nav_menu[1] === "attendance") ? "active" : "" ?>">
							<i class="bi bi-circle"></i><span>Attendance</span>
						</a>
					</li>
					<li>
						<a href="<?= base_url() ?>hr/employee" class="<?= ($this->nav_menu[1] === "employee") ? "active" : "" ?>">
							<i class="bi bi-circle"></i><span>Employee</span>
						</a>
					</li>
				</ul>
			</li>
			<li class="nav-heading">Pages</li>

			<li class="nav-item">
				<a class="nav-link collapsed" href="users-profile.html">
					<i class="bi bi-person"></i>
					<span>Profile</span>
				</a>
			</li>
			<li class="nav-item">
				<a class="nav-link collapsed" href="pages-faq.html">
					<i class="bi bi-question-circle"></i>
					<span>F.A.Q</span>
				</a>
			</li>
		</ul>
	</aside>

<main id="main" class="main">
<?php $this->load->view($main); ?>
</main><!-- End #main -->

<a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

	
	<script src="<?= base_url() ?>assets/vendor/jquery-3.7.0.min.js"></script>
	<script src="<?= base_url() ?>assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="<?= base_url() ?>assets/vendor/echarts/echarts.min.js"></script>
	<script src="<?= base_url() ?>assets/vendor/sweetalert2/dist/sweetalert2.all.min.js"></script>
	<script src="<?= base_url() ?>assets/js/main.js"></script>
	<script src="<?= base_url() ?>assets/js/func.js"></script>
</body>
</html>