<section class="section">
	<div class="row">
		<div class="col-md-5 mx-auto">
			<div class="d-flex justify-content-between align-items-start">
				<div class="pagetitle">
					<h1>OBS Most Likely (ML)</h1>
					<nav>
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
							<li class="breadcrumb-item active">OBS Most Likely (ML)</li>
						</ol>
					</nav>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">File Selection</h5>
					<form class="row g-3" id="form_upload_ml">
						<div class="col-md-12">
							<label class="form-label">ML File</label>
							<input class="form-control" type="file" name="attach">
						</div>
						<div class="text-center pt-3">
							<button type="submit" class="btn btn-primary">Upload</button>
							<button type="reset" class="btn btn-secondary">Reset</button>
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
					<h5 class="card-title">Last Record: <?= $month ?></h5>
					<table class="table">
						<thead>
							<tr>
								<th scope="col"></th>
								<th scope="col" class="text-center">BP</th>
								<th scope="col" class="text-center">Target</th>
								<th scope="col" class="text-center">Monthly Report</th>
								<th scope="col" class="text-center">ML</th>
								<th scope="col" class="text-center">ML Actual</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($rows as $item){ $desc = explode("_", $item["desc"]); ?>
							<tr class="table-<?= count($desc) == 1 ? "dark" : (count($desc) == 2 ? "success" : "") ?>">
								<td><div class="ms-<?= 2 * (count($desc)-1) ?>"><?= $desc[count($desc)-1] ?></div></td>
								<td class="text-end"><?= number_format($item["bp"], 2) ?></td>
								<td class="text-end"><?= number_format($item["target"], 2) ?></td>
								<td class="text-end"><?= number_format($item["monthly_report"], 2) ?></td>
								<td class="text-end"><?= number_format($item["ml"], 2) ?></td>
								<td class="text-end"><?= number_format($item["ml_actual"], 2) ?></td>
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
	$("#form_upload_ml").submit(function(e) {
		e.preventDefault();
		$("#form_upload_ml .sys_msg").html("");
		ajax_form_warning(this, "data_upload/obs_most_likely/upload", "Do you upload data?").done(function(res) {
			//swal_redirection(res.type, res.msg, "data_upload/obs_most_likely");
			swal_open_tab(res.type, res.msg, "obs_most_likely/process");
		});
	});
});
</script>