<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Ngsi inventory</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">NGSI inventory</li>
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
								<th scope="col">Date</th>
								<th scope="col">Division</th>
								<th scope="col">Inv. Org.</th>
								<th scope="col">Sub Inv</th>
								<th scope="col">Qty Onhand</th>
								<th scope="col">Amt Onhand</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($stocks as $item){ ?>
							<tr>
								<td><?= $item->date ?></td>
								<td><?= $item->division ?></td>
								<td><?= $item->inv_org ?></td>
								<td><?= $item->sub_inv ?></td>
								<td><?= $item->qty_onhand ?></td>
								<td><?= $item->amt_onhand_1 ?></td>
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
		ajax_form_warning(this, "data_upload/ngsi_inventory/update", "Do you want to upload stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/ngsi_inventory");
		});
	});
});
</script>

<script> document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload_ml").submit(function(e) {
		e.preventDefault(); const form = this;
		const formData = new FormData(form);
		
		$.ajax({
			url: "<?= base_url() ?>ngsi_inventory/upload",
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