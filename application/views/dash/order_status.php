<head>
    <title>Mi Página</title>
</head>
<div class="card mt-3">
	<div class="card-body">
		<div class="d-flex justify-content-between align-items-center">
			<h5 class="card-title">Order Status <?= $period ?></h5>
			<div class="d-flex justify-content-end">
				<select class="form-select me-1" id="sl_period" style="width: 150px;">
					<?php foreach($periods as $item){  ?>
					<option value="<?= $item ?>" <?= ($item === $period) ? "selected" : "" ?>><?= $item ?></option>
					<?php } ?>
					<option value="2024-12">2024-12</option>
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
		<table class="table align-middle text-center" style="font-size: 0.8rem;">
			<thead class="sticky-top" style="z-index: 10;">
				<tr>
					<th scope="col" class="text-start">K USD (Net Amount)</th>
					<th scope="col">Monthly Report</th>
					<th scope="col">ML</th>
					<th scope="col">ML Actual</th>
					<th scope="col">Po Needs</th>
					<th scope="col">Sales Projection</th>
					<th scope="col">Actual</th>
					<th scope="col">Expected</th>
					<th scope="col">Shipped</th>
					<th scope="col">Ready</th>
					<th scope="col">Picking</th>
					<th scope="col">Appointment</th>
					<th scope="col">Entered</th>
					<th scope="col">In Transit</th>
					<th scope="col">No Alloc.</th>
					<th scope="col">SD</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td class="text-start">Total</td>
					<td><?= number_format($total["monthly_report"] / 1000 ) ?></td>
					<td><?= number_format($total["ml"] / 1000 ) ?></td>
					<td><?= number_format($total["ml_actual"] / 1000 ) ?></td>
					<td><?= number_format($total["po_needs"] / 1000 ) ?></td>
					<td><?= number_format($total["sales_projection"] / 1000 ) ?> (<?= number_format($total["sales_projection_per"] * 100) ?>%)</td>
					<td><?= number_format($total["actual"] / 1000 ) ?> (<?= number_format($total["actual_per"] * 100) ?>%)</td>
					<td><?= number_format($total["expected"] / 1000 ) ?></td>
					<td><?= number_format($total["shipped"] / 1000 ) ?></td>
					<td><?= number_format($total["shipping"] / 1000 ) ?></td>
					<td><?= number_format($total["picking"] / 1000 ) ?></td>
					<td><?= number_format($total["appointment"] / 1000 ) ?></td>
					<td><?= number_format($total["entered"] / 1000 ) ?></td>
					<td><?= number_format($total["in_transit"] / 1000 ) ?></td>
					<td><?= number_format($total["no_alloc"] / 1000 ) ?></td>
					<td><?= number_format($total["sales_deduction"] * 100) ?>%</td>
				</tr>
				<?php
				foreach($rows as $dpt => $dpt_item){
					//echo $dpt."<br/>";
					//print_r($dpt_item["data"]);
					//echo "<br/><br/>";
					
					foreach($dpt_item["coms"] as $com => $com_item){
						//echo $dpt." >>> ".$com."<br/>";
						//print_r($com_item["data"]);
						//echo "<br/><br/>";	
						
						foreach($com_item["divs"] as $div => $div_item){
							//echo $dpt." >>> ".$com." >>> ".$div."<br/>";
							//print_r($div_item["data"]);
							//echo "<br/><br/>";
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
	
	ajax_simple({p: $("#ip_period").val()}, "page/lgepr_punctuality/export").done(function(res) {
		$("#btn_export").removeClass("d-none");
		$("#btn_export").attr("href", res.url);
	});
	
});
</script>

