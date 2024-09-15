<div class="row">
	<div class="col-md-12">
		<div class="pagetitle">
			<h1>Employee</h1>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-12">
		<?php 
		
		foreach($datas as $day => $coms){
			echo $day." ----------------------------------------------<br/>";
			foreach($coms as $com){
				print_r($com["company"]); echo " /// ";
				print_r($com["stat"]); echo "<br/>";
				
				$divs = $com["divs"];
				foreach($divs as $div){
					echo "--- ";
					print_r($div["division"]); echo " /// ";
					print_r($div["stat"]); echo "<br/>";
					
					$models = $div["models"];
					foreach($models as $model){
						echo "------ ";
						print_r($model["model"]); echo " /// ";
						print_r($model["stat"]); echo "<br/>";
						
						$bill_tos = $model["bill_tos"];
						foreach($bill_tos as $bill_to){
							echo "--------- ";
							print_r($bill_to["bill_to"]); echo " /// ";
							print_r($bill_to["stat"]); echo "<br/>";
						}
						echo "<br/>";
					}
					echo "<br/>";
				}
				echo "<br/>";
			}
		}
		?>
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
});
</script>