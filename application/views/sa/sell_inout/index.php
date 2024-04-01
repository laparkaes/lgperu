<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Sell-In/Out</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard?m=sa">Sales Admin</a></li>
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
		<a href="#" type="button" class="btn btn-success">
			<i class="bi bi-search"></i>
		</a>
		<a href="#" type="button" class="btn btn-success">
			<i class="bi bi-plus-lg"></i>
		</a>
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
						$grp = $this->input->get("grp");
						$cat = $this->input->get("cat");
						$prd = $this->input->get("prd");
						$cus = $this->input->get("cus");
						?>
						<div class="col-lg-2 col-md-3">
							<label class="form-label">Group</label>
							<select class="form-select" id="sl_group" name="grp">
								<option value="" selected="">Choose...</option>
								<?php foreach($groups as $g){ ?>
								<option value="<?= $g->group_id ?>" <?= ($grp == $g->group_id) ? "selected" : "" ?>><?= $g->group_name ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-lg-2 col-md-3">
							<label class="form-label">Category</label>
							<select class="form-select" id="sl_category" name="cat">
								<option value="" selected="">Choose...</option>
								<?php foreach($categories as $c){ ?>
								<option class="g_all g_<?= $c->group_id ?> <?= ($grp == $c->group_id) ? "" : "d-none" ?>" <?= ($cat == $c->category_id) ? "selected" : "" ?> value="<?= $c->category_id ?>"><?= $c->category ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-lg-3 col-md-6">
							<label class="form-label">Product</label>
							<select class="form-select" id="sl_product" name="prd">
								<option value="" selected="">Choose...</option>
								<?php foreach($products as $p){ if ($p->category_id){ ?>
								<option class="c_all c_<?= $p->category_id ?> <?= ($cat == $p->category_id) ? "" : "d-none" ?>" <?= ($prd == $p->product_id) ? "selected" : "" ?> value="<?= $p->product_id ?>"><?= $p->model ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-lg-4 col-md-8">
							<label class="form-label">Customer</label>
							<select class="form-select" name="cus">
								<option value="" selected="">Choose...</option>
								<?php foreach($customers as $c){ if($c->bill_to_code){ ?>
								<option <?= ($cus == $c->customer_id) ? "selected" : "" ?> value="<?= $c->customer_id ?>"><?= $c->bill_to_code ?> - <?= $c->customer ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-lg-1 col-md-4 flex-fill align-self-end">
							<div class="d-grid gap-2">
								<button type="submit" class="btn btn-primary">Submit</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
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
								<table class="table datatable align-middle">
									<thead>
										<tr>
											<th scope="col" style="width: 80px;">#</th>
											<th scope="col">Date</th>
											<th scope="col">U/Price</th>
											<th scope="col">Sell-in</th>
											<th scope="col">Sell-out</th>
											<th scope="col">Stock<br/>(Cust. / LG / Diff)</th>
											<th scope="col">Alert</th>
											<th scope="col">Invoice</th>
											<th scope="col">Invoices</th>
										</tr>
									</thead>
									<tbody>
										<?php foreach($ios as $i => $i_io){ ?>
										<tr>
											<td class="text-nowrap"><?= number_format($i + 1) ?></td>
											<td><?= $i_io->date ?></td>
											<td><?= (($i_io->u_price > 0) ? $i_io->currency." ".number_format($i_io->u_price, 2) : "") ?></td>
											<td><?= $i_io->sell_in ?></td>
											<td><?= $i_io->sell_out ?></td>
											<td>
												<?php $aux = [];
												$aux[] = $i_io->stock_customer ? $i_io->stock_customer : 0;
												$aux[] = $i_io->stock_lg ? $i_io->stock_lg : 0;
												$aux[] = $i_io->stock_diff ? $i_io->stock_diff : 0;
												echo (($i_io->stock_customer) ? implode(" / ", $aux) : ""); ?>
											</td>
											<td>
												<?php if ($i_io->sell_out > 0){ switch(true){
													case (abs($i_io->stock_diff) > 10) : $c = "text-danger"; break;
													case (abs($i_io->stock_diff) > 5) : $c = "text-warning"; break;
													default: $c = "text-success";
												} ?>
												<i class="bi bi-circle-fill <?= $c ?>"></i>
												<?php } ?>
											</td>
											<td><?= $i_io->invoice ?></td>
											<td>
												<?php $aux = []; foreach($i_io->invoices as $inv){
												$i_aux = $inv["invoice"];
												$i_code = ($i_aux) ? $i_aux->invoice : "No Invoice";
												$i_price = ($i_aux) ? " * ".$i_aux->currency." ".number_format($i_aux->u_price, 2) : "";
												$aux[] = $i_code." (".number_format($inv["qty"]).$i_price.")";
												} ?>
												<?= implode("<br/>", $aux) ?>
											</td>
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
						Select customer and product category at least to make Sell-In/Out report.
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
											<th scope="col">Category</th>
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
											<td><?= $product_arr[$in->product_id]->group." > ".$product_arr[$in->product_id]->category ?></td>
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
											<th scope="col">Category</th>
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
											<td><?= $product_arr[$out->product_id]->group." > ".$product_arr[$out->product_id]->category ?></td>
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