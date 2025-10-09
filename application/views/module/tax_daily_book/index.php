<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1> Tax Daily Book </h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">Tax Daily Book</li>
			</ol>	
		</nav>
	</div>	
	<div>
		<a href="../user_manual/module/tax_daily_book/tax_daily_book_en.pptx" class="text-primary p-3">User Manual</a>
	</div>
</div>

<section class="section">
	<div class="row justify-content-center">
    <!-- Columna Única que contiene ambos formularios uno encima del otro -->
	
		<!-- Formulario para Subir Excel -->
		<div class="col-md-3">    
			<div class="card shadow-sm">
				<div class="card-body">
					<h5 class="card-title text-center">Upload Daily Book</h5>
		  
					<form class="row g-3" id="form_tax_update" href="<?= base_url() ?>" enctype="multipart/form-data">
						<div class="col-md-12">
							<label class="form-label">Select File (raw data)</label>
							<input class="form-control" type="file" name="attach">
						</div>
                
						<div class="text-center pt-3">
							<button type="submit" class="btn btn-primary">
							<i class="bi bi-upload"></i> Upload
							</button>
						</div>
					</form>	
				</div>
			</div>
		</div>
		
		<!-- Formulario para Subir Trial Balance -->
		<!--<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title text-center">Upload Trial Balance</h5>
		  
					<form class="row g-3" id="form_tax_trial_balance_update" href="<?= base_url() ?>" enctype="multipart/form-data">
						<div class="col-md-12">
							<label class="form-label">Select File</label>
							<input class="form-control" type="file" name="attach">
						</div>
				
						<div class="text-center pt-3">
							<button type="submit" class="btn btn-primary">
							<i class="bi bi-upload"></i> Upload
							</button>
						</div>
					</form>	
				</div>
			</div>
		</div> -->
		
		<!-- Formulario para Exportar Reporte Excel -->
		<div class="col-md-4">	
			<div class="card shadow-sm">
				<div class="card-body">
					<h5 class="card-title text-center">Export Daily Book</h5>
					
					<form class="row g-3 justify-content-center" id="export_report_form" action="<?= base_url('module/tax_daily_book/export_to_excel')?>" method="POST">
						<div class="col-md-3">
							<label for="period" class="form-label">Period</label>
							<select class="form-select flex-grow-1" id="period" name="period" required>
								<option value="">Period...</option>
								<?php foreach ($period as $periodName) { ?>
									<option value="<?php echo $periodName; ?>"><?php echo $periodName; ?></option>
								<?php } ?>
							</select>
							<!-- <div class="form-text">Select the period to export.</div> -->
						</div>
						
							<div class="col-md-4"> 
								<label for="debe" class="form-label">Debe</label>
								<input type="text" class="form-control bg-light" id="debe" name="debe" value="Valor del Debe" readonly>
							</div>
							<div class="col-md-4"> 
								<label for="haber" class="form-label">Haber</label>
								<input type="text" class="form-control bg-light" id="haber" name="haber" value="Valor del Haber" readonly>
							</div>
						
						<div class="text-center pt-3">
							<button type="submit" class="btn btn-primary">
								<i class="bi bi-file-earmark-arrow-down me-2"></i> Export
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<!--<div class="card">
			<div class="card-body">
				<h5 class="card-title text-center">Export Daily Book</h5>		  
				<form class="row g-3" id="export_report_form" action="<?= base_url('module/tax_daily_book/export_to_excel')?>" method="POST">
			  
					<!--<div class="col-md-6">
						<select id="period" name="period" required>
							<option value="">Choose period...</option>
							<?php foreach ($period as $periodName) { ?>
								<option value="<?php echo htmlspecialchars($periodName); ?>"><?php echo htmlspecialchars($periodName); ?></option>
							<?php } ?>
						</select>
					</div>-->
					<!--<div class="col-md-6">
					  <label class="form-label">From</label>
					  <input type="date" class="form-control" id="effective_from" name="effective_from" required>
					</div>
					
					<div class="col-md-6">
					  <label class="form-label">To</label>
					  <input type="date" class="form-control" id="effective_to" name="effective_to" required>
					</div>
					
					<div class="text-center pt-3">
					  <button type="submit" class="btn btn-primary">
						<i class="bi bi-file-earmark-arrow-down"></i> Export				
					  </button>
					</div>
				</form>			  
			</div>
		</div> -->
		
       
	  
	</div>
	<div class="row justify-content-center mt-4">
		<div class="col-lg-8 col-xl-7">		
			<div class="card shadow-sm">
				<div class="card-body p-0">
					<table class="table table-striped table-hover mb-0">
						<thead>
							<tr>
								<th scope="col" style="width: 5%;">#</th>
								<th scope="col" style="width: 45%;">Module</th>
								<th scope="col" style="width: 15%;">Team</th>
								<th scope="col" style="width: 25%;">Last updated</th>
								<th scope="col" style="width: 10%;" class="text-center"></th>
							</tr>
						</thead>
						<tbody>
							<?php $count = 1; ?>
							<?php foreach($last_modules_info as $item){ 
								// Obtener el enlace del módulo, si existe en el array de mapeo
								$link = $module_links[$item['module']] ?? '#'; 
							?>
							
							<tr>
								<td><?= $count?></td>
								<td><?= $item['module']?></td>
								<td><?= $item['team']?></td>
								<td><?= $item['last_updated']?></td>
								<td class="text-center">
									<a href="<?= $link ?>" target="_blank" title="Go to module"> 
										<i class="bi bi-arrow-up-right-square-fill text-primary"></i>
									</a>
								</td>
							</tr>
							<?php $count += 1; ?>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>

<script> // Upload Data
document.addEventListener("DOMContentLoaded", () => {
	$("#form_tax_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "module/tax_daily_book/upload", "Do you want to update Tax Daily Book data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/tax_daily_book");
		});
	});

	$("#form_tax_trial_balance_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "module/tax_daily_book/upload_trial_balance", "Do you want to update Tax Trial Balance data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/tax_daily_book");
		});
	});
});
</script>

<script>
    const accumValues = <?php echo json_encode($accum_values); ?>;

    const periodSelect = document.getElementById('period');
    const debeInput = document.getElementById('debe');
    const haberInput = document.getElementById('haber');
    
    // Función para formatear números como moneda (opcional)
    const formatter = new Intl.NumberFormat('es-PE', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    periodSelect.addEventListener('change', function() {
        const selectedPeriod = this.value;

        if (!selectedPeriod) {
            debeInput.value = 'Valor del Debe';
            haberInput.value = 'Valor del Haber';
            return;
        }

        if (accumValues[selectedPeriod]) {
            const data = accumValues[selectedPeriod];
            
            // --- MODIFICACIÓN CLAVE ---
            // 1. Obtener el valor numérico (data.debe)
            // 2. Convertirlo a una cadena de texto (toString())
            // 3. Reemplazar todas las comas (si existen) por una cadena vacía.
            const debeSinComas = data.debe.toString().replace(/,/g, '');
            const haberSinComas = data.haber.toString().replace(/,/g, '');
            
            // Asignar los valores sin comas a los inputs
            debeInput.value = debeSinComas;
            haberInput.value = haberSinComas;
            
        } else {
            debeInput.value = 'No Data';
            haberInput.value = 'No Data';
        }
    });

    periodSelect.dispatchEvent(new Event('change'));

</script>