
			<div class="card mt-3">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title">Punctuality <?= $period ?></h5>
						<div class="d-flex justify-content-end">
							<select class="form-select me-1" id="sl_period" style="width: 150px;">
								<?php foreach($periods as $item){  ?>
								<option value="<?= $item ?>" <?= ($item === $period) ? "selected" : "" ?>><?= $item ?></option>
								<?php } ?>
							</select>
							<input type="text" class="form-control me-1" id="ip_search" placeholder="Search" style="width: 300px;">
							<a href="" class="btn btn-success d-none me-1" id="btn_export" download="Punctuality <?= $period ?>">
								<i class="bi bi-file-earmark-spreadsheet"></i> Export
							</a>
						</div>
					</div>
					<table class="table align-middle" style="font-size: 0.8rem;">
						<thead class="sticky-top" style="z-index: 10;">
							<tr>
								<th scope="col">Employee</th>
								<th scope="col">Days</th>
								<th scope="col">T<br/>E</th>
								<th scope="col" class="border-end">Time</th>
								<?php foreach($days as $item){ ?>
								<th scope="col" class="text-center">
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
									<div class="search_criteria d-none"><?= $item["data"]->name." ".$item["data"]->dept." ".$item["data"]->employee_number." ".$item["data"]->ep_mail ?></div>
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
								<td><?= $item["summary"]["tardiness"] ?><br/><?= $item["summary"]["early_out"] ?></td>
								<td class="border-end">
									<?= date("H:i", strtotime($schedule_pr[$item["data"]->employee_number][$to]["start"])) ?><br/>
									<?= date("H:i", strtotime($schedule_pr[$item["data"]->employee_number][$to]["end"])) ?>
								</td>
								<?php foreach($days as $item_day){ ?>
								<td class="text-center">
									<?php
									$now = $item["access"][$item_day["day"]];
									$aux = [];
									
									if ($now["first_access"]["time"]){
										if ($now["first_access"]["remark"] === "MV") $aux[] = $now["first_access"]["remark"];
										$aux[] = '<span class="text-'.($now["first_access"]["remark"] === "T" ? "danger" : "").'">'.$now["first_access"]["time"].'</span>';
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
			
<div class="d-none" id="bl_export_result"></div>

<input type="hidden" id="ip_period" value="<?= $period ?>">

<script>
document.addEventListener("DOMContentLoaded", () => {
	
	$("#form_exp_report").submit(function(e) {
		e.preventDefault();
		$("#form_exp_report .sys_msg").html("");
		ajax_form_warning(this, "page/lgepr_punctuality/export_monthly_report", "Do you want to export monthly punctuality report?").done(function(res) {
			swal_redirection(res.type, res.msg, "page/lgepr_punctuality");
		});
	});
	
	$("#sl_period").change(function(e) {
		window.location.href = "/llamasys/page/lgepr_punctuality?p=" + $(this).val();
	});
	
	$("#ip_search").keyup(function(e) {
		var criteria = $(this).val().toUpperCase();
		
		$(".row_emp").each(function(index, elem) {
			if ($(elem).find(".search_criteria").html().toUpperCase().includes(criteria)) $(elem).show();
			else $(elem).hide();
		});
	});
	
	ajax_simple({p: $("#ip_period").val()}, "page/lgepr_punctuality/export").done(function(res) {
		$("#btn_export").removeClass("d-none");
		$("#btn_export").attr("href", res.url);
	});
	
});
</script>

