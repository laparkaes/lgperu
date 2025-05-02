<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Employee</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item">Employee</li>
				<li class="breadcrumb-item active">Create</li>
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
					<form class="row g-3" id="form_save_create_data">
						<div class="col-md-8">
							<label class="form-label">Name</label>
							<input class="form-control" type="text" name="name">
						</div>
						<div class="col-md-4">
							<label class="form-label">Department</label>
							<select class="form-select" name="dpt">
								<option value="">--</option>
								<?php foreach($dpts as $item){ ?>
								<option value="<?= $item ?>"><?= $item ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-4">
							<label class="form-label">Location</label>
							<input class="form-control" type="text" name="location">
						</div>
						<div class="col-md-4">
							<label class="form-label">Employee No.</label>
							<input class="form-control" type="text" name="employee_number">
						</div>
						<div class="col-md-4">
							<label class="form-label">EP Mail</label>
							<input class="form-control" type="text" name="ep_mail">
						</div>
						<div class="col-md-12 pt-3">
							<div class="row">
								<div class="col-md-4">
									<div class="form-check form-switch">
										<input class="form-check-input me-3" type="checkbox" name="active" id="active">
										<label class="form-check-label" for="active">Active</label>
									</div>
								</div>
							</div>
						</div>
						<div class="pt-3 text-center">
							<input type="hidden" name="employee_id">
							<button type="submit" class="btn btn-primary">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>
<!--<input type="hidden" id="emp_id" value="<?= $emp->employee_id ?>"-->
<script>
document.addEventListener("DOMContentLoaded", () => {
	
	$("#form_save_create_data").submit(function(e) {
		e.preventDefault();
		$("#form_save_create_data .sys_msg").html("");
		ajax_form_warning(this, "module/hr_employee/save_create_data", "Do you want to create employee?").done(function(res) {
			swal_redirection(res.type, res.msg, res.url);
		});
	});
	
	// $("#form_save_create_worktime").submit(function(e) {
		// e.preventDefault();
		// $("#form_save_create_worktime .sys_msg").html("");
		// ajax_form_warning(this, "module/hr_employee/save_create_worktime", "Do you want to save worktime?").done(function(res) {
			// swal_redirection(res.type, res.msg, res.url);
		// });
	// });
});
</script>

