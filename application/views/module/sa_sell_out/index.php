<section class="section">
	<div class="row">
		<div class="col-md-5 mx-auto">
			<div class="d-flex justify-content-between align-items-start">
				<div class="pagetitle">
					<h1>Sell Out</h1>
					<nav>
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
							<li class="breadcrumb-item active">Sell Out</li>
						</ol>
					</nav>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Upload</h5>
					<form class="row g-3" id="form_upload_data">
						<div class="col-12">
							<input class="form-control" type="file" name="attach">
						</div>
						<div class="col-12 text-center pt-3">
							<button type="submit" class="btn btn-primary">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Lastest 1,000 Records</h5>
					<table class="table datatable align-middle">
						<thead>
							<tr>
								<th scope="col" style="width: 80px;">#</th>
								<th scope="col">Bill to</th>
								<th scope="col">Bill name</th>
								<th scope="col">SKU</th>
								<th scope="col">Model</th>
								<th scope="col">Date</th>
								<th scope="col">Store</th>
								<th scope="col">Qty</th>
								<th scope="col">Amount</th>
								<th scope="col">Stock</th>
								<th scope="col">Ticket</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($records as $i => $item){ ?>
							<tr>
								<td class="text-nowrap"><?= number_format($i + 1) ?></td>
								<td><?= $item->customer ?></td>
								<td><?= $item->acct_gtm ?></td>
								<td><?= $item->customer_model ?></td>
								<td><?= $item->model_suffix_code ?></td>
								<td><?= $item->txn_date ?></td>
								<td><?= $item->cust_store_name ?> (<?= $item->cust_store_code ?>)</td>
								<td><?= number_format($item->sellout_unit) ?></td>
								<td><?= number_format($item->sellout_amt, 2) ?></td>
								<td><?= number_format($item->stock) ?></td>
								<td><?= number_format($item->ticket, 2) ?></td>
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
	$("#form_upload_data").submit(function(e) {
		e.preventDefault();
		$("#form_upload_data .sys_msg").html("");
		ajax_form_warning(this, "module/sa_sell_out/upload", "Are you sure to upload sell-out records?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/sa_sell_out");
		});
	});
});
</script>