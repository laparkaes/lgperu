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
						<div class="col-md-12">
							<label class="form-label">Name</label>
							<input class="form-control" type="text" name="name" value="<?= $emp->name ?>">
						</div>
						<div class="col-md-4">
							<label class="form-label">Subsidiary</label>
							<select class="form-select" name="subsidiary">
								<option value="">--</option>
								<?php foreach($subs as $item){ if ($item->subsidiary){ ?>
								<option value="<?= $item->subsidiary ?>" <?= $emp->subsidiary === $item->subsidiary ? "selected" : "" ?>><?= $item->subsidiary ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">Organization</label>
							<select class="form-select" name="organization">
								<option value="">--</option>
								<?php foreach($orgs as $item){ if ($item->organization){ ?>
								<option value="<?= $item->organization ?>" <?= $emp->organization === $item->organization ? "selected" : "" ?>><?= $item->organization ?></option>
								<?php }} ?>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">Department</label>
							<select class="form-select" name="department">
								<option value="">--</option>
								<?php foreach($dpts as $item){ if ($item->department){ ?>
								<option value="<?= $item->department ?>" <?= $emp->department === $item->department ? "selected" : "" ?>><?= $item->department ?></option>
								<?php }} ?>
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
		</div> 
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Access</h5>
					<ul class="list-group">
						<?php foreach($acc as $item){ ?>
						<li class="list-group-item">
							<div class="form-check form-switch">
								<input class="form-check-input me-3" type="checkbox" id="sw_<?= str_replace(" ", "_", $item[1]) ?>" value="<?= $item[0] ?>">
								<label class="form-check-label" for="sw_<?= str_replace(" ", "_", $item[1]) ?>"><?= $item[1] ?></label>
							</div>
						</li>
						<?php } ?>
					</ul>
					
				</div>
			</div>
		</div>
	</div>
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {
	
	$("#form_save_data").submit(function(e) {
		e.preventDefault();
		$("#form_save_data .sys_msg").html("");
		ajax_form_warning(this, "module/hr_employee/save_data", "Do you want to save data?").done(function(res) {
			swal(res.type, res.msg);
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