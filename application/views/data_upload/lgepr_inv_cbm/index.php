<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>LGEPR Inventory CBM</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">Lgepr Inventory CBM</li>
			</ol>
		</nav>
	</div>
</div>
<section class="section">	
	<div class="row">
		<div class="col">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title">Last Record: <?= 2025-2 ?></h5>
						<form id="form_mdms_update">
							<div class="input-group">							
								<input class="form-control" type="file" name="attach">
								<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i></button>
							</div>
						</form>
					</div>
					<table class="table">
						<thead>
							<tr>
								<th scope="col" class="text-center">Period</th>
								<th scope="col" class="text-center">Company</th>
								<th scope="col" class="text-center">Division</th>
								<th scope="col" class="text-center">Model</th>
								<th scope="col" class="text-center">Model Gross CBM</th>
								<th scope="col" class="text-center">⁬Inventory Org Code</th>
								<th scope="col" class="text-center">⁬Subinventory Code</th>
								<th scope="col" class="text-center">⁬Begining Qty</th>
								<th scope="col" class="text-center">Total CBM Day 1</th>
								<th scope="col" class="text-center">Balance Day 1</th>
								<th scope="col" class="text-center">In Day 1</th>
								<th scope="col" class="text-center">Out Day 1</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($inv as $item){?>
								<td class="text-end text-center"><?= $item->period?></td>
								<td class="text-end text-center"><?= $item->company?></td>
								<td class="text-end text-center"><?= $item->division?></td>
								<td class="text-end text-center"><?= $item->model?></td>
								<td class="text-end text-center"><?= $item->model_gross_cbm?></td>
								<td class="text-end text-center"><?= $item->inventory_org_code?></td>
								<td class="text-end text-center"><?= $item->subinventory_code?></td>
								<td class="text-end text-center"><?= $item->begining_qty?></td>
								<td class="text-end text-center"><?= $item->total_cbm_day1?></td>
								<td class="text-end text-center"><?= $item->balance_day1?></td>
								<td class="text-end text-center"><?= $item->in_day1?></td>
								<td class="text-end text-center"><?= $item->out_day1?></td>
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
		ajax_form_warning(this, "data_upload/lgepr_inv_cbm/update", "Do you want to upload stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/lgepr_inv_cbm");
		});
	});
});
</script>

<script> document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload_ml").submit(function(e) {
		e.preventDefault(); const form = this;
		const formData = new FormData(form);
		
		$.ajax({
			url: "<?= base_url() ?>lgepr_inv_cbm/upload",
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