<section class="section">
	<div class="row">
		<div class="col-md-4 mx-auto">
			<div class="d-flex justify-content-between align-items-start">
				<div class="pagetitle">
					<h1>SCM - SKU Management</h1>
					<nav>
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
							<li class="breadcrumb-item active">SCM - SKU Management</li>
						</ol>
					</nav>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">SKU File Upload</h5>
					<form class="row g-3" id="form_upload">
						<div class="col-md-12">
							<label class="form-label">Template</label>
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
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload").submit(function(e) {
		e.preventDefault();
		$("#form_upload .sys_msg").html("");
		ajax_form_warning(this, "module/scm_sku_management/upload", "Do you upload data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/scm_sku_management");
		});
	});
});
</script>