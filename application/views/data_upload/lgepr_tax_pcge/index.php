<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>LGEPR TAX PCGE</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">LGEPR TAX PCGE</li>
			</ol>
		</nav>
	</div>
	<div>
		<a href="../user_manual/data_upload/acc_pcge/acc_pcge_en.pptx" class="text-primary">User Manual</a>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title">Last records</h5>
						<form id="form_pcge_update">						
							<div class="input-group">							
								<a class="btn btn-success" href="<?= base_url('data_upload/lgepr_tax_pcge/export_excel') ?>">
									<i class="bi bi-file-earmark-spreadsheet"></i> Export
								</a>
								<input class="form-control" type="file" name="attach">
								<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i></button>
							</div>
						</form>
					</div>
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">From Period</th>
								<th scope="col">To Period Unit</th>
								<th scope="col">Accounting Unit</th>
								<th scope="col">Account</th>
								<th scope="col">Account Desc</th>
								<th scope="col">PCGE</th>
								<th scope="col">PCGE Decripción</th>
								<th scope="col">Updated</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($pcge as $item){ ?>
							<tr>
								<td><?= $item->from_period ?></td>
								<td><?= $item->to_period ?></td>
								<td><?= $item->accounting_unit ?></td>
								<td><?= $item->account ?></td>
								<td><?= $item->account_desc ?></td>
								<td><?= $item->pcge ?></td>
								<td><?= $item->pcge_decripcion ?></td>
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
	$("#form_pcge_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/lgepr_tax_pcge/update", "Do you want to upload stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/lgepr_tax_pcge");
		});
	});
});
</script>