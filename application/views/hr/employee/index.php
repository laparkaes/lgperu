<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Employee</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Home</a></li>
				<li class="breadcrumb-item active">Employee</li>
			</ol>
		</nav>
	</div>
	<div>
		<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#md_uff">
			<i class="bi bi-upload"></i>
		</button>
		<a href="#" type="button" class="btn btn-success">
			<i class="bi bi-search"></i>
		</a>
		<a href="#" type="button" class="btn btn-success">
			<i class="bi bi-plus-lg"></i>
		</a>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">List</h5>
					<div class="table-responsive">
						<table class="table align-middle">
							<thead>
								<tr>
									<th scope="col" style="width: 80px;">#</th>
									<th scope="col">Organization</th>
									<th scope="col">Emp. Num.</th>
									<th scope="col">Name</th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>
								<?php $base_i = ($page-1) * 30; foreach($employees as $i => $emp){ ?>
								<tr>
									<td><?= number_format($base_i + $i + 1) ?></td>
									<td><?= $emp->subsidiary ?><br/><?= $emp->organization  ?></td>
									<td><?= $emp->employee_number  ?></td>
									<td><?= $emp->name  ?></td>
									<td class="text-end">
										<button type="button" class="btn btn-link">
											<i class="bi bi-file-earmark-fill"></i>
										</button>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
					<div class="btn-group" role="group" aria-label="paging">
						<?php 
						$f_url = $this->input->get();
						foreach($paging as $p){
						$f_url["page"] = $p[0]; ?>
						<a href="<?= base_url() ?>hr/employee?<?= http_build_query($f_url) ?>" class="btn btn-<?= $p[2] ?>">
							<?= $p[1] ?>
						</a>
						<?php } ?>
					</div>
				</div>
			</div>
		</div> 
	</div>
</section>
<div>
	<div class="modal fade" id="md_uff" tabindex="-1" style="display: none;" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Upload from file</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<ul class="nav nav-tabs" id="myTab" role="tablist">
						<li class="nav-item" role="presentation">
							<button class="nav-link active" id="employee-tab" data-bs-toggle="tab" data-bs-target="#employee" type="button" role="tab" aria-controls="employee" aria-selected="true">Employee</button>
						</li>
						<li class="nav-item" role="presentation">
							<button class="nav-link" id="vacation-tab" data-bs-toggle="tab" data-bs-target="#vacation" type="button" role="tab" aria-controls="vacation" aria-selected="false" tabindex="-1">Vacation</button>
						</li>
						<li class="nav-item" role="presentation">
							<button class="nav-link" id="working_hours-tab" data-bs-toggle="tab" data-bs-target="#working_hours" type="button" role="tab" aria-controls="working_hours" aria-selected="false" tabindex="-1">W. Hours</button>
						</li>
					</ul>
					<div class="tab-content pt-2" id="myTabContent">
						<div class="tab-pane fade show active" id="employee" role="tabpanel" aria-labelledby="employee-tab">
							<div class="my-3">
								<a href="<?= base_url() ?>form_file/employee.xlsx" download="employee_form.xlsx">Download employee upload file</a>
							</div>
							<form class="row g-3" id="form_uff_employee">
								<div class="col-12">
									<label class="form-label">Employee List File</label>
									<input type="file" class="form-control" name="md_uff_file" accept=".xls,.xlsx">
								</div>
								<div class="text-end pt-3">
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
									<button type="submit" class="btn btn-primary">Upload</button>
								</div>
							</form>
						</div>
						<div class="tab-pane fade" id="vacation" role="tabpanel" aria-labelledby="vacation-tab">
							<div class="my-3">
								<a href="<?= base_url() ?>form_file/vacation.xlsx" download="vacation_form.xlsx">Download vacation upload file</a>
							</div>
							<form class="row g-3" id="form_uff_vacation">
								<div class="col-12">
									<label for="md_uff_file" class="form-label">Vacation List File</label>
									<input type="file" class="form-control" id="md_uff_file" name="md_uff_file" accept=".xls,.xlsx">
								</div>
								<div class="text-end pt-3">
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
									<button type="submit" class="btn btn-primary">Upload</button>
								</div>
							</form>
						</div>
						<div class="tab-pane fade" id="working_hours" role="tabpanel" aria-labelledby="working_hours-tab">
							<div class="my-3">
								<a href="<?= base_url() ?>form_file/working_hour.xlsx" download="vacation_form.xlsx">Download working hours upload file</a>
							</div>
							<form class="row g-3" id="form_uff_w_hour">
								<div class="col-12">
									<label for="md_uff_file" class="form-label">Working hours List File</label>
									<input type="file" class="form-control" id="md_uff_file" name="md_uff_file" accept=".xls,.xlsx">
								</div>
								<div class="text-end pt-3">
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
									<button type="submit" class="btn btn-primary">Upload</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_uff_employee").submit(function(e) {
		e.preventDefault();
		$("#form_uff_employee .sys_msg").html("");
		ajax_form_warning(this, "hr/employee/upload_employee_from_file", "Do you want to upload employee data from selected file?").done(function(res) {
			if (res.type == "success") window.location.href = base_url + "upload/employee.xlsx";
			swal_redirection(res.type, res.msg, "hr/employee");
		});
	});
	
	$("#form_uff_vacation").submit(function(e) {
		e.preventDefault();
		$("#form_uff_vacation .sys_msg").html("");
		ajax_form_warning(this, "hr/employee/upload_vacation_from_file", "Do you want to upload vacation data from selected file?").done(function(res) {
			if (res.type == "success") window.location.href = base_url + "upload/vacation.xlsx";
			swal_redirection(res.type, res.msg, "hr/employee");
		});
	});
	
	$("#form_uff_w_hour").submit(function(e) {
		e.preventDefault();
		$("#form_uff_w_hour .sys_msg").html("");
		ajax_form_warning(this, "hr/employee/upload_w_hour_from_file", "Do you want to upload working hours data from selected file?").done(function(res) {
			if (res.type == "success") window.location.href = base_url + "upload/working_hour.xlsx";
			swal_redirection(res.type, res.msg, "hr/employee");
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