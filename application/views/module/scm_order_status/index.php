<section class="section">
	<div class="row">
		<div class="col-12">
			<div class="d-flex justify-content-between align-items-start">
				<div class="pagetitle">
					<h1>SCM - Order Status</h1>
					<nav>
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
							<li class="breadcrumb-item active">SCM - Order Status</li>
						</ol>
					</nav>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Order List</h5>
					<?php print_r($sales[0]); ?>
					<div><?= number_format(count($sales)) ?> records</div>
					<table class="table">
						<thead>
							<tr>
								<th scope="col">OM</th>
								<th scope="col">Remark</th>
								<th scope="col">Status</th>
								<th scope="col">Order</th>
								<th scope="col">Line</th>
								<th scope="col">Customer</th>
								<th scope="col">Model</th>
								<th scope="col">Qty</th>
								<th scope="col">Instock</th>
								<th scope="col">Hold</th>
								<th scope="col">Reason</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($sales as $item){ /* if (!in_array($item->so_status, ["AWAITING_FULFILLMENT"])){ */ ?>
							<tr>
								<td>
									<select class="form-select" name="line_status_detail" sales_order_id="<?= $item->sales_order_id ?>">
										<option value="">---</option>
										<?php foreach($line_status_detail_list as $op){ ?>
										<option value="<?= $op ?>" <?= trim($item->line_status_detail) === $op ? "selected" : "" ?>><?= $op ?></option>
										<?php } ?>
									</select>
									<input type="date" class="form-control">
									<div class="input-group">
										<input type="time" class="form-control">
									</div>
								</td>
								<td><textarea class="form-control"></textarea></td>
								<td><?= str_replace("_", " ", $item->so_status) ?></td>
								<td><?= $item->order_no ?></td>
								<td><?= $item->line_no ?></td>
								<td><?= $item->bill_to_name ?></td>
								<td><?= $item->dash_company ?><br/><?= $item->dash_division ?><br/><?= $item->model ?></td>
								<td><?= $item->ordered_qty ?></td>
								<td><?= $item->instock_flag === "Y" ? $item->instock_flag : "" ?></td>
								<td><?= $item->hold_flag === "Y" ? $item->hold_flag : "" ?></td>
								<td>
								<?php 
								$aux = [];
								if ($item->back_order_hold === "Y") $aux[] = "BACK ORDER";
								if ($item->credit_hold === "Y") $aux[] = "CREDIT";
								if ($item->overdue_hold === "Y") $aux[] = "OVERDUE";
								if ($item->customer_hold === "Y") $aux[] = "CUSTOMER";
								if ($item->payterm_term_hold === "Y") $aux[] = "PAYTERM TERM";
								if ($item->fp_hold === "Y") $aux[] = "FP";
								if ($item->minimum_hold === "Y") $aux[] = "MINIMUM";
								if ($item->future_hold === "Y") $aux[] = "FUTURE";
								if ($item->reserve_hold === "Y") $aux[] = "RESERVE";
								if ($item->manual_hold === "Y") $aux[] = "MANUAL";
								if ($item->auto_pending_hold === "Y") $aux[] = "AUTO PENDING";
								if ($item->sa_hold === "Y") $aux[] = "SA";
								if ($item->form_hold === "Y") $aux[] = "FORM";
								if ($item->bank_collateral_hold === "Y") $aux[] = "BANK COLLATERAL";
								if ($item->insurance_hold === "Y") $aux[] = "INSURANCE";
								if ($item->partial_flag === "Y") $aux[] = "PARTIAL";
								if ($item->load_hold_flag === "Y") $aux[] = "LOAD";
								
								echo implode("<br/>", $aux);
								?>
								</td>
							</tr>
							<?php /* } */ } ?>
						</tbody>
					</table>
					
					
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload").submit(function(e) {
		e.preventDefault();
		$("#form_upload .sys_msg").html("");
		ajax_form_warning(this, "module/scm_sku_management/upload", "Do you upload data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/scm_sku_management");
		});
	});
});
</script>