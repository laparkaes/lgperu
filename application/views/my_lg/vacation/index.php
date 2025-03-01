<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Vacation</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">My LG</li>
				<li class="breadcrumb-item active">Vacation</li>
			</ol>
		</nav>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">New</h5>
					<form class="row g-3">
						<div class="col-md-12">
							<label class="form-label">Type</label>
							<select class="form-select">
								<option selected="">Choose...</option>
								<option>...</option>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">From</label>
							<input type="date" class="form-control">
						</div>
						<div class="col-md-6">
							<label class="form-label">To</label>
							<input type="date" class="form-control">
						</div>
						<div class="col-md-12">
							<label class="form-label">1st Approver</label>
							<select class="form-select">
								<option selected="">Choose...</option>
								<option>...</option>
							</select>
						</div>
						<div class="col-md-12">
							<label class="form-label">2nd Approver</label>
							<select class="form-select">
								<option selected="">Choose...</option>
								<option>...</option>
							</select>
						</div>
						<div class="col-md-12">
							<label class="form-label">3rd Approver</label>
							<select class="form-select">
								<option selected="">Choose...</option>
								<option>...</option>
							</select>
						</div>
						<div class="text-center pt-3">
							<button type="submit" class="btn btn-primary">Request</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_sales_order_upload").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/lgepr_sales_order/upload", "Do you want to update sales order data?").done(function(res) {
			//swal_redirection(res.type, res.msg, "data_upload/lgepr_sales_order");
			swal_open_tab(res.type, res.msg, "lgepr_sales_order/process");
		});
	});
});
</script>