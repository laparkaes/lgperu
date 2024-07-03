<section class="section">
	<div class="row">
		<div class="col-md-5 mx-auto">
			<div class="d-flex justify-content-between align-items-start">
				<div class="pagetitle">
					<h1>Promotion</h1>
					<nav>
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
							<li class="breadcrumb-item active">Promotion</li>
						</ol>
					</nav>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Promotion</h5>
					<form class="row g-3" id="form_promotion_calculation">
						<div class="col-md-6">
							<label class="form-label">Promotion</label>
							<input type="file" class="form-control" name="file_p">
						</div>
						<div class="col-md-6">
							<label class="form-label">GEPR</label>
							<input type="file" class="form-control" name="file_g">
						</div>
						<div class="col-md-12 pt-3 text-center">
							<button type="submit" class="btn btn-primary">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-5 mx-auto">
			<div class="row">
				<div class="col-md-6">
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">Sell-In</h5>
							<div class="row g-3">
								<div class="col-12">
									<div class="input-group">
										<input class="form-control" type="text" value="<?= $f_sellin ?>" readonly>
										<span class="input-group-text">~</span>
										<input class="form-control" type="text" value="<?= $l_sellin ?>" readonly>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="card">
						<div class="card-body">
							<h5 class="card-title">Sell-Out</h5>
							<div class="row g-3">
								<div class="col-12">
									<div class="input-group">
										<input class="form-control" type="text" value="<?= $f_sellout ?>" readonly>
										<span class="input-group-text">~</span>
										<input class="form-control" type="text" value="<?= $l_sellout ?>" readonly>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-5 mx-auto">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Sell-In/Out Upload</h5>
					<form class="row g-3" id="form_upload_sell_inout">
						<div class="col-12">
							<input type="file" class="form-control" name="md_uff_file" accept=".xls,.xlsx,.csv">
						</div>
						<div class="col-12">
							<span class="text-danger">Data processing can take several minutes.</span>
						</div>
						<div class="col-12 text-center pt-3">
							<button type="submit" class="btn btn-primary">Upload</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_promotion_calculation").submit(function(e) {
		e.preventDefault();
		$("#form_promotion_calculation .sys_msg").html("");
		ajax_form_warning(this, "module/promotion/calculation", "Do you want to generate promotion calculation report?").done(function(res) {
			swal_open_tab(res.type, res.msg, res.url);
		});
	});
	
	$("#form_upload_sell_inout").submit(function(e) {
		e.preventDefault();
		$("#form_upload_sell_inout .sys_msg").html("");
		ajax_form_warning(this, "module/sell_inout/upload_sell_inout_file", "Do you upload data?").done(function(res) {
			swal_open_tab(res.type, res.msg, res.url);
		});
	});
});
</script>