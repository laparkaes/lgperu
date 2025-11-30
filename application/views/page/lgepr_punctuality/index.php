<head>
    <title>Mi Página</title>
    <style>
        .fixed-bottom-right {
			position: fixed;
			bottom: 20px; /* Aumenta el espacio desde la parte inferior */
			right: 20px; /* Aumenta el espacio desde la derecha */
			z-index: 1000;
			width: 40px; /* Aumenta el ancho del botón */
			height: 40px; /* Aumenta la altura del botón */
			font-size: 24px; /* Aumenta el tamaño de la fuente del signo de interrogación */
			display: flex; /* Centra el contenido vertical y horizontalmente */
			align-items: center;
			justify-content: center;
		}
    </style>
</head>
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
							<a href="<?= base_url() ?>page/lgepr_punctuality/daily/<?= $item["data"]->employee_number ?>/<?= $period ?>" target="_blank"><?= implode(", ", $aux) ?></a>
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
						
						// if ($now["first_access"]["time"]){
							// if ($now["first_access"]["remark"] === "MV") $aux[] = $now["first_access"]["remark"];
							
							// switch($now["first_access"]["remark"]){
								// case "T": $color = "danger"; break;
								// case "TT": $color = "success"; break;
								// default: $color = "";
							// }
							
							// $aux[] = '<span class="text-'.$color.'">'.$now["first_access"]["time"].'</span>';
						// }else $aux[] = $now["first_access"]["remark"];
						
						// if ($now["last_access"]["time"]){
							// $aux[] = '<span class="text-'.($now["last_access"]["remark"] === "E" ? "danger" : "").'">'.$now["last_access"]["time"].'</span>';
							// if ($now["last_access"]["remark"] === "AV") $aux[] = $now["last_access"]["remark"];
						// }else $aux[] = $now["last_access"]["remark"];
						
						
						$mRemarks = ["MV", "MB", "MBT", "MCO", "MCMP", "MHO", "MT", "NEF"];
						$aRemarks = ["AV", "AB", "ABT", "ACO", "ACMP", "AHO", "AT"];
						if ($now["first_access"]["time"]){
							if (in_array($now["first_access"]["remark"], $mRemarks)) $aux[] = $now["first_access"]["remark"];
							
							switch($now["first_access"]["remark"]){
								case "T": $color = "danger"; break;
								//case "TT": $color = "success"; break;
								case "NEF": $color = "danger"; break;
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

<button type="button" class="btn btn-primary btn-xl rounded-circle fixed-bottom-right" data-bs-toggle="modal" data-bs-target="#legendModal">
    ?
</button>

<div class="modal fade" id="legendModal" tabindex="-1" aria-labelledby="legendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="legendModalLabel">Types Exception List</h5>
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
							<tr><td>J</td><td>Justified</td></tr>
							<tr><td>NEF</td><td>No Early Friday</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
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