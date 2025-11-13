<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>AP Report</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">AP Report</li>
			</ol>
		</nav>
	</div>
	<div>
		<a href="../user_manual/data_upload/ap_report/ap_report_es.pptx" class="btn btn-sm btn-outline-primary p-2">
			<i class="bi bi-file-earmark-text me-1"></i> User Manual
		</a>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title">Last records</h5>
						<form id="form_ap_upload">						
							<div class="input-group">
								<input type="text" class="form-control" value="http://136.166.13.9/llamasys/api/lgepr/get_ap_report?key=lgepr" readonly title="URL Api" style="min-width: 500px;">
								<a class="btn btn-success" href="<?= base_url('./template/ap_report_template.xlsx') ?>">
									<i class="bi bi-file-earmark-spreadsheet"></i> Template
								</a>
								<input class="form-control" type="file" name="attach">
								<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
							</div>
						</form>
					</div>
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">Division Name</th>
								<th scope="col">Account Name</th>
								<th scope="col">Invoice Num</th>
								<th scope="col">Invoice Date</th>
								<th scope="col">Currency</th>
								<th scope="col">Invoice Amount</th>
								<th scope="col">Payment Amount</th>
								<th scope="col">Due Date</th>
								<th scope="col">Voucher Number</th>
								<th scope="col">Biz Reg No</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($pcge as $item){ ?>
							<tr>
								<td><?= $item->division_name ?></td>
								<td><?= $item->account_name ?></td>
								<td><?= $item->invoice_num ?></td>
								<td><?= $item->invoice_date ?></td>
								<td><?= $item->currency ?></td>
								<td><?= $item->invoice_amount ?></td>
								<td><?= $item->payment_amount ?></td>
								<td><?= $item->due_date ?></td>
								<td><?= $item->voucher_number ?></td>
								<td><?= $item->last_updated ?></td>
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
	$("#form_ap_upload").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/ap_report/upload", "Do you want to upload ap data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/ap_report");
		});
	});
});
</script>