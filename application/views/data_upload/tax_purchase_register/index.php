<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Tax purchase register</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">Tax purchase register</li>
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
						<h5 class="card-title">Last 1,000 records</h5>
						<form id="form_mdms_update">
							<div class="input-group">							
								<input class="form-control" type="file" name="attach">
								<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i></button>
							</div>
						</form>
					</div>
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">Voucher_No</th>
								<th scope="col">Invoice_Date</th>
								<th scope="col">Payment_Due_Date</th>
								<th scope="col">Document Type</th>
								<th scope="col">Serial Number</th>
								<th scope="col">Invoice Number</th>
								<th scope="col">Tax_Invoice_Type_Code</th>
								<th scope="col">Customer_Vat_No</th>
								<th scope="col">Id_Business_Type</th>
								<th scope="col">Tax_Rate_Code</th>
								<th scope="col">Report_Net_Amount</th>
								<th scope="col">Report_Vat_Amount</th>
								<th scope="col">Inafecto(input)</th>
								<th scope="col">Inafecto(calculated)</th>
								<th scope="col">Total Amount</th>
								<th scope="col">Exchange_Rate</th>								
								<th scope="col">Updated</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($stocks as $item){ ?>
							<tr>
								<td><?= $item->voucher_no ?></td>
								<td><?= $item->invoice_date ?></td>
								<td><?= $item->payment_due_date ?></td>
								<td><?= $item->document_type ?></td>
								<td><?= $item->serial_number ?></td>
								<td><?= $item->invoice_number ?></td>
								<td><?= $item->tax_invoice_type_code ?></td>
								<td><?= $item->customer_vat_no ?></td>
								<td><?= $item->id_business_type ?></td>
								<td><?= $item->tax_rate_code ?></td>
								<td><?= $item->report_net_amount ?></td>
								<td><?= $item->report_vat_amount ?></td>
								<td><?= $item->inafecto_input ?></td>
								<td><?= $item->inafecto_calculated ?></td>
								<td><?= $item->total_amount ?></td>
								<td><?= $item->exchange_rate ?></td>
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
	$("#form_mdms_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/tax_purchase_register/update", "Do you want to upload stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/tax_purchase_register");
		});
	});
});
</script>

<script> document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload_ml").submit(function(e) {
		e.preventDefault(); const form = this;
		const formData = new FormData(form);
		
		$.ajax({
			url: "<?= base_url() ?>tax_purchase_register/upload",
			type: "POST",
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				const blob = new Blob([response], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
				const url = window.URL.createObjectURL(blob);
				const a = document.createElement("a");
				a.href = url;
				a.download = "archivo_modificado.xlsx";
				document.body.appendChild(a);
				a.click();
				document.body.removeChild(a);
				},
				error: function() {
					swal("Error", "Hubo un problema al procesar el archivo.", "error");
				}
			});
		});
	});
</script>