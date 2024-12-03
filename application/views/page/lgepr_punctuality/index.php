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
				<select class="form-select me-1" id="sl_dept" style="width: 250px;">
					<option value="">All departments --</option>
					<?php foreach($depts as $item){  ?>
					<option value="<?= str_replace([" ", ">", "&"], "", $item) ?>"><?= str_replace("LGEPR > ", "", $item) ?></option>
					<?php } ?>
				</select>
				<input type="text" class="form-control me-1" id="ip_search" placeholder="Search [Type 'enter' to apply filter]" style="width: 300px;">
				<a href="" class="btn btn-success d-none me-1" id="btn_export" download="Punctuality <?= $period ?>">
					<i class="bi bi-file-earmark-spreadsheet"></i> Export
				</a>
			</div>
		</div>
		<table class="table align-middle" style="font-size: 0.8rem;">
			<thead class="sticky-top" style="z-index: 10;">
				<tr>
					<th scope="col">Employee</th>
					<th scope="col" class="text-center">Working<br/>Days</th>
					<th scope="col" class="text-center">T<br/>E</th>
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
					if (($item["summary"]["check_days"] > 0) and ($item["data"]->employee_number !== "PR009182") and ($item["data"]->employee_number !== "PR009297")){ 
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
					<td class="text-center"><?= $item["summary"]["check_days"] ?></td>
					<td>
						<div class="px-1 text-center text-<?= $item["summary"]["tardiness"] > 4 ? "light bg-danger" : "" ?>"><?= $item["summary"]["tardiness"] ?></div>
						<div class="px-1 text-center text-<?= $item["summary"]["early_out"] > 4 ? "light bg-danger" : "" ?>"><?= $item["summary"]["early_out"] ?></div>
					</td>
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
							
							switch($now["first_access"]["remark"]){
								case "T": $color = "danger"; break;
								case "TT": $color = "success"; break;
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
			
<div class="d-none" id="bl_export_result"></div>

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
		ajax_form_warning(this, "page/lgepr_punctuality/export_monthly_report", "Do you want to export monthly punctuality report?").done(function(res) {
			swal_redirection(res.type, res.msg, "page/lgepr_punctuality");
		});
	});
	
	$("#sl_period").change(function(e) {
		window.location.href = "/llamasys/page/lgepr_punctuality?p=" + $(this).val();
	});
	
	$("#sl_dept").change(function(e) {
		$("#ip_search").val('');
		apply_filter(this);
	});
	
	$("#ip_search").change(function(e) {
		$("#sl_dept").val('');
		apply_filter(this);
	});
	
	ajax_simple({p: $("#ip_period").val()}, "page/lgepr_punctuality/export").done(function(res) {
		$("#btn_export").removeClass("d-none");
		$("#btn_export").attr("href", res.url);
	});
	
});
</script>

