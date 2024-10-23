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
	<div class="row">
		<div class="col">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title"><?= $period ?></h5>
						<div class="d-flex justify-content-end">
							<select class="form-select me-1" id="sl_period" style="width: 150px;">
								<?php foreach($periods as $item){  ?>
								<option value="<?= $item ?>" <?= ($item === $period) ? "selected" : "" ?>><?= $item ?></option>
								<?php } ?>
							</select>
							<input type="text" class="form-control me-1" id="ip_search" placeholder="Search" style="width: 300px;">
							<button type="button" class="btn btn-success" disabled>
								<i class="bi bi-file-earmark-spreadsheet"></i> Export
							</button>
						</div>
					</div>
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
							<?php foreach($employees as $pr => $item){ if ($item["data"]->name and ($item["summary"]["check_days"] > 0)){ ?>
							<tr class="row_emp">
								<td>
									<div class="search_criteria d-none"><?= $item["data"]->name." ".$item["data"]->dept." ".$item["data"]->employee_number." ".$item["data"]->ep_mail ?></div>
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
								<td class="md_exception" 
									emp_pr="<?= $item["data"]->employee_number ?>" 
									emp_name="<?= $item["data"]->name ?>" 
									date="<?= $item_day["date"] ?>" 
									data-bs-toggle="modal" data-bs-target="#md_sch_exception">
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

<div class="modal fade" id="md_sch_exception" tabindex="-1" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Vertically Centered</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form class="row g-3" id="form_add_exception">
					<div class="col-md-6">
						<label for="ip_from" class="form-label">From</label>
						<input type="text" class="form-control datepicker" id="ip_from" name="d_from" readonly>
					</div>
					<div class="col-md-6">
						<label for="ip_to" class="form-label">To</label>
						<input type="text" class="form-control datepicker" id="ip_to" name="d_to" readonly>
					</div>
					<div class="col-md-12">
						<label for="sl_type" class="form-label">Type</label>
						<select id="sl_type" class="form-select" name="exc[type]">
							<option value="">Select...</option>
							<?php foreach($exceptions as $item){ ?>
							<option value="<?= $item[0] ?>"><?= $item[1] ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-md-12">
						<label for="ip_remark" class="form-label">Remark</label>
						<input type="text" class="form-control" id="ip_remark" name="exc[remark]" placeholder="Optional">
					</div>
					<div class="col-md-8">
						<label for="ip_name" class="form-label">Name</label>
						<input type="text" class="form-control" id="ip_name" readonly>
					</div>
					<div class="col-md-4">
						<label for="ip_pr" class="form-label">PR</label>
						<input type="text" class="form-control" id="ip_pr" name="exc[pr]" readonly>
					</div>
					<div class="text-center pt-3">
						<button type="submit" class="btn btn-primary">Submit</button>
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
			swal_redirection(res.type, res.msg, "module/hr_attendance");
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
	
	$("#sl_period").change(function(e) {
		window.location.href = "/llamasys/module/hr_attendance?p=" + $(this).val();
	});
	
	$("#ip_search").keyup(function(e) {
		var criteria = $(this).val().toUpperCase();
		
		$(".row_emp").each(function(index, elem) {
			if ($(elem).find(".search_criteria").html().toUpperCase().includes(criteria)) $(elem).show();
			else $(elem).hide();
		});
	});
	
	$(".md_exception").click(function() {
		$("#sl_type").val("");
		$("#ip_pr").val($(this).attr("emp_pr"));
		$("#ip_name").val($(this).attr("emp_name"));
		$("#ip_from").val($(this).attr("date"));
		$("#ip_to").val($(this).attr("date"));
		$('.datepicker').datepicker('update', $(this).attr("date"));
	});
	
	$("#form_add_exception").submit(function(e) {
		e.preventDefault();
		$("#form_add_exception .sys_msg").html("");
		ajax_form_warning(this, "module/hr_attendance/add_exception", "Do you want to add exception? (You can remove exceptions in employee detail page.)").done(function(res) {
			swal_redirection(res.type, res.msg, "module/hr_attendance");
		});
	});
});
</script>