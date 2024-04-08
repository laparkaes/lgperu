<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Purchase Order</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item">SCM</li>
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
						<div class="col-md-3 col-12">
							<label class="form-label">PDF File</label>
							<input type="file" class="form-control" name="pdf_file">
						</div>
						<div class="col-md-3 col-12">
							<label class="form-label">PDF Template</label>
							<select class="form-select" name="po_file">
								<option value="" selected="">Choose...</option>
								<?php foreach($purchase_order_pdfs as $p){ ?>
								<option value="<?= $p->pdf_id ?>"><?= $p->pdf ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Ship to</label>
							<select class="form-select" id="sl_ship_to" name="ship_to">
								<option value="" selected="">Choose...</option>
								<?php foreach($ship_tos as $s){ if($s->ship_to_code){ ?>
								<option value="<?= $s->ship_to_id ?>" class="c_all c_<?= $s->customer_id ?>"><?= $s->op ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-md-12 flex-fill align-self-end">
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
	$("#form_convert_po").submit(function(e) {
		e.preventDefault();
		$("#form_convert_po .sys_msg").html("");
		ajax_form_warning(this, "scm/purchase_order/convert_po", "Do you want to convert PO to Excel?").done(function(res) {
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