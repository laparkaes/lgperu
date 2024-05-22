<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Invoice</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item">Tax</li>
				<li class="breadcrumb-item active">Invoice</li>
			</ol>
		</nav>
	</div>
	<div></div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Invoice Report</h5>
					<form class="row g-3" id="form_comparison_report">
						<div class="col-md-6 col-12">
							<label class="form-label">Paperless</label>
							<input type="file" class="form-control" name="file_p">
						</div>
						<div class="col-md-6 col-12">
							<label class="form-label">GEPR</label>
							<input type="file" class="form-control" name="file_g">
						</div>
						<div class="col-md-12 pt-3 flex-fill align-self-end">
							<div class="d-grid gap-2">
								<button type="submit" class="btn btn-primary">Submit</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Bad Invoices</h5>
					<table class="table">
						<thead>
							<tr>
								<th scope="col">Name</th>
								<th scope="col">Position</th>
								<th scope="col">Age</th>
								<th scope="col">Start Date</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Brandon Jacob</td>
								<td>Designer</td>
								<td>28</td>
								<td>2016-05-25</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_comparison_report").submit(function(e) {
		e.preventDefault();
		$("#form_comparison_report .sys_msg").html("");
		ajax_form_warning(this, "tax/invoice/comparison_report", "Do you want to generate invoice comparison report?").done(function(res) {
			swal_open_tab(res.type, res.msg, res.url);
			if (res.type == "success"){
				console.log(res.bads);
			}
		});
	});
});
</script>