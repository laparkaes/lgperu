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
							<select class="form-select me-1" id="sl_dept" style="width: 250px;">
								<option value="">All departments --</option>
								<?php foreach($depts as $item){  ?>
								<option value="<?= str_replace([" ", ">", "&"], "", $item) ?>"><?= str_replace("LGEPR > ", "", $item) ?></option>
								<?php } ?>
							</select>
							<input type="text" class="form-control me-1" id="ip_search" placeholder="Search [Type 'enter' to apply filter]" style="width: 300px;">
							<a href="" class="btn btn-success d-none me-1" id="btn_export" download="Attendance <?= $period ?>">
								<i class="bi bi-file-earmark-spreadsheet"></i> Export
							</a>
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#md_exception_list">
								<i class="bi bi-calendar2-week-fill"></i>
							</button>
						</div>
					</div>
					<table class="table align-middle" style="font-size: 0.8rem;">
						<thead class="sticky-top" style="z-index: 10; top: 60px;">
							<tr>
								<th scope="col">Employee</th>
								<th scope="col">Days</th>
								<th scope="col">T<br/>E</th>
								<th scope="col" class="border-end">Time</th>
								<?php foreach($days as $item){ ?>
								<th scope="col" class="text-center md_holiday" 
									date="<?= $item["date"] ?>" 
									data-bs-toggle="modal" data-bs-target="#md_com_exception">
									<div class="text-<?= (in_array($item["day"], $free_days)) ? "danger" : "" ?>">
										<?= $item["day"] ?><br/><?= substr($days_week[$item["day"]], 0, 3) ?>
									</div>
								</th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<?php foreach($employees as $pr => $item){ 
								//if (($item["data"]->name) and ($item["summary"]["check_days"] > 0)){ 
								if ($item["summary"]["check_days"] > 0){ 
							?>
							<tr class="row_emp">
								<td>
									<div class="search_criteria d-none"><?= $item["data"]->name." ".str_replace([" ", "&", ">"], "", $item["data"]->dept)." ".$item["data"]->employee_number." ".$item["data"]->ep_mail ?></div>
									<div>
										<?php
										$aux = [];
										if ($item["data"]->name) $aux[] = $item["data"]->name;
										if ($item["data"]->employee_number) $aux[] = $item["data"]->employee_number;
										?>
										<?= implode(", ", $aux) ?>
										<br/><small><?= $item["data"]->dept ?></small>
									</div>
								</td>
								<td><?= $item["summary"]["check_days"] ?></td>
								<td>
									<div class="text-center text-<?= $item["summary"]["tardiness"] > 4 ? "light bg-danger" : "" ?>"><?= $item["summary"]["tardiness"] ?></div>
									<div class="text-center text-<?= $item["summary"]["early_out"] > 4 ? "light bg-danger" : "" ?>"><?= $item["summary"]["early_out"] ?></div>
								</td>
								<td class="border-end">
									<?= date("H:i", strtotime($schedule_pr[$item["data"]->employee_number][$to]["start"])) ?><br/>
									<?= date("H:i", strtotime($schedule_pr[$item["data"]->employee_number][$to]["end"])) ?>
								</td>
								<?php foreach($days as $item_day){ ?>
								<td class="text-center md_exception" 
									emp_pr="<?= $item["data"]->employee_number ?>" 
									emp_name="<?= $item["data"]->name ?>" 
									date="<?= $item_day["date"] ?>" 
									data-bs-toggle="modal" data-bs-target="#md_emp_exception">
									<?php
									$now = $item["access"][$item_day["day"]];
									$aux = [];
									
									if ($now["first_access"]["time"]){
										if ($now["first_access"]["remark"] === "MV") $aux[] = $now["first_access"]["remark"];
										
										switch($now["first_access"]["remark"]){
											case "T": $color = "danger"; break;
											case "TT": $color = "warning bg-secondary"; break;
											default: $color = "";
										}
										
										$aux[] = '<span class="text-'.$color.'">'.$now["first_access"]["time"].'</span>';
									}else $aux[] = $now["first_access"]["remark"];
									
									if ($now["last_access"]["time"]){
										$aux[] = '<span class="text-'.($now["last_access"]["remark"] === "E" ? "danger" : "").'">'.$now["last_access"]["time"].'</span>';
										if ($now["last_access"]["remark"] === "AV") $aux[] = $now["last_access"]["remark"];
									}else $aux[] = $now["last_access"]["remark"];
									
									if ($aux) echo implode("<br/>", array_unique($aux));
									?>
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
<div class="d-none" id="bl_export_result"></div>

<div class="modal fade" id="md_emp_exception" tabindex="-1" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Employee Exception</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form class="row g-3 form_add_exception">
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
							<?php foreach($exceptions_emp as $item){ ?>
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

<div class="modal fade" id="md_com_exception" tabindex="-1" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Company Exception</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form class="row g-3 form_add_exception">
					<div class="col-md-6">
						<label for="ip_ho_from" class="form-label">From</label>
						<input type="text" class="form-control datepicker" id="ip_ho_from" name="d_from" readonly>
					</div>
					<div class="col-md-6">
						<label for="ip_ho_to" class="form-label">To</label>
						<input type="text" class="form-control datepicker" id="ip_ho_to" name="d_to" readonly>
					</div>
					<div class="col-md-12">
						<label for="sl_ho_type" class="form-label">Type</label>
						<select id="sl_ho_type" class="form-select" name="exc[type]">
							<?php foreach($exceptions_com as $item){ ?>
							<option value="<?= $item[0] ?>"><?= $item[1] ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-md-12">
						<label for="ip_ho_remark" class="form-label">Remark</label>
						<input type="text" class="form-control" id="ip_ho_remark" name="exc[remark]" placeholder="Optional">
					</div>
					<div class="col-md-12">
						<label for="ip_ho_pr" class="form-label">PR</label>
						<input type="text" class="form-control" id="ip_ho_pr" name="exc[pr]" value="LGEPR" readonly>
					</div>
					<div class="text-center pt-3">
						<button type="submit" class="btn btn-primary">Submit</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="md_exception_list" tabindex="-1" style="display: none;" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Exceptions</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div>H: Holiday | EF: Early Friday<br/>MV: Morning Vacation | AV: Afternoon Vacation | V: Vacation | MED: Medical Vacation</div>
				<table class="table">
					<thead>
						<tr>
							<th scope="col">PR</th>
							<th scope="col">Name</th>
							<th scope="col">Date</th>
							<th scope="col">Type</th>
							<th scope="col">Remark</th>
							<th scope="col"></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($exceptions as $item){ // [0] => stdClass Object ( [exception_id] => 4 [pr] => LGEPR [exc_date] => 2024-10-08 [type] => H [remark] => ) ?>
						<tr id="row_exc_<?= $item->exception_id ?>">
							<td><?= $item->pr ?></td>
							<td><?= $item->name ?></td>
							<td><?= $item->exc_date ?></td>
							<td><?= $item->type ?></td>
							<td><?= $item->remark ?></td>
							<td>
								<div class="text-end">
									<button type="button" class="btn btn-outline-danger btn-sm btn_remove_exc" value="<?= $item->exception_id ?>"><i class="bi bi-x-lg"></i></button>
								</div>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
				<a class="btn btn-primary mt-3" href="<?= base_url() ?>module/hr_attendance">Close & Refresh Page</a>
			</div>
		</div>
	</div>
</div>
<input type="hidden" id="ip_period" value="<?= $period ?>">

<script>
function apply_filter(dom){
	var criteria = $(dom).val().toUpperCase();
	
	$(".row_emp").each(function(index, elem) {
		if ($(elem).find(".search_criteria").html().toUpperCase().includes(criteria)) $(elem).show();
		else $(elem).hide();
	});
}

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
	
	$("#sl_dept").change(function(e) {
		$("#ip_search").val('');
		apply_filter(this);
	});
	
	$("#ip_search").change(function(e) {
		$("#sl_dept").val('');
		apply_filter(this);
	});
	
	$(".md_exception").click(function() {
		$("#sl_type").val("");
		$("#ip_pr").val($(this).attr("emp_pr"));
		$("#ip_name").val($(this).attr("emp_name"));
		$("#ip_from").val($(this).attr("date"));
		$("#ip_to").val($(this).attr("date"));
		$('.datepicker').datepicker('update', $(this).attr("date"));
	});
	
	$(".md_holiday").click(function() {
		$("#ip_from").val($(this).attr("date"));
		$("#ip_to").val($(this).attr("date"));
		$('.datepicker').datepicker('update', $(this).attr("date"));
	});
	
	$(".form_add_exception").submit(function(e) {
		e.preventDefault();
		$("#form_add_exception .sys_msg").html("");
		ajax_form_warning(this, "module/hr_attendance/add_exception", "Do you want to add exception?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/hr_attendance");
		});
	});
	
	$(".btn_remove_exc").click(function() {
		var exc_id = $(this).val();
		
		ajax_simple_warning({exc_id: exc_id}, "module/hr_attendance/remove_exception", "Remove selected exception?").done(function(res) {
			toastr.success("Exception removed !!!", null, {timeOut: 5000});
			$("#row_exc_" + exc_id).remove();
		});
	});
	
	ajax_simple({p: $("#ip_period").val()}, "module/hr_attendance/export").done(function(res) {
		$("#btn_export").removeClass("d-none");
		$("#btn_export").attr("href", res.url);
	});
	
});
</script>