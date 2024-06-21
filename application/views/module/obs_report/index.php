<div class="d-md-flex justify-content-between align-items-center">
	<div class="pagetitle">
		<h1>OBS - Report</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">OBS - Report</li>
			</ol>
		</nav>
	</div>
</div>					
<section class="section">
	<div class="row">
		<div class="col-md-6 text-start">
			<div class="mb-3">
				<a class="btn btn-primary" target="blank_" href="<?= base_url() ?>module/obs_report/progress/w">Last 12 Weeks</a>
				<a class="btn btn-primary" target="blank_" href="<?= base_url() ?>module/obs_report/progress/m">Last 12 Months</a>
			</div>
		</div>
		<div class="col-md-6 text-end">
			<form class="d-flex justify-content-end mb-3">
				<select class="form-select ms-1" id="sl_by_week" name="w">
					<option value="">By Week</option>
					<?php
					$today = strtotime(date("Y-m-d"));
					foreach($weeks as $w){ ?>
					<option value="<?= $w["week"] ?>" <?= ($w["week"] == $this->input->get("w")) ? "selected": "" ?>>W<?= str_pad($w["week"], 2, '0', STR_PAD_LEFT); ?> | <?= implode(" ~ ", $w["dates"]) ?></option>
					<?php } ?>
				</select>
				<select class="form-select ms-1" id="sl_by_month" name="m">
					<option value="">By Month</option>
					<?php foreach($months as $m){ ?>
					<option value="<?= $m ?>" <?= ($m == $this->input->get("m")) ? "selected": "" ?>><?= $m ?></option>
					<?php } ?>
				</select>
				<button type="submit" class="btn btn-primary ms-1">Submit</button>
			</form>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between">
						<h5 class="card-title">OBS Dashboard</h5>
						<h5 class="card-title"><?= $from." ~ ".$to ?> | <strong>USD</strong></h5>
					</div>
					<table class="table align-middle text-center">
						<thead>
							<tr>
								<th scope="col" style="width: 100px;">Subsidiary</th>
								<th scope="col" style="width: 100px;">Division</th>
								<th scope="col" class="border-end" style="width: 250px;">Category</th>
								<th scope="col" colspan="2">Sales Projection</th>
								<th scope="col">Actual</th>
								<th scope="col">Expected</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($subsidiaries as $sub => $subsidiary){ $total = $subsidiary["summary"]["total"]; ?>
							<tr>
								<td><strong><?= $sub ?></strong></td>
								<td></td>
								<td class="border-end"></td>
								<td><strong><?= number_format($subsidiary["summary"]["total"], 2) ?></strong></td>
								<td><strong><?= $total ? number_format($subsidiary["summary"]["total"] / $total * 100, 2) : "0.00" ?>%</strong></td>
								<td><strong><?= number_format($subsidiary["summary"]["closed"], 2) ?></strong></td>
								<td><strong><?= number_format($subsidiary["summary"]["on_process"], 2) ?></strong></td>
							</tr>
							<?php foreach($subsidiary["divisions"] as $div => $division){ ?>
							<tr>
								<td></td>
								<td><strong><?= $div ?></strong></td>
								<td class="border-end"></td>
								<td><strong><?= number_format($division["summary"]["total"], 2) ?></strong></td>
								<td><strong><?= $total ? number_format($division["summary"]["total"] / $total * 100, 2) : "0.00" ?>%</strong></td>
								<td><strong><?= number_format($division["summary"]["closed"], 2) ?></strong></td>
								<td><strong><?= number_format($division["summary"]["on_process"], 2) ?></strong></td>
							</tr>
							<?php foreach($division["categories"] as $cat => $category){ ?>
							<tr>
								<td></td>
								<td></td>
								<td class="border-end"><?= $cat ?></td>
								<td><?= number_format($category["summary"]["total"], 2) ?></td>
								<td><?= $total ? number_format($category["summary"]["total"] / $total * 100, 2) : "0.00" ?>%</td>
								<td><?= number_format($category["summary"]["closed"], 2) ?></td>
								<td><?= number_format($category["summary"]["on_process"], 2) ?></td>
							</tr>
							<?php }}} ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Best Seller</h5>
					<div class="mb-3">
						<div class="btn-group" role="group" aria-label="btn_subsidiaries">
							<?php foreach($sales as $subsidiary => $sales_sub){ ?>
							<button type="button" class="btn btn-primary btn_bs" value="<?= $subsidiary ?>"><?= $subsidiary ?></button>
							<?php } ?>
						</div>
						<div class="btn-group" role="group" aria-label="btn_divisions">
							<?php $div_map = $this->division_map; foreach($sales as $subsidiary => $sales_sub) foreach($div_map as $div => $categories){ ?>
							<button type="button" class="btn btn-primary btn_bs" value="<?= $div ?>"><?= $div ?></button>
							<?php } ?>
						</div>
					</div>
					<?php foreach($sales as $subsidiary => $sales_sub){ ?>
					<div class="row bl_bs_<?= $subsidiary ?>">
						<?php
						foreach($div_map as $div => $categories){
							foreach($categories as $cat){
							?>
						<div class="col-md-3 col-sm-6 bl_bs_<?= $div ?>">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title"><?= $cat." │ ".$div." │ ".$subsidiary ?></h5>
									<div class="overflow-auto" style="height: 500px;">
										<table class="table">
											<thead>
												<tr>
													<th scope="col">Model</th>
													<th scope="col" class="text-center">Qty</th>
													<th scope="col" class="text-end text-nowrap">K USD</th>
												</tr>
											</thead>
											<tbody>
												<?php foreach($sales[$subsidiary][$div][$cat] as $model => $data){ if ($data["qty"]){ ?>
												<tr>
													<td><?= $model ?></td>
													<td class="text-center"><?= number_format($data["qty"]) ?></td>
													<td class="text-end"><?= number_format($data["amount"], 2) ?></td>
												</tr>
												<?php }} ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
							<?php
							}
						}
						?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Statistics</h5>
					<div class="row">
						<div class="col-12">
						<?php 
						$cus_group = $statistics["cus_group"];
						$devices = $statistics["devices"];
						$d2b2c = $statistics["d2b2c"];
						$cupons = $statistics["cupons"];
						$departments = $statistics["departments"];
						
						unset($statistics["cus_group"]);
						unset($statistics["devices"]);
						unset($statistics["d2b2c"]);
						unset($statistics["cupons"]);
						unset($statistics["departments"]);
						
						
						print_r($statistics);
						?>
						</div>
						<div class="col-md-6">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Customer Group</h5>
									<div class="row">
										<div class="col-md-6">
											<div class="overflow-auto">
												<table class="table">
													<thead>
														<tr>
															<th scope="col">Group</th>
															<th scope="col" class="text-center">Qty</th>
															<th scope="col" class="text-end text-nowrap">K USD</th>
															<th scope="col" class="text-end">Perc.</th>
														</tr>
													</thead>
													<tbody>
														<?php foreach($cus_group as $item){ if ($item["qty"]){ ?>
														<tr>
															<td><?= $item["customer_group"] ?></td>
															<td class="text-center"><?= number_format($item["qty"]) ?></td>
															<td class="text-end"><?= number_format($item["amount"], 2) ?></td>
															<td class="text-end"><?= number_format($item["amount"] * 100 / $cus_group["total"]["amount"], 2) ?>%</td>
														</tr>
														<?php }} ?>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Devices</h5>
									<div class="row">
										<div class="col-md-6">
											<div class="overflow-auto">
												<table class="table">
													<thead>
														<tr>
															<th scope="col">Device</th>
															<th scope="col" class="text-center">Qty</th>
															<th scope="col" class="text-end text-nowrap">K USD</th>
															<th scope="col" class="text-end">Perc.</th>
														</tr>
													</thead>
													<tbody>
														<?php foreach($devices as $item){ if ($item["qty"]){ ?>
														<tr>
															<td><?= $item["device"] ?></td>
															<td class="text-center"><?= number_format($item["qty"]) ?></td>
															<td class="text-end"><?= number_format($item["amount"], 2) ?></td>
															<td class="text-end"><?= number_format($item["amount"] * 100 / $devices["total"]["amount"], 2) ?>%</td>
														</tr>
														<?php }} ?>
													</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Location</h5>
									<table class="table">
										<thead>
											<tr>
												<th scope="col">Department</th>
												<th scope="col">Province</th>
												<th scope="col" class="text-center">Qty</th>
												<th scope="col" class="text-end text-nowrap">K USD</th>
												<th scope="col" class="text-end">Perc.</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach($departments as $item){ if ($item["qty"]){ ?>
											<tr>
												<td><?= $item["department"] ?></td>
												<td><?= $item["province"] ?></td>
												<td class="text-center"><?= number_format($item["qty"]) ?></td>
												<td class="text-end"><?= number_format($item["amount"], 2) ?></td>
												<td class="text-end"><?= number_format($item["amount"] * 100 / $departments["total"]["amount"], 2) ?>%</td>
											</tr>
											<?php }} ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">D2B2C</h5>
									<table class="table">
										<thead>
											<tr>
												<th scope="col">Company</th>
												<th scope="col" class="text-center">Qty</th>
												<th scope="col" class="text-end text-nowrap">K USD</th>
												<th scope="col" class="text-end">Perc.</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach($d2b2c as $item){ if ($item["qty"]){ ?>
											<tr>
												<td><?= $item["company"] ?></td>
												<td class="text-center"><?= number_format($item["qty"]) ?></td>
												<td class="text-end"><?= number_format($item["amount"], 2) ?></td>
												<td class="text-end"><?= number_format($item["amount"] * 100 / $d2b2c["total"]["amount"], 2) ?>%</td>
											</tr>
											<?php }} ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
						<div class="col-md-5">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Cupon</h5>
									<table class="table">
										<thead>
											<tr>
												<th scope="col">Cupon</th>
												<th scope="col" class="text-center">Qty</th>
												<th scope="col" class="text-end text-nowrap">K USD</th>
												<th scope="col" class="text-end">Perc.</th>
											</tr>
										</thead>
										<tbody>
											<?php foreach($cupons as $item){ if ($item["qty"]){ ?>
											<tr>
												<td><?= $item["cupon"] ?></td>
												<td><?= $item["rule"] ?></td>
												<td class="text-center"><?= number_format($item["qty"]) ?></td>
												<td class="text-end"><?= number_format($item["amount"], 2) ?></td>
												<td class="text-end"><?= number_format($item["amount"] * 100 / $cupons["total"]["amount"], 2) ?>%</td>
											</tr>
											<?php }} ?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">GERP Orders</h5>
					<table class="table datatable align-middle">
						<thead>
							<tr>
								<th scope="col">Date</th>
								<th scope="col">Type</th>
								<th scope="col">Status</th>
								<th scope="col">Subsidiary</th>
								<th scope="col">Group</th>
								<th scope="col">Order</th>
								<th scope="col">Line</th>
								<th scope="col">Item Type</th>
								<th scope="col">Category</th>
								<th scope="col">Model/Product</th>
								<th scope="col">Currency</th>
								<th scope="col">U/Price</th>
								<th scope="col">Qty</th>
								<th scope="col">Amount</th>
								<th scope="col">USD</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($gerps as $g){ ?>
							<tr>
								<td><div class="text-nowrap"><?= $g->create_date ?></div></td>
								<td><?= $g->order_category ?></td>
								<td><div style="width: 90px;"><?= $g->line_status ?></div></td>
								<td><?= $g->customer_department ?></td>
								<td><?= $g->bill_to_name ?></td>
								<td><?= $g->order_no ?></td>
								<td><?= $g->line_no ?></td>
								<td><?= $g->item_type_desctiption ?></td>
								<td><?= $g->model_category ?></td>
								<td><?= $g->model."<br/>".str_replace("_", " ", $g->product_level4_name)."<br/>".$g->product_level1_name ?></td>
								<td><?= $g->currency ?></td>
								<td><?= number_format($g->unit_selling_price, 2) ?></td>
								<td><?= number_format($g->ordered_qty) ?></td>
								<td><?= number_format($g->sales_amount, 2) ?></td>
								<td><?= number_format($g->sales_amount / $exchange_rate, 2) ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Magento Orders</h5>
					<table class="table datatable align-middle">
						<thead>
							<tr>
								<th scope="col">Date</th>
								<th scope="col">Status</th>
								<th scope="col">Order</th>
								<th scope="col">Group</th>
								<th scope="col">Device</th>
								<th scope="col">Dept/Prov</th>
								<th scope="col">Company</th>
								<th scope="col">Cupon</th>
								<th scope="col">Discount</th>
								<th scope="col">Amount</th>
								<th scope="col">USD</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($magentos as $m){ $no_tax = $m->grand_total_purchased / 1.18; ?>
							<tr>
								<td><div style="width: 90px;"><?= $m->local_time ?></div></td>
								<td><?= ucwords(str_replace("_", " ", $m->status)) ?></td>
								<td><?= $m->gerp_order_no ?></td>
								<td><?= $m->customer_group ?></td>
								<td><?= $m->devices ?></td>
								<td><?= $m->department."/".$m->province ?></td>
								<td>
									<?php
									$aux = [];
									if ($m->company_name_through_vipkey) $aux[] = $m->company_name_through_vipkey;
									if ($m->vipkey) $aux[] = $m->vipkey;
									echo implode("<br/>", $aux);
									?>
								</td>
								<td>
									<?php
									$aux = [];
									if ($m->coupon_code) $aux[] = $m->coupon_code;
									if ($m->coupon_rule) $aux[] = $m->coupon_rule;
									echo implode("<br/>", $aux);
									?>
								</td>
								<td><?= $m->discount_amount ? number_format($m->discount_amount, 2) : "" ?></td>
								<td><?= $no_tax ? number_format($no_tax, 2) : "" ?></td>
								<td><?= $no_tax ? number_format($no_tax / $exchange_rate, 2) : "" ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
function set_status_chart(){
	var data = JSON.parse($("#status_chart_data").html());
	
	echarts.init(document.querySelector("#status_chart_amount")).setOption({
		title	: {text: 'By Order Amount', left: 'center'},
		tooltip	: {trigger: 'item'},
		//legend	: {orient: 'vertical', left: 'left'},
		series	: [{type: 'pie', data: data.amount, label: {show: false}, labelLine: {show: false}}],
	});
	
	echarts.init(document.querySelector("#status_chart_qty")).setOption({
		title	: {text: ' By Order Qty', left: 'center'},
		tooltip	: {trigger: 'item'},
		legend	: {orient: 'vertical', left: 'right'},
		series	: [{type: 'pie', data: data.qty, label: {show: false}, labelLine: {show: false}}],
	});
}

document.addEventListener("DOMContentLoaded", () => {
	$("#sl_by_week").on("change", function() {
		if ($(this).val() != "") $("#sl_by_month").val("");
	});
	
	$("#sl_by_month").on("change", function() {
		if ($(this).val() != "") $("#sl_by_week").val("");
	});
	
	$(".btn_bs").on("click", function() {
		var val = $(this).val();
		if ($(this).hasClass("btn-primary")){
			$(".bl_bs_" + val).addClass("d-none");
			
			$(this).removeClass("btn-primary")
			$(this).addClass("btn-outline-primary")
		}else{
			$(".bl_bs_" + val).removeClass("d-none");
			
			$(this).removeClass("btn-outline-primary")
			$(this).addClass("btn-primary")
			
		}
		
	});
	
	
	
	
	//set_status_chart();
	
	$("#report_from").on( "change", function() {
		$("#report_to").attr("min", $(this).val());
	});
	
	$("#report_to").on( "change", function() {
		$("#report_from").attr("max", $(this).val());
	});
	
	$("#form_upload_magento").submit(function(e) {
		e.preventDefault();
		$("#form_upload_magento .sys_msg").html("");
		ajax_form_warning(this, "module/obs_magento/upload", "Do you upload data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/obs_magento");
		});
	});
});
</script>