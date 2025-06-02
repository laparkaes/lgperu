<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>GERP Stock</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">GERP Stock</li>
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
						<form id="form_stock_update">
							<div class="input-group">
								<a class="btn btn-success" href="<?= base_url() ?>template/lgepr_stock_template.xlsx" download="lgepr_stock_template"><i class="bi bi-file-earmark-spreadsheet"></i></a>
								<input class="form-control" type="file" name="attach">
								<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i></button>
							</div>
						</form>
					</div>
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">Org</th>
								<th scope="col">Sub</th>
								<th scope="col">Grade</th>
								<th scope="col">Company</th>
								<th scope="col">Division</th>
								<th scope="col">Model</th>
								<th scope="col">Description</th>
								<th scope="col">Available</th>
								<th scope="col">On Hand</th>
								<th scope="col">On Hand CBM</th>
								<th scope="col">Status</th>
								<th scope="col">Sea stock total</th>
								<th scope="col">Sea stock W1</th>
								<th scope="col">Sea stock W2</th>
								<th scope="col">Sea stock W3</th>
								<th scope="col">Sea stock W4</th>
								<th scope="col">Sea stock W5</th>
								<th scope="col">Updated</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($stocks as $item){ ?>
							<tr>
								<td><?= $item->org ?></td>
								<td><?= $item->sub_inventory ?></td>
								<td><?= $item->grade ?></td>
								<td><?= $item->dash_company ?></td>
								<td><?= $item->dash_division ?></td>
								<td><?= $item->model ?></td>
								<td><?= $item->model_description ?></td>
								<td><?= $item->available_qty ?></td>
								<td><?= $item->on_hand ?></td>
								<td><?= $item->on_hand_cbm ?></td>
								<td><?= $item->model_status ?></td>
								<td><?= $item->seaStockTotal ?></td>
								<td><?= $item->seaStockW1 ?></td>
								<td><?= $item->seaStockW2 ?></td>
								<td><?= $item->seaStockW3 ?></td>
								<td><?= $item->seaStockW4 ?></td>
								<td><?= $item->seaStockW5 ?></td>
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
	$("#form_stock_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/lgepr_stock/update", "Do you want to update stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/lgepr_stock");
		});
	});
});
</script>

<script> document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload_ml").submit(function(e) {
		e.preventDefault(); const form = this;
		const formData = new FormData(form);
		
		$.ajax({
			url: "<?= base_url() ?>Lgepr_tax/upload",
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