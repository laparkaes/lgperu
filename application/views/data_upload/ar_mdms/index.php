<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>AR MDMS</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">AR MDMS</li>
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
						<h5 class="card-title">Last 5,000 records</h5>
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
								<th scope="col">Class ID</th>
								<th scope="col">BP Code</th>
								<th scope="col">BP Group[NAME]</th>
								<th scope="col">Country[NAME]</th>
								<th scope="col">Name(Local)</th>
								<th scope="col">Biz Registration No</th>
								<th scope="col">City</th>
								<th scope="col">State[NAME]</th>
								<th scope="col">Tax Payer ID</th>
								<th scope="col">Start Date Active</th>
								<th scope="col">End Date Active</th>
								<th scope="col">STATUS[ID]</th>
								<th scope="col">SSTATUS[NAME]</th>
								<th scope="col">Updated</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($stocks as $item){ ?>
							<tr>
								<td><?= $item->class_id ?></td>
								<td><?= $item->bp_code ?></td>
								<td><?= $item->bp_group_name ?></td>
								<td><?= $item->country_name ?></td>
								<td><?= $item->name_local ?></td>
								<td><?= $item->biz_registration_no ?></td>
								<td><?= $item->city ?></td>
								<td><?= $item->state_name ?></td>
								<td><?= $item->tax_payer_id ?></td>
								<td><?= $item->start_date_active ?></td>
								<td><?= $item->end_date_active ?></td>
								<td><?= $item->status_id ?></td>
								<td><?= $item->status_name ?></td>
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
		ajax_form_warning(this, "data_upload/ar_mdms/update", "Do you want to upload stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/ar_mdms");
		});
	});
});
</script>

<script> document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload_ml").submit(function(e) {
		e.preventDefault(); const form = this;
		const formData = new FormData(form);
		
		$.ajax({
			url: "<?= base_url() ?>Ar_mdms/upload",
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