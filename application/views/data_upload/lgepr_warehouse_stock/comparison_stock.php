<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparación de Stock (LG vs. WMS)</title>
    <style>
        body {
            padding: 20px;
        }
        .table-title {
            margin-bottom: 20px;
            text-align: center;
        }
		
		.table {
			background-color: transparent !important; 
		}

		.table th,
		.table td {
			background-color: transparent !important;
		}
    </style>
</head>
<body>
	
    <div class="d-flex justify-content-end mb-3 no-print">
		<button class="btn btn-success me-2" id="export-excel-btn">
         <i class="bi bi-file-earmark-spreadsheet"></i> Export
		</button>
        <button class="btn btn-danger" onclick="window.close()">Close</button>
    </div>
	
	<ul class="nav nav-tabs nav-tabs-bordered" id="borderedTab" role="tablist">
		<li class="nav-item" role="presentation">
			<button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#bordered-general" type="button" role="tab" aria-controls="general" aria-selected="true">General</button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-link" id="comparison-tab" data-bs-toggle="tab" data-bs-target="#bordered-comparison" type="button" role="tab" aria-controls="comparison" aria-selected="false" tabindex="-1">Comparison</button>
		</li>
	</ul>
	
	<div class="tab-content pt-2" id="borderedTabContent">
		<div class="tab-pane fade active show" id="bordered-general" role="tabpanel" aria-labelledby="general-tab">
			<div class="table-responsive">
				<table class="table table-hover table-bordered table-sm">
					<thead>
						<tr>
							<th>Warehouse</th>
							<th>Model</th>
							<th>Sub Inventory</th>
							<th class="text-end">LG Stock</th>
							<th class="text-end">Warehouse Stock</th>
							<th class="text-end">Difference</th>
							<th class="text-center">Status</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($final_result)): ?>
							<tr>
								<td colspan="6" class="text-center">Don't find data to show.</td>
							</tr>
						<?php else: ?>
							<?php foreach ($final_result as $row): ?>
								
								<?php
									if ($row['diff'] == 0){
										$status = 'text-success';
									} elseif($row['diff'] <= 5 && $row['diff'] >= -5 && $row['diff'] != 0){
										$status = 'text-warning';
									} else{
										$status = 'text-danger';
									}
								?>
								<tr>
									<td><?= htmlspecialchars($row['warehouse']) ?></td>
									<td><?= htmlspecialchars($row['model']) ?></td>
									<td><?= htmlspecialchars($row['sub_inventory']) ?></td>
									<td class="text-end"><?= htmlspecialchars($row['lg_stock']) ?></td>
									<td class="text-end"><?= htmlspecialchars($row['w_stock']) ?></td>
									<td class="text-end"><?= htmlspecialchars($row['diff']) ?></td>
									<td class="text-center"><i class="bi bi-circle-fill activity-badge <?= $status ?> align-self-start"></i></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="tab-pane fade" id="bordered-comparison" role="tabpanel" aria-labelledby="comparison-tab">
			<div class="table-responsive">
				<table class="table table-hover table-bordered table-sm">
					<thead>
						<tr>
							<th>Warehouse</th>
							<th>Model</th>
							<th>Sub Inventory</th>
							<th class="text-end">LG Stock</th>
							<th class="text-end">Warehouse Stock</th>
							<th class="text-end">Difference</th>
							<th class="text-center">Status</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($final_result)): ?>
							<tr>
								<td colspan="6" class="text-center">Don't find data to show.</td>
							</tr>
						<?php else: ?>
							<?php foreach ($final_result as $row): ?>
								<?php if ($row['diff'] != 0){?>							
									<?php
										if ($row['diff'] == 0){
											$status = 'text-success';
										} elseif($row['diff'] <= 5 && $row['diff'] >= -5 && $row['diff'] != 0){
											$status = 'text-warning';
										} else{
											$status = 'text-danger';
										}
									?>
									<tr>
										<td><?= htmlspecialchars($row['warehouse']) ?></td>
										<td><?= htmlspecialchars($row['model']) ?></td>
										<td><?= htmlspecialchars($row['sub_inventory']) ?></td>
										<td class="text-end"><?= htmlspecialchars($row['lg_stock']) ?></td>
										<td class="text-end"><?= htmlspecialchars($row['w_stock']) ?></td>
										<td class="text-end"><?= htmlspecialchars($row['diff']) ?></td>
										<td class="text-center"><i class="bi bi-circle-fill activity-badge <?= $status ?> align-self-start"></i></td>
									</tr>
								<?php } ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
			  
    
</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const exportBtn = document.getElementById('export-excel-btn');
	if (exportBtn) {
		exportBtn.addEventListener('click', function() {
			window.location.href = '<?= base_url("data_upload/lgepr_warehouse_stock/export_comparison_data_to_excel"); ?>';
		});
	}
});
</script>