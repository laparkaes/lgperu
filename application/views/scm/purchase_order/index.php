<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Purchase Order</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard?m=scm">Supply Chain Management</a></li>
				<li class="breadcrumb-item active">Purchase Order</li>
			</ol>
		</nav>
	</div>
	<div></div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">PDF to Excel</h5>
					<form class="row g-3" id="form_convert_po">
						<div class="col-md-6 col-12">
							<label class="form-label">PDF Template</label>
							<select class="form-select" name="type">
								<option value="" selected="">Choose...</option>
								<?php foreach($purchase_order_pdfs as $p){ ?>
								<option value="<?= $p->pdf_id ?>"><?= $p->pdf ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-3 col-6">
							<label class="form-label">Customer</label>
							<select class="form-select" name="type">
								<option value="" selected="">Choose...</option>
								<?php foreach($customers as $c){ if($c->bill_to_code){ ?>
								<option value="<?= $c->customer_id ?>"><?= $c->bill_to_code ?> - <?= $c->customer ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-md-3 col-6">
							<label class="form-label">Ship to</label>
							<input type="text" class="form-control" name="Ship_to">
						</div>
						<div class="col-12 pt-3 text-center">
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
	$('#sl_group').change(function(){
		$("#sl_category").val("");
		$('#sl_category option.g_all').addClass('d-none');
		$('#sl_category option.g_' + $(this).val()).removeClass('d-none');
		
		$("#sl_product").val("");
		$('#sl_product option.c_all').addClass('d-none');
    });
	
	$('#sl_category').change(function(){
		$("#sl_product").val("");
		$('#sl_product option.c_all').addClass('d-none');
		$('#sl_product option.c_' + $(this).val()).removeClass('d-none');
    });
	
	
	$("#form_upload_sell_inout").submit(function(e) {
		e.preventDefault();
		$("#form_upload_sell_inout .sys_msg").html("");
		ajax_form_warning(this, "sa/sell_inout/upload_sell_inout_file", "Do you upload data?").done(function(res) {
			swal_open_tab(res.type, res.msg, res.url);
		});
	});
	
	$("#form_exp_report").submit(function(e) {
		e.preventDefault();
		$("#form_exp_report .sys_msg").html("");
		ajax_form_warning(this, "sa/sell_inout/exp_report", "Do you want to export sell-in/out report in excel?").done(function(res) {
			swal(res.type, res.msg);
			if (res.type == "success") window.location.href = res.url;
		});
	});
});
</script>