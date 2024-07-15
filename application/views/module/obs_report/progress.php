<div class="d-md-flex justify-content-between align-items-center">
	<div class="pagetitle">
		<h1>OBS - Progress</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item"><a href="<?= base_url() ?>module/obs_report">OBS - Report</a></li>
				<li class="breadcrumb-item active">Monthly Progress</li>
			</ol>
		</nav>
	</div>
</div>					
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between">
						<h5 class="card-title">OBS Monthly Progress</h5>
						<h5 class="card-title"><strong>USD</strong></h5>
					</div>
					<div id="chart_progress" style="min-height: 500px;"></div>
					<table class="table align-middle text-center">
						<thead>
							<tr>
								<th scope="col" class="text-nowrap" style="width: 100px;">Period</th>
								<?php foreach($headers as $h){ ?>
								<th scope="col" class="text-nowrap"><?= $h ?></th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Closed</td>
								<?php foreach($dates as $i => $d){ ?>
								<td><?= $dashs[$i]["LGEPR"]["closed"] ? number_format($dashs[$i]["LGEPR"]["closed"], 2) : "-" ?></td>
								<?php } ?>
							</tr>
							<tr>
								<td rowspan="2">ML</td>
								<?php foreach($dates as $i => $d){ ?>
								<td><?= $dashs[$i]["LGEPR"]["ml_actual"] ? number_format($dashs[$i]["LGEPR"]["ml_actual"], 2) : "-" ?></td>
								<?php } ?>
							</tr>
							<tr>
								<?php foreach($dates as $i => $d){ ?>
								<td class="text-<?= $dashs[$i]["LGEPR"]["ml_color"] ?>"><?= $dashs[$i]["LGEPR"]["ml_per"] ? number_format($dashs[$i]["LGEPR"]["ml_per"], 2)."%" : "-" ?></td>
								<?php } ?>
							</tr>
							<tr>
								<td rowspan="2">Target</td>
								<?php foreach($dates as $i => $d){ ?>
								<td><?= $dashs[$i]["LGEPR"]["target"] ? number_format($dashs[$i]["LGEPR"]["target"], 2) : "-" ?></td>
								<?php } ?>
							</tr>
							<tr>
								<?php foreach($dates as $i => $d){ ?>
								<td class="text-<?= $dashs[$i]["LGEPR"]["target_color"] ?>"><?= $dashs[$i]["LGEPR"]["target_per"] ? number_format($dashs[$i]["LGEPR"]["target_per"], 2)."%" : "-" ?></td>
								<?php } ?>
							</tr>
						</tbody>
					</table>
					<div class="d-none">
						<div id="chart_xaxis_data"><?= json_encode($headers) ?></div>
						<div id="chart_target_data"><?= json_encode($chart_target) ?></div>
						<div id="chart_ml_data"><?= json_encode($chart_ml) ?></div>
						<div id="chart_closed_data"><?= json_encode($chart_closed) ?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	//chart_progress
	echarts.init(document.querySelector("#chart_progress")).setOption({
		tooltip: {trigger: 'axis', axisPointer: {type: 'shadow'}},
		legend: {},
		grid: {left: '100px', right: '0%', bottom: '10%'},
		xAxis: [{type: 'category', data: JSON.parse($("#chart_xaxis_data").html())}],
		yAxis: [{type: 'value'}],
		series: [
			{name: 'Closed', type: 'bar', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_closed_data").html())},
			{name: 'ML', type: 'line', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_ml_data").html())},
			{name: 'Target', type: 'line', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_target_data").html())},
		]
	});
});
</script>