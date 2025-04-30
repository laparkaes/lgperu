<head>
    <title>Mi Página</title>
</head>
<div class="card mt-3">
	<div class="card-body">
		<div class="d-flex justify-content-between align-items-center">
			<h5 class="card-title">Order Status</h5>
			<div class="d-flex justify-content-end">
				<select class="form-select me-1" id="sl_period" style="width: 150px;">
					<?php foreach($periods as $item){  ?>
					<option value="<?= $item ?>" <?= ($item === $period) ? "selected" : "" ?>><?= $item ?></option>
					<?php } ?>
				</select>
			</div>
		</div>
		<table class="table table-hover align-middle text-end" style="font-size: 0.9rem;">
			<thead class="sticky-top" style="z-index: 10;">
				<tr class="text-start">
					<th scope="col" class="align-middle" style="border-right-width:thin;">K USD<br/>(Net Amount)</th>
					<th scope="col" class="align-middle" style="width:110px;">Monthly<br/>Report</th>
					<th scope="col" class="align-middle" style="width:110px;">ML</th>
					<th scope="col" class="align-middle" style="width:110px;">ML Actual</th>
					<th scope="col" class="align-middle" style="width:110px;">Po Needs</th>
					<th scope="col" class="align-middle" style="border-right-width:thin;width:120px;">Sales<br/>Projection</th>
					<th scope="col" class="align-middle" style="width:120px;">Actual</th>
					<th scope="col" class="align-middle" style="width:110px;">Expected</th>
					<th scope="col" class="align-middle" style="width:110px;">Shipped</th>
					<th scope="col" class="align-middle" style="width:110px;">Ready</th>
					<th scope="col" class="align-middle" style="width:110px;">Appointment</th>
					<th scope="col" class="align-middle" style="width:110px;">Entered</th>
					<th scope="col" class="align-middle" style="border-right-width:thin;width:110px;">In Transit</th>
					<th scope="col" class="align-middle" style="width:110px;">No Stock</th>
					<th scope="col" class="align-middle" style="border-right-width:thin;width:110px;">Hold</th>
					<th scope="col" class="align-middle" style="width:70px;">SD</th>
				</tr>
			</thead>
			<tbody>
				<tr class="table-dark">
					<td class="text-start" style="border-right-width:thin;">Total</td>
					<td><?= number_format($total["monthly_report"] / 1000 ) ?></td>
					<td><?= number_format($total["ml"] / 1000 ) ?></td>
					<td><?= number_format($total["ml_actual"] / 1000 ) ?></td>
					<td><?= number_format($total["po_needs"] / 1000 ) ?></td>
					<td style="border-right-width:thin;"><?= number_format($total["sales_projection"] / 1000 ) ?> (<?= number_format($total["sales_projection_per"] * 100) ?>%)</td>
					<td><?= number_format($total["actual"] / 1000 ) ?> (<?= number_format($total["actual_per"] * 100) ?>%)</td>
					<td><?= number_format($total["expected"] / 1000 ) ?></td>
					<td><?= number_format($total["shipped"] / 1000 ) ?></td>
					<td><?= number_format($total["ready"] / 1000 ) ?></td>
					<td><?= number_format($total["appointment"] / 1000 ) ?></td>
					<td><?= number_format($total["entered"] / 1000 ) ?></td>
					<td style="border-right-width:thin;"><?= number_format($total["in_transit"] / 1000 ) ?></td>
					<td><?= number_format($total["no_stock"] / 1000 ) ?></td>
					<td style="border-right-width:thin;"><?= number_format($total["hold"] / 1000 ) ?></td>
					<td><?= number_format($total["sales_deduction"] * 100) ?>%</td>
				</tr>
				<?php
				foreach($rows as $dpt => $dpt_item){
					?>
				<tr class="table-secondary">
					<td class="text-start" style="border-right-width:thin;padding-left: 15px;"><?= $dpt ?></td>
					<td><?= number_format($dpt_item["data"]["monthly_report"] / 1000 ) ?></td>
					<td><?= number_format($dpt_item["data"]["ml"] / 1000 ) ?></td>
					<td><?= number_format($dpt_item["data"]["ml_actual"] / 1000 ) ?></td>
					<td><?= number_format($dpt_item["data"]["po_needs"] / 1000 ) ?></td>
					<td style="border-right-width:thin;"><?= number_format($dpt_item["data"]["sales_projection"] / 1000 ) ?> (<?= number_format($dpt_item["data"]["sales_projection_per"] * 100) ?>%)</td>
					<td><?= number_format($dpt_item["data"]["actual"] / 1000 ) ?> (<?= number_format($dpt_item["data"]["actual_per"] * 100) ?>%)</td>
					<td><?= number_format($dpt_item["data"]["expected"] / 1000 ) ?></td>
					<td><?= number_format($dpt_item["data"]["shipped"] / 1000 ) ?></td>
					<td><?= number_format($dpt_item["data"]["ready"] / 1000 ) ?></td>
					<td><?= number_format($dpt_item["data"]["appointment"] / 1000 ) ?></td>
					<td><?= number_format($dpt_item["data"]["entered"] / 1000 ) ?></td>
					<td style="border-right-width:thin;"><?= number_format($dpt_item["data"]["in_transit"] / 1000 ) ?></td>
					<td><?= number_format($dpt_item["data"]["no_stock"] / 1000 ) ?></td>
					<td style="border-right-width:thin;"><?= number_format($dpt_item["data"]["hold"] / 1000 ) ?></td>
					<td><?= number_format($dpt_item["data"]["sales_deduction"] * 100) ?>%</td>
				</tr>
					<?php
					foreach($dpt_item["coms"] as $com => $com_item){
						?>
				<tr class="table-light">
					<td class="text-start" style="border-right-width:thin;padding-left: 25px;"><?= $com ?></td>
					<td><?= number_format($com_item["data"]["monthly_report"] / 1000 ) ?></td>
					<td><?= number_format($com_item["data"]["ml"] / 1000 ) ?></td>
					<td><?= number_format($com_item["data"]["ml_actual"] / 1000 ) ?></td>
					<td><?= number_format($com_item["data"]["po_needs"] / 1000 ) ?></td>
					<td style="border-right-width:thin;"><?= number_format($com_item["data"]["sales_projection"] / 1000 ) ?> (<?= number_format($com_item["data"]["sales_projection_per"] * 100) ?>%)</td>
					<td><?= number_format($com_item["data"]["actual"] / 1000 ) ?> (<?= number_format($com_item["data"]["actual_per"] * 100) ?>%)</td>
					<td><?= number_format($com_item["data"]["expected"] / 1000 ) ?></td>
					<td><?= number_format($com_item["data"]["shipped"] / 1000 ) ?></td>
					<td><?= number_format($com_item["data"]["ready"] / 1000 ) ?></td>
					<td><?= number_format($com_item["data"]["appointment"] / 1000 ) ?></td>
					<td><?= number_format($com_item["data"]["entered"] / 1000 ) ?></td>
					<td style="border-right-width:thin;"><?= number_format($com_item["data"]["in_transit"] / 1000 ) ?></td>
					<td><?= number_format($com_item["data"]["no_stock"] / 1000 ) ?></td>
					<td style="border-right-width:thin;"><?= number_format($com_item["data"]["hold"] / 1000 ) ?></td>
					<td><?= number_format($com_item["data"]["sales_deduction"] * 100) ?>%</td>
				</tr>
						<?php
						foreach($com_item["divs"] as $div => $div_item){
							?>
				<tr>
					<td class="text-start" style="border-right-width:thin;padding-left: 35px;"><?= $div ?></td>
					<td><?= number_format($div_item["data"]["monthly_report"] / 1000 ) ?></td>
					<td><?= number_format($div_item["data"]["ml"] / 1000 ) ?></td>
					<td><?= number_format($div_item["data"]["ml_actual"] / 1000 ) ?></td>
					<td><?= number_format($div_item["data"]["po_needs"] / 1000 ) ?></td>
					<td style="border-right-width:thin;"><?= number_format($div_item["data"]["sales_projection"] / 1000 ) ?> (<?= number_format($div_item["data"]["sales_projection_per"] * 100) ?>%)</td>
					<td><?= number_format($div_item["data"]["actual"] / 1000 ) ?> (<?= number_format($div_item["data"]["actual_per"] * 100) ?>%)</td>
					<td><?= number_format($div_item["data"]["expected"] / 1000 ) ?></td>
					<td><?= number_format($div_item["data"]["shipped"] / 1000 ) ?></td>
					<td><?= number_format($div_item["data"]["ready"] / 1000 ) ?></td>
					<td><?= number_format($div_item["data"]["appointment"] / 1000 ) ?></td>
					<td><?= number_format($div_item["data"]["entered"] / 1000 ) ?></td>
					<td style="border-right-width:thin;"><?= number_format($div_item["data"]["in_transit"] / 1000 ) ?></td>
					<td><?= number_format($div_item["data"]["no_stock"] / 1000 ) ?></td>
					<td style="border-right-width:thin;"><?= number_format($div_item["data"]["hold"] / 1000 ) ?></td>
					<td><?= number_format($div_item["data"]["sales_deduction"] * 100) ?>%</td>
				</tr>
							<?php
						}	
					}
				}
				?>
			</tbody>
		</table>
	</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
	
	$("#sl_period").change(function(e) {
		window.location.href = "/llamasys/dash/order_status?d=" + $(this).val();
	});
	
});
</script>

