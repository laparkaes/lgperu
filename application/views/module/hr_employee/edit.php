<section class="section">
	<div class="row">
		<div class="col-6 mx-auto">
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
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Employee Information</h5>
					<form class="row g-3" id="form_save_employee">
						<div class="col-md-12">
							<label class="form-label">Name</label>
							<input class="form-control" type="text" name="name" value="<?= $employee->name ?>">
						</div>
						<div class="col-md-4">
							<label class="form-label">Subsidiary</label>
							<input class="form-control" type="text" name="name" value="<?= $employee->subsidiary ?>">
						</div>
						<div class="col-md-4">
							<label class="form-label">Organization</label>
							<input class="form-control" type="text" name="name" value="<?= $employee->organization ?>">
						</div>
						<div class="col-md-4">
							<label class="form-label">Department</label>
							<input class="form-control" type="text" name="name" value="<?= $employee->department ?>">
						</div>
						<div class="col-md-4">
							<label class="form-label">Location</label>
							<input class="form-control" type="text" name="name" value="<?= $employee->location ?>">
						</div>
						<div class="col-md-4">
							<label class="form-label">Employee No.</label>
							<input class="form-control" type="text" name="name" value="<?= $employee->employee_number ?>">
						</div>
						<div class="col-md-4">
							<label class="form-label">EP Mail</label>
							<input class="form-control" type="text" name="name" value="<?= $employee->ep_mail ?>">
						</div>
						<div class="col-md-6">
							<div class="form-control">
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" name="is_supervised" id="is_supervised" <?= $employee->is_supervised ? "checked" : "" ?>>
									<label class="form-check-label" for="is_supervised">Is Supervised</label>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-control">
								<div class="form-check form-switch">
									<input class="form-check-input" type="checkbox" name="access" id="access" <?= $employee->access ? "checked" : "" ?>>
									<label class="form-check-label" for="access">Access</label>
								</div>
							</div>
						</div>
						<div class="pt-3 text-center">
							<input type="hidden" name="employee_id" value="<?= $employee->employee_id ?>">
							<button type="submit" class="btn btn-primary">Update Employee</button>
						</div>
					</form>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Password Change</h5>
					<form class="row g-3" id="form_change_password">
						<div class="col-md-12">
							<label class="form-label">Password</label>
							<input class="form-control" type="password" name="password">
						</div>
						<div class="col-md-6">
							<label class="form-label">New</label>
							<input class="form-control" type="password" name="name">
						</div>
						<div class="col-md-6">
							<label class="form-label">Confirm</label>
							<input class="form-control" type="password" name="name">
						</div>
						<div class="pt-3 text-center">
							<input type="hidden" name="employee_id" value="<?= $employee->employee_id ?>">
							<button type="submit" class="btn btn-primary">Update Password</button>
						</div>
					</form>
				</div>
			</div>
		</div> 
	</div>
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {
	//ic_control_access
	
	
	/*
	$("#form_uff_w_hour").submit(function(e) {
		e.preventDefault();
		$("#form_uff_w_hour .sys_msg").html("");
		ajax_form_warning(this, "module/employee/upload_w_hour_from_file", "Do you want to upload working hours data from selected file?").done(function(res) {
			if (res.type == "success") window.location.href = base_url + "upload/working_hour.xlsx";
			swal_redirection(res.type, res.msg, "module/employee");
		});
	});
	
	//cancel purchase
	$("#btn_delete_payment").click(function() {
		ajax_simple_warning({id: $(this).val()}, "commerce/purchase/delete_payment", "wm_payment_delete").done(function(res) {
			swal_redirection(res.type, res.msg, window.location.href);
		});
	});
	*/
});
</script>