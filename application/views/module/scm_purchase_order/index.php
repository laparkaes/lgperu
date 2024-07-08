<section class="section">
	<div class="pagetitle">
		<h1>Purchase Order</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">Purchase Order</li>
			</ol>
		</nav>
	</div>
	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">PO to Excel</h5>
					<form class="row g-3" id="form_convert_po">
						<div class="col-md-6 col-12">
							<label class="form-label">PO File</label>
							<input type="file" class="form-control" name="po_file">
						</div>
						<div class="col-md-6 col-12">
							<label class="form-label">Template</label>
							<select class="form-select" name="po_template" id="sl_po_template">
								<option value="" selected="">Choose...</option>
								<?php foreach($po_templates as $item){ ?>
								<option value="<?= $item->template_id ?>"><?= $item->template ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-12">
							<label class="form-label">Ship to</label>
							<select class="form-select" id="sl_ship_to" name="ship_to">
								<option value="" selected="">Choose...</option>
								<?php foreach($ship_tos as $item){ ?>
								<option value="<?= $item->ship_to_id ?>">[<?= $item->bill_to_code ?>] <?= $item->bill_to_name ?> / [<?= $item->ship_to_code ?>] <?= $item->address ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-12 pt-3">
							<button type="submit" class="btn btn-primary">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">SKU Registration</h5>
					<form class="row g-3" id="form_add_sku">
						<div class="col-12">
							<label class="form-label">Customer</label>
							<select class="form-select" name="bill_to_code">
								<option value="" selected="">Choose...</option>
								<?php $printed = []; foreach($ship_tos as $item){ if (!in_array($item->bill_to_code, $printed)){ ?>
								<option value="<?= $item->bill_to_code ?>">[<?= $item->bill_to_code ?>] <?= $item->bill_to_name ?></option>
								<?php $printed[] = $item->bill_to_code;}} ?>
							</select>
						</div>
						<div class="col-md-6 col-12">
							<label class="form-label">SKU LG</label>
							<input type="text" class="form-control" name="sku">
						</div>
						<div class="col-md-6 col-12">
							<label class="form-label">SKU Customer</label>
							<input type="text" class="form-control" name="sku_customer">
						</div>
						<div class="col-md-12 pt-3">
							<button type="submit" class="btn btn-primary">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Ship to Registration</h5>
					<form class="row g-3" id="form_add_ship_to">
						<div class="col-6">
							<label class="form-label">Customer</label>
							<select class="form-select" name="bill_to_code">
								<option value="" selected="">Choose...</option>
								<?php $printed = []; foreach($ship_tos as $item){ if (!in_array($item->bill_to_code, $printed)){ ?>
								<option value="<?= $item->bill_to_code ?>">[<?= $item->bill_to_code ?>] <?= $item->bill_to_name ?></option>
								<?php $printed[] = $item->bill_to_code;}} ?>
							</select>
						</div>
						<div class="col-md-6 col-12">
							<label class="form-label">Ship To</label>
							<input type="text" class="form-control" name="ship_to_code">
						</div>
						<div class="col-md-12 col-12">
							<label class="form-label">Address</label>
							<input type="text" class="form-control" name="address">
						</div>
						<div class="col-md-12 pt-3">
							<button type="submit" class="btn btn-primary">Submit</button>
							
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-12 text-end">
			<span class="text-danger">Contact with PI if you want to remove wrong data.</span>
		</div>
	</div>
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#sl_po_template").on("change", function() {
		if ($(this).val() != "") alert($(this).val());
	});
	
	$("#form_convert_po").submit(function(e) {
		e.preventDefault();
		$("#form_convert_po .sys_msg").html("");
		ajax_form_warning(this, "module/scm_purchase_order/convert_po", "Do you want to convert PO to Excel?").done(function(res) {
			swal_open_tab(res.type, res.msg, res.url);
		});
	});
	
	$("#form_add_sku").submit(function(e) {
		e.preventDefault();
		$("#form_add_sku .sys_msg").html("");
		ajax_form_warning(this, "module/scm_purchase_order/add_sku", "Do you add new sku?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/scm_purchase_order");
		});
	});
	
	$("#form_add_ship_to").submit(function(e) {
		e.preventDefault();
		$("#form_add_ship_to .sys_msg").html("");
		ajax_form_warning(this, "module/scm_purchase_order/add_ship_to", "Do you add new ship to?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/scm_purchase_order");
		});
	});
});
</script>