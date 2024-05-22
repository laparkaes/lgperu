<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Sell Out Upload</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item">SOM</li>
				<li class="breadcrumb-item active">Sell Out Upload</li>
			</ol>
		</nav>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Data</h5>
					<form class="row g-3" id="form_upload_data">
						<div class="col-md-10">
							<label class="form-label">File</label>
							<input class="form-control" type="file" name="datafile">
						</div>
						<div class="col-md-2 flex-fill align-self-end">
							<div class="d-grid gap-2">
								<button type="submit" class="btn btn-primary">Submit</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload_data").submit(function(e) {
		e.preventDefault();
		$("#form_upload_data .sys_msg").html("");
		ajax_form_warning(this, "som/sell_out_upload/upload_data", "Do you want to upload data file and upload sell-out records?").done(function(res) {
			swal(res.type, res.msg);
		});
	});
});
</script>