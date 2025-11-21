<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>AR Report</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">AR Report</li>
			</ol>
		</nav>
	</div>
	<!--<div>
		<a href="../user_manual/data_upload/scm_tracking_dispatch/scm_tracking_dispatch_en.pptx" class="text-primary">User Manual</a>
	</div>-->
</div>
<section class="section">
	<div class="row justify-content-center">
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">AR Detail</h5>
					<form id="form_ar_detail">
						<div class="input-group">
							<input class="form-control" type="file" name="attach">
							<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
						</div>
					</form>
					<div class="mt-3">
						<!--<a href="<?= base_url() ?>template/ar_detail_template.xlsx" download="ar_detail_template">
							AR Detail template
						</a>-->
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">AR Aging</h5>
					<form id="form_ar_aging">
						<div class="input-group">
							<input class="form-control" type="file" name="attach">
							<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
						</div>
					</form>
					<div class="mt-3">
						<!--<a href="<?= base_url() ?>template/ar_aging_template.xlsx" download="ar_aging_template">
							AR Aging template
						</a>-->
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Cash Report</h5>
					<form id="form_ar_cash_report">
						<div class="input-group">
							<input class="form-control" type="file" name="attach">
							<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
						</div>
					</form>
					<div class="mt-3">
						<!--<a href="<?= base_url() ?>template/ar_cash_report_template.xlsx" download="ar_cash_report_template">
							Cash Report template
						</a>-->
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body mt-2">
					<ul class="nav nav-tabs nav-tabs-bordered d-flex" id="borderedTabJustified" role="tablist">
						<li class="nav-item flex-fill" role="presentation">
							<button class="nav-link w-100 active" id="home-tab" data-bs-toggle="tab" data-bs-target="#bordered-justified-home" type="button" role="tab" aria-controls="home" aria-selected="true">AR Detail</button>
						</li>
						<li class="nav-item flex-fill" role="presentation">
							<button class="nav-link w-100" id="profile-tab" data-bs-toggle="tab" data-bs-target="#bordered-justified-profile" type="button" role="tab" aria-controls="profile" aria-selected="false" tabindex="-1">AR Aging</button>
						</li>
						<li class="nav-item flex-fill" role="presentation">
							<button class="nav-link w-100" id="contact-tab" data-bs-toggle="tab" data-bs-target="#bordered-justified-contact" type="button" role="tab" aria-controls="contact" aria-selected="false" tabindex="-1">Cash Report</button>
						</li>
					</ul>
					<div class="tab-content pt-2" id="borderedTabJustifiedContent">
						<div class="tab-pane fade show active" id="bordered-justified-home" role="tabpanel" aria-labelledby="home-tab">
							<div class="d-flex justify-content-between align-items-center">
								<h5 class="card-title"> Last <?= count($detail_data) ?> records</h5>
								
							</div>
							<table class="table datatable">
								<thead>
									<tr>
										<th scope="col">Invoice No.</th>
										<th scope="col">Trx Date</th>
										<th scope="col">Due Date</th>
										<th scope="col">GL Date</th>
										<th scope="col">Period</th>
										<th scope="col">Currency</th>
										<th scope="col">Original Amount  (Entered Curr.)</th>
										<th scope="col">Balance Total</th>
										<th scope="col">Original Amount (Functional Curr.)</th>
										<th scope="col">Bill To Code</th>
										<th scope="col">Payment Term</th>
										<th scope="col">Order Number</th>
										<th scope="col">AR No.</th>
										<th scope="col">Biz No.</th>
										<th scope="col">Updated</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($detail_data as $item){ ?>
									<tr>
										<td><?= $item->invoice_no?></td>
										<td><?= $item->trx_date?></td>
										<td><?= $item->due_date?></td>
										<td><?= $item->gl_date?></td>
										<td><?= $item->period?></td>
										<td><?= $item->currency?></td>
										<td><?= $item->original_amount_entered_curr?></td>
										<td><?= $item->balance_total?></td>
										<td><?= $item->original_amount_functional_curr?></td>
										<td><?= $item->bill_to_code?></td>
										<td><?= $item->payment_term?></td>
										<td><?= $item->order_number?></td>
										<td><?= $item->ar_no?></td>
										<td><?= $item->biz_no?></td>
										<td><?= $item->last_updated?></td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
						<div class="tab-pane fade" id="bordered-justified-profile" role="tabpanel" aria-labelledby="profile-tab">
							<div class="d-flex justify-content-between align-items-center">
								<h5 class="card-title"> Last <?= count($aging_data) ?> records</h5>
								
							</div>
							<table class="table datatable">
								<thead>
									<tr>
										<th scope="col">Period</th>
										<th scope="col">ER</th>
										<th scope="col">Invoice No</th>
										<th scope="col">Bill Code</th>
										<th scope="col">Bill Name</th>
										<th scope="col">Currency</th>
										<th scope="col">Amount</th>
										<th scope="col">Invoice Date</th>
										<th scope="col">Due Date</th>
										<th scope="col">Trx Number</th>
										<th scope="col">Payment Term Name</th>
										<th scope="col">Updated</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($aging_data as $item){ ?>
									<tr>
										<td><?= $item->period?></td>
										<td><?= $item->er?></td>
										<td><?= $item->invoice_no?></td>
										<td><?= $item->bill_code?></td>
										<td><?= $item->bill_name?></td>
										<td><?= $item->currency?></td>
										<td><?= $item->amount?></td>
										<td><?= $item->invoice_date?></td>
										<td><?= $item->due_date?></td>
										<td><?= $item->trx_number?></td>
										<td><?= $item->payment_term_name?></td>
										<td><?= $item->last_updated?></td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
						<div class="tab-pane fade" id="bordered-justified-contact" role="tabpanel" aria-labelledby="contact-tab">
							<div class="d-flex justify-content-between align-items-center">
								<h5 class="card-title"> Last <?= count($aging_data) ?> records</h5>
								
							</div>
							<table class="table datatable">
								<thead>
									<tr>
										<th scope="col">Statement ID</th>
										<th scope="col">GL Date</th>
										<th scope="col">Deposit Amount</th>
										<th scope="col">Bill To Code</th>
										<th scope="col">Bill To Name</th>
										<th scope="col">Deposit Currency</th>
										<th scope="col">Alloc. Amount</th>
										<th scope="col">Bank Name</th>
										<th scope="col">Status</th>
										<th scope="col">Requested Date</th>
										<th scope="col">Batch No</th>
										<th scope="col">Updated</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($cash_data as $item){ ?>
									<tr>
										<td><?= $item->statement_id?></td>
										<td><?= $item->gl_date?></td>
										<td><?= $item->deposit_amount?></td>
										<td><?= $item->bill_to_code?></td>
										<td><?= $item->bill_to_name?></td>
										<td><?= $item->deposit_currency?></td>
										<td><?= $item->alloc_amount?></td>
										<td><?= $item->bank_name?></td>
										<td><?= $item->status?></td>
										<td><?= $item->requested_date?></td>
										<td><?= $item->batch_no?></td>
										<td><?= $item->last_updated?></td>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_ar_detail").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/ar_report/upload_ar_detail", "Do you want to upload AR Detail data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/ar_report");
		});
	});
	
	$("#form_ar_aging").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/ar_report/upload_ar_aging", "Do you want to upload AR Aging data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/ar_report");
		});
	});
	
	$("#form_ar_cash_report").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/ar_report/upload_ar_cash_report", "Do you want to upload AR Cash Report data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/ar_report");
		});
	});
});
</script>