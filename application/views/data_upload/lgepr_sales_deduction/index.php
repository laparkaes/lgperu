<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Lgepr Sales Deduction</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">Lgepr Sales Deduction</li>
			</ol>
		</nav>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title">Last 100 records</h5>
						<form id="form_mdms_update">
							<div class="input-group">							
								<input class="form-control" type="file" name="attach">
								<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i></button>
							</div>
						</form>
					</div>
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">Country</th>
								<th scope="col">Company</th>
								<th scope="col">Division</th>
								<th scope="col">Period</th>
								<th scope="col">MP Sales Deduction</th>
								<th scope="col">SD Rate</th>
								<th scope="col">Updated</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($ml as $item){ ?>
							<tr>
								<td><?= $item->country ?></td>
								<td><?= $item->company ?></td>
								<td><?= $item->division ?></td>
								<td><?= $item->yyyy . "-" . $item->mm ?></td>
								<td><?= $item->mp_sales_deduction ?></td>
								<td><?= $item->sd_rate ?></td>
								<td><?= $item->updated ?></td>
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
	$("#form_mdms_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/lgepr_sales_deduction/update", "Do you want to upload stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/lgepr_sales_deduction");
		});
	});
});
</script>

<script> document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload_ml").submit(function(e) {
		e.preventDefault(); const form = this;
		const formData = new FormData(form);
		
		$.ajax({
			url: "<?= base_url() ?>lgepr_sales_deduction/upload",
			type: "POST",
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				const blob = new Blob([response], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
				const url = window.URL.createObjectURL(blob);
				const a = document.createElement("a");
				a.href = url;
				a.download = "archivo_modificado.xlsx";
				document.body.appendChild(a);
				a.click();
				document.body.removeChild(a);
				},
				error: function() {
					swal("Error", "There was a problem processing the file.", "error");
				}
			});
		});
	});
</script>