<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>SA - Sell-In/Out</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">SA - Sell-In/Out</li>
			</ol>
		</nav>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Sell-in/out Summary</h5>
					<form class="row g-3">
						<div class="col-md-12">
							<label class="form-label">Customer</label>
							<select class="form-select" name="cus">
								<option value="" selected="">Choose...</option>
								<?php foreach($customers as $c){ if($c->bill_to_code){ ?>
								<option <?= ($this->input->get("cus") == $c->bill_to_code) ? "selected" : "" ?> value="<?= $c->bill_to_code ?>">[<?= $c->bill_to_code ?>] <?= $c->bill_to_name ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-12 text-center pt-3">
							<button type="submit" class="btn btn-primary">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Export</h5>
					<form class="row g-3" id="form_exp_report">
						<div class="col-12">
							<label class="form-label">Customer</label>
							<select class="form-select" name="cus">
								<option value="" selected="">Choose...</option>
								<?php foreach($customers as $c){ if($c->bill_to_code){ ?>
								<option value="<?= $c->bill_to_code ?>">[<?= $c->bill_to_code ?>] <?= $c->bill_to_name ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-12 text-center pt-3">
							<button type="submit" class="btn btn-primary">Export</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title">Upload Data</h5>
						<div>
							Templates: <a href="<?= base_url() ?>template/sa_sell_in.xlsx">Sell-In</a> / <a href="<?= base_url() ?>template/sa_sell_out.xlsx">Sell-Out</a>
						</div>
					</div>
					<form class="row g-3" id="form_upload_sell_inout">
						<div class="col-12">
							<label class="form-label">File</label>
							<input type="file" class="form-control" name="attach" accept=".xls,.xlsx,.csv">
						</div>
						<div class="col-12 text-center pt-3">
							<button type="submit" class="btn btn-primary">Upload</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Sell-In/Out Report</h5>
					<?php $rp = [" ", "(", ")", "/"]; ?>
					<div class="row">
						<div class="col-md-2">
							<label class="form-label">Model Category</label>
							<select class="form-select mb-3" id="sl_mc">
								<option value="" selected="">--</option>
								<?php foreach($model_categories as $item){ ?>
								<option value="<?= str_replace($rp, "_", $item->model_category) ?>"><?= $item->model_category ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-2">
							<label class="form-label">Level 1</label>
							<select class="form-select mb-3" id="sl_lvl1">
								<option value="" selected="">--</option>
								<?php foreach($lvl1s as $item){ ?>
								<option class="sl_lvl1 sl_mc_<?= str_replace($rp, "_", $item->model_category) ?>" value="<?= str_replace(" ", "_", $item->product_level1_name) ?>"><?= $item->product_level1_name ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-2">
							<label class="form-label">Level 2</label>
							<select class="form-select mb-3" id="sl_lvl2">
								<option value="" selected="">--</option>
								<?php foreach($lvl2s as $item){ ?>
								<option class="d-none sl_lvl2 sl_lvl1_<?= str_replace($rp, "_", $item->product_level1_name) ?>" value="<?= str_replace(" ", "_", $item->product_level2_name) ?>"><?= $item->product_level2_name ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-2">
							<label class="form-label">Level 3</label>
							<select class="form-select mb-3" id="sl_lvl3">
								<option value="" selected="">--</option>
								<?php foreach($lvl3s as $item){ ?>
								<option class="d-none sl_lvl3 sl_lvl2_<?= str_replace($rp, "_", $item->product_level2_name) ?>" value="<?= str_replace(" ", "_", $item->product_level3_name) ?>"><?= $item->product_level3_name ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-2">
							<label class="form-label">Level 4</label>
							<select class="form-select mb-3" id="sl_lvl4">
								<option value="" selected="">--</option>
								<?php foreach($lvl4s as $item){ ?>
								<option class="d-none sl_lvl4 sl_lvl3_<?= str_replace($rp, "_", $item->product_level3_name) ?>" value="<?= str_replace(" ", "_", $item->product_level4_name) ?>"><?= $item->product_level4_name ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-2">
							<label class="form-label">Model</label>
							<select class="form-select mb-3" id="sl_mo">
								<option value="" selected="">--</option>
								<?php foreach($models as $item){ ?>
								<option class="sl_mo sl_mc_<?= str_replace($rp, "_", $item->model_category) ?> sl_lvl1_<?= str_replace(" ", "_", $item->product_level1_name) ?> sl_lvl2_<?= str_replace(" ", "_", $item->product_level2_name) ?> sl_lvl3_<?= str_replace(" ", "_", $item->product_level3_name) ?> sl_lvl4_<?= str_replace(" ", "_", $item->product_level4_name) ?>" value="<?= $item->model ?>"><?= $item->model ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12">
							<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
								<?php foreach($models as $m){ ?>
								<li class="nav-item" role="presentation">
									<button class="nav-link" id="pills-<?= $m->model ?>-tab" data-bs-toggle="pill" data-bs-target="#pills-<?= $m->model ?>" type="button" role="tab" aria-controls="pills-<?= $m->model ?>" aria-selected="true"><?= $m->model ?></button>
								</li>
								<?php } ?>
							</ul>
							<div class="tab-content pt-2">
								<?php foreach($models as $m){ ?>
								<div class="tab-pane fade" id="pills-<?= $m->model ?>" role="tabpanel" aria-labelledby="<?= $m->model ?>-tab">
									<table class="table align-middle text-center">
										<thead>
											<tr>
												<th scope="col">Date</th>
												<th scope="col">Type</th>
												<th scope="col">Qty</th>
												<th scope="col">Stock Cus</th>
												<th scope="col">LG</th>
												<th scope="col">Diff</th>
												<th scope="col">Alert</th>
												<th scope="col">Amount</th>
												<th scope="col">U/Price</th>
												<th scope="col">U/Cost</th>
												<th scope="col">U/Profit</th>
												<th scope="col">Invoices</th>
											</tr>
										</thead>
										<tbody>
											<?php $list = array_reverse($sell_inouts[$m->model]); foreach($list as $item){ ?>
											<tr>
												<td><?= $item->date ?></td>
												<td><?= $item->type === "in" ? "Sell-In" : "Sell-out" ?></td>
												<td><?= number_format($item->qty) ?></td>
												<td><?= $item->type === "out" ? number_format($item->stock_cus) : "" ?></td>
												<td><?= number_format($item->stock_lg) ?></td>
												<td><?= $item->type === "out" ? number_format($item->stock_diff) : "" ?></td>
												<td>
													<?php
													if ($item->type === "out"){
														$val = abs($item->stock_diff);
														if ($val < 5) $color = "success";
														else if ($val < 10) $color = "warning";
														else $color = "danger";
													?>
													<i class="bi bi-circle-fill text-<?= $color ?>"></i>
													<?php } ?>
												</td>
												<td><?= number_format($item->amount, 2) ?></td>
												<td><?= number_format($item->unit_price, 2) ?></td>
												<td><?= $item->unit_cost > 0 ? number_format($item->unit_cost, 2) : "-" ?></td>
												<td><?= (($item->unit_profit > 0) and ($item->unit_cost > 0)) ? number_format($item->unit_profit, 2) : "-" ?></td>
												<td>
													<?php foreach($item->invoices as $inv){ ?>
													<div><?= $inv["no"] ?> (<?= $inv["qty"] ?> * <?= number_format($inv["unit_price"], 2) ?>)</div>
													<?php } ?>
												</td>
											</tr>
											<?php } ?>
										</tbody>
									</table>
								</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Lastest 1,000 records</h5>
					<ul class="nav nav-tabs nav-tabs-bordered" role="tablist">
						<li class="nav-item" role="presentation">
							<button class="nav-link active" id="sell_in_t-tab" data-bs-toggle="tab" data-bs-target="#sell_in_t" type="button" role="tab" aria-controls="sell_in_t" aria-selected="true">Sell-In</button>
						</li>
						<li class="nav-item" role="presentation">
							<button class="nav-link" id="sell_out_t-tab" data-bs-toggle="tab" data-bs-target="#sell_out_t" type="button" role="tab" aria-controls="sell_out_t" aria-selected="false">Sell-Out</button>
						</li>
					</ul>
					<div class="tab-content pt-3">
						<div class="tab-pane fade show active" id="sell_in_t" role="tabpanel" aria-labelledby="sell_in_t-tab">
							<div class="table-responsive">
								<table class="table datatable align-middle">
									<thead>
										<tr>
											<th scope="col" style="width: 80px;">#</th>
											<th scope="col">Bill to</th>
											<th scope="col">Bill name</th>
											<th scope="col">Date</th>
											<th scope="col">Division</th>
											<th scope="col">Level 1</th>
											<th scope="col">Model</th>
											<th scope="col">Invoice</th>
											<th scope="col">Qty</th>
											<th scope="col">U/Price</th>
											<th scope="col">Amount</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach($sell_ins as $i => $in){ 
											$pre = ($in->order_amount < 0) ? "-" : ""; ?>
										<tr>
											<td class="text-nowrap"><?= number_format($i + 1) ?></td>
											<td><?= $in->bill_to_code ?></td>
											<td><?= $in->bill_to_name ?></td>
											<td><?= $in->closed_date ?></td>
											<td><?= $in->model_category ?></td>
											<td><?= $in->product_level1_name ?></td>
											<td><?= $in->model ?></td>
											<td><div class="text-nowrap"><?= $in->invoice_no ?></div></td>
											<td><?= number_format($in->order_qty) ?></td>
											<td><div class="text-nowrap"><?= $in->currency." ".number_format($in->unit_selling_price, 2) ?></div></td>
											<td><div class="text-nowrap"><?= $pre." ".$in->currency." ".number_format(abs($in->order_amount), 2) ?></div></td>
										</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade" id="sell_out_t" role="tabpanel" aria-labelledby="sell_out_t-tab">
							<div class="table-responsive">
								<table class="table datatable align-middle">
									<thead>
										<tr>
											<th scope="col" style="width: 80px;">#</th>
											<th scope="col">Bill to</th>
											<th scope="col">Customer</th>
											<th scope="col">Channel</th>
											<th scope="col">Date</th>
											<th scope="col">Division</th>
											<th scope="col">Line</th>
											<th scope="col">Model</th>
											<th scope="col">Qty</th>
											<th scope="col">Amount</th>
											<th scope="col">Stock</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach($sell_outs as $i => $out){ ?>
										<tr>
											<td class="text-nowrap"><?= number_format($i + 1) ?></td>
											<td><?= $out->customer_code ?></td>
											<td><?= $out->account ?></td>
											<td><?= $out->channel ?></td>
											<td><?= $out->sunday ?></td>
											<td><?= $out->division ?></td>
											<td><?= $out->line ?></td>
											<td><?= $out->suffix ?></td>
											<td><?= number_format($out->units) ?></td>
											<td><?= "PEN ".number_format($out->amount, 2) ?></td>
											<td><?= number_format($out->stock) ?></td>
										</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div> 
	</div>
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {
	$('#sl_mc').change(function(){
		$("#sl_lvl1").val(""); $('#sl_lvl1 option.sl_lvl1').addClass('d-none'); 
		$("#sl_lvl2").val(""); $('#sl_lvl2 option.sl_lvl2').addClass('d-none'); 
		$("#sl_lvl3").val(""); $('#sl_lvl3 option.sl_lvl3').addClass('d-none'); 
		$("#sl_lvl4").val(""); $('#sl_lvl4 option.sl_lvl4').addClass('d-none'); 
		$("#sl_mo").val(""); $('#sl_mo option.sl_mo').addClass('d-none'); 
		
		var selected = $(this).val();
		if (selected != "") $('option.sl_mc_' + selected).removeClass('d-none');
		else{
			$('#sl_lvl1 option.sl_lvl1').removeClass('d-none');
			$('#sl_mo option.sl_mo').removeClass('d-none');
		}
    });
	
	$('#sl_lvl1').change(function(){
		$("#sl_lvl2").val(""); $('#sl_lvl2 option.sl_lvl2').addClass('d-none'); 
		$("#sl_lvl3").val(""); $('#sl_lvl3 option.sl_lvl3').addClass('d-none'); 
		$("#sl_lvl4").val(""); $('#sl_lvl4 option.sl_lvl4').addClass('d-none'); 
		$("#sl_mo").val(""); $('#sl_mo option.sl_mo').addClass('d-none'); 
		
		var selected = $(this).val();
		if (selected != "") $('option.sl_lvl1_' + selected).removeClass('d-none');
		else $('#sl_mo option.sl_mo_' + $("#sl_mc").val()).removeClass('d-none');
    });
	
	$('#sl_lvl2').change(function(){
		$("#sl_lvl3").val(""); $('#sl_lvl3 option.sl_lvl3').addClass('d-none'); 
		$("#sl_lvl4").val(""); $('#sl_lvl4 option.sl_lvl4').addClass('d-none'); 
		$("#sl_mo").val(""); $('#sl_mo option.sl_mo').addClass('d-none'); 
		
		var selected = $(this).val();
		if (selected != "") $('option.sl_lvl2_' + selected).removeClass('d-none');
		else $('#sl_mo option.sl_lvl1_' + $("#sl_lvl1").val()).removeClass('d-none');
    });
	
	$('#sl_lvl3').change(function(){
		$("#sl_lvl4").val(""); $('#sl_lvl4 option.sl_lvl4').addClass('d-none'); 
		$("#sl_mo").val(""); $('#sl_mo option.sl_mo').addClass('d-none'); 
		
		var selected = $(this).val();
		if (selected != "") $('option.sl_lvl3_' + selected).removeClass('d-none');
		else $('#sl_mo option.sl_lvl2_' + $("#sl_lvl2").val()).removeClass('d-none');
    });
	
	$('#sl_lvl4').change(function(){
		$("#sl_mo").val(""); $('#sl_mo option.sl_mo').addClass('d-none'); 
		
		var selected = $(this).val();
		if (selected != "") $('option.sl_lvl4_' + selected).removeClass('d-none');
		else $('#sl_mo option.sl_lvl3_' + $("#sl_lvl3").val()).removeClass('d-none');
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
		ajax_form_warning(this, "module/sa_sell_inout/upload_sell_inout_file", "Do you upload data?").done(function(res) {
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