<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>ESPR File</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">ESPR File</li>
			</ol>
		</nav>
	</div>
	<div></div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-5 mx-auto">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Order Inquiry Files</h5>
					<form class="row g-3" id="form_merge_order_inquiry">
						<div class="col-12">
							<label class="form-label">COI</label>
							<input type="file" class="form-control" name="file_coi">
						</div>
						<div class="col-12">
							<label class="form-label">SOI 1</label>
							<input type="file" class="form-control" name="file_soi1">
						</div>
						<div class="col-12">
							<label class="form-label">SOI 2</label>
							<input type="file" class="form-control" name="file_soi2">
						</div>
						<div class="col-md-12 flex-fill align-self-end pt-3">
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
	$("#form_merge_order_inquiry").submit(function(e) {
		e.preventDefault();
		$("#form_merge_order_inquiry .sys_msg").html("");
		ajax_form_warning(this, "module/espr_file/merge_order_inquiry", "Do you want to generate ESPR template file?").done(function(res) {
			swal_open_tab(res.type, res.msg, res.url);
		});
	});
	
	/*
	$('#sl_group').change(function(){
		$("#sl_category").val("");
		$('#sl_category option.g_all').addClass('d-none');
		$('#sl_category option.g_' + $(this).val()).removeClass('d-none');
		
		$("#sl_product").val("");
		$('#sl_product option.c_all').addClass('d-none');
    });
	
	
	
	$("#form_exp_report").submit(function(e) {
		e.preventDefault();
		$("#form_exp_report .sys_msg").html("");
		ajax_form_warning(this, "sa/sell_inout/exp_report", "Do you want to export sell-in/out report in excel?").done(function(res) {
			swal(res.type, res.msg);
			if (res.type == "success") window.location.href = res.url;
		});
	});
	*/
});
</script>