<section class="section">
	<div class="row">
		<div class="col-md-4 mx-auto">
			<div class="d-flex justify-content-between align-items-start">
				<div class="pagetitle">
					<h1>OBS - GERP Sale Order</h1>
					<nav>
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
							<li class="breadcrumb-item active">OBS - GERP Sale Order</li>
						</ol>
					</nav>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Backdata</h5>
					<form class="row g-3" id="form_upload_gerp">
						<div class="col-md-12">
							<label class="form-label">GERP Sale Order</label>
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
		<div class="col-md-4 mx-auto">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Sale Order Datas</h5>
					<div class="row g-3">
						<div class="col-md-6">
							<label class="form-label">First Record</label>
							<input class="form-control" type="text" value="<?= $sales_first->booked_date ?>" readonly>
						</div>
						<div class="col-md-6">
							<label class="form-label">Last Record</label>
							<input class="form-control" type="text" value="<?= $sales_last->booked_date ?>" readonly>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload_gerp").submit(function(e) {
		e.preventDefault();
		$("#form_upload_gerp .sys_msg").html("");
		ajax_form_warning(this, "module/obs_gerp/upload", "Do you upload data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/obs_gerp");
		});
	});
});
</script>