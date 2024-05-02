<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Sales Order</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item">SCM</li>
				<li class="breadcrumb-item active">Sales Order</li>
			</ol>
		</nav>
	</div>
	<div></div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-5">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Update Sales Order by File</h5>
					<form class="row g-3" id="form_upload_sales_order">
						<div class="col-12">
							<label class="form-label">File</label>
							<input type="file" class="form-control" name="file_order">
						</div>
						<div class="col-md-12 flex-fill align-self-end">
							<div class="d-grid gap-2">
								<button type="submit" class="btn btn-primary">Submit</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-7">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Lastest 2,000 orders</h5>
					<div class="table-responsive">
						<table class="table datatable align-middle">
							<thead>
								<tr>
									<th scope="col" style="width: 80px;">#</th>
									<th scope="col">Order Date</th>
									<th scope="col">Closed Date</th>
									<th scope="col">Is closed</th>
									<th scope="col">Order No.</th>
									<th scope="col">Line No.</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($orders as $i => $o){ ?>
								<tr>
									<td class="text-nowrap"><?= number_format($i + 1) ?></td>
									<td><?= $o->order_date ?></td>
									<td><?= $o->closed_date ?></td>
									<td><?= $o->is_co ? "Yes" : "No"; ?></td>
									<td><?= $o->order_no ?></td>
									<td><?= $o->line_no ?></td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload_sales_order").submit(function(e) {
		e.preventDefault();
		$("#form_upload_sales_order .sys_msg").html("");
		ajax_form_warning(this, "scm/sales_order/upload_sales_order", "Do you want to upload sales order?").done(function(res) {
			swal_open_tab(res.type, res.msg, res.url);
		});
	});
});
</script>