<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>GERP - Stock Update</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">GERP - Stock Update</li>
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
						<h5 class="card-title">Stock List (Last 10,000 records)</h5>
						<form id="form_stock_update">
							<div class="input-group">
								<a class="btn btn-success" href="<?= base_url() ?>template/gerp_stock_template.xlsx" download="stock_template"><i class="bi bi-file-earmark-spreadsheet"></i></a>
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
								<th scope="col">Category</th>
								<th scope="col">Model</th>
								<th scope="col">Description</th>
								<th scope="col">Qty</th>
								<th scope="col">Status</th>
								<th scope="col">Updated</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($stocks as $item){ ?>
							<tr>
								<td><?= $item->org ?></td>
								<td><?= $item->sub_inventory ?></td>
								<td><?= $item->grade ?></td>
								<td><?= $item->model_category_code ?></td>
								<td><?= $item->model ?></td>
								<td><?= $item->model_description ?></td>
								<td><?= $item->available_qty ?></td>
								<td><?= $item->model_status ?></td>
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
		ajax_form_warning(this, "module/gerp_stock_update/update", "Do you want to update stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/gerp_stock_update");
		});
	});
});
</script>