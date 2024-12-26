<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>LGEPR Closed Order Update</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">LGEPR Closed Order Update </li>
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
						<h5 class="card-title">Last 5,000 sales orders</h5>
						<form id="form_closed_order_upload">
							<div class="input-group">
								<a class="btn btn-success" href="<?= base_url() ?>template/lgepr_closed_order_template.xls" download="lgepr_closed_order_template"><i class="bi bi-file-earmark-spreadsheet"></i></a>
								<input class="form-control" type="file" name="attach">
								<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i></button>
							</div>
						</form>
					</div>
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">Order</th>
								<th scope="col">Status</th>
								<th scope="col">Inventory</th>
								<th scope="col">Bill to</th>
								<th scope="col">Order no & Line</th>
								<th scope="col">Model</th>
								<th scope="col">Qty</th>
								<th scope="col">Currency</th>
								<th scope="col">Sales Amount</th>
								<th scope="col">Created</th>
								<th scope="col">Requested</th>
								<th scope="col">Closed</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($closed_orders as $item){ ?>
							<tr>
								<td><?= $item->order_category ?></td>
								<td><?= $item->line_status ?></td>
								<td><?= $item->inventory_org ?></td>
								<td><?= $item->bill_to_name ?> (<?= $item->bill_to ?>)</td>
								<td>
									<div><?= $item->order_no ?></div>
									<div><?= $item->line_no ?></div>
								</td>
								<td>
									<div><?= $item->model_category ?></div>
									<div><?= $item->model ?></div>
								</td>
								<td><?= number_format($item->ordered_qty) ?></td>
								<td><?= $item->currency ?></td>
								<td><?= number_format($item->sales_amount, 2) ?></td>
								<td><?= $item->create_date ?></td>
								<td><?= $item->req_arrival_date_to ?></td>
								<td><?= $item->close_date ?></td>
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
	$("#form_closed_order_upload").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/lgepr_closed_order/upload", "Do you want to update closed order data?").done(function(res) {
			//swal_redirection(res.type, res.msg, "data_upload/lgepr_closed_order");
			swal_open_tab(res.type, res.msg, "lgepr_closed_order/process");
		});
	});
});
</script>