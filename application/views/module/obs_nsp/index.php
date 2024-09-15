<div class="row">
	<div class="col-md-12">
		<div class="pagetitle">
			<h1>Employee</h1>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<div class="card overflow-scroll" style="height: 80vh;">
			<div class="card-body p-0">
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
						<tr class="table-success fw-bold row_<?= $com["company"] ?>">
							<td class="text-start"><div class="ps-0"><i class="bi bi-plus-square"></i> <?= $com["company"] ?></div></td>
							<td></td>
							<?php foreach($com["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"], 2) : "" ?></td>
							<?php } ?>
						</tr>
						<?php foreach($com["divs"] as $div){ ?>
						<tr class="row_<?= $com["company"] ?> row_<?= $div["division"] ?>">
							<td class="text-start"><div class="ps-1"><i class="bi bi-plus-square"></i> <?= $div["division"] ?></div></td>
							<td></td>
							<?php foreach($div["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"], 2) : "" ?></td>
							<?php } ?>
						</tr>
						<?php foreach($div["models"] as $model){ $model_aux = str_replace(".", "", $model["model"]); ?>
						<tr class="table-secondary row_<?= $com["company"] ?> row_<?= $div["division"] ?> row_<?= $model_aux ?>">
							<td class="text-start"><div class="ps-2"><i class="bi bi-plus-square"></i> <?= $model["model"] ?></div></td>
							<td></td>
							<?php foreach($model["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"], 2) : "" ?></td>
							<?php } ?>
						</tr>
						<?php foreach($model["bill_tos"] as $bill_to){ if ($bill_to["stat"]["total"]["sales"]){ ?>
						<tr class="row_<?= $com["company"] ?> row_<?= $div["division"] ?> row_<?= $model_aux ?>">
							<td class="text-start"><div class="ps-3"><?= $bill_to["bill_to"] ?></div></td>
							<td class="text-start" id="nsp_<?= $model_aux ?>_<?= $bill_to["bill_to"] ?>_values" rowspan="3"></td>
							<?php $nsp_total = 0; foreach($bill_to["stat"] as $day => $stat){ ?>
							<td><?= $stat["sales"] ? number_format($stat["sales"], 2) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="row_<?= $com["company"] ?> row_<?= $div["division"] ?> row_<?= $model_aux ?>" style="font-size: .9rem;">
							<td class="text-start"><div class="ps-4">Qty</div></td>
							<?php foreach($bill_to["stat"] as $day => $stat){ ?>
							<td><?= $stat["qty"] ? number_format($stat["qty"]) : "" ?></td>
							<?php } ?>
						</tr>
						<tr class="row_<?= $com["company"] ?> row_<?= $div["division"] ?> row_<?= $model_aux ?>" style="font-size: .9rem;">
							<td class="text-start"><div class="ps-4">NSP</div></td>
							<?php $nsp_arr = []; foreach($bill_to["stat"] as $day => $stat){ 
								$nsp = $stat["qty"] > 0 ? round($stat["sales"] / $stat["qty"], 2) : 0;
								if ($nsp) $nsp_arr[] = $nsp;
								if (!$nsp_total) $nsp_total = $nsp; ?>
							<td class="text-<?= $nsp >= $nsp_total ? "success" : "danger" ?>"><?= $nsp ? number_format($nsp, 2) : "" ?></td>
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
function set_charts(){
	//chart_purchase_amount
	echarts.init(document.querySelector("#chart_purchase_amount")).setOption({
		legend: {data: ['~4 Hr', '~8 Hr', '~12 Hr', '~16 Hr', '~20 Hr', '~24 Hr', 'Total', 'IOD']},
		tooltip: {trigger: 'axis', axisPointer: {type: 'cross', label: {backgroundColor: '#6a7985'}}},
		grid: {left: '30px', right: '30px', top: '0%', bottom: '3%', containLabel: true},
		xAxis: [{type: 'category', data: JSON.parse($("#chart_purchase_xaxis").html())}],
		yAxis: [{show: false, type: 'value'}],
		series: [
			{name: '~4 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_amount_4").html()), barGap: 0},
			{name: '~8 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_amount_8").html())},
			{name: '~12 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_amount_12").html())},
			{name: '~16 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_amount_16").html())},
			{name: '~20 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_amount_20").html())},
			{name: '~24 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_amount_24").html())},
			{name: 'Total', barWidth: 5, type: 'bar', stack: 'total', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_amount_total").html())},
			{name: 'IOD', type: 'line', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_closed_amount").html())},
		]
	});
	
	//chart_purchase_qty
	echarts.init(document.querySelector("#chart_purchase_qty")).setOption({
		legend: {data: ['~4 Hr', '~8 Hr', '~12 Hr', '~16 Hr', '~20 Hr', '~24 Hr', 'Total', 'IOD']},
		tooltip: {trigger: 'axis', axisPointer: {type: 'cross', label: {backgroundColor: '#6a7985'}}},
		grid: {left: '30px', right: '30px', top: '0%', bottom: '3%', containLabel: true},
		xAxis: [{type: 'category', data: JSON.parse($("#chart_purchase_xaxis").html())}],
		yAxis: [{show: false, type: 'value'}],
		series: [
			{name: '~4 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_qty_4").html()), barGap: 0},
			{name: '~8 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_qty_8").html())},
			{name: '~12 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_qty_12").html())},
			{name: '~16 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_qty_16").html())},
			{name: '~20 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_qty_20").html())},
			{name: '~24 Hr', type: 'bar', stack: 'hr', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_qty_24").html())},
			{name: 'Total', barWidth: 5, type: 'bar', stack: 'total', areaStyle: {}, emphasis: {focus: 'series'}, data: JSON.parse($("#chart_purchase_qty_total").html())},
			{name: 'IOD', type: 'line', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_closed_qty").html())},
		]
	});
	
	//chart_cus_group
	echarts.init(document.querySelector("#chart_cus_group")).setOption({
		tooltip: {trigger: 'item'},
		series: [{name: 'Customer Group', type: 'pie', radius: '90%', data: JSON.parse($("#chart_cus_group_data").html()),}]
	});
	
	//chart_device
	echarts.init(document.querySelector("#chart_device")).setOption({
		tooltip: {trigger: 'item'},
		series: [{name: 'Device', type: 'pie', radius: '90%', data: JSON.parse($("#chart_device_data").html()),}]
	});
}

document.addEventListener("DOMContentLoaded", () => {
	//$("span.pie").peity("pie");
	
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
					return value >= avg ? "green" : "red"
				},
				width: 150,
				height: 90,
			});
			
		 }
		 
	});
	
	
	/*
	$("#sl_by_week").on("change", function() {
		if ($(this).val() != "") $("#sl_by_month").val("");
	});
	
	$("#sl_by_month").on("change", function() {
		if ($(this).val() != "") $("#sl_by_week").val("");
	});
	
	$(".btn_bs").on("click", function() {
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