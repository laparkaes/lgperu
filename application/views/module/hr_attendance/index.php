<head>
	<style>
		/* Aseguramos que el contenedor del input tenga posición relativa */
		.position-relative {
			position: relative;
		}

		/* Estilo para el input */
		#search_input {
			padding-right: 20px;  /* Hacer espacio para la X dentro del input */
		}

		/* Estilo del botón X */
		.clear-input {
			position: absolute;
			right: 20px;
			top: 50%;
			transform: translateY(-50%);
			cursor: pointer;
			font-size: 18px;
			color: #888;
			display: none; /* Inicialmente oculto */
		}

		/* Estilo para el dropdown */
		.dropdown-list {
			display: none;
			max-height: 200px;
			overflow-y: auto;
			border: 1px solid #ccc;
			position: absolute;
			width: 100%;
			background-color: white;
			z-index: 9999;
		}

		.dropdown-item {
			padding: 8px;
			cursor: pointer;
		}

		.dropdown-item:hover {
			background-color: #f1f1f1;
		}
	</style>
</head>
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
							<button type="button" class="btn btn-primary me-1" data-bs-toggle="modal" data-bs-target="#md_exception_list">
								<i class="bi bi-calendar2-week-fill"></i>
							</button>
							<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#export_att_report">
								<i class="bi bi-file-earmark-spreadsheet"></i>
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
									<div class="d-flex justify-content-between align-items-center">
										<div>
											<?php
											$aux = [];
											if ($item["data"]->name) $aux[] = $item["data"]->name;
											if ($item["data"]->employee_number) $aux[] = $item["data"]->employee_number;
											?>
											<?= implode(", ", $aux) ?>
											<br/><small><?= $item["data"]->dept ?></small>
											
										</div>
										
										<!--<a href="" class="btn btn-sm btn-primary me-1" id="btn_export_employee"  data-bs-toggle="modal" data-bs-target="#exportModalEmployee" data-pr="<?= $item["data"]->employee_number ?>" data-name="<?= $item["data"]->name ?>">
											<i class="bi bi-file-earmark-spreadsheet"></i>
										</a>-->
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
									$mRemarks = ["MV", "MB", "MBT", "MCO", "MCMP", "MHO", "MT", "NEF"];
									$aRemarks = ["AV", "AB", "ABT", "ACO", "ACMP", "AHO", "AT"];
									//$exc_days = ["NEF"]
									if ($now["first_access"]["time"]){
										if (in_array($now["first_access"]["remark"], $mRemarks)) $aux[] = $now["first_access"]["remark"];
										
										//print_r($now["first_access"]["remark"]);
										switch($now["first_access"]["remark"]){
											case "T": $color = "danger"; break;
											case "NEF": $color = ""; break;
											//case "TT": $color = "success"; break;
											default: $color = "";
										}
										// elseif($now["first_access"]["remark"]==='E'){
											// switch($now["first_access"]["remark"]){
												// case "T": $color = "danger"; break;
												// case "TT": $color = "success"; break;
												// default: $color = "";
											// }
										// }
										
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

<!--<div class="d-none" id="bl_export_result"></div>
	<div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Report Employee </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
			
            <form action="<?= base_url('module/hr_attendance/generate_report_attendance')?>" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">PR</label>
                        <input type="text" class="form-control" id="prCode" name="prCode" readonly required>
                    </div>
                    <div class="mb-3">
                        <label for="personName" class="form-label">Employee Name</label>
                        <input type="text" class="form-control" id="personName" name="personName" readonly required>
                    </div>
                    <div class="mb-3">
                        <label for="fromDate" class="form-label">From</label>
                        <input type="date" class="form-control" id="fromDate" name="fromDate" required>
                    </div>
                    <div class="mb-3">
                        <label for="toDate" class="form-label">To</label>
                        <input type="date" class="form-control" id="toDate" name="toDate" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="exportDataButton">Export</button>
                </div>
            </form>
        </div>
    </div>
</div>-->

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
			
            <div class="modal-body" style="overflow-y: auto; max-height: 600px;">
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
                        <tr><td>MV</td><td>Vacation Morning</td></tr>
                        <tr><td>AV</td><td>Vacation Afternoon</td></tr>
                        <tr><td>V</td><td>Vacation</td></tr>
                        <tr><td>MED</td><td>Medical</td></tr>
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
						<tr><td>J</td><td>Justified</td></tr>
						<tr><td>NEF</td><td>No Early Friday</td></tr>
						<tr><td>S</td><td>Suspension</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" id="backToPrevious">
                     <span class="fs-2"><i class="bi bi-arrow-left-circle"></i></span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="DeleteModal" tabindex="-1" aria-labelledby="DeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
				<h5 class="card-title">Delete Last Files <?= $period ?></h5>
                <!--<h5 class="modal-title" id="DeleteModalLabel">Delete Last Files</h5>-->
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="deleteFilesForm">
					<table class="table datatable-editor-editable">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>File Name</th>
                                <th>Date Uploaded</th>
                            </tr>
                        </thead>
                        <tbody id="filesToDelete">
                        </tbody>
                    </table>
                    <div id="fileSummary"></div>
					
					<div class="d-flex justify-content-between mt-3">
						<button type="button" class="btn btn-white" id="backToPreviousDeleteFiles">						
							<span class="fs-4"><i class="bi bi-arrow-left-circle"></i></span>
						</button>
						<button type="submit" class="btn btn-danger" id="confirmDeleteButton">Delete</button>
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
                <h5 class="card-title">Exceptions <?= $period ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
				<div class="row align-items-center mb-3">
					<div class="col-md">
						<div class="row align-items-center">
							<div class="col-auto ms-1">
								<label for="filter_exc_type" class="form-label visually-hidden">Type</label>
								<select id="filter_exc_type" class="form-select form-select-sm" name="filter_exc_type">
									<option value="">All Types</option>
									<?php foreach($exceptions_emp as $item){ ?>
									<option value="<?= $item[0] ?>"><?= $item[1] ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="col-auto ms-1">
								<label for="filter_exc_date_fm" class="form-label visually-hidden">Filter by Date</label>
								<input type="date" class="form-control form-control-sm" id="filter_exc_date_fm">
							</div>
						</div>
					</div>
					
					<div class="col-auto">
						<button type="button" class="btn btn-sm btn-outline-success me-2" data-bs-toggle="modal" data-bs-target="#DeleteModal">
							<a style='display:None;'> Files Uploaded </a>
 							<i class="bi bi-file-earmark-spreadsheet-fill"></i>
						</button>
						
						<button type="button" class="btn btn-sm btn-outline-primary float-end" data-bs-toggle="modal" data-bs-target="#legendModal">
							<i class="bi bi-card-checklist"></i>
							
						</button>
					</div>
				</div>
				
				<form id="exceptions_table_body">
					<table class="table datatable-editor-editable" >
						<thead>
							<tr>
								<th scope="col">Select</th>
								<th scope="col">PR</th>
								<th scope="col">Name</th>
								<th scope="col">Date</th>
								<th scope="col">Type</th>
								<th scope="col">Remark</th>
								<th scope="col"></th>
							</tr>
						</thead>
						<tbody >
							<?php foreach($exceptions as $item){ ?>
							<tr name="row_exc_[]" id="row_exc_<?=$item->exception_id?>" data-exc-type="<?= $item->type ?>" data-exc-date="<?= $item->exc_date ?>" data-index="<?= $item->exception_id ?>" >
								<td><input type="checkbox" name="checkbox_exc[]" id="checkbox_exc_<?= $item->exception_id ?>" class="file-checkbox-exc" value="<?= $item->exception_id ?>" data-index="<?= $item->exception_id ?>"></td>
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
				</form>
                <div class="d-flex justify-content-between mt-3">
                    <a class="btn btn-primary" style="display:None;" href="<?= base_url() ?>module/hr_attendance">Close &amp; Refresh Page</a>
                    
                    <button type="button" class="btn btn-danger" id="confirmDeleteButtonExc">Delete</button>
                </div>
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
					</form>												
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="export_att_report" tabindex="-1" aria-labelledby="export_att_reportLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="export_att_reportLabel">Attendance Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3 align-items-center">
                    <div class="col-md-12 d-flex align-items-center">
                        <div id="select_all_container" class="form-check me-2">
                            <input type="checkbox" id="select_all" class="form-check-input" name="report_type" value="all">
                            <label class="form-check-label" for="select_all">Select All</label>
                        </div>
                        <div id="select_department_container" class="form-check me-2">
                            <input type="checkbox" id="select_department" class="form-check-input" name="report_type" value="department">
                            <label class="form-check-label" for="select_department">Department</label>
                        </div>
                        <div class="flex-grow-1 position-relative me-2"> <input type="text" id="search_input" placeholder="Search PR" class="form-control">
                            <span id="clear_input" class="clear-input" style="display: none;">&times;</span>
                            <div id="dropdown_list" class="dropdown-list"></div>
                        </div>
                        <button type="button" id="add_item" class="btn btn-success">+</button>
                    </div>
                </div>
                <div id="added_items" class="d-flex flex-wrap gap-2 mb-3">
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="from_date">From:</label>
                        <input type="date" id="from_date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label for="to_date">To:</label>
                        <input type="date" id="to_date" class="form-control">
                    </div>
                </div>

                <div id="loading_spinner" style="display: none; text-align: center;">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Generating Report...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="export_report_btn">Export</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="success_modal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">Report Generated Successfully</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                <p class="mt-3">The report has been generated successfully.<br>Click OK to download.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success btn-lg" id="download_report_btn">OK</button>
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
        $('#backToPrevious').click(function() {
            $('#legendModal').modal('hide'); // Oculta la modal de la leyenda
            $('#md_exception_list').modal('show'); // Muestra la modal anterior
        });
    });
	
	$(document).ready(function() {
        $('#backToPreviousDeleteFiles').click(function() {
            $('#DeleteModal').modal('hide'); // Oculta la modal de la leyenda
            $('#md_exception_list').modal('show'); // Muestra la modal anterior
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search_input');
    const clearInputButton = document.getElementById('clear_input');
    const addItemButton = document.getElementById('add_item');
    const addedItemsDiv = document.getElementById('added_items');
    const exportButton = document.getElementById('export_report_btn');
    const dropdownList = document.getElementById('dropdown_list');
    const selectAllCheckbox = document.getElementById('select_all');
    const selectDepartmentCheckbox = document.getElementById('select_department');
    const loadingSpinner = document.getElementById('loading_spinner');

    let addedItems = [];
    let employeeData = [];
    let successModal;

    function clearAttendanceModal() {
        searchInput.value = '';
        clearInputButton.style.display = 'none';
        dropdownList.innerHTML = '';
        addedItems = [];
        addedItemsDiv.innerHTML = '';
        document.getElementById('from_date').value = '';
        document.getElementById('to_date').value = '';
        selectAllCheckbox.checked = false;
        selectDepartmentCheckbox.checked = false;
        selectAllCheckbox.disabled = false; // Asegúrate de habilitar de nuevo
        selectDepartmentCheckbox.disabled = false; // Asegúrate de habilitar de nuevo
        searchInput.disabled = false;
        addItemButton.disabled = false;
        searchInput.placeholder = 'Search PR';
    }

    function getEmployeeData() {
        fetch('hr_attendance/get_info_employee')
            .then(response => response.json())
            .then(data => {
                employeeData = data;
            })
            .catch(error => console.error('Error al obtener los datos de empleados:', error));
    }

    getEmployeeData();

    function getUniqueDepartments() {
        const uniqueDepartments = {};
        employeeData.forEach(employee => {
            const departmentKey = `${employee.organization} - ${employee.department}`;
            if (!uniqueDepartments[departmentKey]) {
                uniqueDepartments[departmentKey] = {
                    organization: employee.organization,
                    department: employee.department
                };
            }
        });
        return Object.values(uniqueDepartments);
    }

    function renderDropdown(data, type = 'employee') {
        dropdownList.innerHTML = '';
        data.forEach(item => {
            const div = document.createElement('div');
            div.classList.add('dropdown-item');
            div.textContent = type === 'employee' ? `${item.employee_number} - ${item.name}` : `${item.organization} - ${item.department}`;
            div.addEventListener('click', function() {
                searchInput.value = type === 'employee' ? `${item.employee_number} - ${item.name}` : `${item.organization} - ${item.department}`;
                dropdownList.innerHTML = '';
                clearInputButton.style.display = 'inline';
            });
            dropdownList.appendChild(div);
        });
        dropdownList.style.display = data.length > 0 ? 'block' : 'none';
    }

    searchInput.addEventListener('focus', function() {
        if (!selectAllCheckbox.checked && !selectDepartmentCheckbox.checked) {
            renderDropdown(employeeData);
        } else if (selectDepartmentCheckbox.checked) {
            renderDropdown(getUniqueDepartments(), 'department');
        }
    });

    searchInput.addEventListener('input', function() {
        if (!selectAllCheckbox.checked && !selectDepartmentCheckbox.checked) {
            const searchTerm = searchInput.value.toLowerCase();
            const filteredData = employeeData.filter(employee =>
                employee.employee_number.toLowerCase().includes(searchTerm) ||
                employee.name.toLowerCase().includes(searchTerm)
            );
            renderDropdown(filteredData);
        } else if (selectDepartmentCheckbox.checked) {
            const searchTerm = searchInput.value.toLowerCase();
            const uniqueDepartments = getUniqueDepartments();
            const filteredData = uniqueDepartments.filter(dept =>
                `${dept.organization} - ${dept.department}`.toLowerCase().includes(searchTerm)
            );
            renderDropdown(filteredData, 'department');
        }
    });

    document.addEventListener('click', function(event) {
        if (!searchInput.contains(event.target) && !dropdownList.contains(event.target)) {
            dropdownList.style.display = 'none';
        }
    });

    clearInputButton.addEventListener('click', function() {
        searchInput.value = '';
        clearInputButton.style.display = 'none';
        dropdownList.innerHTML = '';
    });

    addItemButton.addEventListener('click', function() {
        const itemText = `${searchInput.value}`;
        if (searchInput.value && !addedItems.includes(itemText)) {
            addedItems.push(itemText);
            const newItem = document.createElement('div');
            newItem.classList.add('border', 'p-1', 'rounded', 'd-inline-flex', 'align-items-center');
            newItem.innerHTML = `
                <small>${itemText}</small>
                <button type="button" class="btn-close btn-close-sm ms-1" aria-label="Eliminar"></button>
            `;
            addedItemsDiv.appendChild(newItem);
            newItem.querySelector('.btn-close').addEventListener('click', function() {
                addedItems = addedItems.filter(item => item !== itemText);
                newItem.remove();
            });
        }
        searchInput.value = ''; // Clear input after adding
        clearInputButton.style.display = 'none';
        dropdownList.innerHTML = '';
    });

    selectAllCheckbox.addEventListener('change', function() {
        addedItems = [];
        addedItemsDiv.innerHTML = '';
        searchInput.value = '';
        clearInputButton.style.display = 'none';
        dropdownList.innerHTML = '';
        if (this.checked) {
            selectDepartmentCheckbox.checked = false;
            searchInput.value = 'All data selected';
            searchInput.disabled = true;
            addItemButton.disabled = true;
            selectDepartmentCheckbox.disabled = true;
        } else {
            searchInput.disabled = false;
            addItemButton.disabled = false;
            selectDepartmentCheckbox.disabled = false;
            searchInput.placeholder = 'Search PR';
        }
    });

    selectDepartmentCheckbox.addEventListener('change', function() {
        addedItems = [];
        addedItemsDiv.innerHTML = '';
        searchInput.value = '';
        clearInputButton.style.display = 'none';
        dropdownList.innerHTML = '';
        if (this.checked) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.disabled = true;
            searchInput.placeholder = 'Search Organization - Department';
        } else {
            selectAllCheckbox.disabled = false;
            searchInput.placeholder = 'Search PR';
        }
    });

    // Inicializar el estado del modal al cargar
    searchInput.disabled = false;
    addItemButton.disabled = false;
    selectAllCheckbox.disabled = false;
    selectDepartmentCheckbox.disabled = false;

    function handleDownload(response, fromDate, toDate) {
        response.blob().then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Attendance_Report_${fromDate}_to_${toDate}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            successModal.hide();
        });
        clearAttendanceModal();
    }

    exportButton.addEventListener('click', function() {
        loadingSpinner.style.display = 'block';

        let dataToSend = {};

        if (selectAllCheckbox.checked) {
            dataToSend.prCodes = JSON.stringify(employeeData.map(employee => employee.employee_number));
        } else if (selectDepartmentCheckbox.checked && addedItems.length > 0) {
            const departmentsArray = addedItems.map(item => {
                const parts = item.split(' - ');
                return [parts[0].trim(), parts[1].trim()];
            });
            dataToSend.departments = JSON.stringify(departmentsArray);
        } else if (!selectAllCheckbox.checked && !selectDepartmentCheckbox.checked && addedItems.length > 0) {
            dataToSend.prCodes = JSON.stringify(addedItems.map(item => item.split(' - ')[0].trim()));
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Please select employees or departments.',
            });
            loadingSpinner.style.display = 'none';
            return;
        }

        const fromDateInitial = document.getElementById('from_date').value; // Captura las fechas aquí para la petición
        const toDateInitial = document.getElementById('to_date').value;
		
		
        if (!fromDateInitial || !toDateInitial) {
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Please select a valid date range.',
            });
            loadingSpinner.style.display = 'none';
            return;
        }

        dataToSend.fromDate = fromDateInitial;
        dataToSend.toDate = toDateInitial;

        const requestBody = new URLSearchParams(dataToSend).toString();
        const reportEndpoint = 'hr_attendance/generate_report_attendance';

        fetch(reportEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: requestBody,
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            loadingSpinner.style.display = 'none';

            successModal = new bootstrap.Modal(document.getElementById('success_modal'));
            successModal.show();

            clearAttendanceModal(); // Limpia el modal principal después de mostrar el éxito
			console.log("fromDateInitial: ", fromDateInitial);
			console.log("toDateInitial: ", toDateInitial);
            // Obtén las fechas DENTRO del listener del botón de descarga
            document.getElementById('download_report_btn').addEventListener('click', function() {
				handleDownload(response, fromDateInitial, toDateInitial); // Pasa response, fromDate y toDate
			});
        })
        .catch(error => {
            console.error('Error al generar el reporte:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to generate the report.',
            });
            loadingSpinner.style.display = 'none';
        });
    });
});
</script>
		
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Manejo de eliminación masiva de excepciones
    document.getElementById("confirmDeleteButtonExc").addEventListener("click", function() {
        var selectedExceptions = [];

        // Recorrer todos los checkboxes marcados
        document.querySelectorAll('.file-checkbox-exc:checked').forEach(function(checkbox) {
            console.log(checkbox.value);
            var row = checkbox.closest('tr'); // Obtener la fila del checkbox
            var exceptionId = row.id.replace('row_exc_', ''); // Extraer el exception_id del id del <tr>
            console.log(exceptionId);
            selectedExceptions.push(checkbox.value);
            //selectedExceptions.push(exceptionId);
            console.log(row);
        });

        // Verificar si hay excepciones seleccionadas
        if (selectedExceptions.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No exceptions selected',
                text: 'Please select at least one exception to delete.'
            });
            return;
	}

        // Confirmación antes de eliminar
        Swal.fire({
            title: 'Are you sure?',
            text: 'You are about to delete the selected exceptions.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar datos por AJAX
                $.ajax({
                    url: '<?= base_url('module/hr_attendance/delete_data_file_first_modal'); ?>',
                    type: 'POST',
                    data: { archivos: selectedExceptions },
                    dataType: 'json',
                    success: function(response) {
                        if (response.type === 'success') {
                            // Eliminar las filas de la tabla después de borrar en la DB
                            selectedExceptions.forEach(function(id) {
                                document.getElementById("row_exc_" + id)?.remove();
                            });

                            Swal.fire(
                                'Deleted!',
                                'Selected exceptions have been deleted.',
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error!',
                                'An error occurred while deleting exceptions.',
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'There was a problem connecting to the server.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Lógica de filtrado para el modal exceptions
    const mdExceptionList = document.getElementById('md_exception_list');
    if (mdExceptionList) {
        mdExceptionList.addEventListener('shown.bs.modal', function () {
            const filterExcType = document.getElementById('filter_exc_type');
            const filterExcDateFm = document.getElementById('filter_exc_date_fm');
            const exceptionsTableBody = document.getElementById('exceptions_table_body');

            if (exceptionsTableBody) {
                const exceptionRows = exceptionsTableBody.querySelectorAll('tbody > tr');

                function filterTable() {
                    const selectedType = filterExcType?.value?.toUpperCase() || '';
                    const selectedDate = filterExcDateFm?.value || '';

                    exceptionRows.forEach(row => {
                        // Obtener todas las celdas <td> dentro de la fila
                        const cells = row.querySelectorAll('td');
                        const rowType = row.dataset.excType || '';
                        const rowDate = row.dataset.excDate || '';

                        const rowTypeCellText = cells[4]?.textContent?.toUpperCase().trim() || '';
                        const rowDateCellText = cells[3]?.textContent?.trim() || '';

                        let typeMatch = true;
                        let dateMatch = true;

                        if (selectedType && selectedType !== '') {
                            typeMatch = rowTypeCellText === selectedType;
                        } else if (selectedType === '') {
                            typeMatch = true; // Si no se selecciona tipo, todas coinciden
                        }

                        if (selectedDate && selectedDate !== '') {
                            dateMatch = rowDateCellText === selectedDate;
                        } else if (selectedDate === '') {
                            dateMatch = true; // Si no se selecciona fecha, todas coinciden
                        }

                        if (typeMatch && dateMatch) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                }

                if (filterExcType) {
                    filterExcType.addEventListener('change', filterTable);
                } else {
                    console.error("Error: El elemento filter_exc_type no fue encontrado.");
                }

                if (filterExcDateFm) {
                    filterExcDateFm.addEventListener('change', filterTable);
                } else {
                    console.error("Error: El elemento filter_exc_date_fm no fue encontrado.");
                }
            } else {
                console.error("Error: No se encontró el elemento exceptions-table-body después de mostrar el modal.");
            }
        });
    } else {
        console.error("Error: No se encontró el modal md_exception_list.");
    }

	$(document).on('dblclick', '#exceptions_table_body tr > td:not(:first-child):not(:last-child)', function () {
        var cell = $(this);
        var originalValue = cell.text().trim();
        var columnIndex = cell.index();
        var inputElement;
        var row = cell.closest('tr');
        var exceptionId = row.find('input[type="checkbox"].file-checkbox-exc').val();
        var columnName = $(this).closest('table').find('thead th').eq(columnIndex).text().toLowerCase().replace(/ /g, '_'); // Obtener el nombre de la columna del header

        cell.data('original-value', originalValue);
        cell.empty();
		
        var editContainer = $('<div class="edit-container-exceptions" style="display: flex; align-items: center;"></div>');

        if (columnName === 'type') {
            var options = ["Vacation - V", "Vacation Morning - MV", "Vacation Afternoon - AV", "Medical - MED", "Morning Birthday - MB", "Afternoon Birthday - AB", "Biz Trip - BT",
                "Morning Biz Trip - MBT", "Afternoon Biz Trip - ABT", "Ceased - CE", "Commission - CO", "Morning Commission - MCO", "Afternoon Commission - ACO", "Compensation - CMP",
                "Morning Compensation - MCMP", "Afternoon Compensation - ACMP", "Home Office - HO", "Morning Home Office - MHO", "Afternoon Home Office - AHO", "License - L", "Morning Topic - MT",
                "Afternoon Topic - AT", "Justified - J", "No Early Friday - NEF", "Suspension - S"];
            let type_selected = {
                "Vacation - V": "V", "Vacation Morning - MV": "MV", "Vacation Afternoon - AV": "AV", "Medical - MED": "MED", "Morning Birthday - MB": "MB", "Afternoon Birthday - AB": "AB",
                "Biz Trip - BT": "BT", "Morning Biz Trip - MBT": "MBT", "Afternoon Biz Trip - ABT": "ABT", "Ceased - CE": "CE", "Commission - CO": "CO", "Morning Commission - MCO": "MCO",
                "Afternoon Commission - ACO": "ACO", "Compensation - CMP": "CMP", "Morning Compensation - MCMP": "MCMP", "Afternoon Compensation - ACMP": "ACMP", "Home Office - HO": "HO",
                "Morning Home Office - MHO": "MHO", "Afternoon Home Office - AHO": "AHO", "License - L": "L", "Morning Topic - MT": "MT", "Afternoon Topic - AT": "AT", "Justified - J": "J", "No Early Friday - NEF": "NEF", "Suspension - S": "S"
            };
            inputElement = $('<select class="form-control form-control-sm">');
            $.each(options, function (i, option) {
                inputElement.append($('<option>', {
                    value: type_selected[option],
                    text: option,
                    selected: type_selected[option] === originalValue
                }));
            });
			inputElement.keydown(function (event) {
				if (event.keyCode === 13) { // Enter key
					event.preventDefault(); // Evitar la acción predeterminada
					$(this).closest('.edit-container-exceptions').find('.btn-primary').click(); // Simular clic en el botón "Guardar"
				}
			});
        } else {
            inputElement = $('<input type="text" class="form-control-sm form-control" value="' + originalValue + '">');
			inputElement.keydown(function (event) {
				if (event.keyCode === 13) { // Enter key
					event.preventDefault(); // Evitar la acción predeterminada
					$(this).closest('.edit-container-exceptions').find('.btn-primary').click(); // Simular clic en el botón "Guardar"
				}
			});
        }

        var saveButton = $('<button type="button" class="btn btn-primary btn-sm ms-2"><i class="bi bi-check-lg"></i></button>');
        editContainer.append(inputElement).append(saveButton);
        cell.append(editContainer);
        inputElement.focus();

        inputElement.on('blur', function () {
            setTimeout(() => {
                if (!cell.find('.edit-container-exceptions:focus-within').length && !$(document.activeElement).is(saveButton)) {
                    cell.html(cell.data('original-value'));
                }
            }, 100);
        });
		
    });
	// Evento click to save new data
	$(document).off('click', '#exceptions_table_body .edit-container-exceptions .btn-primary').on('click', '#exceptions_table_body .edit-container-exceptions .btn-primary', function () {	
        var saveButton = $(this);
        var cell = saveButton.closest('td');
        var editContainer = saveButton.closest('.edit-container-exceptions');
        var inputElement = editContainer.find('input.form-control-sm, select.form-control-sm');
        var newValue = inputElement.val();
        var row = cell.closest('tr');
		
        var exceptionId = row.find('input[type="checkbox"].file-checkbox-exc').val();
        var columnIndex = cell.index();
        var columnName = $(this).closest('table').find('thead th').eq(columnIndex).text().toLowerCase().replace(/ /g, '_'); // Obtener el nombre de la columna del header

        if (exceptionId && columnName) {
            // Aquí realizas la llamada AJAX para guardar el newValue en tu backend
            $.ajax({
                url: '<?= base_url('module/hr_attendance/update_exception_cell'); ?>', 
                type: 'POST',
                data: {
                    exception_id: exceptionId,
                    column: columnName,
                    value: newValue
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        Swal.fire('Modified!', 'The cell value has been modified.', 'success').then(() => {
                            cell.html(newValue); // Actualizar la celda con el nuevo valor
                        });
                    } else {
                        Swal.fire('Error!', 'There was an error modifying the cell.', 'error').then(() => {
                            cell.html(cell.data('original-value')); // Revertir al valor original
                        });
                    }
                },
                error: function () {
                    Swal.fire('Error!', 'There was an error connecting to the server.', 'error').then(() => {
                        cell.html(cell.data('original-value')); // Revertir al valor original
                    });
                }
            });
        } else {
            Swal.fire('Error!', 'Exception ID not found.', 'error').then(() => {
                cell.html(cell.data('original-value')); // Revertir
            });
        }
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Evento para confirmar la eliminación
    var selectedFiles = [];
    $("#deleteFilesForm").submit(function(e) {
		e.preventDefault();

		var archivos_a_eliminar = $('.file-checkbox:checked').map(function(){
			return $(this).val();
		}).get();

		if (archivos_a_eliminar.length === 0) {
			Swal.fire({
				icon: 'warning',
				title: 'No files selected',
				text: 'Please select at least one file to delete.',
			});
			return;
		}

		//console.log("Archivos a eliminar:", archivos_a_eliminar); // <--- Agrega esta línea

		ajax_form_warning(this, "module/hr_attendance/delete_data_file", "Are you sure you want to delete the selected files?")
		.done(function(res) {
			swal_redirection(res.type, res.msg, "module/hr_attendance");
			$('#DeleteModal').modal('hide');
			// Recargar la tabla de archivos si es necesario
			// $('#filesToDelete').empty();
			// $('#DeleteModal').modal('show');
		});
	});


    $('#DeleteModal').on('show.bs.modal', function () {
		var period = $('#sl_period').val();
        $.ajax({
            url: '<?= base_url('module/hr_attendance/get_files'); ?>',
            type: 'GET',
            dataType: 'json',
			data: { period: period },
            success: function (data) {
                var filesHtml = '';
                data.forEach(function (file) {
                    filesHtml += '<tr>' +
                        '<td><input type="checkbox" name="archivos[]" value="' + 'C:/xampp/htdocs' + file.file_path + '" class="file-checkbox"></td>' +
                        '<td>' + file.file_name
						+ '</td>' +
                        '<td>' + new Date(file.modified * 1000).toLocaleString() + '</td>' +
                        '</tr>';
                });
                $('#filesToDelete').html(filesHtml);

                // Evento para mostrar resumen al seleccionar un checkbox
                $('.file-checkbox').change(function () {
                    selectedFiles = [];
                    $('.file-checkbox:checked').each(function () {
                        selectedFiles.push($(this).val());
                    });

                    if (selectedFiles.length > 0) {
                        showMultipleFileSummaries(selectedFiles);
                    } else {
                        $('#fileSummary').empty();
                    }
                });
            }
        });
    });

    function showMultipleFileSummaries(filePaths) {
        $('#fileSummary').empty();

        filePaths.forEach(function (filePath) {
            $.ajax({
                url: '<?= base_url('./module/hr_attendance/data_db_file'); ?>',
                type: 'POST',
                data: { file_path: filePath },
                dataType: 'json',
                success: function (data) {
                    var summaryHtml = '<div style="max-height: 300px; overflow-y: auto; margin-bottom: 15px;"><table class="table" id="editable-summary-table"><thead><tr>' +
                        '<th class="text-center" style="width: 80px;">PR</th>' +
                        '<th class="text-center" style="width: 120px;">Date</th>' +
                        '<th class="text-center" style="width: 150px;">Exception Type</th>' +
                        '<th class="text-center">Remark</th>' +
                        '<th style="width: 80px;"></th>' +
                        '</tr></thead><tbody>';
                    data.forEach(function (row) {
                        summaryHtml += '<tr>' +
                            '<td class="text-center">' + row.pr + '</td>' +
                            '<td class="text-center">' + row.exc_date + '</td>' +
                            '<td class="text-center" data-original-value="' + row.type + '">' + row.type + '</td>' +
                            '<td class="text-center">' + row.remark + '</td>' +
                            '<td>' +
                            '<div class="text-end">' +
                            '<button type="button" class="btn btn-outline-danger btn-sm btn_remove_exc_row" data-pr="' + row.pr + '" data-exc_date="' + row.exc_date + '" data-file_path="' + filePath + '"><i class="bi bi-x-lg"></i></button>' +
                            '</div>' +
                            '</td>' +
                            '</tr>';
                    });
                    summaryHtml += '</tbody></table></div>';
                    $('#fileSummary').append(summaryHtml);

                    // **Modificar el evento dblclick**
                    $('#fileSummary').off('dblclick').on('dblclick', 'td:not(:last-child)', function () {
                        var cell = $(this);
                        var originalValue = cell.text();
                        var columnIndex = cell.index();
                        var inputElement;

                        var row = cell.closest('tr');
                        var pr = row.find('td:eq(0)').text();
                        var excDate = row.find('td:eq(1)').text();
                        var type = row.find('td:eq(2)').data('original-value') || row.find('td:eq(2)').text();
                        var remark = row.find('td:eq(3)').text();
                        var filePath = row.find('.btn_remove_exc_row').data('file_path');
                        var columnNames = { 0: 'pr', 1: 'exc_date', 2: 'type', 3: 'remark' };
                        var columnName = columnNames[columnIndex];

                        cell.data('original-value', originalValue);
                        cell.empty(); // Limpiar el contenido de la celda

                        var editContainer = $('<div class="edit-container" style="display: flex; align-items: center;"></div>');

                        if (columnName === 'type') {
                            var options = ["Vacation - V", "Vacation Morning - MV", "Vacation Afternoon - AV", "Medical - MED", "Morning Birthday - MB", "Afternoon Birthday - AB", "Biz Trip - BT",
                                "Morning Biz Trip - MBT", "Afternoon Biz Trip - ABT", "Ceased - CE", "Commission - CO", "Morning Commission - MCO", "Afternoon Commission - ACO", "Compensation - CMP",
                                "Morning Compensation - MCMP", "Afternoon Compensation - ACMP", "Home Office - HO", "Morning Home Office - MHO", "Afternoon Home Office - AHO", "License - L", "Morning Topic - MT", "Afternoon Topic - AT", "Justified - J", "No Early Friday - NEF", "Suspension - S"];
                            let type_selected = {
                                "Vacation - V": "V", "Vacation Morning - MV": "MV", "Vacation Afternoon - AV": "AV", "Medical - MED": "MED", "Morning Birthday - MB": "MB", "Afternoon Birthday - AB": "AB",
                                "Biz Trip - BT": "BT", "Morning Biz Trip - MBT": "MBT", "Afternoon Biz Trip - ABT": "ABT", "Ceased - CE": "CE", "Commission - CO": "CO", "Morning Commission - MCO": "MCO",
                                "Afternoon Commission - ACO": "ACO", "Compensation - CMP": "CMP", "Morning Compensation - MCMP": "MCMP", "Afternoon Compensation - ACMP": "ACMP", "Home Office - HO": "HO",
                                "Morning Home Office - MHO": "MHO", "Afternoon Home Office - AHO": "AHO", "License - L": "L", "Morning Topic - MT": "MT", "Afternoon Topic - AT": "AT", "Justified - J": "J", "No Early Friday - NEF": "NEF", "Suspension - S": "S"
                            };
                            inputElement = $('<select class="form-control form-control-sm">');
                            $.each(options, function (i, option) {
                                inputElement.append($('<option>', {
                                    value: type_selected[option],
                                    text: option,
                                    selected: type_selected[option] === originalValue
                                }));
                            });
							inputElement.keydown(function (event) {
								if (event.keyCode === 13) { // Enter key
									event.preventDefault(); // Evitar la acción predeterminada
									$(this).closest('.edit-container').find('.btn-primary').click(); // Simular clic en el botón "Guardar"
								}
							});
                        } else {
                            inputElement = $('<input type="text" class="form-control-sm form-control" value="' + originalValue + '">');
							inputElement.keydown(function (event) {
								if (event.keyCode === 13) { // Enter key
									event.preventDefault(); // Evitar la acción predeterminada
									$(this).closest('.edit-container').find('.btn-primary').click(); // Simular clic en el botón "Guardar"
								}
							});
                        }

                        var saveButton = $('<button type="button" class="btn btn-primary btn-sm ms-2"><i class="bi bi-check-lg"></i></button>');

                        editContainer.append(inputElement).append(saveButton);
                        cell.append(editContainer);
                        inputElement.focus();

                        // Opcional: Evento blur para cancelar la edición
						inputElement.on('blur', function(event) {
							// Esperar un breve momento para ver si el clic ocurrió en el botón "Guardar"
							setTimeout(() => {
								if (!cell.find('.edit-container:focus-within').length && !$(document.activeElement).is(saveButton)) {
									cell.html(cell.data('original-value')); // Revertir al valor original
								}
							}, 100); // Un pequeño retraso
						});
                    });

                    // **Evento click para el botón "Guardar"**
                    $('#fileSummary').off('click', '.edit-container .btn-primary').on('click', '.edit-container .btn-primary', function () {
                        var saveButton = $(this);
                        var cell = saveButton.closest('td');
                        var editContainer = saveButton.closest('.edit-container');
                        var inputElement = editContainer.find('input.form-control-sm, select.form-control-sm');
                        var newValue = inputElement.val();
						//console.log("newValue: ", newValue);
                        var row = cell.closest('tr');
                        var pr = row.find('td:eq(0)').text();
                        var excDate = row.find('td:eq(1)').text();
                        var type = row.find('td:eq(2)').data('original-value') || row.find('td:eq(2)').text();
                        var remark = row.find('td:eq(3)').text();
                        var filePath = row.find('.btn_remove_exc_row').data('file_path');
                        var columnIndex = cell.index();
                        var columnNames = { 0: 'pr', 1: 'exc_date', 2: 'type', 3: 'remark' };
                        var columnName = columnNames[columnIndex];

                        if (columnName === 'type') {
                            var validTypes = ['EF', 'BT', 'CE', 'CO', 'CMP', 'HO', 'L', 'MV', 'AV', 'V', 'MED', 'MB', 'AB', 'MBT', 'ABT', 'MCO', 'ACO', 'MCMP', 'ACMP', 'MHO', 'AHO', 'MT', 'AT', 'J', 'NEF', 'S'];
                        }

                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You are about to modify this cell's value.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, modify it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: '<?= base_url('./module/hr_attendance/update_db_file_cell'); ?>',
                                    type: 'POST',
                                    data: { pr: pr, exc_date: excDate, type: type, remark: remark, file_path: filePath, column: columnName, value: newValue },
                                    dataType: 'json',
                                    success: function (response) {
                                        if (response.success) {
                                            Swal.fire('Modified!', 'The cell value has been modified.', 'success').then(() => {
                                                showMultipleFileSummaries(selectedFiles);
                                            });
                                        } else {
                                            Swal.fire('Error!', 'There was an error modifying the cell.', 'error');
                                            cell.html(cell.data('original-value')); // Revertir
                                        }
                                    },
                                    error: function () {
                                        Swal.fire('Error!', 'There was an error connecting to the server.', 'error');
                                        cell.html(cell.data('original-value')); // Revertir
                                    }
                                });
                            } else {
                                cell.html(cell.data('original-value')); // Revertir
                            }
                        });
                    });

                    // Mantener el evento para eliminar filas
                    $('.btn_remove_exc_row').off('click').on('click', function () {
                        var pr = $(this).data('pr');
                        var excDate = $(this).data('exc_date');
                        var filePath = $(this).data('file_path');
                        var button = $(this);

                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Delete'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: '<?= base_url('./module/hr_attendance/delete_db_file_row'); ?>',
                                    type: 'POST',
                                    data: { pr: pr, exc_date: excDate, file_path: filePath },
                                    success: function (response) {
                                        console.log("success option");
                                        if (response.success) {
                                            button.closest('tr').remove();
                                            Swal.fire(
                                                'Deleted!',
                                                'Your file has been deleted.',
                                                'success'
                                            );
                                            showMultipleFileSummaries(selectedFiles); // Recargar después de eliminar
                                        } else {
                                            Swal.fire(
                                                'Error!',
                                                'There was an error deleting the file.',
                                                'error'
                                            );
                                        }
                                    },
                                    error: function () {
                                        Swal.fire(
                                            'Error!',
                                            'There was an error connecting to the server.',
                                            'error'
                                        );
                                    }
                                });
                            }
                        });
                    });
                },
                error: function (xhr, status, error) {
                    console.error("Error al cargar el resumen del archivo:", filePath, status, error);
                }
            });
        });
    }
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$(".btn_remove_exc_files").click(function() {
		var exc_id = $(this).val();
		
		ajax_simple_warning({exc_id: exc_id}, "module/hr_attendance/remove_exception", "Remove selected exception?").done(function(res) {
			toastr.success("Exception removed !!!", null, {timeOut: 5000});
			$("#row_exc_" + exc_id).remove();
		});
	});
});
</script>