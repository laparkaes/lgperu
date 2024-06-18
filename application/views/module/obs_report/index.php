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
	<form class="d-flex justify-content-end m-0">
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
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<div class="mb-3">
				<a class="btn btn-primary" target="blank_" href="<?= base_url() ?>module/obs_report/progress/w">Last 12 Weeks</a>
				<a class="btn btn-primary" target="blank_" href="<?= base_url() ?>module/obs_report/progress/m">Last 12 Months</a>
			</div>
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
								<th scope="col" style="width: 150px;">Subsidiary</th>
								<th scope="col" style="width: 150px;">Division</th>
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
	$("#sl_by_week").on( "change", function() {
		if ($(this).val() != "") $("#sl_by_month").val("");
	});
	
	$("#sl_by_month").on( "change", function() {
		if ($(this).val() != "") $("#sl_by_week").val("");
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