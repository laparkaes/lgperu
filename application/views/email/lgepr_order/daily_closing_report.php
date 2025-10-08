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
	<h4>Daily Logistic Progress & Plan</h4>
	<table style="border-collapse: collapse;">
		<tr>
			<td>K USD</td>
			<?php foreach($daily_plan_header as $item){ ?>
			<td style="background:<?= $item["day"] == $today ? "#fff3cd;" : "#fff;" ?>"><?= $item["day"] ?></td>
			<?php } ?>
		</tr>
		<?php foreach($daily_plan as $total){ ?>
		<tr style="font-style:bold;">
			<td><?= $total["rowname"] ?></td>
			<?php foreach($total["closing"] as $item){ ?>
			<td style="background:<?= $item["day"] == $today ? "#fff3cd;" : "#fff;" ?>">
				<?php
				$val = $item["day"] < $today ? $item["amount"]["closed"] : $item["amount"]["plan"];
				if ($val) echo number_format(($item["day"] < $today ? $item["amount"]["closed"] : $item["amount"]["plan"])/1000);
				?>
			</td>
			<?php } ?>
		</tr>
			<?php foreach($total["departments"] as $department){ ?>
			<tr>
				<td style="padding-left:20px;"><?= $department["rowname"] ?></td>
				<?php foreach($department["closing"] as $item){ ?>
				<td style="background:<?= $item["day"] == $today ? "#fff3cd;" : "#fff;" ?>">
					<?php
					$val = $item["day"] < $today ? $item["amount"]["closed"] : $item["amount"]["plan"];
					if ($val) echo number_format(($item["day"] < $today ? $item["amount"]["closed"] : $item["amount"]["plan"])/1000);
					?>
				</td>
				<?php } ?>
			</tr>
				<?php foreach($department["companies"] as $company){ ?>
				<tr>
					<td style="padding-left:40px;"><?= $company["rowname"] ?></td>
					<?php foreach($company["closing"] as $item){ ?>
					<td style="background:<?= $item["day"] == $today ? "#fff3cd;" : "#fff;" ?>">
						<?php
						$val = $item["day"] < $today ? $item["amount"]["closed"] : $item["amount"]["plan"];
						if ($val) echo number_format(($item["day"] < $today ? $item["amount"]["closed"] : $item["amount"]["plan"])/1000);
						?>
					</td>
					<?php } ?>
				</tr>
					<?php foreach($company["divisions"] as $division){ ?>
					<tr>
						<td style="padding-left:60px;"><?= $division["rowname"] ?></td>
						<?php foreach($division["closing"] as $item){ ?>
						<td style="background:<?= $item["day"] == $today ? "#fff3cd;" : "#fff;" ?>">
							<?php
							$val = $item["day"] < $today ? $item["amount"]["closed"] : $item["amount"]["plan"];
							if ($val) echo number_format(($item["day"] < $today ? $item["amount"]["closed"] : $item["amount"]["plan"])/1000);
							?>
						</td>
						<?php } ?>
					</tr>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		<?php } ?>
	</table>
</html>