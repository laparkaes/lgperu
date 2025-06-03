<html>
	<head>
	</head>
	<body>
		<table>
			<tr>
				<th>Company</th>
				<th>Division</th>
				<th>Container</th>
				<th>ETA</th>
				<th>ATA</th>
				<th>Picked up</th>
				<th>Warehouse</th>
				<th>Returned</th>
				<th>Return due</th>
				<th>Dem. days</th>
				<th>Det. days</th>
				<th>No data</th>
			</tr>
			<?php foreach($containers as $item){ ?>
			<tr>
				<td><?= $item->company ?></td>
				<td><?= $item->division ?></td>
				<td><?= $item->container ?></td>
				<td><?= $item->eta ?></td>
				<td><?= $item->ata ?></td>
				<td><?= $item->picked_up ?></td>
				<td><?= $item->wh_arrival ?></td>
				<td><?= $item->returned ?></td>
				<td><?= $item->return_due ?></td>
				<td><?= $item->dem_days ?></td>
				<td><?= $item->det_days ?></td>
				<td><?= $item->no_data ?></td>
			</tr>
			<?php } ?>
		</table>
	</body>
</html>