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
								<th scope="col">Action</th>
								<th scope="col">Order Mng</th>
								<th scope="col">Order</th>
								<th scope="col">Status</th>
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
									<button type="button" class="btn btn-primary btn_om_update" data-bs-toggle="modal" data-bs-target="#md_om_mng" value="<?= $item->sales_order_id ?>">Update</button>
								</td>
								<td>
									<?php
									$aux = [];
									if ($item->om_line_status) $aux[] = $item->om_line_status;
									if ($item->om_appointment) $aux[] = $item->om_appointment;
									if ($item->om_appointment_remark) $aux[] = $item->om_appointment_remark;
									
									echo implode("<br/>", $aux);
									?>
								</td>
								<td><?= $item->order_no ?><br/><?= $item->line_no ?></td>
								<td><?= str_replace("_", " ", $item->so_status) ?></td>
								<td><?= $item->bill_to_name ?></td>
								<td><?= $item->model ?><br/><?= $item->dash_division ?><br/><?= $item->dash_company ?></td>
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

<div class="modal fade" id="md_om_mng" tabindex="-1" style="display: none;" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Basic Modal</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
			Non omnis incidunt qui sed occaecati magni asperiores est mollitia. Soluta at et reprehenderit. Placeat autem numquam et fuga numquam. Tempora in facere consequatur sit dolor ipsum. Consequatur nemo amet incidunt est facilis. Dolorem neque recusandae quo sit molestias sint dignissimos.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Save changes</button>
			</div>
		</div>
	</div>
</div>

<script>
function update_appointment_om(field, order_id, val){
	
	alert(field + ' ' + order_id + ' ' + val);
}

document.addEventListener("DOMContentLoaded", () => {
	$('.sl_line_status_om').change(function() {
		var val = $(this).val();
		var order_id = $(this).attr("order_id");
		
		update_appointment_om("line_status_om", order_id, val);
	});
	
	$('.in_appointment_om_date').focusout(function() {
        var val = $(this).val();
		var order_id = $(this).attr("order_id");
        
		update_appointment_om("appointment_om_date", order_id, val);
    });
	
	$('.in_appointment_om_time').focusout(function() {
        var val = $(this).val();
		var order_id = $(this).attr("order_id");
        
		update_appointment_om("appointment_om_time", order_id, val);
    });
	
	$('.tx_appointment_remark').focusout(function() {
        var val = $(this).val();
		var order_id = $(this).attr("order_id");
        
		update_appointment_om("appointment_remark", order_id, val);
    });
	
	/*
	sl_line_status_om
	in_appointment_om_date
	in_appointment_om_time
	tx_appointment_remark
	*/
	
	$('.btn_om_update').click(function() {
        var val = $(this).val();
        
		$.ajax({
			url: base_url + "module/scm_order_status/load_sales_order",
			type: "POST",
			data: {order_id: val},
			success:function(res){
				console.log(res);
			}
		});
		//alert(val);
		//update_appointment_om("appointment_remark", order_id, val);
    });
	
	$("#form_upload").submit(function(e) {
		e.preventDefault();
		$("#form_upload .sys_msg").html("");
		ajax_form_warning(this, "module/scm_sku_management/upload", "Do you upload data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/scm_sku_management");
		});
	});
});
</script>