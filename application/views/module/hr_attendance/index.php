<div class="pagetitle">
	<h1>Attendance</h1>
	<nav>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
			<li class="breadcrumb-item active">Attendance</li>
		</ol>
	</nav>
</div>
<section class="section">
	<div class="row row-cols-1 row-cols-md-3 g-3">
		<div class="col">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Functions</h5>
					<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#md_exr">
						<i class="bi bi-file-earmark-spreadsheet"></i> Report
					</button>
				</div>
			</div>
		</div>
		<div class="col">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Access Records</h5>
					<form class="input-group" id="form_upload_access">
						<input type="file" class="form-control" name="attach" accept=".xls,.xlsx,.csv">
						<button type="submit" class="btn btn-success">
							<i class="bi bi-upload"></i>
						</button>
					</form>
				</div>
			</div>
		</div>
		<div class="col">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Employee's Schedules</h5>
					<form class="input-group" id="form_upload_schedule">
						<input type="file" class="form-control" name="attach" accept=".xls,.xlsx,.csv">
						<button type="submit" class="btn btn-success">
							<i class="bi bi-upload"></i>
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title"><?= $period ?></h5>
						<table class="table align-middle">
							<thead>
								<tr>
									<th scope="col">Employee</th>
									<th scope="col">PR</th>
									<th scope="col">Days</th>
									<th scope="col">T<br/>E</th>
									<th scope="col" class="border-end">Time</th>
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
								<?php foreach($employees as $i => $item){ if ($item["summary"]["check_days"] > 0){ ?>
								<tr>
									<td>
										<div><?= $item["data"]->name ?></div>
										<div class="text-nowrap"><small><?= $item["data"]->dept ?></small></div>
									</td>
									<td><?= $item["data"]->employee_number ?></td>
									<td><?= $item["summary"]["check_days"] ?></td>
									<td><?= $item["summary"]["tardiness"] ?><br/><?= $item["summary"]["early_out"] ?></td>
									<td class="border-end">
										<div><?= date("H:i", strtotime($schedule_pr[$item["data"]->employee_number][$to]["start"])) ?></div>
										<div><?= date("H:i", strtotime($schedule_pr[$item["data"]->employee_number][$to]["end"])) ?></div>
									</td>
									<?php foreach($days as $item_day){ ?>
									<td>
										<div class="text-<?= $item["access"][$item_day["day"]]["first_access"]["remark"] === "T" ? "danger" : "" ?>">
											<?= $item["access"][$item_day["day"]]["first_access"]["time"] ?>
										</div>
										<div class="text-<?= $item["access"][$item_day["day"]]["last_access"]["remark"] === "E" ? "danger" : "" ?>">
											<?= $item["access"][$item_day["day"]]["last_access"]["time"] ?>
										</div>
									</td>
									<?php } ?>
								</tr>
								<?php }} ?>
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
	
	$("#form_upload_access").submit(function(e) {
		e.preventDefault();
		ajax_form(this, "module/hr_attendance/upload_access").done(function(res) {
			swal_redirection(res.type, res.msg, "module/hr_attendance");
		});
	});
	
	$("#form_upload_schedule").submit(function(e) {
		e.preventDefault();
		ajax_form(this, "module/hr_attendance/upload_schedule").done(function(res) {
			swal_redirection(res.type, res.msg, "module/hr_attendance");
		});
	});
});
</script>