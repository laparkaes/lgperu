<div class="pagetitle">
	<h1>Access Record</h1>
	<nav>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
			<li class="breadcrumb-item active">Access Record</li>
		</ol>
	</nav>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-5 mx-auto">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Access Records</h5>
					<form class="input-group" id="form_upload_access">
						<input type="file" class="form-control" name="attach" accept=".xls,.xlsx,.csv">
						<button type="submit" class="btn btn-success">
							<i class="bi bi-upload"></i> Upload
						</button>
					</form>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Employee's Schedules</h5>
					<form class="input-group" id="form_upload_schedule">
						<input type="file" class="form-control" name="attach" accept=".xls,.xlsx,.csv">
						<button type="submit" class="btn btn-success">
							<i class="bi bi-upload"></i> Upload
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {
	
	$("#form_upload_access").submit(function(e) {
		e.preventDefault();
		ajax_form(this, "module/hr_access_record/upload_access").done(function(res) {
			swal_redirection(res.type, res.msg, "module/hr_access_record");
		});
	});
	
	$("#form_upload_schedule").submit(function(e) {
		e.preventDefault();
		ajax_form(this, "module/hr_access_record/upload_schedule").done(function(res) {
			swal_redirection(res.type, res.msg, "module/hr_access_record");
		});
	});
	
});
</script>