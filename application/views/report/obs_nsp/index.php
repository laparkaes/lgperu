<div class="row">
	<div class="col-md-12 pt-3">
		<div class="card overflow-scroll" style="height: 97vh;">
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center">
					<div class="d-flex justify-content-start align-items-center">
						<h5 class="card-title me-3">OBS NSP, <?= $period ?></h5>
					</div>
				</div>
				<table class="table text-end align-middle" style="font-size: 0.75em;">
					<thead class="sticky-top text-center">
						<tr>
							<th scope="col">
								<div class="form-check text-start" style="width: 90px;">
									<input class="form-check-input" type="checkbox" id="chk_bill_to">
									<label class="form-check-label" for="chk_bill_to">Bill to</label>
								</div>
							</th>
							<th scope="col">NSP</th>
							<th scope="col"><div style="width: 65px;">Total</div></th>
							<?php foreach($days as $day){ ?>
							<th scope="col"><div style="width: 45px;"><?= $day ?></div></th>
							<?php } ?>
						</tr>
					</thead>
					<?php foreach($datas as $sub){ $aux_sub = $sub["subsidiary"]; ?>
					<tbody class="tb_<?= $aux_sub ?>">
						<tr class="rows table-danger fw-bold">
							<td class="text-start"><div class="text-nowrap ps-0"><?= $sub["subsidiary"] ?></div></td>
							<td class="align-middle" id="nsp_<?= $aux_sub ?>_values" rowspan="3"></td>
							<?php foreach($sub["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows">
							<td class="text-start"><div class="ps-0">Qty</div></td>
							<?php foreach($sub["stat"] as $day => $stat){ ?>
							<td><?= $stat["qty"] ? number_format($stat["qty"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows">
							<td class="text-start"><div class="ps-0">NSP</div></td>
							<?php $nsp_total = 0; $nsp_arr = []; foreach($sub["stat"] as $day => $stat){ 
								$nsp = $stat["nsp"];
								$nsp_arr[] = $nsp;
								if (!$nsp_total) $nsp_total = $nsp; ?>
							<td><?= $nsp ? number_format($nsp) : "" ?></td>
							<?php } ?>
							<td class="d-none nsp_summary" id="nsp_<?= $aux_sub ?>"><?= implode(",",$nsp_arr); ?></td>
						</tr>
					</tbody>
					<?php foreach($sub["coms"] as $com){ $aux_com = str_replace("&", "", $com["company"]); ?>
					<tbody class="tb_<?= $aux_sub ?> tb_<?= $aux_com ?>">
						<tr class="rows table-success fw-bold">
							<td class="text-start"><div class="text-nowrap ps-1"><?= $com["company"] ?></div></td>
							<td class="align-middle" id="nsp_<?= $aux_sub."_".$aux_com ?>_values" rowspan="3"></td>
							<?php foreach($com["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows">
							<td class="text-start"><div class="ps-1">Qty</div></td>
							<?php foreach($com["stat"] as $day => $stat){ ?>
							<td><?= $stat["qty"] ? number_format($stat["qty"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows">
							<td class="text-start"><div class="ps-1">NSP</div></td>
							<?php $nsp_total = 0; $nsp_arr = []; foreach($com["stat"] as $day => $stat){ 
								$nsp = $stat["nsp"];
								$nsp_arr[] = $nsp;
								if (!$nsp_total) $nsp_total = $nsp; ?>
							<td><?= $nsp ? number_format($nsp) : "" ?></td>
							<?php } ?>
							<td class="d-none nsp_summary" id="nsp_<?= $aux_sub."_".$aux_com ?>"><?= implode(",",$nsp_arr); ?></td>
						</tr>
					</tbody>
					<?php foreach($com["divs"] as $div){ if ($div["stat"]["total"]["sales"]){ $aux_div = str_replace("/", "", str_replace(" ", "_", $div["division"])); ?>
					<tbody class="tb_<?= $aux_sub ?> tb_<?= $aux_com ?> tb_<?= $aux_div ?>">
						<tr class="rows table-warning fw-bold">
							<td class="text-start">
								<div class="d-flex justify-content-between align-items-center ps-2">
									<div class="text-nowrap"><?= $div["division"] ?></div>
									<button type="button" class="btn btn-sm btn_view_models p-0" id="btn_show_models_<?= $aux_div ?>" value="<?= $aux_div ?>">
										<i class="bi bi-caret-left-fill"></i>
									</button>
									<button type="button" class="btn btn-sm btn_hide_models p-0 d-none" id="btn_hide_models_<?= $aux_div ?>" value="<?= $aux_div ?>">
										<i class="bi bi-caret-down-fill"></i>
									</button>
								</div>
							</td>
							<td class="align-middle" id="nsp_<?= $aux_sub."_".$aux_com."_".$aux_div ?>_values" rowspan="3"></td>
							<?php foreach($div["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows">
							<td class="text-start"><div class="ps-2">Qty</div></td>
							<?php foreach($div["stat"] as $day => $stat){ ?>
							<td><?= $stat["qty"] ? number_format($stat["qty"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows">
							<td class="text-start"><div class="ps-2">NSP</div></td>
							<?php $nsp_total = 0; $nsp_arr = []; foreach($div["stat"] as $day => $stat){ 
								$nsp = $stat["nsp"];
								$nsp_arr[] = $nsp;
								if (!$nsp_total) $nsp_total = $nsp; ?>
							<td><?= $nsp ? number_format($nsp) : "" ?></td>
							<?php } ?>
							<td class="d-none nsp_summary" id="nsp_<?= $aux_sub."_".$aux_com."_".$aux_div ?>"><?= implode(",",$nsp_arr); ?></td>
						</tr>
						<?php foreach($div["bill_tos"] as $bill_to){ if ($bill_to["stat"]["total"]["sales"]){ ?>
						<tr class="rows rows_bill_to d-none fw-bold">
							<td class="text-start"><div class="ps-3"><?= $bill_to["bill_to"] ?></div></td>
							<td class="align-middle text-center" id="nsp_<?= $aux_div ?>_<?= $bill_to["bill_to"] ?>_values" rowspan="3"></td>
							<?php foreach($bill_to["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows rows_bill_to d-none">
							<td class="text-start"><div class="ps-3">Qty</div></td>
							<?php foreach($bill_to["stat"] as $day => $stat){ ?>
							<td><?= $stat["qty"] ? number_format($stat["qty"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows rows_bill_to d-none">
							<td class="text-start"><div class="ps-3">NSP</div></td>
							<?php $nsp_total = 0; $nsp_arr = []; foreach($bill_to["stat"] as $day => $stat){ 
								$nsp = $stat["nsp"];
								$nsp_arr[] = $nsp;
								if (!$nsp_total) $nsp_total = $nsp; ?>
							<td><?= $nsp ? number_format($nsp) : "" ?></td>
							<?php } ?>
							<td class="d-none nsp_summary" id="nsp_<?= $aux_div ?>_<?= $bill_to["bill_to"] ?>"><?= implode(",",$nsp_arr); ?></td>
						</tr>
						<?php }} ?>
					</tbody>
					<?php foreach($div["models"] as $model){ $model_aux = str_replace(".", "", $model["model"]); ?>
					<tbody class="tb_<?= $aux_sub ?> tb_<?= $aux_com ?> tb_<?= $aux_div ?>_models tb_<?= $model_aux ?> d-none" f_model="<?= $model_aux ?>">
						<tr class="rows table-primary fw-bold">
							<td class="text-start"><div class="text-nowrap ps-3"><?= $model["model"] ?></div></td>
							<td class="td_chart align-middle" id="nsp_<?= $model_aux ?>_values" rowspan="3"></td>
							<?php foreach($model["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows">
							<td class="text-start"><div class="ps-3">Qty</div></td>
							<?php foreach($model["stat"] as $day => $stat){ ?>
							<td><?= $stat["qty"] ? number_format($stat["qty"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows">
							<td class="text-start"><div class="ps-3">NSP</div></td>
							<?php $nsp_total = 0; $nsp_arr = []; foreach($model["stat"] as $day => $stat){ 
								$nsp = $stat["nsp"];
								$nsp_arr[] = $nsp;
								if (!$nsp_total) $nsp_total = $nsp; ?>
							<td class="text-<?= $nsp >= ($nsp_total * 0.95) ? "success" : "danger" ?>"><?= $nsp ? number_format($nsp) : "" ?></td>
							<?php } ?>
							<td class="d-none nsp_summary_<?= $aux_div ?>" id="nsp_<?= $model_aux ?>"><?= implode(",",$nsp_arr); ?></td>
						</tr>
						<?php foreach($model["bill_tos"] as $bill_to){ if ($bill_to["stat"]["total"]["sales"]){ ?>
						<tr class="rows rows_bill_to d-none fw-bold">
							<td class="text-start"><div class="ps-4"><?= $bill_to["bill_to"] ?></div></td>
							<td class="td_chart align-middle" id="nsp_<?= $model_aux ?>_<?= $bill_to["bill_to"] ?>_values" rowspan="3"></td>
							<?php foreach($bill_to["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows rows_bill_to d-none">
							<td class="text-start"><div class="ps-4">Qty</div></td>
							<?php foreach($bill_to["stat"] as $day => $stat){ ?>
							<td><?= $stat["qty"] ? number_format($stat["qty"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows rows_bill_to d-none">
							<td class="text-start"><div class="ps-4">NSP</div></td>
							<?php $nsp_total = 0; $nsp_arr = []; foreach($bill_to["stat"] as $day => $stat){ 
								$nsp = $stat["nsp"];
								$nsp_arr[] = $nsp;
								if (!$nsp_total) $nsp_total = $nsp; ?>
							<td class="text-<?= $nsp >= ($nsp_total * 0.95) ? "success" : "danger" ?>"><?= $nsp ? number_format($nsp) : "" ?></td>
							<?php } ?>
							<td class="d-none nsp_summary_<?= $aux_div ?>" id="nsp_<?= $model_aux ?>_<?= $bill_to["bill_to"] ?>"><?= implode(",",$nsp_arr); ?></td>
						</tr>
						<?php }} ?>
					</tbody>
					<?php }}}}} ?>
				</table>
			</div>
		</div>
	</div>
</div>
<input type="hidden" id="val_max_day" value="<?= max($days) ?>">
<script>
document.addEventListener("DOMContentLoaded", () => {
	var max_day = $("#val_max_day").val();
	
	$("#chk_bill_to").on("change", function() {
		var is_checked = $(this).is(":checked");
		if ($(this).is(":checked")) $(".rows_bill_to").removeClass("d-none"); else $(".rows_bill_to").addClass("d-none");
	});
	
	$(".btn_hide_models").on("click", function() {
		var selected_div = $(this).val();
		
		$(".td_chart").html("");
		
		$(".tb_" + selected_div + "_models").addClass("d-none");
		
		$("#btn_show_models_" + selected_div).removeClass("d-none");
		$(this).addClass("d-none");
	});
	
	$(".btn_view_models").on("click", function() {
		var selected_div = $(this).val();
		
		$(".tb_" + selected_div + "_models").removeClass("d-none");
		
		$("#btn_hide_models_" + selected_div).removeClass("d-none");
		$(this).addClass("d-none");
		
		$(".nsp_summary_" + selected_div).each(function (index, item) {
			var vals = $(item).html().split(",");
			if (vals.length > 1){
				var avg = vals.shift();//nsp avg
				var vals_min = Math.max(...vals);//select max value to get minimum
				var colors = [];
				
				for (let i = 0; i < vals.length; i++) {
					vals[i] = parseFloat(vals[i]);
				
					if ((vals_min > vals[i]) && (vals[i] > 0)) {
						vals_min = vals[i];
					}
					
					if (vals[i] > 0) color = (vals[i] >= (avg * 0.95)) ? "green" : "red";
					else color = "#e2e3e5";
					
					colors.push(color);
				}
				
				var reduce = vals_min * 0.9;
				
				if (vals_min > 0){
					for (let i = 0; i < vals.length; i++) {
						if (vals[i] > 0) vals[i] = vals[i] - reduce;
					}
				}
				
				var td_id = "#" + $(item).attr("id") + "_values";
				var chart_id = $(item).attr("id") + "_chart";
				var val_str = vals.join(",");
				 
				$(td_id).append('<span class="d-none" id="' + chart_id + '">' + val_str + '</span>');
				
				$("#" + chart_id).peity("bar", {
					fill: colors,
					width: (6 * vals.length),
					height: 70,
				});
				
				$("rect").attr("width", "5");
				
				$(td_id).append('<div class="d-flex justify-content-between fw-light"><small>1</small><small>15</small><small>' + max_day + '</small></div>');
			}
			 
		});
	});
	
	
	$(".nsp_summary").each(function (index, item) {
		var vals = $(item).html().split(",");
		if (vals.length > 1){
			var avg = vals.shift();//nsp avg
			var vals_min = Math.max(...vals);//select max value to get minimum
			var colors = [];
			
			for (let i = 0; i < vals.length; i++) {
				vals[i] = parseFloat(vals[i]);
				
				if ((vals_min > vals[i]) && (vals[i] > 0)) {
					vals_min = vals[i];
				}
				
				if (vals[i] > 0) color = (vals[i] >= (avg * 0.95)) ? "green" : "red";
				else color = "#e2e3e5";
				
				colors.push(color);
			}
			
			var reduce = vals_min * 0.9;
			
			if (vals_min > 0){
				for (let i = 0; i < vals.length; i++) {
					if (vals[i] > 0) vals[i] = vals[i] - reduce;
				}
			}
			
			var td_id = "#" + $(item).attr("id") + "_values";
			var chart_id = $(item).attr("id") + "_chart";
			var val_str = vals.join(",");
			
			$(td_id).append('<span class="d-none" id="' + chart_id + '">' + val_str + '</span>');
			
			$("#" + chart_id).peity("bar", {
				fill: function(value) {
					return "#0000007a";
				},
				width: (6 * vals.length),
				height: 70,
			});
			
			$("rect").attr("width", "5");
			
			$(td_id).append('<div class="d-flex justify-content-between fw-light"><small>1</small><small>15</small><small>' + max_day + '</small></div>');
		}
		 
	});
	
});
</script>