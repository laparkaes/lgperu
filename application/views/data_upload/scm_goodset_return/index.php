<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>SCM Goodset Return</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">SCM Goodset Return</li>
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
						<h5 class="card-title">Goodset Return (Last 5,000 records)</h5>
						<form id="form_goodset_return_upload">
							<div class="input-group">
								<a class="btn btn-success" href="<?= base_url() ?>template/scm_goodset_return_template.xls" download="scm_goodset_return_template"><i class="bi bi-file-earmark-spreadsheet"></i></a>
								<input class="form-control" type="file" name="attach">
								<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i></button>
							</div>
						</form>
					</div>
					<table class="table datatable table-sm">
						<thead>
							<tr>
								<th scope="col">Type</th>
								<th scope="col">RMA</th>
								<th scope="col">Status</th>
								<th scope="col">Customer</th>
								<th scope="col">Model</th>
								<th scope="col">Qty / Amount</th>
								<th scope="col">Invo Date (SO)</th>
								<th scope="col">Entry<br/>Approved<br/>Received</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($goodset_returns as $item){ ?>
							<tr>
								<td><?= $item->rma_type ?></td>
								<td><?= $item->rma_no ?>_<?= $item->rma_line_no ?></td>
								<td><?= $item->status ?></td>
								<td><?= $item->bill_to_name ?></td>
								<td><?= $item->model ?><br/><?= $item->product_level1 ?></td>
								<td><?= abs($item->entry_qty) ?> units<br/><?= $item->currency ?> <?= number_format(abs($item->entry_amt), 2) ?></td>
								<td><?= $item->sales_invoice_date ?><br/><?= $item->sales_order_no ?></td>
								<td><?= $item->entry_date ?><br/><?= $item->approval_date ?><br/><?= $item->receiving_date ?></td>
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
	$("#form_goodset_return_upload").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/scm_goodset_return/upload", "Do you want to update return data?").done(function(res) {
			swal_open_tab(res.type, res.msg, "scm_goodset_return/process");
		});
	});
});
</script>