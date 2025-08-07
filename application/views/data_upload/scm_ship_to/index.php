<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Ship To</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">SCM Ship To</li>
			</ol>
		</nav>
	</div>
	<!--<div>
		<a href="../user_manual/data_upload/scm_container_status/scm_container_status_en.pptx" class="text-primary">User Manual</a>
	</div>-->
</div>
<section class="section">	
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title"><?= $count_tracking ?> records</h5>	

						<form id="form_ship_to_update">
							<div class="input-group">
								<a class="btn btn-success" href="<?= base_url() ?>template/scm_container_status_template.xlsx" download="scm_container_status_template"><i class="bi bi-file-earmark-spreadsheet"></i></a>
								<input class="form-control" type="file" name="attach">
								<button type="submit" class="btn btn-primary me-2"><i class="bi bi-upload"></i></button>
							</div>
						</form>
					</div>
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">Bill To Code</th>
								<th scope="col">Bill To Name</th>
								<th scope="col">Ship To</th>
								<th scope="col">Address</th>
								<th scope="col">Warehouse</th>
								<th scope="col">Updated</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($tracking as $item){ ?>
							<tr>
								<td><?= $item->bill_to_code?></td>
								<td><?= $item->bill_to_name?></td>
								<td><?= $item->ship_to_code?></td>
								<td><?= $item->address?></td>
								<td><?= $item->warehouse?></td>
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
	$("#form_ship_to_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/scm_ship_to/upload", "Do you want to upload Ship To data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/scm_ship_to");
		});
	});
});
</script>