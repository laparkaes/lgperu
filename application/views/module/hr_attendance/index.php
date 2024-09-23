<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Attendance</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">Attendance</li>
			</ol>
		</nav>
	</div>
	<div class="d-flex justify-content-end">
		<form class="input-group me-1" id="form_upload">
			<input type="file" class="form-control" name="attach" accept=".xls,.xlsx,.csv">
			<button type="submit" class="btn btn-success">
				<i class="bi bi-upload"></i>
			</button>
		</form>
		<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#md_exr">
			<i class="bi bi-file-earmark-spreadsheet"></i>
		</button>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title"><?= $period ?></h5>
						<table class="table align-middle">
							<thead>
								<tr>
									<th scope="col">Department</th>
									<th scope="col">Employee</th>
									<th scope="col">PR</th>
									<?php foreach($days as $item){ ?>
									<th scope="col">
										<div class="text-center text-<?= (in_array($item["day"], $free_days)) ? "danger" : "" ?>">
											<?= $item["day"] ?><br/><?= substr($days_week[$item["day"]], 0, 3) ?>
										</div>
									</th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach($employees as $i => $item){ ?>
								<tr>
									<td><div class="text-nowrap"><?= $item["data"]->dept ?></div></td>
									<td><div style="width: 200px;" title="<?= $item["data"]->name ?>"><?= $item["data"]->name ?></div></td>
									<td><?= $item["data"]->employee_number ?></td>
									<?php foreach($days as $item_day){ ?>
									<td>
										<div><?= $item["access"][$item_day["day"]]["first_access"]["time"] ?></div>
										<div><?= $item["access"][$item_day["day"]]["last_access"]["time"] ?></div>
									</td>
									<?php } ?>
								</tr>
								<?php } ?>
							</tbody>
						</table>
				</div>
			</div>
		</div> 
	</div>
</section>
<div class="modal fade" id="md_exr" tabindex="-1" style="display: none;" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Export Report</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form class="row g-3" id="form_exp_report">
					<div class="col-12">
						<label class="form-label">Period</label>
						<select class="form-select" name="period">
							<option value="2024-02">2024-02</option>
						</select>
					</div>
					<div class="text-end pt-3">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
						<button type="submit" class="btn btn-primary">Export</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_exp_report").submit(function(e) {
		e.preventDefault();
		$("#form_exp_report .sys_msg").html("");
		ajax_form_warning(this, "module/attendance/export_monthly_report", "Do you want to export monthly attendance report?").done(function(res) {
			window.location.href = res.url;
			//alert();
			//swal_redirection(res.type, res.msg, "module/attendance");
		});
	});
	
	$("#form_upload").submit(function(e) {
		e.preventDefault();
		ajax_form(this, "module/hr_attendance/upload").done(function(res) {
			swal_redirection(res.type, res.msg, "module/hr_attendance");
		});
	});
});
</script>