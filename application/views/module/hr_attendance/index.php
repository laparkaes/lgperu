<div class="pagetitle d-flex justify-content-between align-items-center">
    <h1>Attendance</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#optionsModal">
		<i class="bi bi-gear"></i>
	</button>
</div>
<nav>
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active">Attendance</li>
    </ol>
</nav>

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
								<th scope="col" class="text-center">Working<br/>Days</th>
								<th scope="col" class="text-center">T<br/>E</th>
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
									//print_r($now); echo '<br>';
									$aux = [];
									$mRemarks = ["MV", "MB", "MBT", "MCO", "MCMP", "MHO", "MT"];
									$aRemarks = ["AV", "AB", "ABT", "ACO", "ACMP", "AHO", "AT"];
									if ($now["first_access"]["time"]){
										if (in_array($now["first_access"]["remark"], $mRemarks)) $aux[] = $now["first_access"]["remark"];
										
										switch($now["first_access"]["remark"]){
											case "T": $color = "danger"; break;
											case "TT": $color = "success"; break;
											default: $color = "";
										}
										
										$aux[] = '<span class="text-'.$color.'">'.$now["first_access"]["time"].'</span>';
									}else $aux[] = $now["first_access"]["remark"];
									
									if ($now["last_access"]["time"]){
																			
										switch($now["last_access"]["remark"]){
											case "E": $color = "danger"; break;
											//case "TT": $color = "success"; break;
											default: $color = "";
										}
										
										$aux[] = '<span class="text-'.$color.'">'.$now["last_access"]["time"].'</span>';
										
										if (in_array($now["last_access"]["remark"], $aRemarks)) $aux[] = $now["last_access"]["remark"];
									}else $aux[] = $now["last_access"]["remark"];
									
									// if ($now["last_access"]["time"]){
										
										// $aux[] = '<span class="text-'.($now["last_access"]["remark"] === "E" ? "danger" : "").'">'.$now["last_access"]["time"].'</span>';
										// if (in_array($now["last_access"]["remark"], $aRemarks)) $aux[] = $now["last_access"]["remark"];
									// }else $aux[] = $now["last_access"]["remark"];
									
									
									
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

<div class="modal fade" id="legendModal" tabindex="-1" aria-labelledby="legendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="legendModalLabel">Type Exception List</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
			
            <div class="modal-body">
                <table id="legendTable" class="table datatable" style="width:100%">
                    <thead>
                        <tr>
                            <th>Type Exception</th>
                            <th>Incidence</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>H</td><td>Holiday</td></tr>
                        <tr><td>EF</td><td>Early Friday</td></tr>
                        <tr><td>BT</td><td>Biz Trip</td></tr>
                        <tr><td>CE</td><td>Ceased</td></tr>
                        <tr><td>CO</td><td>Commission</td></tr>
                        <tr><td>CMP</td><td>Compensation</td></tr>
                        <tr><td>HO</td><td>Home Office</td></tr>
                        <tr><td>L</td><td>License</td></tr>
                        <tr><td>MV</td><td>Morning Vacation</td></tr>
                        <tr><td>AV</td><td>Afternoon Vacation</td></tr>
                        <tr><td>V</td><td>Vacation</td></tr>
                        <tr><td>MED</td><td>Medical Vacation</td></tr>
                        <tr><td>MB</td><td>Morning Birthday</td></tr>
                        <tr><td>AB</td><td>Afternoon Birthday</td></tr>
                        <tr><td>MBT</td><td>Morning Biz Trip</td></tr>
                        <tr><td>ABT</td><td>Afternoon Biz Trip</td></tr>
                        <tr><td>MCO</td><td>Morning Commission</td></tr>
                        <tr><td>ACO</td><td>Afternoon Commission</td></tr>
                        <tr><td>MCMP</td><td>Morning Compensation</td></tr>
                        <tr><td>ACMP</td><td>Afternoon Compensation</td></tr>
                        <tr><td>MHO</td><td>Morning Home Office</td></tr>
                        <tr><td>AHO</td><td>Afternoon Home Office</td></tr>
                        <tr><td>MT</td><td>Morning Topic</td></tr>
                        <tr><td>AT</td><td>Afternoon Topic</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="backToPrevious">
                    <i class="bi bi-arrow-left-circle"></i>
                </button>
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
				
				<!--<div>H: Holiday | EF: Early Friday<br/>MV: Morning Vacation | AV: Afternoon Vacation | V: Vacation | MED: Medical Vacation<br/>
				MB: Morning Birthday | AB: Afternoon Birthday | BT: Biz Trip | MBT: Morning Biz Trip<br/>ABT: Afternoon Biz Trip
				| CE: Ceased | CO: Commission | MCO: Morning Commission<br/>ACO: Afternoon Commission | CMP: Compensation | MCMP: Morning Compensation <br/>
				ACMP: Afternoon Compensation | HO: Home Office | MHO: Morning Home Office <br/>AHO: Afternoon Home Office | L: License | 
				MT: Morning Topic | AT: Afternoon Topic</div>-->
				
				<table class="table datatable">
					<div class="d-flex justify-content-left mb-2">
						<button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#legendModal">
							Type Exceptions List
						</button>
					</div>
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

<div class="modal fade" id="optionsModal" tabindex="-1" aria-labelledby="optionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="optionsModalLabel">Upload Absenteeism Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="d-grid gap-3">
						<form id="form_hr_attendance_update">
							<!--<button type="button" class="btn btn-primary btn-lg" id="uploadButton">-->
								<div class="input-group">
									<a class="btn btn-success" href="<?= base_url() ?>template/hr_incidence_template.xlsx" download="hr_incidence_template"><i class="bi bi-file-earmark-spreadsheet"></i> </a>
									
									<input class="form-control" type="file" name="attach">
									<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i></button>
									<!-- <i class="bi bi-upload me-2"></i> Upload Absenteeism -->
								</div>
							</button>
						</form>
						
						
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="ip_period" value="<?= $period ?>">



<script>
function apply_filter(dom){
	var criteria = $(dom).val().toUpperCase();
	
	if (criteria == ""){
		$(".row_emp").show();
	}else{
		$(".row_emp").each(function(index, elem) {
			if ($(elem).find(".search_criteria").html().toUpperCase().includes(criteria)) $(elem).show();
			else $(elem).hide();
		});	
	}
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

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_hr_attendance_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "module/hr_attendance/update", "Do you want to upload stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/hr_attendance");
		});
	});
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    $(document).ready(function() {
        // ... (tu código existente) ...

        $('#backToPrevious').click(function() {
            $('#legendModal').modal('hide'); // Oculta la modal de la leyenda
            $('#md_exception_list').modal('show'); // Muestra la modal anterior
        });
    });
});
</script>