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
					<h5 class="card-title">Filter</h5>
					<form class="row g-3">
						<div class="col-md-2">
							<label class="form-label">Company</label>
							<select class="form-select" name="f_company">
								<option value="">All</option>
								<?php foreach($company_list as $item){ if ($item->dash_company){ ?>
								<option value="<?= $item->dash_company ?>" <?= $filter["f_company"] === $item->dash_company ? "selected" : "" ?>><?= $item->dash_company ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-md-2">
							<label class="form-label">Division</label>
							<select class="form-select" name="f_division">
								<option value="">All</option>
								<?php foreach($division_list as $item){ if ($item->dash_division){ ?>
								<option value="<?= $item->dash_division ?>" <?= $filter["f_division"] === $item->dash_division ? "selected" : "" ?>><?= $item->dash_division ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-md-2">
							<label class="form-label">OM Status</label>
							<select class="form-select" name="f_om_status">
								<option value="">All</option>
								<?php foreach($om_status_list as $item){ ?>
								<option value="<?= $item ?>" <?= $filter["f_om_status"] === $item ? "selected" : "" ?>><?= $item ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-2">
							<label class="form-label">SO Status</label>
							<select class="form-select" name="f_so_status">
								<option value="">All</option>
								<?php foreach($so_status_list as $item){ ?>
								<option value="<?= $item->so_status ?>" <?= $filter["f_so_status"] === $item->so_status ? "selected" : "" ?>><?= $item->so_status ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">Customer</label>
							<input type="text" class="form-control" name="f_customer" value="<?= $filter["f_customer"] ?>">
						</div>
						<div class="text-center">
							<button type="submit" class="btn btn-primary">Search</button>
						</div>
					</form>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title">Order List</h5>
						<a class="btn btn-success" href="<?= base_url() ?>module/scm_order_status/download_report" target="_blank">Download</a>
					</div>
					<div><?= number_format(count($sales)) ?> records</div>
					<table class="table table-sm align-middle">
						<thead>
							<tr class="sticky-top" style="top: 60px;">
								<th scope="col"></th>
								<th scope="col">Order Mng</th>
								<th scope="col">Status</th>
								<th scope="col">Order</th>
								<th scope="col">Line</th>
								<th scope="col">Customer</th>
								<th scope="col">Com</th>
								<th scope="col">Div</th>
								<th scope="col">Model</th>
								<th scope="col">Qty</th>
								<th scope="col">K USD</th>
								<th scope="col">Instock</th>
								<th scope="col">Hold</th>
								<th scope="col">Reason</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($sales as $item){ if (!in_array($item->bill_to_name, ["B2B2C", "B2C"])){ ?>
							<tr>
								<td>
									<button type="button" class="btn btn-primary btn-sm btn_om_update_modal" data-bs-toggle="modal" data-bs-target="#md_om_mng" value="<?= $item->sales_order_id ?>">
										<i class="bi bi-pencil-square"></i>
									</button>
								</td>
								<td style="max-width: 200px;">
									<?php
									$aux = [];
									if ($item->om_line_status) $aux[] = $item->om_line_status;
									if ($item->om_appointment) $aux[] = $item->om_appointment;
									if ($item->om_appointment_remark) $aux[] = $item->om_appointment_remark;
									
									echo implode("<br/>", $aux);
									?>
								</td>
								<td><?= str_replace("_", " ", $item->so_status) ?></td>
								<td><?= $item->order_no ?></td>
								<td><?= $item->line_no ?></td>
								<td><?= $item->bill_to_name ?></td>
								<td><?= $item->model ?></td>
								<td><?= $item->dash_division ?></td>
								<td><?= $item->dash_company ?></td>
								<td><?= $item->ordered_qty ?></td>
								<td><?= round($item->sales_amount_usd/1000) ?></td>
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
							<?php }} ?>
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
				<h5 class="modal-title">Order Management</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form class="row g-3" id="form_update_order_status">
					<input type="hidden" name="order_id" id="md_om_order_id">
					<div class="col-md-12">
						<label class="form-label">OM Line Status</label>
						<select class="form-select" id="sl_om_line_status" name="om_line_status">
							<option value="">Choose...</option>
							<?php foreach($om_status_list as $item){ ?>
							<option value="<?= $item ?>"><?= $item ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-md-6">
						<label class="form-label">Appointment Date</label>
						<input type="date" class="form-control" id="ip_om_appointment_date" name="om_appointment_date">
					</div>
					<div class="col-md-6">
						<label class="form-label">Time</label>
						<div class="input-group">
							<select class="form-select" id="ip_om_appointment_time_hh" name="om_appointment_time_hh">
								<?php for($i = 0; $i < 24; $i++){ ?>
								<option value="<?= $i ?>"><?= $i ?></option>
								<?php } ?>
							</select>
							<span class="input-group-text">:</span>
							<select class="form-select" id="ip_om_appointment_time_mm" name="om_appointment_time_mm">
								<option value="00">00</option>
								<option value="30">30</option>
							</select>
						</div>
					</div>
					<div class="col-md-12">
						<label class="form-label">Remark</label>
						<textarea class="form-control" id="tx_om_appointment_remark" name="om_appointment_remark" style="height: 200px"></textarea>
					</div>
					<div class="pt-3">
						<button type="submit" class="btn btn-primary">Update</button>
						<button type="reset" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					</div>
              </form>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
	
	$('#btn_download').click(function() {
		//var f_json = $(this).val();
		//alert(f_json);
		var f = JSON.parse($(this).val());
		console.log(f);
		$.ajax({
			url: base_url + "module/scm_order_status/make_report",
			type: "POST",
			data: f,
			success:function(res){
				console.log(res);
			}
		});
	});
	
	
	$('.btn_om_update_modal').click(function() {
		
		$("#md_om_order_id").val($(this).val());
		
		$.ajax({
			url: base_url + "module/scm_order_status/load_sales_order",
			type: "POST",
			data: {order_id: $(this).val()},
			success:function(res){
				//console.log(res);
				if (res.om_line_status != null) $("#sl_om_line_status").val(res.om_line_status);
				if (res.om_appointment_date != null) $("#ip_om_appointment_date").val(res.om_appointment_date);
				if (res.om_appointment_time_hh != null) $("#ip_om_appointment_time_hh").val(res.om_appointment_time_hh);
				if (res.om_appointment_time_mm != null) $("#ip_om_appointment_time_mm").val(res.om_appointment_time_mm);
				if (res.om_appointment_remark != null) $("#tx_om_appointment_remark").val(res.om_appointment_remark);
			}
		});
    });
	
	$("#form_update_order_status").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "module/scm_order_status/om_update", "Do you update data?").done(function(res) {
			
			//console.log(res);
			
			Swal.fire({
				title: res.type.toUpperCase() + " !!!",
				icon: res.type,
				html: res.msg,
				confirmButtonText: "Confirm",
				cancelButtonText: "Cancel",
			}).then((result) => {
				if (result.isConfirmed) if (res.type == "success") {
					const currentUrl = window.location.href;
					window.location.href = currentUrl;
				}
			});
		});
	});
});
</script>