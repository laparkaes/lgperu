<section class="section">
	<div class="row">
		<div class="col-md-5 mx-auto">
			<div class="d-flex justify-content-between align-items-start">
				<div class="pagetitle">
					<h1>Purchase Order</h1>
					<nav>
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
							<li class="breadcrumb-item active">Purchase Order</li>
						</ol>
					</nav>
				</div>
			</div>
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
							<select class="form-select" name="po_template">
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
	$("#form_convert_po").submit(function(e) {
		e.preventDefault();
		$("#form_convert_po .sys_msg").html("");
		ajax_form_warning(this, "module/scm_purchase_order/convert_po", "Do you want to convert PO to Excel?").done(function(res) {
			swal_open_tab(res.type, res.msg, res.url);
		});
	});
});
</script>