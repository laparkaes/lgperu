<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>LGEPR Price</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item">Data Uploads</li>
				<li class="breadcrumb-item active">LGEPR Price</li>
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
						<h5 class="card-title">Last 1,000 records</h5>
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
								<th scope="col">Customer</th>
								<th scope="col">Currency</th>
								<th scope="col">Model Code</th>
								<th scope="col">Unit Price</th>
								<th scope="col">Status</th>
								<th scope="col">Request Date</th>						
								<th scope="col">Updated</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($stocks as $item){ ?>
							<tr>
								<td><?= $item->customer ?></td>
								<td><?= $item->currency ?></td>
								<td><?= $item->model_code ?></td>
								<td><?= $item->unit_price ?></td>
								<td><?= $item->status ?></td>
								<td><?= $item->request_date ?></td>
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
		ajax_form_warning(this, "data_upload/lgepr_price/update", "Do you want to upload stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/lgepr_price");
		});
	});
});
</script>

<script> document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload_ml").submit(function(e) {
		e.preventDefault(); const form = this;
		const formData = new FormData(form);
		
		$.ajax({
			url: "<?= base_url() ?>lgepr_price/upload",
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