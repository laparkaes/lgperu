<section class="section">
	<div class="row">
		<div class="col-md-5 mx-auto">
			<div class="d-flex justify-content-between align-items-start">
				<div class="pagetitle">
					<h1>OBS - Most Likely (ML)</h1>
					<nav>
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
							<li class="breadcrumb-item active">OBS - Most Likely (ML)</li>
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
		<div class="col-md-5 mx-auto">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">ML Datas</h5>
					<div class="row g-3">
						<div class="col-md-6">
							<label class="form-label">First Record</label>
							<input class="form-control" type="text" value="<?= $ml_first->year."-".$ml_first->month ?>" readonly>
						</div>
						<div class="col-md-6">
							<label class="form-label">Last Record</label>
							<input class="form-control" type="text" value="<?= $ml_last->year."-".$ml_last->month ?>" readonly>
						</div>
					</div>
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
		ajax_form_warning(this, "module/obs_most_likely/upload", "Do you upload data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/obs_most_likely");
		});
	});
});
</script>