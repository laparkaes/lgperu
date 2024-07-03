<div class="d-md-flex justify-content-between align-items-center">
	<div class="pagetitle">
		<h1>OBS - Progress</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item"><a href="<?= base_url() ?>module/obs_report">OBS - Report</a></li>
				<li class="breadcrumb-item active"><?= $period ?> Progress</li>
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
						<h5 class="card-title">OBS <?= $period ?> Progress</h5>
						<h5 class="card-title"><strong>USD</strong></h5>
					</div>
					<div id="chart_progress" style="min-height: 500px;"></div>
					<table class="table align-middle text-center">
						<thead>
							<tr>
								<th scope="col" style="width: 100px;">Subsidiary</th>
								<th scope="col" style="width: 100px;">Division</th>
								<th scope="col" style="width: 100px;" class="border-end">Category</th>
								<?php foreach($headers as $h){ ?>
								<th scope="col" class="text-nowrap"><?= $h ?></th>
								<?php } ?>
							</tr>
						</thead>
						<tbody>
							<?php 
							$chart_datas = [];
							foreach($row_headers as $rh){ $chart_datas[$rh["sub"].$rh["div"].$rh["cat"]] = []; ?>
							<tr>
								<td><?= $rh["sub"] ?></td>
								<td><?= $rh["div"] ?></td>
								<td class="border-end"><?= $rh["cat"] ?></td>
								<?php for($i = 0; $i <= $qty; $i++){ $chart_datas[$rh["sub"].$rh["div"].$rh["cat"]][] += round($dashs[$i][$rh["code"]]["actual"], 2); ?>
								<td><?= $dashs[$i][$rh["code"]]["actual"] ? number_format($dashs[$i][$rh["code"]]["actual"], 2) : "-" ?></td>
								<?php } ?>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					<div class="d-none">
						<div id="chart_xaxis_data"><?= json_encode($headers) ?></div>
						<?php foreach($chart_datas as $label => $datas){ ?>
						<div id="chart_<?= str_replace("/", "", $label) ?>_datas"><?= json_encode($datas) ?></div>
						<?php } ?>
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
		grid: {left: '3%', right: '3%', bottom: '10%'},
		xAxis: [{type: 'category', data: JSON.parse($("#chart_xaxis_data").html())}],
		yAxis: [{type: 'value'}],
		series: [
			{name: 'Total', type: 'bar', barWidth: 20, color: "#a50034", data: JSON.parse($("#chart_LGEPR_datas").html()), emphasis: {focus: 'series'}},
			{name: 'HA', type: 'bar', barWidth: 10, stack: 'div', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_HA_datas").html())},
			{name: 'HE', type: 'bar', stack: 'div', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_HE_datas").html())},
			{name: 'BS', type: 'bar', stack: 'div', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_BS_datas").html())},
			/*
			{name: 'REF', type: 'bar', barWidth: 10, stack: 'cat', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_REF_datas").html())},
			{name: 'COOK', type: 'bar', stack: 'cat', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_COOK_datas").html())},
			{name: 'W/M', type: 'bar', stack: 'cat', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_WM_datas").html())},
			{name: 'RAC', type: 'bar', stack: 'cat', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_RAC_datas").html())},
			{name: 'SAC', type: 'bar', stack: 'cat', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_SAC_datas").html())},
			{name: 'A/C', type: 'bar', stack: 'cat', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_AC_datas").html())},
			{name: 'TV', type: 'bar', stack: 'cat', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_TV_datas").html())},
			{name: 'AV', type: 'bar', stack: 'cat', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_AV_datas").html())},
			{name: 'MNT', type: 'bar', stack: 'cat', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_MNT_datas").html())},
			{name: 'PC', type: 'bar', stack: 'cat', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_PC_datas").html())},
			{name: 'DS', type: 'bar', stack: 'cat', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_DS_datas").html())},
			{name: 'SGN', type: 'bar', stack: 'cat', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_SGN_datas").html())},
			{name: 'CTV', type: 'bar', stack: 'cat', emphasis: {focus: 'series'}, data: JSON.parse($("#chart_CTV_datas").html())},
			*/
		]
	});
});
</script>