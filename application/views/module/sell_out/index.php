<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Sell Out</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">Sell Out</li>
			</ol>
		</nav>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Upload Sell-Out</h5>
					<form class="row g-3" id="form_upload_data">
						<div class="col-12">
							<input class="form-control" type="file" name="datafile">
						</div>
						<div class="col-12 text-center pt-3">
							<button type="submit" class="btn btn-primary">Submit</button>
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
		ajax_form_warning(this, "module/sell_out/upload_data", "Do you want to upload data file and upload sell-out records?").done(function(res) {
			swal(res.type, res.msg);
		});
	});
});
</script>