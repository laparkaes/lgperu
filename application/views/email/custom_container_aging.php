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
	<table style="text-align: center; border-collapse: collapse;">
		<tr>
			<td style="width: 150px; border-left: 1px solid black; border-right: 1px solid black;"><strong>Generation date</strong></td>
			<td style="width: 200px; border-right: 1px solid black;"><?= $today ?></td>
		</tr>
		<tr>
			<td style="border-left: 1px solid black; border-right: 1px solid black;"><strong>Container period</strong></td>
			<td style="border-right: 1px solid black;"><?= $eta_from ?> ~ <?= $today ?></td>
		</tr>
	</table>
	<br/>
	<br/>
	<div><strong><< Demurrage >></strong><br/>Containers not picked up from the port within free time (2 days in Peru).</div>
	<br/>
	<table style="text-align: center; border-collapse: collapse;">
		<tr>
			<td style="width: 100px; border-left: 1px solid black; border-right: 1px solid black;"><strong>Free Time</strong></td>
			<td style="width: 100px; color: red;"><strong>Overdue</strong></td>
			<td style="width: 100px;"><strong>0 day</strong></td>
			<td style="width: 100px;"><strong>1 day</strong></td>
			<td style="width: 100px;"><strong>2 days</strong></td>
			<td style="width: 100px; border-left: 1px solid black; border-right: 1px solid black;"><strong>Total</strong></td>
		</tr>
		<?php foreach($demurrage as $port => $item){ if ($port !== "Total"){ ?>
		<tr>
			<td style="border-left: 1px solid black; border-right: 1px solid black;"><strong><?= $port ?></strong></td>
			<td style="color: red;"><?= $item["overdue"] ? number_format($item["overdue"]) : "-" ?></td>
			<td><?= $item["0d"] ? number_format($item["0d"]) : "-" ?></td>
			<td><?= $item["1d"] ? number_format($item["1d"]) : "-" ?></td>
			<td><?= $item["2d"] ? number_format($item["2d"]) : "-" ?></td>
			<td style="border-left: 1px solid black; border-right: 1px solid black;"><?= $item["total"] ? number_format($item["total"]) : "-" ?></td>
		</tr>
		<?php }} ?>
	</table>
	<div>* Ports will be splitted when Custom team is ready to upload correct data.</div>
	<br/>
	<br/>
	<div><strong><< Detention >></strong><br/>Containers not returned to the port within the free days.</div>
	<br/>
	<table style="text-align: center; border-collapse: collapse;">
		<tr>
			<td style="width: 100px; border-left: 1px solid black; border-right: 1px solid black;"><strong>Free Time</strong></td>
			<td style="width: 100px; color: red;"><strong>Overdue</strong></td>
			<td style="width: 100px;"><strong>0 day</strong></td>
			<td style="width: 100px;"><strong>~3 day</strong></td>
			<td style="width: 100px;"><strong>~7 days</strong></td>
			<td style="width: 100px;"><strong>~14 days</strong></td>
			<td style="width: 100px;"><strong>~21 days</strong></td>
			<td style="width: 100px;"><strong>21 days~</strong></td>
			<td style="width: 100px; border-left: 1px solid black; border-right: 1px solid black;"><strong>Total</strong></td>
		</tr>
		<?php foreach($detention as $carrier => $item){ if ($carrier !== "Total"){ ?>
		<tr>
			<td style="border-left: 1px solid black; border-right: 1px solid black;"><strong><?= $carrier ?></strong></td>
			<td style="color: red;"><?= $item["overdue"] ? number_format($item["overdue"]) : "-" ?></td>
			<td><?= $item["0d"] ? number_format($item["0d"]) : "-" ?></td>
			<td><?= $item["3d"] ? number_format($item["3d"]) : "-" ?></td>
			<td><?= $item["7d"] ? number_format($item["7d"]) : "-" ?></td>
			<td><?= $item["14d"] ? number_format($item["14d"]) : "-" ?></td>
			<td><?= $item["21d"] ? number_format($item["21d"]) : "-" ?></td>
			<td><?= $item["99d"] ? number_format($item["99d"]) : "-" ?></td>
			<td style="border-left: 1px solid black; border-right: 1px solid black;"><?= $item["total"] ? number_format($item["total"]) : "-" ?></td>
		</tr>
		<?php }} ?>
		<tr>
			<td style="border-left: 1px solid black; border-right: 1px solid black;"><strong>Total</strong></td>
			<td style="color: red;"><?= number_format($detention["Total"]["overdue"]) ?></td>
			<td><?= number_format($detention["Total"]["0d"]) ?></td>
			<td><?= number_format($detention["Total"]["3d"]) ?></td>
			<td><?= number_format($detention["Total"]["7d"]) ?></td>
			<td><?= number_format($detention["Total"]["14d"]) ?></td>
			<td><?= number_format($detention["Total"]["21d"]) ?></td>
			<td><?= number_format($detention["Total"]["99d"]) ?></td>
			<td style="border-left: 1px solid black; border-right: 1px solid black;"><?= number_format($detention["Total"]["total"]) ?></td>
		</tr>
	</table>
	<br/>
	<br/>
	[End of Document]
</body>
</html>