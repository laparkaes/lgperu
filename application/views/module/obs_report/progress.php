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
					<table class="table align-middle text-center">
						<thead>
							<tr>
								<th scope="col">Subsidiary</th>
								<th scope="col">Division</th>
								<th scope="col" class="border-end">Category</th>
								<?php foreach($headers as $h){ ?>
								<th scope="col" class="text-nowrap"><?= $h ?></th>
								<?php } ?>
								
							</tr>
						</thead>
						<tbody>
							<?php foreach($progress[0]["subsidiaries"] as $sub => $subsidiary){ ?>
							<tr class="table-danger fw-bold">
								<td><?= $sub ?></td>
								<td></td>
								<td class="border-end"></td>
								<?php foreach($headers as $i => $h){ ?>
								<td><?= number_format($progress[$i]["subsidiaries"][$sub]["summary"]["total"], 2) ?></td>
								<?php } ?>
							</tr>
							<?php foreach($subsidiary["divisions"] as $div => $division){ ?>
							<tr class="fw-bold">
								<td></td>
								<td><?= $div ?></td>
								<td class="border-end"></td>
								<?php foreach($headers as $i => $h){ ?>
								<td><?= number_format($progress[$i]["subsidiaries"][$sub]["divisions"][$div]["summary"]["total"], 2) ?></td>
								<?php } ?>
							</tr>
							<?php foreach($division["categories"] as $cat => $category){ ?>
							<tr>
								<td></td>
								<td></td>
								<td class="border-end"><?= $cat ?></td>
								<?php foreach($headers as $i => $h){ ?>
								<td><?= number_format($progress[$i]["subsidiaries"][$sub]["divisions"][$div]["categories"][$cat]["summary"]["total"], 2) ?></td>
								<?php } ?>
							</tr>
							<?php }}} ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
function set_status_chart(){
	var data = JSON.parse($("#status_chart_data").html());
	
	echarts.init(document.querySelector("#status_chart_amount")).setOption({
		title	: {text: 'By Order Amount', left: 'center'},
		tooltip	: {trigger: 'item'},
		//legend	: {orient: 'vertical', left: 'left'},
		series	: [{type: 'pie', data: data.amount, label: {show: false}, labelLine: {show: false}}],
	});
	
	echarts.init(document.querySelector("#status_chart_qty")).setOption({
		title	: {text: ' By Order Qty', left: 'center'},
		tooltip	: {trigger: 'item'},
		legend	: {orient: 'vertical', left: 'right'},
		series	: [{type: 'pie', data: data.qty, label: {show: false}, labelLine: {show: false}}],
	});
}

document.addEventListener("DOMContentLoaded", () => {
	
	//set_status_chart();
	
	$("#report_from").on( "change", function() {
		$("#report_to").attr("min", $(this).val());
	});
	
	$("#report_to").on( "change", function() {
		$("#report_from").attr("max", $(this).val());
	});
	
	$("#form_upload_magento").submit(function(e) {
		e.preventDefault();
		$("#form_upload_magento .sys_msg").html("");
		ajax_form_warning(this, "module/obs_magento/upload", "Do you upload data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/obs_magento");
		});
	});
});
</script>