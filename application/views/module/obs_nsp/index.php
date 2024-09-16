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
							<select class="form-select" id="f_company" style="width: 200px;">
								<option value="" selected="">Company</option>
								<?php foreach($datas as $com){ ?>
								<option value="row_<?= str_replace("&", "", $com["company"]) ?>"><?= $com["company"] ?></option>
								<?php } ?>
							</select>
							<select class="form-select" id="f_division" style="width: 200px;">
								<option value="" selected="">Division</option>
								<?php foreach($datas as $com){ foreach($com["divs"] as $div){ ?>
								<option value="row_<?= str_replace(" ", "_", $div["division"]) ?>" class="f_division row_<?= str_replace("&", "", $com["company"]) ?> d-none"><?= $div["division"] ?></option>
								<?php }} ?>
							</select>
							<input type="text" class="form-control" id="f_model" placeholder="Model" style="width: 200px;">
							<button type="button" class="btn btn-primary" id="f_submit"><i class="bi bi-funnel-fill"></i></button>
						</div>
					</div>
				</div>
				<table class="table text-center">
					<thead class="sticky-top text-center">
						<tr>
							<th scope="col">Day</th>
							<th scope="col">NSP</th>
							<th scope="col"><div style="width: 120px;">Total</div></th>
							<?php foreach($days as $day){ ?>
							<th scope="col"><div style="width: 90px;"><?= $day ?></div></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach($datas as $com){ ?>
						<tr class="rows row_<?= str_replace("&", "", $com["company"]) ?> table-success fw-bold">
							<td class="text-start"><div class="ps-0"><?= $com["company"] ?></div></td>
							<td></td>
							<?php foreach($com["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"], 2) : "" ?></td>
							<?php } ?>
						</tr>
						<?php foreach($com["divs"] as $div){ ?>
						<tr class="rows row_<?= str_replace("&", "", $com["company"]) ?> row_<?= str_replace(" ", "_", $div["division"]) ?> table-warning">
							<td class="text-start"><div class="ps-1"><?= $div["division"] ?></div></td>
							<td></td>
							<?php foreach($div["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"], 2) : "" ?></td>
							<?php } ?>
						</tr>
						<?php foreach($div["models"] as $model){ $model_aux = str_replace(".", "", $model["model"]); ?>
						<tr class="rows row_<?= str_replace("&", "", $com["company"]) ?> row_<?= str_replace(" ", "_", $div["division"]) ?> table-secondary" f_model="<?= $model_aux ?>">
							<td class="text-start"><div class="ps-2"><?= $model["model"] ?></div></td>
							<td></td>
							<?php foreach($model["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"], 2) : "" ?></td>
							<?php } ?>
						</tr>
						<?php foreach($model["bill_tos"] as $bill_to){ if ($bill_to["stat"]["total"]["sales"]){ ?>
						<tr class="rows row_<?= str_replace("&", "", $com["company"]) ?> row_<?= str_replace(" ", "_", $div["division"]) ?>" f_model="<?= $model_aux ?>">
							<td class="text-start"><div class="ps-3"><strong><?= $bill_to["bill_to"] ?></strong></div></td>
							<td class="text-start" id="nsp_<?= $model_aux ?>_<?= $bill_to["bill_to"] ?>_values" rowspan="3"></td>
							<?php $nsp_total = 0; foreach($bill_to["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"], 2) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows row_<?= str_replace("&", "", $com["company"]) ?> row_<?= str_replace(" ", "_", $div["division"]) ?>" f_model="<?= $model_aux ?>" style="font-size: .9rem;">
							<td class="text-start"><div class="ps-4">Qty</div></td>
							<?php foreach($bill_to["stat"] as $day => $stat){ ?>
							<td><?= $stat["qty"] ? number_format($stat["qty"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="rows row_<?= str_replace("&", "", $com["company"]) ?> row_<?= str_replace(" ", "_", $div["division"]) ?>" f_model="<?= $model_aux ?>" style="font-size: .9rem;">
							<td class="text-start"><div class="ps-4">NSP</div></td>
							<?php $nsp_arr = []; foreach($bill_to["stat"] as $day => $stat){ 
								$nsp = $stat["nsp"];
								$nsp_arr[] = $nsp;
								if (!$nsp_total) $nsp_total = $nsp; ?>
							<td class="text-<?= $nsp >= ($nsp_total * 0.95) ? "success" : "danger" ?>"><?= $nsp ? number_format($nsp, 2) : "" ?></td>
							<?php } ?>
							<td class="d-none nsp_summary" id="nsp_<?= $model_aux ?>_<?= $bill_to["bill_to"] ?>"><?= implode(",",$nsp_arr); ?></td>
						</tr>
						<?php }} //bill_to end ?>
						<?php } //model end ?>
						<?php } //div end ?>
						<?php } //com end ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
	
<script>
document.addEventListener("DOMContentLoaded", () => {
	
	$(".nsp_summary").each(function (index, item) {
		var vals = $(item).html().split(",");
		if (vals.length > 1){
			var avg = vals.shift();//nsp avg
			var td_id = "#" + $(item).attr("id") + "_values";
			var chart_id = $(item).attr("id") + "_chart";
			var val_str = vals.join(",");
			 
			$(td_id).append('<span id="' + chart_id + '">' + val_str + '</span>');
			$("#" + chart_id).peity("bar", {
				fill: function(value) {
					return value >= (avg * 0.95) ? "green" : "red"
				},
				width: 150,
				height: 90,
			});
			
			$("rect").attr("width", "10");
		}
		 
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
	
	
	$("#sl_by_month").on("change", function() {
		if ($(this).val() != "") $("#sl_by_week").val("");
	});
	
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