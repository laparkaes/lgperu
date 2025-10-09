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
	<table style="border-collapse:collapse;font-size:.8rem;text-align:right;">
		<tr>
			<td style="text-align:left;">K USD</td>
			<?php foreach($daily_plan_header as $item){ ?>
			<td style="background:<?= $item["day"] == $today ? "#fff3cd;" : "#fff;" ?> width:40px;"><?= $item["day"] ?></td>
			<?php } ?>
		</tr>
		<?php foreach($daily_plan as $total){ ?>
		<tr>
			<td style="text-align:left;"><?= $total["rowname"] ?></td>
			<?php foreach($total["closing"] as $item){ ?>
			<td style="background:<?= $item["day"] == $today ? "#fff3cd;" : "#fff;" ?>">
				<?php
				$val = $item["amount"]["closed"] + $item["amount"]["plan"];
				if ($val) echo number_format($val/1000);
				?>
			</td>
			<?php } ?>
		</tr>
			<?php foreach($total["departments"] as $department){ ?>
			<tr>
				<td style="text-align:left;padding-left:20px;"><?= $department["rowname"] ?></td>
				<?php foreach($department["closing"] as $item){ ?>
				<td style="background:<?= $item["day"] == $today ? "#fff3cd;" : "#fff;" ?>">
					<?php
					$val = $item["amount"]["closed"] + $item["amount"]["plan"];
					if ($val) echo number_format($val/1000);
					?>
				</td>
				<?php } ?>
			</tr>
				<?php foreach($department["companies"] as $company){ ?>
				<tr>
					<td style="text-align:left;padding-left:40px;"><?= $company["rowname"] ?></td>
					<?php foreach($company["closing"] as $item){ ?>
					<td style="background:<?= $item["day"] == $today ? "#fff3cd;" : "#fff;" ?>">
						<?php
						$val = $item["amount"]["closed"] + $item["amount"]["plan"];
						if ($val) echo number_format($val/1000);
						?>
					</td>
					<?php } ?>
				</tr>
					<?php foreach($company["divisions"] as $division){
						$val = $division["closing"]["50"]["amount"]["plan"] + $division["closing"]["50"]["amount"]["closed"];
						if ($val){
					?>
					<tr>
						<td style="text-align:left;padding-left:60px;"><?= $division["rowname"] ?></td>
						<?php foreach($division["closing"] as $item){ ?>
						<td style="background:<?= $item["day"] == $today ? "#fff3cd;" : "#fff;" ?>">
							<?php
							$val = $item["amount"]["closed"] + $item["amount"]["plan"];
							if ($val) echo number_format($val/1000);
							?>
						</td>
						<?php } ?>
					</tr>
					<?php }} ?>
				<?php } ?>
			<?php } ?>
		<?php } ?>
	</table>
</html>