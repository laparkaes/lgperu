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
						<div class="d-flex gap-2 align-items-center">
							<form id="form_shipping_status_upload">
								<div class="input-group">
									<a class="btn btn-success" href="<?= base_url() ?>template/scm_shipping_status_template.xls" download="scm_shipping_status_template" title="Download Template">
										<i class="bi bi-file-earmark-spreadsheet"></i>
									</a>
									<input class="form-control" type="file" name="attach" required>
									<button type="submit" class="btn btn-primary" title="Upload File"> 
										<i class="bi bi-upload"></i> Upload
									</button>
								</div>
							</form>

							<form id="form_shipping_status_export" class="d-flex gap-2">
								<div class="input-group" style="width: 400px;"> 
									<span class="input-group-text" title="From Date"><i class="bi bi-calendar-range"></i></span>
									<input type="date" class="form-control" placeholder="From" name="date_from" id="date_from" required>
									<span class="input-group-text" title="To Date">~</span>
									<input type="date" class="form-control" placeholder="To" name="date_to" id="date_to" required>
								</div>

								<button type="submit" class="btn btn-primary" id="btn_export"> 
									<i class="bi bi-download"></i> Export
								</button>
							</form>
						</div>
					</div>
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">Order No.</th>
								<th scope="col">Line No</th>
								<th scope="col">Bill To Code</th>
								<th scope="col">Bill To Name</th>
								<th scope="col">Inventory Org</th>
								<th scope="col">Sub - Inventory</th>
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
		ajax_form_warning(this, "data_upload/scm_shipping_status/upload", "Do you want to upload this data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/scm_shipping_status");
		});
	});
});
</script>

<script>
	function showLoadingScreen(message) {
		//console.log("Loading Screen ON: " + message);
		$('#btn_export').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...').prop('disabled', true);
	}

	function hideLoadingScreen() {
		//console.log("Loading Screen OFF");
		$('#btn_export').html('<i class="bi bi-download"></i> Export').prop('disabled', false);
	}
	
document.addEventListener("DOMContentLoaded", () => {
    $("#form_shipping_status_export").submit(function(e) {
		e.preventDefault();

		const dateFrom = $('#date_from').val();
		const dateTo = $('#date_to').val();
		const exportUrl = "data_upload/scm_shipping_status/export_data"; 

		if (!dateFrom || !dateTo) {
			alert("Please select a date range.");
			return;
		}
		
		showLoadingScreen("Preparing file for download...");

		const xhr = new XMLHttpRequest();
		const params = `date_from=${dateFrom}&date_to=${dateTo}`;
		
		xhr.open('GET', `<?= base_url() ?>${exportUrl}?${params}`, true);
		xhr.responseType = 'blob';

		xhr.onload = function() {
			hideLoadingScreen();
			
			if (xhr.status === 200) {
				const blob = xhr.response;
				const defaultFilename = 'shipping_status_export.xlsx';
				const url = window.URL.createObjectURL(blob);
				const a = document.createElement('a');
				a.href = url;
				a.download = defaultFilename;
				document.body.appendChild(a);
				a.click();
				a.remove();
				
				window.URL.revokeObjectURL(url);
			} else {
				alert('Error during export. Status: ' + xhr.status);
			}
		};

		xhr.onerror = function() {
			hideLoadingScreen();
			alert('Connection error during export.');
		};

		xhr.send();
	});
});
</script>