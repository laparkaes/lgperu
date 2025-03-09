<div class="pagetitle">
	<h1>SCM Warehouse</h1>
	<nav>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
			<li class="breadcrumb-item active">SCM Warehouse</li>
		</ol>
	</nav>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-6">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Upload Datas</h5>
					<form class="row g-3" id="form_warehouse" action="scm_warehouse/upload" method="post" target="_blank" enctype="multipart/form-data">
						<div class="col-md-4">
							<label class="form-label">Type</label>
							<select class="form-select" name="type">
								<option value="receiving">Receiving Instructions</option>
								<option value="shipping">Picking Instructions</option>
								<option value="iod">IOD</option>
								<option value="sa_report">SA Report</option>
							</select>
						</div>
						<div class="col-md-8">
							<label class="form-label">File</label>
							<input class="form-control" type="file" name="attach">
						</div>
						<div class="text-center pt-3">
							<button type="submit" class="btn btn-primary">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Instructions</h5>
					<div>1. Receiving Instructions: I/F sent from LG to 3PL to receive products in warehouse.</div>
					<div>2. Shipping Instructions: I/F sent from LG to 3PL to take out products from current warehouse.</div>
					<div>3. IOD: I/F received from 3PL about sales process finish confirmation.</div>
					<div>4. SA Report: Upload container information. This will assign ETA to products.</div>
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
		ajax_form_warning(this, "data_upload/obs_gerp/upload", "Do you upload data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/obs_gerp");
		});
	});
});
</script>