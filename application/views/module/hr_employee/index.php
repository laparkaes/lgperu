<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Employee</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">Employee</li>
			</ol>
		</nav>
	</div>
	<div>
		<a href="<?= base_url() ?>module/hr_employee/create" type="button" class="btn btn-success">
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
									<th scope="col">Department</th>
									<th scope="col">PR</th>
									<th scope="col">Name</th>
									<th scope="col" class="text-center">Leader</th>
									<th scope="col" class="text-center">Status</th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($employees as $i => $emp){ ?>
								<tr>
									<td><?= number_format($i + 1) ?></td>
									<td>
										<?php
										$aux = [];
										if ($emp->subsidiary) $aux[] = $emp->subsidiary;
										if ($emp->organization) $aux[] = $emp->organization;
										if ($emp->department) $aux[] = $emp->department;
										
										if ($aux) echo implode(" > ", $aux);
										?>
									</td>
									<td><?= $emp->employee_number  ?></td>
									<td><?= $emp->name  ?></td>
									<td class="text-center"><?= $emp->is_leader ? '<i class="bi bi-circle-fill text-success"></i>' : '' ?></td>
									<td class="text-center"><i class="bi bi-circle-fill text-<?= $emp->active ? "success" : "danger" ?>"></i></td>
									<td>
										<div class="text-end">
											<a href="<?= base_url() ?>module/hr_employee/edit/<?= $emp->employee_id ?>" class="btn btn-link">
												<i class="bi bi-pencil-fill"></i>
											</a>
										</div>
									</td>
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