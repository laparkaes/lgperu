<html>
<head>
	<style>
	td{
		border-top: 1px solid black;
		border-bottom: 1px solid black;
		padding: 10px 5px;
	}
	</style>
</head>
<body>
	<div>[This is auto generated container daily report]</div>
	<br/>
	<div><?= number_format($no_data_qty) ?> containers require to be reviewed.</div>
	<br/>
	<br/>
	<table style="text-align: center; border-collapse: collapse;">
		<tr>
			<td style="width: 100px; border-left: 1px solid black; border-right: 1px solid black;"><strong>Date</strong></td>
			<td style="width: 200px; border-right: 1px solid black;"><?= $today ?></td>
		</tr>
		<tr>
			<td style="border-left: 1px solid black; border-right: 1px solid black;"><strong>Period</strong></td>
			<td style="border-right: 1px solid black;"><?= $eta_from ?> ~ <?= $today ?></td>
		</tr>
	</table>
	<br/>
	<br/>
	<div><strong>[Warning!!!] Remind days (Container qty)</strong></div>
	<br/>
	<table style="text-align: center; border-collapse: collapse;">
		<tr>
			<td style="border-left: 1px solid black; border-right: 1px solid black;"><strong>Type</strong></td>
			<td colspan="4" style="border-right: 1px solid black;"><strong>Demurrage</strong></td>
			<td colspan="4" style="border-right: 1px solid black;"><strong>Detention</strong></td>
		</tr>
		<tr>
			<td style="width: 100px; border-left: 1px solid black; border-right: 1px solid black;"><strong>Remind</strong></td>
			<td style="width: 100px;"><strong>2 days</strong></td>
			<td style="width: 100px;"><strong>1 day</strong></td>
			<td style="width: 100px;"><strong>0 day</strong></td>
			<td style="width: 100px; border-right: 1px solid black;"><strong>Issuing</strong></td>
			<td style="width: 100px;"><strong>6~10 days</strong></td>
			<td style="width: 100px;"><strong>1~5 day</strong></td>
			<td style="width: 100px;"><strong>0 day</strong></td>
			<td style="width: 100px; border-right: 1px solid black;"><strong>Issuing</strong></td>
		</tr>
		<tr>
			<td style="border-left: 1px solid black; border-right: 1px solid black;"><strong>CTN qty</strong></td>
			<td><?= number_format($remind["dem"]["2_days"]) ?></td>
			<td><?= number_format($remind["dem"]["1_day"]) ?></td>
			<td><?= number_format($remind["dem"]["0_day"]) ?></td>
			<td style="border-right: 1px solid black;"><?= number_format($remind["dem"]["issuing"]) ?></td>
			<td><?= number_format($remind["det"]["6_10_days"]) ?></td>
			<td><?= number_format($remind["det"]["1_5_day"]) ?></td>
			<td><?= number_format($remind["det"]["0_day"]) ?></td>
			<td style="border-right: 1px solid black;"><?= number_format($remind["det"]["issuing"]) ?></td>
		</tr>
	</table>
	<br/>
	<br/>
	<div><strong>Container Aging Summary</strong></div>
	<br/>
	<table style="text-align: center; border-collapse: collapse;">
		<tr>
			<td rowspan="2" style="width: 100px; border-left: 1px solid black; border-right: 1px solid black;"><strong>Period</strong></td>
			<?php foreach($issued as $period => $item){ ?>
			<td colspan="3" style="border-right: 1px solid black;"><strong><?= $period ?></strong></td>
			<?php } ?>
		</tr>
		<tr>
			<?php foreach($issued as $period => $item){ ?>
			<td style="width: 100px;"><strong>CTNs</strong></td>
			<td style="width: 100px;"><strong>Days</strong></td>
			<td style="width: 100px; border-right: 1px solid black;"><strong>Amount</strong></td>
			<?php } ?>
		</tr>
		<tr>
			<td style="border-left: 1px solid black; border-right: 1px solid black;"><strong>Demurrage</strong></td>
			<?php foreach($issued as $period => $item){ ?>
			<td><?= number_format($item["dem"]["qty"]) ?></td>
			<td><?= number_format($item["dem"]["days"]) ?></td>
			<td style="border-right: 1px solid black;"><?= number_format($item["dem"]["amount"]) ?></td>
			<?php } ?>
		</tr>
		<tr>
			<td style="border-left: 1px solid black; border-right: 1px solid black;"><strong>Detention</strong></td>
			<?php foreach($issued as $period => $item){ ?>
			<td><?= $item["det"]["qty"] ? number_format($item["det"]["qty"]) : "-" ?></td>
			<td><?= $item["det"]["days"] ? number_format($item["det"]["days"]) : "-" ?></td>
			<td style="border-right: 1px solid black;"><?= $item["det"]["amount"] ? number_format($item["det"]["amount"]) : "-" ?></td>
			<?php } ?>
		</tr>
	</table>
	<br/>
	<br/>
	[End of Document]
</body>
</html>