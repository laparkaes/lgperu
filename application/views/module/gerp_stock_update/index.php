<section class="section">
	<div class="row">
		<div class="col-md-4">
			<div class="d-flex justify-content-between align-items-start">
				<div class="pagetitle">
					<h1>GERP - Stock Update</h1>
					<nav>
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
							<li class="breadcrumb-item active">GERP - Stock Update</li>
						</ol>
					</nav>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Stock Update</h5>
					<form class="row g-3" id="form_stock_update">
						<div class="col-md-12">
							<label class="form-label"><?= $updated->updated ?></label>
							<input class="form-control" type="file" name="attach">
						</div>
						<div class="text-center pt-3">
							<button type="submit" class="btn btn-primary">Upload</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<?php print_r($stocks); ?>
		</div>
	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_stock_update").submit(function(e) {
		e.preventDefault();
		$("#form_stock_update .sys_msg").html("");
		ajax_form_warning(this, "module/gerp_stock_update/update", "Do you want to update stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/gerp_stock_update");
		});
	});
});
</script>