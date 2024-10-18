<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Employee</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item">Employee</li>
				<li class="breadcrumb-item active">Edit</li>
			</ol>
		</nav>
	</div>
	<div>
		<a href="<?= base_url() ?>module/hr_employee" type="button" class="btn btn-success">
			<i class="bi bi-arrow-left"></i>
		</a>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-8">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Employee Information</h5>
					<form class="row g-3" id="form_save_data">
						<div class="col-md-8">
							<label class="form-label">Name</label>
							<input class="form-control" type="text" name="name" value="<?= $emp->name ?>">
						</div>
						<div class="col-md-4">
							<label class="form-label">Department</label>
							<select class="form-select" name="dpt">
								<option value="">--</option>
								<?php foreach($dpts as $item){ ?>
								<option value="<?= $item ?>" <?= $emp->dpt === $item ? "selected" : "" ?>><?= $item ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">Location</label>
							<input class="form-control" type="text" name="location" value="<?= $emp->location ?>">
						</div>
						<div class="col-md-4">
							<label class="form-label">Employee No.</label>
							<input class="form-control" type="text" name="employee_number" value="<?= $emp->employee_number ?>">
						</div>
						<div class="col-md-4">
							<label class="form-label">EP Mail</label>
							<input class="form-control" type="text" name="ep_mail" value="<?= $emp->ep_mail ?>">
						</div>
						<div class="col-md-8">
							<label class="form-label">Work Schedule</label>
							<select class="form-select" name="work_schedule">
								<?php foreach($schs as $item){ ?>
								<option value="<?= $item ?>" <?= ($item === $emp->work_sch) ? "selected" : "" ?>><?= $item ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">Schedule Apply From (Just in case)</label>
							<input class="form-control" type="date" name="date_from">
						</div>
						<div class="col-md-12 pt-3">
							<div class="row">
								<div class="col-md-4">
									<div class="form-check form-switch">
										<input class="form-check-input me-3" type="checkbox" name="active" id="active" <?= $emp->active ? "checked" : "" ?>>
										<label class="form-check-label" for="active">Active</label>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-check form-switch">
										<input class="form-check-input me-3" type="checkbox" name="is_supervised" id="is_supervised" <?= $emp->is_supervised ? "checked" : "" ?>>
										<label class="form-check-label" for="is_supervised">Supervised</label>
									</div>
								</div>
								<div class="col-md-4">
									<div class="form-check form-switch">
										<input class="form-check-input me-3" type="checkbox" name="access" id="access" <?= $emp->access ? "checked" : "" ?>>
										<label class="form-check-label" for="access">Access</label>
									</div>
								</div>
							</div>
						</div>
						<div class="pt-3 text-center">
							<input type="hidden" name="employee_id" value="<?= $emp->employee_id ?>">
							<button type="submit" class="btn btn-primary">Update</button>
							<a href="<?= base_url() ?>module/hr_employee" type="button" class="btn btn-secondary">
								Cancel
							</a>
						</div>
					</form>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Work Times</h5>
					<table class="table">
						<thead>
							<tr>
								<th scope="col">From</th>
								<th scope="col">Entrance</th>
								<th scope="col">Leave</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($w_schs as $item){ ?>
							<tr>
								<td><?= $item->date_from ?></td>
								<td><?= $item->work_start ?></td>
								<td><?= $item->work_end ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div> 
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Module</h5>
					<ul class="list-group">
						<?php foreach($acc_mod as $item){ ?>
						<li class="list-group-item">
							<div class="form-check form-switch">
								<input class="form-check-input me-3 chk_acc_ctrl" type="checkbox" id="sw_<?= $item[0] ?>" value="<?= $item[0] ?>" <?= in_array($item[0], $acc_asg) ? "checked" : "" ?>>
								<label class="form-check-label" for="sw_<?= $item[0] ?>"><?= $item[1] ?></label>
							</div>
						</li>
						<?php } ?>
					</ul>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Data Upload</h5>
					<ul class="list-group">
						<?php foreach($acc_du as $item){ ?>
						<li class="list-group-item">
							<div class="form-check form-switch">
								<input class="form-check-input me-3 chk_acc_ctrl" type="checkbox" id="sw_<?= $item[0] ?>" value="<?= $item[0] ?>" <?= in_array($item[0], $acc_asg) ? "checked" : "" ?>>
								<label class="form-check-label" for="sw_<?= $item[0] ?>"><?= $item[1] ?></label>
							</div>
						</li>
						<?php } ?>
					</ul>
				</div>
			</div>
		</div>
	</div>
</section>
<input type="hidden" id="emp_id" value="<?= $emp->employee_id ?>">
<script>
document.addEventListener("DOMContentLoaded", () => {
	
	$("#form_save_data").submit(function(e) {
		e.preventDefault();
		$("#form_save_data .sys_msg").html("");
		ajax_form_warning(this, "module/hr_employee/save_data", "Do you want to save data?").done(function(res) {
			swal_redirection(res.type, res.msg, res.url);
		});
	});
	
	
	$(".chk_acc_ctrl").on("change", function() {
		//update_voice({listening_id: $(this).attr("listening_id"), status: $(this).val()});
		//alert($(this).val());
		ajax_simple({checked: $(this).is(':checked'), employee_id: $("#emp_id").val(), module: $(this).val()}, "module/hr_employee/acc_ctrl").done(function(res) {
			//console.log(res);
			toastr.success(res.msg, null, {timeOut: 5000});
		});
	});
	
	/*
	
	//cancel purchase
	$("#btn_delete_payment").click(function() {
		ajax_simple_warning({id: $(this).val()}, "commerce/purchase/delete_payment", "wm_payment_delete").done(function(res) {
			swal_redirection(res.type, res.msg, window.location.href);
		});
	});
	*/
});
</script>