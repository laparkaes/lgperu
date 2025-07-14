<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Tracking Dispatch</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">SCM Tracking Dispatch</li>
			</ol>
		</nav>
	</div>
	<!--<div>
		<a href="../user_manual/data_upload/scm_tracking_dispatch/scm_tracking_dispatch_en.pptx" class="text-primary">User Manual</a>
	</div>-->
</div>
<section class="section">
	<div class="row justify-content-center">
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">KLO Tracking Dispatch</h5>
					<form id="form_klo_update">
						<div class="input-group">
							<input class="form-control" type="file" name="attach">
							<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
						</div>
					</form>
					<div class="mt-3">
						<a href="<?= base_url() ?>template/scm_tracking_dispatch_klo_template.xlsx" download="scm_tracking_dispatch_klo_template">
							KLO Tracking Dispatch template
						</a>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">APM Tracking Dispatch</h5>
					<form id="form_apm_update">
						<div class="input-group">
							<input class="form-control" type="file" name="attach">
							<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
						</div>
					</form>
					<div class="mt-3">
						<a href="<?= base_url() ?>template/scm_tracking_dispatch_apm_template.xlsx" download="scm_tracking_dispatch_apm_template">
							APM Tracking Dispatch template
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title"><?= $count_tracking ?> records</h5>					
					</div>
					
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">3PL</th>
								<th scope="col">Date</th>
								<th scope="col">Transport</th>
								<th scope="col">Customer</th>
								<th scope="col">Pick Order</th>
								<th scope="col">Guia</th>
								<th scope="col">Model</th>
								<th scope="col">Qty</th>
								<th scope="col">Cbm</th>
								<th scope="col">Status</th>
								<th scope="col">Cita Cliente</th>
								<th scope="col">Cita To</th>
								<th scope="col">H. LLegada</th>
								<th scope="col">H. Descarga</th>
								<th scope="col">Updated</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($tracking as $item){ ?>
							<tr>
								<td><?= $item->_3pl?></td>
								<td><?= $item->date?></td>
								<td><?= $item->transport?></td>
								<td><?= $item->customer?></td>
								<td><?= $item->pick_order?></td>
								<td><?= $item->guide?></td>
								<td><?= $item->model?></td>
								<td><?= $item->qty?></td>
								<td><?= $item->cbm?></td>
								<td><?= $item->status?></td>
								<td><?= $item->client_appointment?></td>
								<td><?= $item->to_appointment?></td>
								<td><?= $item->arrival_time?></td>
								<td><?= $item->download_time?></td>
								<td><?= $item->updated?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_klo_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/scm_tracking_dispatch/upload_tracking_klo", "Do you want to upload Tracking KLO data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/scm_tracking_dispatch");
		});
	});
	
	$("#form_apm_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/scm_tracking_dispatch/upload_tracking_apm", "Do you want to upload Tracking APM data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/scm_tracking_dispatch");
		});
	});
});
</script>