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
	<form class="m-0">
		<div class="input-group">
			<input type="date" class="form-control" id="report_from" value="<?= $from ?>" name="f" placeholder="From" max="<?= $to ?>">
			<span class="input-group-text">~</span>
			<input type="date" class="form-control" id="report_to" value="<?= $to ?>" name="t" placeholder="To" min="<?= $from ?>">
			<button type="submit" class="btn btn-primary">Submit</button>
		</div>
	</form>
</div>					
<section class="section">
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
							</tr>
						</thead>
						<tbody>
							<?php foreach($gerps as $g){ $lvl4 = substr($g->product_level4_code, 0, 2); ?>
							<tr>
								<td><div class="text-nowrap"><?= $g->create_date ?></div></td>
								<td><?= $g->order_category ?></td>
								<td><div style="width: 90px;"><?= $g->line_status ?></div></td>
								<td><?= $g->customer_department ?></td>
								<td><?= $g->bill_to_name ?></td>
								<td><?= $g->order_no ?></td>
								<td><?= $g->line_no ?></td>
								<td><?= $g->item_type_desctiption ?></td>
								<td><?= $g->model_category ? $g->model_category : (array_key_exists($lvl4, $mc_map) ? $mc_map[$lvl4] : null) ?></td>
								<td><?= $g->model."<br/>".str_replace("_", " ", $g->product_level4_name) ?></td>
								<td><?= $g->currency ?></td>
								<td><?= number_format($g->unit_selling_price, 2) ?></td>
								<td><?= number_format($g->ordered_qty) ?></td>
								<td><?= number_format($g->sales_amount, 2) ?></td>
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
					<h5 class="card-title">Sales Records</h5>
					<div class="table-responsive">
						<table class="table datatable align-middle">
							<thead>
								<tr>
									<th scope="col">Sale</th>
									<th scope="col">Purchase</th>
									<th scope="col">Status</th>
									<th scope="col">Cus. Group</th>
									<th scope="col">Coupon</th>
									<th scope="col">SKU</th>
									<th scope="col">Qty</th>
									<th scope="col"><div class="text-end">Total, USD</div></th>
									<th scope="col"><div class="text-end">Total, PEN</div></th>
									<th scope="col"><div class="text-end">Discount, PEN</div></th>
									<!--
									<th scope="col">obs_magento_id</th>
									<th scope="col">magento_id</th>
									<th scope="col">grand_total_base</th>
									<th scope="col">shipping_address</th>
									<th scope="col">shipping_and_handling</th>
									<th scope="col">customer_name</th>
									<th scope="col">sku</th>
									<th scope="col">level_1_code</th>
									<th scope="col">level_2_code</th>
									<th scope="col">level_3_code</th>
									<th scope="col">level_4_code</th>
									<th scope="col">gerp_type</th>
									<th scope="col">gerp_order_no</th>
									<th scope="col">warehouse_code</th>
									<th scope="col">sku_price</th>
									<th scope="col">company_name_through_vipkey</th>
									<th scope="col">vipkey</th>
									<th scope="col">pre_order</th>
									<th scope="col">error_code</th>
									<th scope="col">price_source</th>
									<th scope="col">coupon_code</th>
									<th scope="col">devices</th>
									<th scope="col">knout_status</th>
									<th scope="col">payment_method</th>
									<th scope="col">error_status</th>
									<th scope="col">opt_in_status</th>
									<th scope="col">gerp_selling_price</th>
									<th scope="col">ip_address</th>
									<th scope="col">sale_channel</th>
									<th scope="col">is_export_order_to_gerp</th>
									<th scope="col">sku_without_prefix_and_suffix</th>
									<th scope="col">zipcode</th>
									<th scope="col">department</th>
									<th scope="col">province</th>
									<th scope="col">updated</th>
									<th scope="col">registered</th>
									-->
								</tr>
							</thead>
							<tbody>
								<?php foreach($sales as $i => $sale){ ?>
								<tr>
									<td><?= $sale->local_time ?></td>
									<td><?= $sale->purchase_date ?></td>
									<td><?= ucfirst(str_replace("_", " ", $sale->status)) ?></td>
									<td><?= $sale->customer_group ?></td>
									<td>
										<?php
										$cupon_aux = [];
										if ($sale->company_name_through_vipkey) $cupon_aux[] = $sale->company_name_through_vipkey;
										if ($sale->vipkey) $cupon_aux[] = $sale->vipkey;
										if ($sale->coupon_rule) $cupon_aux[] = $sale->coupon_rule;
										?>
										<?= implode("<br/>", $cupon_aux) ?>
									</td>
									<td><?= str_replace("**", "<br/>", $sale->sku_without_prefix) ?></td>
									<td><?= number_format($sale->qty_ordered) ?></td>
									<td><div class="text-end"><?= number_format($sale->grand_total_purchased / $exchange_rate, 2) ?></div></td>
									<td><div class="text-end"><?= number_format($sale->grand_total_purchased, 2) ?></div></td>
									<td><div class="text-end"><?= abs($sale->discount_amount) > 0 ? number_format($sale->discount_amount, 2) : "" ?></div></td>
									<!--
									<td><?= $sale->obs_magento_id ?></td>
									<td><?= $sale->magento_id ?></td>
									<td><?= $sale->grand_total_base ?></td>
									<td><?= $sale->shipping_address ?></td>
									<td><?= $sale->shipping_and_handling ?></td>
									<td><?= $sale->customer_name ?></td>
									<td><?= $sale->sku ?></td>
									<td><?= $sale->level_1_code ?></td>
									<td><?= $sale->level_2_code ?></td>
									<td><?= $sale->level_3_code ?></td>
									<td><?= $sale->level_4_code ?></td>
									<td><?= $sale->gerp_type ?></td>
									<td><?= $sale->gerp_order_no ?></td>
									<td><?= $sale->warehouse_code ?></td>
									<td><?= $sale->sku_price ?></td>
									<td><?= $sale->company_name_through_vipkey ?></td>
									<td><?= $sale->vipkey ?></td>
									<td><?= $sale->pre_order ?></td>
									<td><?= $sale->error_code ?></td>
									<td><?= $sale->price_source ?></td>
									<td><?= $sale->coupon_code ?></td>
									<td><?= $sale->devices ?></td>
									<td><?= $sale->knout_status ?></td>
									<td><?= $sale->payment_method ?></td>
									<td><?= $sale->error_status ?></td>
									<td><?= $sale->opt_in_status ?></td>
									<td><?= $sale->gerp_selling_price ?></td>
									<td><?= $sale->ip_address ?></td>
									<td><?= $sale->sale_channel ?></td>
									<td><?= $sale->is_export_order_to_gerp ?></td>
									<td><?= $sale->sku_without_prefix_and_suffix ?></td>
									<td><?= $sale->zipcode ?></td>
									<td><?= $sale->department ?></td>
									<td><?= $sale->province ?></td>
									<td><?= $sale->updated ?></td>
									<td><?= $sale->registered ?></td>
									-->
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