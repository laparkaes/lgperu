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
		<div class="col-md-4 mx-auto">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Update</h5>
					<form class="row g-3" id="form_stock_update">
						<div class="col-md-12">
							<label class="form-label"><?= $last->updated ?></label>
							<input class="form-control" type="file" name="attach">
						</div>
						<div class="text-center pt-3">
							<button type="submit" class="btn btn-primary">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Stock List</h5>
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
		$("#form_stock_update .sys_msg").html("");
		ajax_form_warning(this, "module/gerp_stock_update/update", "Do you want to update stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/gerp_stock_update");
		});
	});
});
</script>