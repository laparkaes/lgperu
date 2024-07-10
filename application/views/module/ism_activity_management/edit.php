<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Edit Product</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item">SOM</li>
				<li class="breadcrumb-item"><a href="<?= base_url() ?>module/product">Product</a></li>
				<li class="breadcrumb-item active">Edit</li>
			</ol>
		</nav>
	</div>
	<div>
		<a type="button" class="btn btn-success" href="<?= base_url() ?>module/product">
			<i class="bi bi-arrow-left"></i>
		</a>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Edit Product</h5>
					<form class="row g-3" id="form_update_product">
						<div class="col-md-3">
							<label class="form-label">Category</label>
							<select class="form-select" name="category_id">
								<option value ="" selected="">Choose...</option>
								<?php foreach($categories as $c){ if ($c->category){ ?>
								<option value="<?= $c->category_id ?>"><?= $c->category ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-md-3">
							<label class="form-label">Division</label>
							<select class="form-select" id="sl_lvl0">
								<option value ="" selected="">Choose...</option>
								<?php foreach($lines as $l){ if ($l->level == 0){ ?>
								<option value="<?= $l->line_id ?>"><?= $l->line ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Model</label>
							<input class="form-control" name="model">
						</div>
						<div class="col-md-3">
							<label class="form-label">Product Level 1</label>
							<select class="form-select" id="sl_lvl1">
								<option value ="" selected="">Choose...</option>
								<?php foreach($lines as $l){ if ($l->level == 1){ ?>
								<option class="d-none p_<?= $l->parent_id ?>" value="<?= $l->line_id ?>"><?= $l->line ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-md-3">
							<label class="form-label">Product Level 2</label>
							<select class="form-select" id="sl_lvl2">
								<option value ="" selected="">Choose...</option>
								<?php foreach($lines as $l){ if ($l->level == 2){ ?>
								<option class="d-none p_<?= $l->parent_id ?>" value="<?= $l->line_id ?>"><?= $l->line ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-md-3">
							<label class="form-label">Product Level 3</label>
							<select class="form-select" id="sl_lvl3">
								<option value ="" selected="">Choose...</option>
								<?php foreach($lines as $l){ if ($l->level == 3){ ?>
								<option class="d-none p_<?= $l->parent_id ?>" value="<?= $l->line_id ?>"><?= $l->line ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-md-3">
							<label class="form-label">Product Level 4</label>
							<select class="form-select" id="sl_lvl4" name="line_id">
								<option value="" selected="">Choose...</option>
								<?php foreach($lines as $l){ if ($l->level == 4){ ?>
								<option class="d-none p_<?= $l->parent_id ?>" value="<?= $l->line_id ?>"><?= $l->line ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="text-center pt-3">
							<button type="submit" class="btn btn-primary">Submit</button>
							<button type="reset" class="btn btn-secondary">Reset</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {
	$('#sl_lvl0').change(function(){
		$("#sl_lvl1").val(""); $('#sl_lvl1 option').addClass('d-none'); $('#sl_lvl1 option.p_' + $(this).val()).removeClass('d-none');
		$("#sl_lvl2").val(""); $('#sl_lvl2 option').addClass('d-none');
		$("#sl_lvl3").val(""); $('#sl_lvl3 option').addClass('d-none');
		$("#sl_lvl4").val(""); $('#sl_lvl4 option').addClass('d-none');
    });
	
	$('#sl_lvl1').change(function(){
		$("#sl_lvl2").val(""); $('#sl_lvl2 option').addClass('d-none'); $('#sl_lvl2 option.p_' + $(this).val()).removeClass('d-none');
		$("#sl_lvl3").val(""); $('#sl_lvl3 option').addClass('d-none');
		$("#sl_lvl4").val(""); $('#sl_lvl4 option').addClass('d-none');
    });
	
	$('#sl_lvl2').change(function(){
		$("#sl_lvl3").val(""); $('#sl_lvl3 option').addClass('d-none'); $('#sl_lvl3 option.p_' + $(this).val()).removeClass('d-none');
		$("#sl_lvl4").val(""); $('#sl_lvl4 option').addClass('d-none');
    });
	
	$('#sl_lvl3').change(function(){
		$("#sl_lvl4").val(""); $('#sl_lvl4 option').addClass('d-none'); $('#sl_lvl4 option.p_' + $(this).val()).removeClass('d-none');
    });
});
</script>