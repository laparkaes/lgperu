<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Container Management</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item">Custom</li>
				<li class="breadcrumb-item active">Container Management</li>
			</ol>
		</nav>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">DQ Shipment Advise (Pending CTN)</h5>
					<form id="form_dq_shipment_advise">
						<div class="input-group">
							<input class="form-control" type="file" name="attach">
							<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
						</div>
					</form>
					<div class="mt-3">
						<a href="<?= base_url() ?>template/custom_dq_sa_report_template.xlsx" download="custom_dq_sa_report_template">
							Download template (DQ Shipment Advise)
						</a>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Receiving Confirm (Received CTN)</h5>
					<form id="form_receiving_confirm">
						<div class="input-group">
							<input class="form-control" type="file" name="attach">
							<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
						</div>
					</form>
					<div class="mt-3">
						<a href="<?= base_url() ?>template/custom_receiving_confirm_template.xlsx" download="custom_receiving_confirm_template">
							Download template (Receiving Confirmation)
						</a>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Step 2. Update Container dates</h5>
					<form id="form_custom_container_dates">
						<div class="input-group">
							<input class="form-control" type="file" name="attach">
							<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
						</div>
					</form>
					<div class="mt-3">
						<a href="<?= base_url() ?>template/custom_container_dates.xlsx" download="custom_container_dates_template">
							Download template
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title">Containers</h5>
					</div>
					<ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="ata">
                  <button class="nav-link active" id="ata-tab" data-bs-toggle="tab" data-bs-target="#ata" type="button" role="tab" aria-controls="ata" aria-selected="true">ATA</button>
                </li>
                <li class="nav-item" role="3pl">
                  <button class="nav-link" id="3pl-tab" data-bs-toggle="tab" data-bs-target="#3pl" type="button" role="tab" aria-controls="3pl" aria-selected="false" tabindex="-1">3PL</button>
                </li>
                <li class="nav-item" role="returned">
                  <button class="nav-link" id="returned-tab" data-bs-toggle="tab" data-bs-target="#returned" type="button" role="tab" aria-controls="returned" aria-selected="false" tabindex="-1">Returned</button>
                </li>
              </ul>
					
					
					
					<table class="table">
						<thead>
							<tr>
								<th scope="col">SA</th>
								<th scope="col">Carrier</th>
								<th scope="col">Container</th>
								<th scope="col">Org.</th>
								<th scope="col">Sub-Inv.</th>
								<th scope="col">Model</th>
								<th scope="col">Qty</th>
								<th scope="col">ETA</th>
								<th scope="col">ATA</th>
								<th scope="col">Picked Up</th>
								<th scope="col">3PL Arrival</th>
								<th scope="col">Returned</th>
								<th scope="col">Updated</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($containers as $item){ ?>
							<tr>
								<td><?= $item->sa_no ?>.<?= $item->sa_line_no ?></td>
								<td><?= $item->carrier_line ?></td>
								<td><?= $item->container ?></td>
								<td><?= $item->organization ?></td>
								<td><?= $item->sub_inventory ?></td>
								<td><?= $item->model ?><?php if ($item->company) {?><br/><?= $item->company ?>.<?= $item->division ?><?php } ?></td>
								<td><?= number_format($item->qty) ?></td>
								<td><?= $item->eta ?></td>
								<td><?= $item->ata ?></td>
								<td><?= $item->picked_up ?></td>
								<td><?= $item->wh_arrival ?></td>
								<td><?= $item->returned ?></td>
								<td><?= $item->updated_at ?></td>
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
	$("#form_dq_shipment_advise").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/custom_container_manage/dq_shipment_advise_upload", "Do you want to update container data?").done(function(res) {
			swal_open_tab(res.type, res.msg, "custom_container_manage/dq_shipment_advise_process");
		});
	});
	
	$("#form_receiving_confirm").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/custom_container_manage/receiving_confirm_upload", "Do you want to update container data?").done(function(res) {
			swal_open_tab(res.type, res.msg, "custom_container_manage/receiving_confirm_process");
		});
	});
	
	
	form_receiving_confirm
	
	$("#form_custom_container_dates").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/custom_container_manage/container_dates_upload", "Do you want to update container dates?").done(function(res) {
			swal_open_tab(res.type, res.msg, "custom_container_manage/container_dates_process");
		});
	});
	
	
});
</script>