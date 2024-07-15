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
		<div class="col-md-10 text-start">
			<div class="mb-3">
				<a class="btn btn-primary" target="blank_" href="<?= base_url() ?>module/obs_report/progress/12">Monthly Progress</a>
			</div>
		</div>
		<div class="col-md-2 text-end">
			<form class="d-flex justify-content-end mb-3">
				<select class="form-select ms-1" id="sl_by_month" name="m">
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
						<h5 class="card-title me-3">OBS Dashboard, <?= $from." ~ ".$to ?></h5>
						<h5 class="card-title"><strong>ER: <?= $this->exchange_rate ?> / K USD</strong></h5>
					</div>
					<table class="table align-middle text-center">
						<thead>
							<tr>
								<th scope="col">Subsidiary</th>
								<th scope="col">Division</th>
								<th scope="col" class="border-end">Category</th>
								<th scope="col" style="width: 200px;">Target</th>
								<th scope="col" style="width: 120px;" class="border-end">%</th>
								<th scope="col" style="width: 200px;">ML Actual</th>
								<th scope="col" style="width: 120px;" class="border-end">%</th>
								<th scope="col" style="width: 200px;" class="border-end">Closed</th>
								<th scope="col" style="width: 200px;">M-1</th>
								<th scope="col" style="width: 200px;">Reserved</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($dashboard as $dash){ if($dash["closed"]){ ?>
							<tr>
								<td><?= $dash["sub"] ?></td>
								<td><?= $dash["div"] ? $this->dash_company[$dash["div"]] : "" ?></td>
								<td class="border-end"><?= $dash["cat"] ? $this->dash_division[$dash["cat"]] : "" ?></td>
								<td><?= $dash["target"] ? number_format($dash["target"], 2) : "-" ?></td>
								<td class="border-end text-<?= $dash["target_per"] ? $dash["target_color"] : "" ?>"><?= $dash["target_per"] ? number_format($dash["target_per"], 2)."%" : "-" ?></td>
								<td><?= $dash["ml_actual"] ? number_format($dash["ml_actual"], 2) : "-" ?></td>
								<td class="border-end text-<?= $dash["ml_per"] ? $dash["ml_color"] : "" ?>"><?= $dash["ml_per"] ? number_format($dash["ml_per"], 2)."%" : "-" ?></td>
								<td class="border-end"><?= $dash["closed"] ? number_format($dash["closed"], 2) : "-" ?></td>
								<td><?= $dash["m-1"] ? number_format($dash["m-1"], 2) : "-" ?></td>
								<td><?= $dash["reserved"] ? number_format($dash["reserved"], 2) : "-" ?></td>
							</tr>
							<?php }} ?>
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
					<h5 class="card-title">Statistics</h5>
					<div class="row">
						<div class="col-12">
						<?php
						$days = $statistics["days"];
						$dates_between = $statistics["dates_between"];
						$purchase = $statistics["purchase"];
						$purchase_qty = $statistics["purchase_qty"];
						$purchase_amount = $statistics["purchase_amount"];
						$closed = $statistics["closed"];
						$closed_qty = $statistics["closed_qty"];
						$closed_amount = $statistics["closed_amount"];
						$cus_group = $statistics["cus_group"];
						$devices = $statistics["devices"];
						$cupons = $statistics["cupons"];
						$d2b2c = $statistics["d2b2c"];
						$models = $statistics["models"];
						$departments = $statistics["departments"];
						?>
						</div>
						<div class="col-md-12">
							<div class="d-none">
								<div id="chart_purchase_xaxis"><?= json_encode($days) ?></div>
								<div id="chart_purchase_amount_4"><?= json_encode($purchase_amount[4]) ?></div>
								<div id="chart_purchase_amount_8"><?= json_encode($purchase_amount[8]) ?></div>
								<div id="chart_purchase_amount_12"><?= json_encode($purchase_amount[12]) ?></div>
								<div id="chart_purchase_amount_16"><?= json_encode($purchase_amount[16]) ?></div>
								<div id="chart_purchase_amount_20"><?= json_encode($purchase_amount[20]) ?></div>
								<div id="chart_purchase_amount_24"><?= json_encode($purchase_amount[24]) ?></div>
								<div id="chart_purchase_amount_total"><?= json_encode($purchase_amount["total"]) ?></div>
								<div id="chart_closed_amount"><?= json_encode($closed_amount) ?></div>
								<div id="chart_purchase_qty_4"><?= json_encode($purchase_qty[4]) ?></div>
								<div id="chart_purchase_qty_8"><?= json_encode($purchase_qty[8]) ?></div>
								<div id="chart_purchase_qty_12"><?= json_encode($purchase_qty[12]) ?></div>
								<div id="chart_purchase_qty_16"><?= json_encode($purchase_qty[16]) ?></div>
								<div id="chart_purchase_qty_20"><?= json_encode($purchase_qty[20]) ?></div>
								<div id="chart_purchase_qty_24"><?= json_encode($purchase_qty[24]) ?></div>
								<div id="chart_purchase_qty_total"><?= json_encode($purchase_qty["total"]) ?></div>
								<div id="chart_closed_qty"><?= json_encode($closed_qty) ?></div>
							</div>
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Daily Purchase Amount by Hour Range & IOD</h5>
									<div id="chart_purchase_amount" style="min-height: 400px;"></div>
								</div>
							</div>
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Daily Purchase Qty by Hour Range & IOD</h5>
									<div id="chart_purchase_qty" style="min-height: 400px;"></div>
								</div>
							</div>
						</div>
						<div class="col-md-12">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Best Seller</h5>
									<table class="table datatable align-middle">
										<thead>
											<tr>
												<th scope="col">Company</th>
												<th scope="col">Division</th>
												<th scope="col">Category</th>
												<th scope="col">Model</th>
												<th scope="col">Product Level 1</th>
												<th scope="col">Product Level 4</th>
												<th scope="col">Qty</th>
												<th scope="col"><div class="text-end">Amount (USD)</div></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach($models as $item){ ?>
											<tr>
												<td><?= $this->dash_company[$item["company"]] ?></td>
												<td><?= $this->dash_division[$item["division"]] ?></td>
												<td><?= $item["model_category"] ?></td>
												<td><?= $item["model"] ?></td>
												<td><?= $item["product_level1_name"] ?></td>
												<td><?= $item["product_level4_name"] ?></td>
												<td><?= number_format($item["qty"]) ?></td>
												<td><div class="text-end"><?= number_format($item["amount"], 2) ?></div></td>
											</tr>
											<?php } ?>
										</tbody>
									</table>
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
															<td class="text-end"><?= number_format($item["amount"]/1000, 2) ?></td>
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
															<td class="text-end"><?= number_format($item["amount"]/1000, 2) ?></td>
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
						<div class="col-md-4">
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
											<?php foreach($departments as $item){ ?>
											<tr>
												<td><?= $item["department"] ?></td>
												<td></td>
												<td class="text-center"><?= number_format($item["qty"]) ?></td>
												<td class="text-end"><?= number_format($item["amount"]/1000, 2) ?></td>
												<td class="text-end"><?= number_format($item["amount"] * 100 / $departments["total"]["amount"], 2) ?>%</td>
											</tr>
											<?php foreach($item["provinces"] as $pro){ ?>
											<tr>
												<td></td>
												<td><?= $pro["province"] ?></td>
												<td class="text-center"><?= number_format($pro["qty"]) ?></td>
												<td class="text-end"><?= number_format($pro["amount"]/1000, 2) ?></td>
												<td class="text-end"><?= number_format($pro["amount"] * 100 / $departments["total"]["amount"], 2) ?>%</td>
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
												<td class="text-end"><?= number_format($item["amount"]/1000, 2) ?></td>
												<td class="text-end"><?= number_format($item["amount"] * 100 / $d2b2c["total"]["amount"], 2) ?>%</td>
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
												<td><?= $item["code"] ?></td>
												<td><div class="text-truncate" style="width: 150px;"><?= $item["rule"] ?></div></td>
												<td class="text-center"><?= number_format($item["qty"]) ?></td>
												<td class="text-end"><?= number_format($item["amount"]/1000, 2) ?></td>
												<td class="text-end"><?= number_format($item["per"], 2) ?>%</td>
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
					<h5 class="card-title me-3">Rowdatas</h5>
					<table class="table datatable align-middle">
						<thead>
							<tr>
								<th scope="col">Created</th>
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
								<td><div class="text-nowrap"><?= $g->create_date ?><?= $g->local_time ? "<br/>".explode(" ", $g->local_time)[1] : "" ?></div></td>
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
								<td><?= number_format($g->sales_amount_usd, 2) ?></td>
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
	//chart_purchase_amount
	echarts.init(document.querySelector("#chart_purchase_amount")).setOption({
		legend: {data: ['~4 Hr', '~8 Hr', '~12 Hr', '~16 Hr', '~20 Hr', '~24 Hr', 'Total', 'IOD']},
		tooltip: {trigger: 'axis', axisPointer: {type: 'cross', label: {backgroundColor: '#6a7985'}}},
		grid: {left: '30px', right: '30px', top: '0%', bottom: '3%', containLabel: true},
		xAxis: [{type: 'category', data: JSON.parse($("#chart_purchase_xaxis").html())}],
		yAxis: [{show: false, type: 'value'}],
		series: [
			{name: '~4 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_amount_4").html()), barGap: 0},
			{name: '~8 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_amount_8").html())},
			{name: '~12 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_amount_12").html())},
			{name: '~16 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_amount_16").html())},
			{name: '~20 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_amount_20").html())},
			{name: '~24 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_amount_24").html())},
			{name: 'Total', barWidth: 5, type: 'bar', stack: 'total', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_amount_total").html())},
			{name: 'IOD', type: 'line', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_closed_amount").html())},
		]
	});
	
	//chart_purchase_qty
	echarts.init(document.querySelector("#chart_purchase_qty")).setOption({
		legend: {data: ['~4 Hr', '~8 Hr', '~12 Hr', '~16 Hr', '~20 Hr', '~24 Hr', 'Total', 'IOD']},
		tooltip: {trigger: 'axis', axisPointer: {type: 'cross', label: {backgroundColor: '#6a7985'}}},
		grid: {left: '30px', right: '30px', top: '0%', bottom: '3%', containLabel: true},
		xAxis: [{type: 'category', data: JSON.parse($("#chart_purchase_xaxis").html())}],
		yAxis: [{show: false, type: 'value'}],
		series: [
			{name: '~4 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_qty_4").html()), barGap: 0},
			{name: '~8 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_qty_8").html())},
			{name: '~12 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_qty_12").html())},
			{name: '~16 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_qty_16").html())},
			{name: '~20 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_qty_20").html())},
			{name: '~24 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_qty_24").html())},
			{name: 'Total', barWidth: 5, type: 'bar', stack: 'total', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_qty_total").html())},
			{name: 'IOD', type: 'line', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_closed_qty").html())},
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