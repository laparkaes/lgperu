<section class="section">
	<div class="row">
		<div class="col-md-4 mx-auto">
			<div class="d-flex justify-content-between align-items-start">
				<div class="pagetitle">
					<h1>Dashboard - Order Inquiry</h1>
					<nav>
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
							<li class="breadcrumb-item active">Dashboard - Order Inquiry</li>
						</ol>
					</nav>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Order Inquiry Upload</h5>
					<form class="row g-3" id="form_upload_order_inquiry">
						<div class="col-md-12">
							<label class="form-label">Closed or Sales Order</label>
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
			<div class="row">
				<div class="col-md-6">
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">Sales Order Inquiry</h5>
							<div class="row g-3">
								<div class="col-12">
									<label class="form-label">Last update</label>
									<input class="form-control" type="text" value="<?= $sales_updated->updated ?>" readonly>
								</div>
								<div class="col-12">
									<label class="form-label">Last Sales Order Date</label>
									<input class="form-control" type="text" value="<?= $sales_last->order_date ?>" readonly>
								</div>
								<div class="col-12">
									<label class="form-label">First Sales Order Date</label>
									<input class="form-control" type="text" value="<?= $sales_first->order_date ?>" readonly>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">Closed Order Inquiry</h5>
							<div class="row g-3">
								<div class="col-12">
									<label class="form-label">Last update</label>
									<input class="form-control" type="text" value="<?= $closed_updated->updated ?>" readonly>
								</div>
								<div class="col-12">
									<label class="form-label">Last Closed Order Date</label>
									<input class="form-control" type="text" value="<?= $closed_last->closed_date ?>" readonly>
								</div>
								<div class="col-12">
									<label class="form-label">First Closed Order Date</label>
									<input class="form-control" type="text" value="<?= $closed_first->closed_date ?>" readonly>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload_order_inquiry").submit(function(e) {
		e.preventDefault();
		$("#form_upload_order_inquiry .sys_msg").html("");
		ajax_form_warning(this, "module/dash_order_inquiry/upload", "Do you upload data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/dash_order_inquiry");
		});
	});
});
</script>