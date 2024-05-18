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
<body class="toggle-sidebar">
	<main id="main" class="main mt-0">
		<section class="section">
			<div class="row">
				<?php
				$type = $msg = null;
				if ($this->session->flashdata('success_msg')){
					$type = "success";
					$msg = $this->session->flashdata('success_msg');
				}elseif ($this->session->flashdata('error_msg')){
					$type = "error";
					$msg = $this->session->flashdata('error_msg');
				}
				
				if ($msg){
				?>
				<div class="col-md-12">
					<div class="alert alert-<?= $type ?> fade show" role="alert">
						<?= $msg ?>
					</div>
				</div>
				<?php } ?>
				<div class="col-md-4">
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">Upload File</h5>
							<form class="row g-3" id="form_upload_file">
								<div class="col-md-12">
									<label class="form-label">File</label>
									<input class="form-control" type="file" name="filename">
								</div>
								<div class="text-center pt-3">
									<button type="submit" class="btn btn-primary">Upload</button>
									<button type="reset" class="btn btn-secondary">Reset</button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="col-md-8">
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">Files</h5>
							<table class="table align-middle">
								<thead>
									<tr>
										<th scope="col" style="width: 80px;">#</th>
										<th scope="col">File name</th>
										<th scope="col" class="text-end">Action</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($files as $i => $f){ ?>
									<tr>
										<th scope="row" style="width: 80px;"><?= number_format($i + 1) ?></th>
										<td><?= $f ?></td>
										<td class="text-end">
											<a href="./upload_file/<?= $f ?>" class="btn btn-primary" download><i class="bi bi-download"></i></a>
											<a href="./file_folder/del/<?= base64_encode($f) ?>" class="btn btn-danger btn_del_file"><i class="bi bi-trash"></i></a>
										</td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</section>	
	</main>
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
		$('.btn_del_file').click(function(){
			if (!confirm("Are you sure to remove file?"))
				event.preventDefault();
		});
		
		$("#form_upload_file").submit(function(e) {
			e.preventDefault();
			$("#form_upload_file .sys_msg").html("");
			ajax_form_warning(this, "file_folder/upload", "Do you want to upload file to server?").done(function(res) {
				if (res == "end") location.reload();
			});
		});
		
	});
	</script>
</body>
</html>