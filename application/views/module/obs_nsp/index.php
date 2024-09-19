<div class="row">
	<div class="col-md-12 pt-3">
		<div class="card overflow-scroll" style="height: 97vh;">
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center">
					<div class="d-flex justify-content-start align-items-center">
						<h5 class="card-title me-3">OBS NSP, <?= date("Y-m-d") ?></h5>
						<form>
							<div class="input-group">
								<select class="form-select" name="sort" style="width: 200px;">
									<option value="sales" <?= $this->input->get("sort") === "sales" ? "selected" : "" ?>>Order by Sales</option>
									<option value="qty" <?= $this->input->get("sort") === "qty" ? "selected" : "" ?>>Order by Qty</option>
								</select>
								<button type="submit" class="btn btn-primary"><i class="bi bi-sort-down"></i></button>
							</div>
						</form>
					</div>
					<div>
						<div class="input-group">
							<select class="form-select" id="f_subsidiary" style="width: 200px;">
								<option value="" selected="">Subsidiary</option>
								<?php foreach($datas as $sub){ ?>
								<option value="row_<?= $sub["subsidiary"] ?>"><?= $sub["subsidiary"] ?></option>
								<?php } ?>
							</select>
							<select class="form-select" id="f_company" style="width: 200px;">
								<option value="" selected="">Company</option>
								<?php foreach($datas as $sub){ foreach($sub["coms"] as $com){ ?>
								<option value="row_<?= str_replace("&", "", $com["company"]) ?>"><?= $com["company"] ?></option>
								<?php } break; } ?>
							</select>
							<select class="form-select" id="f_division" style="width: 200px;">
								<option value="" selected="">Division</option>
								<?php foreach($datas as $sub){ foreach($sub["coms"] as $com){ foreach($com["divs"] as $div){ ?>
								<option value="row_<?= str_replace("/", "", str_replace(" ", "_", $div["division"])) ?>" class="f_division row_<?= str_replace("&", "", $com["company"]) ?> row_<?= str_replace("/", "", str_replace(" ", "_", $div["division"])) ?> d-none"><?= $div["division"] ?></option>
								<?php }} break; } ?>
							</select>
							<input type="text" class="form-control" id="f_model" placeholder="Model" style="width: 200px;">
							<button type="button" class="btn btn-primary" id="f_submit"><i class="bi bi-funnel-fill"></i></button>
						</div>
					</div>
				</div>
				<table class="table text-end">
					<thead class="sticky-top text-center">
						<tr>
							<th scope="col"></th>
							<th scope="col">NSP</th>
							<th scope="col"><div style="width: 75px;">Total</div></th>
							<?php foreach($days as $day){ ?>
							<th scope="col"><div style="width: 65px;"><?= $day ?></div></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach($datas as $sub){ $aux_sub = $sub["subsidiary"]; ?>
						<tr class="rows row_<?= $aux_sub ?> table-danger fw-bold">
							<td class="text-start"><div class="ps-0"><?= $sub["subsidiary"] ?></div></td>
							<td class="align-middle" id="nsp_<?= $aux_sub ?>_values" rowspan="3"></td>
							<?php foreach($sub["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows row_<?= $aux_sub ?>">
							<td class="text-start"><div class="ps-0">Qty</div></td>
							<?php foreach($sub["stat"] as $day => $stat){ ?>
							<td><?= $stat["qty"] ? number_format($stat["qty"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows row_<?= $aux_sub ?>">
							<td class="text-start"><div class="ps-0">NSP</div></td>
							<?php $nsp_total = 0; $nsp_arr = []; foreach($sub["stat"] as $day => $stat){ 
								$nsp = $stat["nsp"];
								$nsp_arr[] = $nsp;
								if (!$nsp_total) $nsp_total = $nsp; ?>
							<td><?= $nsp ? number_format($nsp) : "" ?></td>
							<?php } ?>
							<td class="d-none nsp_summary" id="nsp_<?= $aux_sub ?>"><?= implode(",",$nsp_arr); ?></td>
						</tr>
						<?php foreach($sub["coms"] as $com){ $aux_com = str_replace("&", "", $com["company"]); ?>
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?> table-success fw-bold">
							<td class="text-start"><div class="ps-1"><?= $com["company"] ?></div></td>
							<td class="align-middle" id="nsp_<?= $aux_sub."_".$aux_com ?>_values" rowspan="3"></td>
							<?php foreach($com["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?>">
							<td class="text-start"><div class="ps-1">Qty</div></td>
							<?php foreach($com["stat"] as $day => $stat){ ?>
							<td><?= $stat["qty"] ? number_format($stat["qty"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?>">
							<td class="text-start"><div class="ps-1">NSP</div></td>
							<?php $nsp_total = 0; $nsp_arr = []; foreach($com["stat"] as $day => $stat){ 
								$nsp = $stat["nsp"];
								$nsp_arr[] = $nsp;
								if (!$nsp_total) $nsp_total = $nsp; ?>
							<td><?= $nsp ? number_format($nsp) : "" ?></td>
							<?php } ?>
							<td class="d-none nsp_summary" id="nsp_<?= $aux_sub."_".$aux_com ?>"><?= implode(",",$nsp_arr); ?></td>
						</tr>
						<?php foreach($com["divs"] as $div){ $aux_div = str_replace("/", "", str_replace(" ", "_", $div["division"])); ?>
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?> row_<?= $aux_div ?> table-warning fw-bold">
							<td class="text-start"><div class="ps-2"><?= $div["division"] ?></div></td>
							<td class="align-middle" id="nsp_<?= $aux_sub."_".$aux_com."_".$aux_div ?>_values" rowspan="3"></td>
							<?php foreach($div["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?> row_<?= str_replace(" ", "_", $div["division"]) ?>">
							<td class="text-start"><div class="ps-2">Qty</div></td>
							<?php foreach($div["stat"] as $day => $stat){ ?>
							<td><?= $stat["qty"] ? number_format($stat["qty"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?> row_<?= $aux_div ?>">
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
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?> row_<?= str_replace(" ", "_", $div["division"]) ?> fw-bold">
							<td class="text-start"><div class="ps-3"><?= $bill_to["bill_to"] ?></div></td>
							<td class="align-middle text-center" id="nsp_<?= $aux_div ?>_<?= $bill_to["bill_to"] ?>_values" rowspan="3"></td>
							<?php foreach($bill_to["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?> row_<?= str_replace(" ", "_", $div["division"]) ?>">
							<td class="text-start"><div class="ps-3">Qty</div></td>
							<?php foreach($bill_to["stat"] as $day => $stat){ ?>
							<td><?= $stat["qty"] ? number_format($stat["qty"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?> row_<?= str_replace(" ", "_", $div["division"]) ?>">
							<td class="text-start"><div class="ps-3">NSP</div></td>
							<?php $nsp_total = 0; $nsp_arr = []; foreach($bill_to["stat"] as $day => $stat){ 
								$nsp = $stat["nsp"];
								$nsp_arr[] = $nsp;
								if (!$nsp_total) $nsp_total = $nsp; ?>
							<td><?= $nsp ? number_format($nsp) : "" ?></td>
							<?php } ?>
							<td class="d-none nsp_summary" id="nsp_<?= $aux_div ?>_<?= $bill_to["bill_to"] ?>"><?= implode(",",$nsp_arr); ?></td>
						</tr>
						<?php }} foreach($div["models"] as $model){ $model_aux = str_replace(".", "", $model["model"]); ?>
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?> row_<?= $aux_div ?> table-secondary fw-bold" f_model="<?= $model_aux ?>">
							<td class="text-start"><div class="ps-3"><?= $model["model"] ?></div></td>
							<td class="align-middle text-center" id="nsp_<?= $model_aux ?>_values" rowspan="3">
								<button type="button" class="btn btn-primary btn_load_chart btn_load_chart_<?= $aux_div ?>" value="<?= $aux_div ?>">
									<i class="bi bi-bar-chart-line-fill"></i>
									Load <?= $aux_div ?>
								</button>
							</td>
							<?php foreach($model["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?> row_<?= $aux_div ?>" f_model="<?= $model_aux ?>">
							<td class="text-start"><div class="ps-3">Qty</div></td>
							<?php foreach($model["stat"] as $day => $stat){ ?>
							<td><?= $stat["qty"] ? number_format($stat["qty"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?> row_<?= $aux_div ?>" f_model="<?= $model_aux ?>">
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
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?> row_<?= str_replace(" ", "_", $div["division"]) ?> fw-bold" f_model="<?= $model_aux ?>">
							<td class="text-start"><div class="ps-4"><?= $bill_to["bill_to"] ?></div></td>
							<td class="align-middle text-center" id="nsp_<?= $model_aux ?>_<?= $bill_to["bill_to"] ?>_values" rowspan="3"></td>
							<?php foreach($bill_to["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?> row_<?= str_replace(" ", "_", $div["division"]) ?>" f_model="<?= $model_aux ?>">
							<td class="text-start"><div class="ps-4">Qty</div></td>
							<?php foreach($bill_to["stat"] as $day => $stat){ ?>
							<td><?= $stat["qty"] ? number_format($stat["qty"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows row_<?= $aux_sub ?> row_<?= $aux_com ?> row_<?= str_replace(" ", "_", $div["division"]) ?>" f_model="<?= $model_aux ?>">
							<td class="text-start"><div class="ps-4">NSP</div></td>
							<?php $nsp_total = 0; $nsp_arr = []; foreach($bill_to["stat"] as $day => $stat){ 
								$nsp = $stat["nsp"];
								$nsp_arr[] = $nsp;
								if (!$nsp_total) $nsp_total = $nsp; ?>
							<td class="text-<?= $nsp >= ($nsp_total * 0.95) ? "success" : "danger" ?>"><?= $nsp ? number_format($nsp) : "" ?></td>
							<?php } ?>
							<td class="d-none nsp_summary_<?= $aux_div ?>" id="nsp_<?= $model_aux ?>_<?= $bill_to["bill_to"] ?>"><?= implode(",",$nsp_arr); ?></td>
						</tr>
						<?php }} //bill_to end ?>
						<?php } //model end ?>
						<?php } //div end ?>
						<?php } //com end ?>
						<?php } //sub end ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<input type="hidden" id="val_max_day" value="<?= max($days) ?>">
<script>
document.addEventListener("DOMContentLoaded", () => {
	var max_day = $("#val_max_day").val();
	
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
			
			$(td_id).append('<span id="' + chart_id + '">' + val_str + '</span>');
			
			$("#" + chart_id).peity("bar", {
				fill: function(value) {
					return "#0000007a";
				},
				width: (6 * vals.length),
				height: 90,
			});
			
			$("rect").attr("width", "5");
			
			$(td_id).append('<div class="d-flex justify-content-between fw-light"><small>1</small><small>15</small><small>' + max_day + '</small></div>');
		}
		 
	});
	
	$(".btn_load_chart").on("click", function() {
		var selected_div = $(this).val();
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
				 
				$(td_id).append('<span id="' + chart_id + '">' + val_str + '</span>');
				
				$("#" + chart_id).peity("bar", {
					fill: colors,
					width: (6 * vals.length),
					height: 90,
				});
				
				$("rect").attr("width", "5");
				
				$(td_id).append('<div class="d-flex justify-content-between fw-light"><small>1</small><small>15</small><small>' + max_day + '</small></div>');
			}
			 
		});
		
		$(".btn_load_chart_" + selected_div).remove();
		
	});
	
	$("#f_company").on("change", function() {
		var f_company = $("#f_company").val();
		
		$("#f_division .f_division").addClass("d-none");
		if (f_company != "") $("#f_division ." + f_company).removeClass("d-none");
		
		$("#f_division").val("");
	});
	
	$("#f_submit").on("click", function() {
		var f_company = $("#f_company").val();
		var f_division = $("#f_division").val();
		var f_model = $("#f_model").val().toUpperCase();
		
		if (f_company == ""){
			$(".rows").removeClass("d-none");
			$(".f_division").addClass("d-none");
			$("#f_division").val("");
		}else{
			$(".rows").addClass("d-none");
			$(".f_division").addClass("d-none");
			$("." + f_company).removeClass("d-none");
		}
		
		if (f_division == ""){
			$(".rows").addClass("d-none");
			if (f_company != "") $("." + f_company).removeClass("d-none");
			else $(".rows").removeClass("d-none");
		}else{
			$(".rows").addClass("d-none");
			$("." + f_company + "." + f_division).removeClass("d-none");	
		}
		
		if (f_model != ""){
			$(".rows").each(function (index, item) {
				
				if (!$(item).hasClass("d-none")){
					f_model_attr = "" + $(item).attr("f_model");
					
					if (!f_model_attr.toUpperCase().includes(f_model)) $(item).addClass("d-none");
				}
			});	
		}
		
	});
	
	
	/*
	
	
	$(.includes(f)".btn_bs").on("click", function() {
		var val = $(this).val();
		if ($(this).hasClass("btn-primary")){
			$(".bl_bs_" + val).addClass("d-none");
			
			$(this).removeClass("btn-primary")
			$(this).addClass("btn-outline-primary")
		}else{
			$(".bl_bs_" + val).removeClass("d-none");
			
			$(this).removeClass("btn-outline-primary")
			$(this).addClass("btn-primary")
			
		}
		
	});
	
	set_charts();
	*/
});
</script>