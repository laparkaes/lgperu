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
	<div>
		<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#md_exr">
			<i class="bi bi-file-earmark-spreadsheet"></i>
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
					<h5 class="card-title">Promotions</h5>
					<form class="row g-3">
						<div class="col-md-3">
							<label class="form-label">Division</label>
							<select class="form-select" id="sl_lz" name="lz">
								<option value="" selected="">Choose...</option>
								<?php foreach($lvl_z as $l){ $s = ($lz == $l->line_id) ? "selected" : ""; ?>
								<option value="<?= $l->line_id ?>" <?= $s ?>><?= $l->line ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-3">
							<label class="form-label">Line 1</label>
							<select class="form-select" id="sl_li" name="li">
								<option value="" selected="">Choose...</option>
								<?php foreach($lvl_i as $l){ $d = ($lz == $l->parent_id) ? "" : "d-none"; $s = ($li == $l->line_id) ? "selected" : ""; ?>
								<option class="sl_li sl_lz_<?= $l->parent_id ?> <?= $d ?>" value="<?= $l->line_id ?>" <?= $s ?>><?= $l->line ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-2">
							<label class="form-label">Line 2</label>
							<select class="form-select" id="sl_lii" name="lii">
								<option value="" selected="">Choose...</option>
								<?php foreach($lvl_ii as $l){ $d = ($li == $l->parent_id) ? "" : "d-none"; $s = ($lii == $l->line_id) ? "selected" : ""; ?>
								<option class="sl_lii sl_li_<?= $l->parent_id ?> <?= $d ?>" value="<?= $l->line_id ?>" <?= ($lii == $l->line_id) ? "selected" : "" ?>><?= $l->line ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-2">
							<label class="form-label">Line 3</label>
							<select class="form-select" id="sl_liii" name="liii">
								<option value="" selected="">Choose...</option>
								<?php foreach($lvl_iii as $l){ $d = ($lii == $l->parent_id) ? "" : "d-none"; $s = ($liii == $l->line_id) ? "selected" : ""; ?>
								<option class="sl_liii sl_lii_<?= $l->parent_id ?> <?= $d ?>" value="<?= $l->line_id ?>" <?= ($liii == $l->line_id) ? "selected" : "" ?>><?= $l->line ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-2">
							<label class="form-label">Line 4</label>
							<select class="form-select" id="sl_liv" name="liv">
								<option value="" selected="">Choose...</option>
								<?php foreach($lvl_iv as $l){ $d = ($liii == $l->parent_id) ? "" : "d-none"; $s = ($liv == $l->line_id) ? "selected" : ""; ?>
								<option class="sl_liv sl_liii_<?= $l->parent_id ?> <?= $d ?>" value="<?= $l->line_id ?>" <?= ($liv == $l->line_id) ? "selected" : "" ?>><?= $l->line ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Product</label>
							<select class="form-select" id="sl_prd" name="prd">
								<option value="" selected="">Choose...</option>
								<?php foreach($products as $p){ if ($p->line_id){
									if ($liv) $d = $liv == $p->lvl_iv_id ? "" : "d-none";
									elseif ($liii) $d = $liii == $p->lvl_iii_id ? "" : "d-none";
									elseif ($lii) $d = $lii == $p->lvl_ii_id ? "" : "d-none";
									elseif ($li) $d = $li == $p->lvl_i_id ? "" : "d-none";
									elseif ($lz) $d = $lz == $p->lvl_z_id ? "" : "d-none";
									else $d = "d-none";
									?>
								<option class="sl_prd prl_<?= $p->lvl_z_id ?> prl_<?= $p->lvl_i_id ?> prl_<?= $p->lvl_ii_id ?> prl_<?= $p->lvl_iii_id ?> prl_<?= $p->lvl_iv_id ?> <?= $d ?>" <?= ($prd == $p->product_id) ? "selected" : "" ?> value="<?= $p->product_id ?>"><?= $p->model ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-md-6 flex-fill align-self-end">
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
function load_promotion_info(data){
	ajax_simple(data, "module/promotion/load_promotion_info").done(function(res) {
		//swal_open_tab(res.type, res.msg, res.url);
		console.log(res);
	});
	
}

document.addEventListener("DOMContentLoaded", () => {
	$('#btn_search_customer').click(function(){
		ajax_simple({"customer": $('#inp_customer').val()}, "module/promotion/search_customer").done(function(res) {
			console.log(res);
		});
    });
	
	$('#btn_search_product').click(function(){
		ajax_simple({"model": $('#inp_model').val()}, "module/promotion/search_product").done(function(res) {
			console.log(res);
		});
    });
	
	
	
	
	
	
	
	
	
	
	
	if ($(".bl_move").length > 0){
		var height_n = Math.max($(".bl_move")[0].clientHeight, $(".bl_move")[1].clientHeight, $(".bl_move")[2].clientHeight);
		$(".bl_move").height(height_n);
	}
	
	$('#sl_lz').change(function(){
		$("#sl_li").val(""); $('#sl_li option.sl_li').addClass('d-none'); $('#sl_li option.sl_lz_' + $(this).val()).removeClass('d-none');
		$("#sl_lii").val(""); $('#sl_lii option.sl_lii').addClass('d-none');
		$("#sl_liii").val(""); $('#sl_liii option.sl_liii').addClass('d-none');
		$("#sl_liv").val(""); $('#sl_liv option.sl_liv').addClass('d-none');
		$("#sl_prd").val(""); $('#sl_prd option.sl_prd').addClass('d-none'); $('#sl_prd option.prl_' + $(this).val()).removeClass('d-none');
    });
	
	$('#sl_li').change(function(){
		$("#sl_lii").val(""); $('#sl_lii option.sl_lii').addClass('d-none'); $('#sl_lii option.sl_li_' + $(this).val()).removeClass('d-none');
		$("#sl_liii").val(""); $('#sl_liii option.sl_liii').addClass('d-none');
		$("#sl_liv").val(""); $('#sl_liv option.sl_liv').addClass('d-none');
		$("#sl_prd").val(""); $('#sl_prd option.sl_prd').addClass('d-none'); $('#sl_prd option.prl_' + $(this).val()).removeClass('d-none');
    });
	
	$('#sl_lii').change(function(){
		$("#sl_liii").val(""); $('#sl_liii option.sl_liii').addClass('d-none'); $('#sl_liii option.sl_lii_' + $(this).val()).removeClass('d-none');
		$("#sl_liv").val(""); $('#sl_liv option.sl_liv').addClass('d-none');
		$("#sl_prd").val(""); $('#sl_prd option.sl_prd').addClass('d-none'); $('#sl_prd option.prl_' + $(this).val()).removeClass('d-none');
    });
	
	$('#sl_liii').change(function(){
		$("#sl_liv").val(""); $('#sl_liv option.sl_liv').addClass('d-none'); $('#sl_liv option.sl_liii_' + $(this).val()).removeClass('d-none');
		$("#sl_prd").val(""); $('#sl_prd option.sl_prd').addClass('d-none'); $('#sl_prd option.prl_' + $(this).val()).removeClass('d-none');
    });
	
	$('#sl_liv').change(function(){
		$("#sl_prd").val(""); $('#sl_prd option.sl_prd').addClass('d-none'); $('#sl_prd option.prl_' + $(this).val()).removeClass('d-none');
    });
	
	$('#sl_lz_report').change(function(){
		$("#sl_li_report").val(""); $('#sl_li_report option.sl_li').addClass('d-none'); $('#sl_li_report option.sl_lz_' + $(this).val()).removeClass('d-none');
    });
	
	$('.ctrl_inv').click(function(){
		var ln_i = $(this).attr("id").replace("ctrl_", "");
		
		$(".ln_inv").addClass("d-none");
		if ($(this).hasClass("bi-caret-down-square")){//open list
			$(".ctrl_inv").removeClass("bi-caret-up-square");
			$(".ctrl_inv").addClass("bi-caret-down-square");
		
			$(".ln_inv_" + ln_i).removeClass("d-none");
			$(this).removeClass("bi-caret-down-square");
			$(this).addClass("bi-caret-up-square");
		}else{//close list
			$(this).removeClass("bi-caret-up-square");
			$(this).addClass("bi-caret-down-square");
		}
    });
	
	
	$("#form_upload_sell_inout").submit(function(e) {
		e.preventDefault();
		$("#form_upload_sell_inout .sys_msg").html("");
		ajax_form_warning(this, "module/sell_inout/upload_sell_inout_file", "Do you upload data?").done(function(res) {
			swal_open_tab(res.type, res.msg, res.url);
		});
	});
	
	$("#form_exp_report").submit(function(e) {
		e.preventDefault();
		$("#form_exp_report .sys_msg").html("");
		ajax_form_warning(this, "module/sell_inout/exp_report", "Do you want to export sell-in/out report in excel?").done(function(res) {
			if (res.type == "success") swal_open_tab(res.type, res.msg, res.url);
			else swal(res.type, res.msg);
		});
	});
});
</script>