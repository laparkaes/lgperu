<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Lgepr Most Likely</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">Lgepr inventory</li>
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
								<th scope="col">Division</th>
								<th scope="col">Period</th>
								<th scope="col">BP</th>
								<th scope="col">Target</th>
								<th scope="col">MP</th>
								<th scope="col">Monthly Report</th>
								<th scope="col">ML</th>
								<th scope="col">ML Actual</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($ml as $item){ ?>
							<tr>
								<td><?= $item->country ?></td>
								<td><?= $item->division ?></td>
								<td><?= $item->yyyy . "-" . $item->mm ?></td>
								<td><?= $item->bp ?></td>
								<td><?= $item->target ?></td>
								<td><?= $item->mp ?></td>
								<td><?= $item->monthly_report ?></td>
								<td><?= $item->ml ?></td>
								<td><?= $item->ml_actual ?></td>
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
		ajax_form_warning(this, "data_upload/lgepr_ml/update", "Do you want to upload stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/lgepr_ml");
		});
	});
});
</script>

<script> document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload_ml").submit(function(e) {
		e.preventDefault(); const form = this;
		const formData = new FormData(form);
		
		$.ajax({
			url: "<?= base_url() ?>lgepr_ml/upload",
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
					swal("Error", "Hubo un problema al procesar el archivo.", "error");
				}
			});
		});
	});
</script>