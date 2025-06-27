<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>SCM Shipping Status</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">SCM Shipping Status</li>
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
						<h5 class="card-title"><?= number_format(count($shipping_status)) ?> records</h5>
						<form id="form_shipping_status_upload">
							<div class="input-group">
								<a class="btn btn-success" href="<?= base_url() ?>template/scm_shipping_status_template.xls" download="scm_shipping_status_template"><i class="bi bi-file-earmark-spreadsheet"></i></a>
								<input class="form-control" type="file" name="attach">
								<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i></button>
							</div>
						</form>
					</div>
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">Order No.</th>
								<th scope="col">Line No</th>
								<th scope="col">Bill To Code</th>
								<th scope="col">Bill To Name</th>
								<th scope="col">Inventory Org</th>
								<th scope="col">Subinventory</th>
								<th scope="col">Model Category</th>
								<th scope="col">Model</th>
								<th scope="col">Status</th>
								<th scope="col">Order Qty</th>
								<th scope="col">Requested Qty</th>
								<th scope="col">Pick Release Qty</th>
								<th scope="col">Picked Qty</th>
								<th scope="col">Shipped Qty</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($shipping_status as $item){ ?>
							<tr>
								<td><?= $item->order_no ?></td>
								<td><?= $item->line_no ?></td>
								<td><?= $item->bill_to_code ?></td>
								<td><?= $item->bill_to_name ?></td>
								<td><?= $item->inventory_org ?></td>
								<td><?= $item->sub_inventory ?></td>
								<td><?= $item->model_category ?></td>
								<td><?= $item->model ?></td>
								<td><?= $item->status ?></td>
								<td><?= $item->order_qty ?></td>
								<td><?= $item->requested_qty ?></td>
								<td><?= $item->pick_release_qty ?></td>
								<td><?= $item->picked_qty ?></td>
								<td><?= $item->shipped_qty ?></td>
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
	$("#form_shipping_status_upload").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/scm_shipping_status/upload", "Do you want to update sales order data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/scm_shipping_status");
		});
	});
});
</script>