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
	<div>
		<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#md_exr">
			<i class="bi bi-file-earmark-spreadsheet"></i>
		</button>
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
					<h5 class="card-title"><?= $month ?></h5>
					<div class="table-responsive">
						<table class="table datatable align-middle">
							<thead>
								<tr>
									<th scope="col" style="width: 80px;">#</th>
									<th scope="col">Emp.Num.</th>
									<th scope="col">Employee</th>
									<th scope="col">Subsidiary</th>
									<th scope="col">Organization</th>
									<th scope="col">Department</th>
									<th scope="col">Location</th>
									<th scope="col">Vac.</th>
									<th scope="col">Abs.</th>
									<th scope="col">Tard.</th>
									<th scope="col">Tard.Acc.</th>
									<th scope="col">E.Exit</th>
									<?php foreach($headers as $h){ ?>
									<th scope="col">
										<div class="text-<?= ($h["type"] === "H") ? "danger" : "" ?>">
											<?= $h["day"] ?><br/><?= $h["day_w"] ?>
										</div>
									</th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach($employees as $i => $emp){ ?>
								<tr>
									<td><?= number_format($i + 1) ?></td>
									<td><?= $emp->employee_number ?></td>
									<td><div style="overflow: hidden; max-width: 150px; text-overflow: ellipsis;" class="text-nowrap" title="<?= $emp->name ?>"><?= $emp->name ?></div></td>
									<td><?= $emp->subsidiary ?></td>
									<td><div class="text-nowrap"><?= $emp->organization ?></div></td>
									<td><div class="text-nowrap"><?= $emp->department ?></div></td>
									<td><div class="text-nowrap"><?= $emp->location ?></div></td>
									<td><?= ($emp->vacation_qty > 0) ? $emp->vacation_qty : "" ?></td>
									<td><?= ($emp->absence_qty > 0) ? number_format($emp->absence_qty) : "" ?></td>
									<td><?= ($emp->tardiness_qty > 0) ? number_format($emp->tardiness_qty) : ""  ?></td>
									<td><?= ($emp->tardiness_qty > 0) ? $emp->tardiness_acc : "" ?></td>
									<td><?= ($emp->early_exit_qty > 0) ? number_format($emp->early_exit_qty) : "" ?></td>
									<?php foreach($dates as $idate => $d){ ?>
									<td>
										<?php if ($emp->daily[$d]["type"] === "N"){
											if ($emp->daily[$d]["entrance"]["result"] === "V"){ 
												$en_color = "success"; 
												$en_val = $emp->daily[$d]["entrance"]["result"];
											}else{
												$en_color = ($emp->daily[$d]["entrance"]["result"] === "T") ? "danger" : ""; 
												$en_val = date("H:i", strtotime($emp->daily[$d]["entrance"]["time"]));
											}
											
											if ($emp->daily[$d]["exit"]["result"] === "V"){ 
												$ex_color = "success"; 
												$ex_val = $emp->daily[$d]["exit"]["result"];
											}else{
												$ex_color = ($emp->daily[$d]["exit"]["result"] === "E") ? "danger" : ""; 
												$ex_val = date("H:i", strtotime($emp->daily[$d]["exit"]["time"]));
											}
											?>
										<div class="text-<?= $en_color ?>"><?= $en_val ?></div>
										<div class="text-<?= $ex_color ?>"><?= $ex_val ?></div>
										<?php }else{
											if ($emp->daily[$d]["type"] === "X") $d_color = "danger";
											elseif ($emp->daily[$d]["type"] === "V") $d_color = "success";
											else $d_color = "";
											?>
										<div class="text-<?= $d_color ?>">
											<?= $emp->daily[$d]["type"] ?>
										</div>
										<?php } ?>
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

<div class="modal fade" id="md_uff" tabindex="-1" style="display: none;" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Upload Device Check-in</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="my-3">
					<span>You need to upload excel file exported from access device.</span>
				</div>
				<form class="row g-3" id="form_uff_attendance">
					<div class="col-12">
						<label class="form-label">Device File</label>
						<input type="file" class="form-control" name="md_uff_file" accept=".xls,.xlsx,.csv">
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
	
	$("#form_uff_attendance").submit(function(e) {
		e.preventDefault();
		$("#form_uff_attendance .sys_msg").html("");
		ajax_form_warning(this, "module/attendance/upload_device_check", "Do you want to upload device check-in data from selected file?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/attendance");
		});
	});
});
</script>