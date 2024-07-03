<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Sell-In/Out</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">Sell-In/Out</li>
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
					<h5 class="card-title">Filter</h5>
					<form class="row g-3">
						<?php
						$cus = $this->input->get("cus");
						$lz = $this->input->get("lz");
						$li = $this->input->get("li");
						$lii = $this->input->get("lii");
						$liii = $this->input->get("liii");
						$liv = $this->input->get("liv");
						$prd = $this->input->get("prd");
						?>
						<div class="col-md-12">
							<label class="form-label">Customer</label>
							<select class="form-select" name="cus">
								<option value="" selected="">Choose...</option>
								<?php foreach($customers as $c){ if($c->bill_to_code){ ?>
								<option <?= ($cus == $c->customer_id) ? "selected" : "" ?> value="<?= $c->customer_id ?>">[<?= $c->bill_to_code ?>] <?= $c->customer ?></option>
								<?php }} ?>
							</select>
						</div>
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
		<?php if ($sell_inouts){
			$high_t = $sell_inouts[0]["qty"];
			$high_b = $high_t * 0.5;
			$medium_t = $high_b;
			$medium_b = $high_t * 0.25;
			$low_t = $medium_b;
			$low_b = 0;
			
			$arr_h = $arr_m = $arr_l = [];
			
			foreach($sell_inouts as $io) switch(true){
				case (($high_t >= $io["qty"]) and ($io["qty"] > $high_b)): $arr_h[] = $product_arr[$io["product_id"]]->model; break;
				case (($medium_t >= $io["qty"]) and ($io["qty"] > $medium_b)): $arr_m[] = $product_arr[$io["product_id"]]->model; break;
				case (($low_t >= $io["qty"]) and ($io["qty"] > $low_b)): $arr_l[] = $product_arr[$io["product_id"]]->model; break;
			}
			
			sort($arr_h);
			sort($arr_m);
			sort($arr_l);
		?>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">High Rotation</h5>
					<div class="overflow-auto bl_move" style="max-height: 300px;">
						<?php if ($arr_h){ ?>
						<ul class="list-group">
							<?php foreach($arr_h as $a){ ?>
							<li class="list-group-item"><?= $a ?></li>
							<?php } ?>
						</ul>
						<?php }else echo "No data"; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Medium</h5>
					<div class="overflow-auto bl_move" style="max-height: 300px;">
						<?php if ($arr_m){ ?>
						<ul class="list-group">
							<?php foreach($arr_m as $a){ ?>
							<li class="list-group-item"><?= $a ?></li>
							<?php } ?>
						</ul>
						<?php }else echo "No data"; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Low</h5>
					<div class="overflow-auto bl_move" style="max-height: 300px;">
						<?php if ($arr_l){ ?>
						<ul class="list-group">
							<?php foreach($arr_l as $a){ ?>
							<li class="list-group-item"><?= $a ?></li>
							<?php } ?>
						</ul>
						<?php }else echo "No data"; ?>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Sell-In/Out Report</h5>
					<?php if ($sell_inouts){ ?>
					<ul class="nav nav-tabs nav-tabs-bordered" id="myTab" role="tablist">
						<?php $a = "active"; $s = "selected"; foreach($sell_inouts as $io){ if ($io["ios"]){ ?>
						<li class="nav-item" role="presentation">
							<button class="nav-link <?= $a ?>" id="prd<?= $io["product_id"] ?>t-tab" data-bs-toggle="tab" data-bs-target="#prd<?= $io["product_id"] ?>t" type="button" role="tab" aria-controls="prd<?= $io["product_id"] ?>t" aria-selected=" <?= $s ?>"><?= $product_arr[$io["product_id"]]->model ?> (<?= $io["qty"] ?>)</button>
						</li>
						<?php $a = $s = ""; }} ?>
					</ul>
					<div class="tab-content pt-3">
						<?php $a = "show active"; foreach($sell_inouts as $io){ if ($io["ios"]){ $ios = $io["ios"]; ?>
						<div class="tab-pane fade <?= $a ?>" id="prd<?= $io["product_id"] ?>t" role="tabpanel" aria-labelledby="prd<?= $io["product_id"] ?>t-tab">
							<div class="table-responsive">
								<table class="table align-middle">
									<thead>
										<tr>
											<th scope="col" style="width: 80px;">#</th>
											<th scope="col">Date</th>
											<th scope="col">Sell-in</th>
											<th scope="col">Sell-out</th>
											<th scope="col">Stock<br/>(Cust. / LG / Diff)</th>
											<th scope="col">Alert</th>
											<!-- th scope="col">Invoice</th -->
											<th scope="col">Invoices</th>
											<th scope="col">LG Price</th>
											<th scope="col">Avg Price</th>
											<th scope="col">Sele Price</th>
											<th scope="col">Profit</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach($ios as $i => $i_io){ ?>
										<tr>
											<td class="text-nowrap"><?= number_format($i + 1) ?></td>
											<td><?= $i_io->date ?></td>
											<td><?= $i_io->sell_in ?></td>
											<td><?= $i_io->sell_out ?></td>
											<td>
												<?php $aux = [];
												$aux[] = $i_io->stock_customer ? $i_io->stock_customer : 0;
												$aux[] = $i_io->stock_lg ? $i_io->stock_lg : 0;
												$aux[] = $i_io->stock_diff ? $i_io->stock_diff : 0;
												echo (($i_io->sell_out) ? implode(" / ", $aux) : ""); ?>
											</td>
											<td>
												<?php if ($i_io->sell_out){ switch(true){
													case (abs($i_io->stock_diff) > 10) : $c = "text-danger"; break;
													case (abs($i_io->stock_diff) > 5) : $c = "text-warning"; break;
													default: $c = "text-success";
												} ?>
												<i class="bi bi-circle-fill <?= $c ?>"></i>
												<?php } ?>
											</td>
											<!-- td><?= $i_io->invoice ?></td -->
											<td>
												<?php 
												$count = 0; 
												foreach($i_io->invoices as $inv){
													$i_aux = $inv["invoice"];
													$i_code = ($i_aux) ? $i_aux->invoice : "No Invoice";
													$i_price = ($i_aux) ? " * ".$i_aux->currency." ".number_format($i_aux->u_price, 2) : "";
													$row = $i_code." (".number_format($inv["qty"]).$i_price.")"; ?>
												<div class="<?= ($count) ? "d-none ln_inv ln_inv_".$i : "" ?>">
													<?= $row ?>
													<?php if ((!$count) and (count($i_io->invoices) > 1)){ ?>
														<i class="bi bi-caret-down-square ms-1 ctrl_inv" id="ctrl_<?= $i ?>"></i>
													<?php } ?>
												</div>
												<?php $count++;} ?>
											</td>
											<td><?= (($i_io->u_price > 0) ? $i_io->currency." ".number_format($i_io->u_price, 2) : "") ?></td>
											<td><?= (($i_io->price_avg > 0) ? "S/ ".number_format($i_io->price_avg, 2) : "") ?></td>
											<td><?= (($i_io->sale_price > 0) ? "S/ ".number_format($i_io->sale_price, 2) : "") ?></td>
											<td><span class="text-<?= $i_io->profit > 0 ? "success" : "danger" ?>"><?= (($i_io->profit != 0) ? "S/ ".number_format(abs($i_io->profit), 2) : "") ?></span></td>
										</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
						<?php $a = ""; }} ?>
					</div>
					<?php }else{ ?>
					<div class="alert alert-primary alert-dismissible fade show text-center mb-0" role="alert">
						Select customer, product division and line 1 at least to make Sell-In/Out table.
					</div>
					<?php } ?>
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
											<th scope="col">Date</th>
											<th scope="col">Invoice</th>
											<th scope="col">Customer</th>
											<th scope="col">Bill to</th>
											<th scope="col">Line</th>
											<th scope="col">Model</th>
											<th scope="col">Qty</th>
											<th scope="col"><div class="text-end">U/Price</div></th>
											<th scope="col"><div class="text-end">Amount</div></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach($sell_ins as $i => $in){ 
											$curr = $currency_arr[$in->currency_id];
											$pre = ($in->order_amount < 0) ? "-" : ""; ?>
										<tr>
											<td class="text-nowrap"><?= number_format($i + 1) ?></td>
											<td><?= $in->closed_date ?></td>
											<td><?= $invoice_arr[$in->invoice_id]->invoice ?></td>
											<td><?= $customer_arr[$in->customer_id]->customer ?></td>
											<td><?= $customer_arr[$in->customer_id]->bill_to_code ?></td>
											<td><?= $product_arr[$in->product_id]->lines ?></td>
											<td><?= $product_arr[$in->product_id]->model ?></td>
											<td><?= number_format($in->order_qty) ?></td>
											<td><div class="text-end"><?= $currency_arr[$in->currency_id]->symbol." ".number_format($in->unit_selling_price, 2) ?></div></td>
											<td><div class="text-end"><?= $pre." ".$curr->symbol." ".number_format(abs($in->order_amount), 2).(($curr->currency !== "PEN") ? " (".$pre." S/ ".number_format(abs($in->order_amount_pen), 2).")" : "") ?></div></td>
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
											<th scope="col">Date</th>
											<th scope="col">Customer</th>
											<th scope="col">Bill to</th>
											<th scope="col">Line</th>
											<th scope="col">Model</th>
											<th scope="col">Channel</th>
											<th scope="col">Stock</th>
											<th scope="col">Qty</th>
											<th scope="col"><div class="text-end">Amount</div></th>
										</tr>
									</thead>
									<tbody>
										<?php foreach($sell_outs as $i => $out){ //sell-out always use PEN ?>
										<tr>
											<td class="text-nowrap"><?= number_format($i + 1) ?></td>
											<td><?= $out->date ?></td>
											<td><?= $customer_arr[$out->customer_id]->customer ?></td>
											<td><?= $customer_arr[$out->customer_id]->bill_to_code ?></td>
											<td><?= $product_arr[$out->product_id]->lines ?></td>
											<td><?= $product_arr[$out->product_id]->model ?></td>
											<td><?= $channel_arr[$out->channel_id]->channel ?></td>
											<td><?= number_format($out->stock) ?></td>
											<td><?= number_format($out->qty) ?></td>
											<td><div class="text-end"><?= "S/ ".number_format($out->amount, 2) ?></div></td>
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

<div class="modal fade" id="md_exr" tabindex="-1" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Sell-In/Out Report</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form class="row g-3" id="form_exp_report">
					<div class="col-12">
						<label class="form-label">Customer</label>
						<select class="form-select" name="cus">
							<option value="" selected="">Choose...</option>
							<?php foreach($customers as $c){ if($c->bill_to_code){ ?>
							<option <?= ($cus == $c->customer_id) ? "selected" : "" ?> value="<?= $c->customer_id ?>"><?= $c->bill_to_code ?> - <?= $c->customer ?></option>
							<?php }} ?>
						</select>
					</div>
					<div class="col-md-6">
						<label class="form-label">Division</label>
						<select class="form-select" id="sl_lz_report" name="lz">
							<option value="" selected="">Choose...</option>
							<?php foreach($lvl_z as $l){ $s = ($lz == $l->line_id) ? "selected" : ""; ?>
							<option value="<?= $l->line_id ?>" <?= $s ?>><?= $l->line ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-md-6">
						<label class="form-label">Line 1</label>
						<select class="form-select" id="sl_li_report" name="li">
							<option value="" selected="">Choose...</option>
							<?php foreach($lvl_i as $l){ $d = ($lz == $l->parent_id) ? "" : "d-none"; $s = ($li == $l->line_id) ? "selected" : ""; ?>
							<option class="sl_li sl_lz_<?= $l->parent_id ?> <?= $d ?>" value="<?= $l->line_id ?>" <?= $s ?>><?= $l->line ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="text-end pt-3">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary">Export</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="md_uff" tabindex="-1" style="display: none;" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Upload from Excel</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form class="row g-3" id="form_upload_sell_inout">
					<div class="col-12">
						<label class="form-label">File</label>
						<input type="file" class="form-control" name="md_uff_file" accept=".xls,.xlsx,.csv">
					</div>
					<div class="text-end pt-3">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary">Upload</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
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