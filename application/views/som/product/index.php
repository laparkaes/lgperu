<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Product</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item">SOM</li>
				<li class="breadcrumb-item active">Product</li>
			</ol>
		</nav>
	</div>
	<div>
		<button type="button" class="btn btn-success">
			<i class="bi bi-plus-lg"></i>
		</button>
		<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#md_uff">
			<i class="bi bi-upload"></i>
		</button>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">New Product</h5>
					<form class="row g-3" id="form_new_product">
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
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Product List</h5>
					<div class="table-responsive">
						<table class="table datatable align-middle">
							<thead>
								<tr>
									<th scope="col">Category</th>
									<th scope="col">Division</th>
									<th scope="col">Level 1</th>
									<th scope="col">Level 2</th>
									<th scope="col">Level 3</th>
									<th scope="col">Level 4</th>
									<th scope="col">Model</th>
									<th scope="col">Updated</th>
									<th scope="col">Created</th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>
								<?php 
								foreach($products as $prod){
									if ($prod->line_id){
										$lvl4 = $prod->line_id > 0 ? $lines_arr[$prod->line_id] : null;
										$lvl3 = $lvl4 ? $lines_arr[$lvl4->parent_id] : null;
										$lvl2 = $lvl3 ? $lines_arr[$lvl3->parent_id] : null;
										$lvl1 = $lvl2 ? $lines_arr[$lvl2->parent_id] : null;
										$lvl0 = $lvl1 ? $lines_arr[$lvl1->parent_id] : null;
										
										$prod->lvl0 = $lvl0 ? $lvl0->line : "";
										$prod->lvl1 = $lvl1 ? $lvl1->line : "";
										$prod->lvl2 = $lvl2 ? $lvl2->line : "";
										$prod->lvl3 = $lvl3 ? $lvl3->line : "";
										$prod->lvl4 = $lvl4 ? $lvl4->line : "";
									}else $prod->lvl0 = $prod->lvl1 = $prod->lvl2 = $prod->lvl3 = $prod->lvl4 = "";
								?>
								<tr>
									<td><?= $prod->category_id ? $categories_arr[$prod->category_id]->category : "" ?></td>
									<td><?= $prod->lvl0 ?></td>
									<td><?= $prod->lvl1 ?></td>
									<td><?= $prod->lvl2 ?></td>
									<td><?= $prod->lvl3 ?></td>
									<td><?= $prod->lvl4 ?></td>
									<td><?= $prod->model; ?></td>
									<td><?= $prod->updated ?></td>
									<td><?= $prod->registered ?></td>
									<td>
										<button type="button" class="btn btn-primary btn-sm">
											<i class="bi bi-pencil"></i>
										</button>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
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