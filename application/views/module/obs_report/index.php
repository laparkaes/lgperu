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
				<a class="btn btn-primary" target="blank_" href="<?= base_url() ?>module/obs_report/progress/w/12">Weekly Progress</a>
				<a class="btn btn-primary" target="blank_" href="<?= base_url() ?>module/obs_report/progress/m/12">Monthly Progress</a>
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
						<div class="d-flex justify-content-start align-items-center">
							<h5 class="card-title me-3">OBS Dashboard, <?= $from." ~ ".$to ?></h5>
							<span class="badge bg-success me-3">GERP IOD</span>
							<span class="badge bg-primary">ER: <?= $this->exchange_rate ?></span>
						</div>
						<h5 class="card-title"><strong>K USD</strong></h5>
					</div>
					<table class="table align-middle text-center">
						<thead>
							<tr>
								<th scope="col">Subsidiary</th>
								<th scope="col">Division</th>
								<th scope="col" class="border-end">Category</th>
								<th scope="col">Monthly Report</th>
								<th scope="col">ML</th>
								<th scope="col" class="border-end">ML Actual</th>
								<th scope="col" style="width: 180px;">Projection</th>
								<th scope="col" class="border-end">%</th>
								<th scope="col" style="width: 180px;">Sales IOD</th>
								<th scope="col" class="border-end">%</th>
								<th scope="col">Reserved</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($dashboard as $dash){ ?>
							<tr>
								<td><?= $dash["sub"] ?></td>
								<td><?= $dash["div"] ?></td>
								<td class="border-end"><?= $dash["cat"] ?></td>
								<td><?= $dash["monthly_report"] ? number_format($dash["monthly_report"], 2) : "-" ?></td>
								<td><?= $dash["ml"] ? number_format($dash["ml"], 2) : "-" ?></td>
								<td class="border-end"><?= $dash["ml_actual"] ? number_format($dash["ml_actual"], 2) : "-" ?></td>
								<td><?= $dash["projection"] ? number_format($dash["projection"], 2) : "-" ?></td>
								<td class="border-end text-<?= $dash["projection_per"] ? $dash["projection_color"] : "" ?>"><?= $dash["projection_per"] ? number_format($dash["projection_per"], 2)."%" : "-" ?></td>
								<td><?= $dash["actual"] ? number_format($dash["actual"], 2) : "-" ?></td>
								<td class="border-end text-<?= $dash["actual_per"] ? $dash["actual_color"] : "" ?>"><?= $dash["actual_per"] ? number_format($dash["actual_per"], 2)."%" : "-" ?></td>
								<td><?= $dash["expected"] ? number_format($dash["expected"], 2) : "-" ?></td>
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
					<div class="d-flex justify-content-start align-items-center">
						<h5 class="card-title me-3">Best Seller</h5>
						<span class="badge bg-success">GERP IOD</span>
					</div>
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
						<div class="col-md-4 col-sm-6 bl_bs_<?= $div ?>">
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
					<div class="d-flex justify-content-start align-items-center">
						<h5 class="card-title me-3">Statistics</h5>
						<span class="badge bg-secondary">Magento Sale</span>
					</div>
					<div class="row">
						<div class="col-12">
						<?php
						$dates_between = $statistics["dates_between"];
						$daily = $statistics["daily"];
						$cus_group = $statistics["cus_group"];
						$devices = $statistics["devices"];
						$d2b2c = $statistics["d2b2c"];
						$cupons = $statistics["cupons"];
						$departments = $statistics["departments"];
						?>
						</div>
						<div class="col-md-12">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Daily</h5>
									<div id="chart_daily" style="min-height: 500px;"></div>
									<?php 
									$chart_daily_values = [4 => [], 8 => [], 12 => [], 16 => [], 20 => [], 24 => []];
									$chart_daily_xaxis = [];
									$total = []; 
									foreach($dates_between as $date){ $day = date("d", strtotime($date));
										$total[$day] = 0;
										$chart_daily_xaxis[] = $day;
									}
									?>
									<table class="table text-center" style="font-size: 12px;">
										<thead>
											<tr>
												<th scope="col" class="text-start" style="width: 90px;">K USD<br/>Hour\Day</th>
												<?php foreach($dates_between as $date){ $day = date("d", strtotime($date)); ?>
												<th scope="col"><?= $day ?></th>
												<?php } ?>
											</tr>
										</thead>
										<tbody>
											<?php for($i = 1; $i <= 6; $i++){ $time = $i * 4; ?>
											<tr>
												<td class="text-start">~ <?= $time ?> Hr</td>
												<?php foreach($dates_between as $date){ $day = date("d", strtotime($date));
													$total[$day] += $daily[$day][$time]["amount"];
													$chart_daily_values[$time][] = (date("d", strtotime($date)) <= date("d")) ? round($daily[$day][$time]["amount"], 2) : null;
												?>
												<td><?= $daily[$day][$time]["amount"] ? number_format($daily[$day][$time]["amount"], 2) : "-" ?></td>
												<?php } ?>
											</tr>
											<?php } ?>
											<tr>
												<th class="text-start">Total</th>
												<?php foreach($dates_between as $date){ $day = date("d", strtotime($date)); ?>
												<th><?= $total[$day] ? number_format($total[$day], 2) : "0.00" ?></th>
												<?php } ?>
											</tr>
										</tbody>
									</table>
									<div class="d-none">
										<div id="chart_daily_xaxis"><?= json_encode($chart_daily_xaxis) ?></div>
										<div id="chart_daily_4"><?= json_encode($chart_daily_values[4]) ?></div>
										<div id="chart_daily_8"><?= json_encode($chart_daily_values[8]) ?></div>
										<div id="chart_daily_12"><?= json_encode($chart_daily_values[12]) ?></div>
										<div id="chart_daily_16"><?= json_encode($chart_daily_values[16]) ?></div>
										<div id="chart_daily_20"><?= json_encode($chart_daily_values[20]) ?></div>
										<div id="chart_daily_24"><?= json_encode($chart_daily_values[24]) ?></div>
									</div>
								</div>
							</div>
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
															<th scope="col" class="text-end">%</th>
														</tr>
													</thead>
													<tbody>
														<?php 
														$chart_cus_group = [];
														foreach($cus_group as $item){ 
															if ($item["qty"]){
																if ($item["customer_group"] !== "Total") 
																	$chart_cus_group[] = ["value" => round($item["amount"], 2), "name" => $item["customer_group"]];
														?>
														<tr>
															<td><?= $item["customer_group"] ?></td>
															<td class="text-center"><?= number_format($item["qty"]) ?></td>
															<td class="text-end"><?= number_format($item["amount"], 2) ?></td>
															<td class="text-end"><?= number_format($item["amount"] * 100 / $cus_group["total"]["amount"], 2) ?>%</td>
														</tr>
														<?php }} ?>
													</tbody>
												</table>
												<div class="d-none" id="chart_cus_group_data"><?= json_encode($chart_cus_group) ?></div>
											</div>
										</div>
										<div class="col-md-6" id="chart_cus_group" style="min-height: 300px;"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Device</h5>
									<div class="row">
										<div class="col-md-6">
											<div class="overflow-auto">
												<table class="table">
													<thead>
														<tr>
															<th scope="col">Device</th>
															<th scope="col" class="text-center">Qty</th>
															<th scope="col" class="text-end text-nowrap">K USD</th>
															<th scope="col" class="text-end">%</th>
														</tr>
													</thead>
													<tbody>
														<?php 
														$chart_device = [];
														foreach($devices as $item){ 
															if ($item["qty"]){
																if ($item["device"] !== "Total") 
																	$chart_device[] = ["value" => round($item["amount"], 2), "name" => $item["device"]];
																
														?>
														<tr>
															<td><?= $item["device"] ?></td>
															<td class="text-center"><?= number_format($item["qty"]) ?></td>
															<td class="text-end"><?= number_format($item["amount"], 2) ?></td>
															<td class="text-end"><?= number_format($item["amount"] * 100 / $devices["total"]["amount"], 2) ?>%</td>
														</tr>
														<?php }} ?>
													</tbody>
												</table>
												<div class="d-none" id="chart_device_data"><?= json_encode($chart_device) ?></div>
											</div>
										</div>
										<div class="col-md-6" id="chart_device" style="min-height: 300px;"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Location</h5>
									<table class="table datatable align-middle">
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
						<div class="col-md-6">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">D2B2C</h5>
									<table class="table datatable align-middle">
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
						<div class="col-md-12">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Cupon</h5>
									<table class="table datatable align-middle">
										<thead>
											<tr>
												<th scope="col">Cupon</th>
												<th scope="col">Rule</th>
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
					<div class="d-flex justify-content-start align-items-center">
						<h5 class="card-title me-3">GERP Orders</h5>
						<span class="badge bg-success">GERP IOD</span>
					</div>
					<table class="table datatable align-middle">
						<thead>
							<tr>
								<th scope="col">Date</th>
								<th scope="col">Closed</th>
								<th scope="col">Type</th>
								<th scope="col">Status</th>
								<th scope="col">Delivery</th>
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
								<td><div class="text-nowrap"><?= $g->close_date ?></div></td>
								<td><?= $g->order_category ?></td>
								<td><div style="max-width: 90px;"><?= $g->line_status ?></div></td>
								<td><?= $g->delivery ?></td>
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
					<div class="d-flex justify-content-start align-items-center">
						<h5 class="card-title me-3">Magento Orders</h5>
						<span class="badge bg-secondary">Magento Sale</span>
					</div>
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
function set_charts(){
	//chart_daily
	echarts.init(document.querySelector("#chart_daily")).setOption({
		tooltip: {trigger: 'axis', axisPointer: {type: 'cross', label: {backgroundColor: '#6a7985'}}},
		grid: {left: '95px', right: '25px', bottom: '3%', containLabel: true},
		xAxis: [{type: 'category', boundaryGap: false, data: JSON.parse($("#chart_daily_xaxis").html())}],
		yAxis: [{type: 'value'}],
		series: [
			{name: '~4 Hr', smooth: true, showSymbol: false, type: 'line', stack: 'Total', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_daily_4").html())},
			{name: '~8 Hr', smooth: true, showSymbol: false, type: 'line', stack: 'Total', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_daily_8").html())},
			{name: '~12 Hr', smooth: true, showSymbol: false, type: 'line', stack: 'Total', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_daily_12").html())},
			{name: '~16 Hr', smooth: true, showSymbol: false, type: 'line', stack: 'Total', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_daily_16").html())},
			{name: '~20 Hr', smooth: true, showSymbol: false, type: 'line', stack: 'Total', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_daily_20").html())},
			{name: '~24 Hr', smooth: true, showSymbol: false, type: 'line', stack: 'Total', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_daily_24").html())},
		]
	});
	
	//chart_cus_group
	echarts.init(document.querySelector("#chart_cus_group")).setOption({
		tooltip: {trigger: 'item'},
		series: [{name: 'Customer Group', type: 'pie', radius: '90%', data: JSON.parse($("#chart_cus_group_data").html()),}]
	});
	
	//chart_device
	echarts.init(document.querySelector("#chart_device")).setOption({
		tooltip: {trigger: 'item'},
		series: [{name: 'Device', type: 'pie', radius: '90%', data: JSON.parse($("#chart_device_data").html()),}]
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
	
	set_charts();
});
</script>