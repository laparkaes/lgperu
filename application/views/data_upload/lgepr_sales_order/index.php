<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>LGEPR Sale Order</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">LGEPR Sale Order</li>
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
						<h5 class="card-title">Sales Orders</h5>
						<form id="form_sales_order_upload">
							<div class="input-group">
								<a class="btn btn-success" href="<?= base_url() ?>template/lgepr_sales_order_template.xls" download="lgepr_sales_order_template"><i class="bi bi-file-earmark-spreadsheet"></i></a>
								<input class="form-control" type="file" name="attach">
								<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i></button>
							</div>
						</form>
					</div>
					<table class="table">
						<thead>
							<tr>
								<th scope="col">Status</th>
								<th scope="col">Created</th>
								<th scope="col">Type</th>
								<th scope="col">Order #</th>
								<th scope="col">Amount USD</th>
								<th scope="col">Inventory</th>
								<th scope="col">Division</th>
								<th scope="col">Category</th>
								<th scope="col">Model</th>
								<th scope="col">Qty</th>
								<th scope="col">Level 1</th>
								<th scope="col">Level 2</th>
								<th scope="col">Level 3</th>
								<th scope="col">Level 4</th>
								<th scope="col">Dept.</th>
								<th scope="col">Bill To</th>
								<th scope="col">Ship To</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($sales_orders as $item){ ?>
							<tr>
								<td><?= $item->line_status ?></td>
								<td><?= $item->create_date ?></td>
								<td><?= $item->order_category ?></td>
								<td><?= $item->order_no." ".$item->line_no ?></td>
								<td><?= number_format($item->sales_amount_usd, 2) ?></td>
								<td><?= $item->inventory_org ?><br/><?= $item->sub_inventory ?></td>
								<td><?= $item->dash_division ?></td>
								<td><?= $item->dash_category ?></td>
								<td><?= $item->model ?></td>
								<td><?= $item->ordered_qty ?></td>
								<td><?= $item->product_level1_name ?></td>
								<td><?= $item->product_level2_name ?></td>
								<td><?= $item->product_level3_name ?></td>
								<td><?= $item->product_level4_name ?></td>
								<td><?= $item->customer_department ?></td>
								<td><?= $item->bill_to_name ?><br/><?= $item->bill_to ?></td>
								<td><?= $item->ship_to_name ?><br/><?= $item->ship_to ?></td>
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
	$("#form_sales_order_upload").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/lgepr_sales_order/upload", "Do you want to update sales order data?").done(function(res) {
			//swal_redirection(res.type, res.msg, "data_upload/lgepr_sales_order");
			swal_open_tab(res.type, res.msg, "lgepr_sales_order/process");
		});
	});
});
</script>