<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Container Location</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item">SCM</li>
				<li class="breadcrumb-item active">Container Location</li>
			</ol>
		</nav>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Container Location</h5>
					<form id="form_scm_container_location">
						<div class="input-group">
							<input class="form-control" type="file" name="attach">
							<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
						</div>
					</form>
					<div class="mt-3">
						<a href="<?= base_url() ?>template/scm_container_location.xlsx" download="scm_container_location_template">
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
						<h5 class="card-title">Container list</h5>
						<div class="d-flex justify-content-end align-items-center">
							<a type="button" class="btn btn-success me-3" href="<?= base_url() ?>data_upload/scm_container_location/aging_summary?eta_from=<?= $eta_from ?>&eta_to=<?= $eta_to ?>" target="_blank">Aging</a>
							<form class="input-group">
								<span class="input-group-text">ETA</span>
								<input type="date" class="form-control" placeholder="From" name="eta_from" value="<?= $eta_from ?>">
								<span class="input-group-text">~</span>
								<input type="date" class="form-control" placeholder="To" name="eta_to" value="<?= $eta_to ?>">
								<button type="submit" class="btn btn-primary">Apply</button>
							</form>
						</div>
					</div>
					<table class="table">
						<thead class="sticky-top" style="top:60px;">
							<tr>
								<th scope="col">SA #</th>
								<th scope="col">Line</th>
								<th scope="col">Status</th>
								<th scope="col">House BL</th>
								<th scope="col">Carrier</th>
								<th scope="col">Container</th>
								<th scope="col">Org.</th>
								<th scope="col">Company</th>
								<th scope="col">Division</th>
								<th scope="col">Model</th>
								<th scope="col">Qty</th>
								<th scope="col">ETA</th>
								<th scope="col">ATA</th>
								<th scope="col">Picked Up</th>
								<th scope="col">Warehouse</th>
								<th scope="col">Returned</th>
								<th scope="col">Return Due</th>
								<th scope="col">Dem. days</th>
								<th scope="col">Det. days</th>
								<th scope="col">No Data</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($containers as $item){ ?>
							<tr>
								<td><?= $item->sa_no ?></td>
								<td><?= $item->sa_line_no ?></td>
								<td><?= $item->is_received == 1 ? "Received" : "Intransit" ?></td>
								<td><?= $item->house_bl ?></td>
								<td><?= $item->carrier_line ?></td>
								<td><?= $item->container ?></td>
								<td><?= $item->organization ?></td>
								<td><?= $item->company ?></td>
								<td><?= $item->division ?></td>
								<td><?= $item->model ?></td>
								<td><?= number_format($item->qty) ?></td>
								<td><?= $item->eta ?></td>
								<td><?= $item->ata ?></td>
								<td><?= $item->picked_up ?></td>
								<td><?= $item->wh_arrival ?></td>
								<td><?= $item->return_due ?></td>
								<td><?= $item->returned ?></td>
								<td><?= $item->dem_days ? $item->dem_days : ""; ?></td>
								<td><?= $item->det_days ? $item->det_days : ""; ?></td>
								<td><?= $item->no_data ? "<span class='text-danger'>●</span>" : ""; ?></td>
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
	$("#form_scm_container_location").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/scm_container_location/container_location_upload", "Do you want to update container location?").done(function(res) {
			swal_open_tab(res.type, res.msg, "scm_container_location/container_location_process");
		});
	});
	
	
});
</script>