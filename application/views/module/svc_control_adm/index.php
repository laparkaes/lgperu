<head>
	<meta charset="UTF-8">
    <style>
        .table-success {
            border: 2px solid black;
        }
		#resumenSemanaTable thead {
			position: sticky;
			top: 0;
			background-color: white; /* Asegura que el fondo cubra el contenido desplazado */
			z-index: 1; /* Asegura que la cabecera esté encima del contenido */
		}

		#resumenSemanaTable th {
			white-space: nowrap; /* Evita que el texto se envuelva */
		}
		#serviceTable th[data-sortable="true"] {
			cursor: pointer;
		}
    </style>
</head>
<div class="pagetitle">
    <h1>Service Control Admin </h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Admin Control</li>
        </ol>
    </nav>
</div>
<section class="section">
	<br>
    <div class="row g-3 justify-content-center">
        <div class="row justify-content-center">
		
            
        </div>
    </div>
	
	
	<div class="row">
        <div class="col">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Administración de citas</h5>
					
					<ul class="nav nav-tabs nav-tabs-bordered" id="myTabjustified" role="tablist">
						<li class="nav-item" role="presentation">
						  <button class="nav-link w-100 active" id="cita-tab" data-bs-toggle="tab" data-bs-target="#cita-justified" type="button" role="tab" aria-controls="cita" aria-selected="false" tabindex="-1">Citas</button>
						</li>
						<li class="nav-item" role="presentation">
						  <button class="nav-link w-100" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard-justified" type="button" role="tab" aria-controls="dashboard" aria-selected="true">Dashboard</button>
						</li>
					</ul>
			  
					<div class="tab-content pt-2" id="myTabjustifiedContent">
						<div class="tab-pane fade active show" id="cita-justified" role="tabpanel" aria-labelledby="cita-tab">
						
							<div class="row g-3" style="margin-top: 10px;">

								<div class="col-md-2">
									<input type="date" class="form-control" id="citaFilter" name="citaFilter">
								</div>
								
								<div class="col-md-2">
									<select id="technicianFilter" class="form-select">
										<option value="">Técnico</option>
										<?php
										$tecnicos_unicos = [];
										foreach ($info as $fila) {
											if (!in_array($fila->technical, $tecnicos_unicos) && !empty($fila->technical)) {
												$tecnicos_unicos[] = $fila->technical;
												echo '<option value="' . $fila->technical . '">' . $fila->technical . '</option>';
											}
										}
										?>
									</select>
								</div>
								<div class="col-md-1">
									<select id="statusFilter" class="form-select">
										<option value="">Status</option>
										<?php
										foreach ($status as $item_status) { ?>
											<option><?= $item_status ?></option>									
										<?php }
										?>
									</select>
								</div>
								<div class="col-md-2">
									<select id="serviceFilter" class="form-select">
										<option value="">Servicio</option>
											<?php
											foreach ($service_type as $item_service_type) {
												?>
												<option><?= $item_service_type ?></option>
											<?php } ?>
										</select>
									</select>
								</div>
								<div class="col-md-2">
									<select id="districtFilter" class="form-select">
										<option value="">Distrito</option>
											<?php
											foreach ($state as $item_state) {
												?>
												<option><?= $item_state ?></option>
											<?php } ?>
										</select>
									</select>
								</div>
								<div class="col-md-2">
									<input type="text"  style="display:None;" class="form-control" id="availabilityValue" name="availabilityValue" placeholder="Disponibilidad: Not Found" readonly>
									<!--<div class="control border p-1 m-0">
										<span>Disponibilidad: </span><span id="availabilityValue">Not Found</span>
									</div>-->
								</div>
								
								<div class="col-md-1 d-flex justify-content-end">
									<!--<button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#uploadAvailable">
										<i class="bi bi-upload"></i>
									</button>
									<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#optionsModal">
										<i class="bi bi-gear"></i>
									</button>-->
									
									

									  <!--<a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">-->
									<a href="#" class="nav-link nav-profile d-flex align-items-center pe-0 text-primary border-bottom border-primary round p-2" data-bs-toggle="dropdown" >
										Opciones				
										<i class="bi bi-three-dots-vertical"></i>
								
										<!--<span class="d-none d-md-block dropdown-toggle ps-2">Opciones</span>-->
									</a><!-- End Profile Iamge Icon -->

									<ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
										<li class="dropdown-header">
											<span>Choose an option</span>
										</li>
										
										<li>
											<hr class="dropdown-divider">
										</li>

										<li>
											<a class="dropdown-item d-flex align-items-center" href="" data-bs-toggle="modal" data-bs-target="#uploadAvailable">
												<i class="bi bi-upload m-2"></i> 
												<span>Upload schedule</span>
											</a>
										</li>
										<li>
											<hr class="dropdown-divider">
										</li>

										<li>
											<a class="dropdown-item d-flex align-items-center" href="" data-bs-toggle="modal" data-bs-target="#optionsModal">
												<i class="bi bi-gear m-2"></i>
												<span>Service Settings</span>
											 </a>
										</li>
			

									</ul><!-- End Profile Dropdown Items -->
															
								</div>
							</div>
							<br>
							<div style="overflow-y: auto; max-height: 600px;">
								<table class="table table-striped" id="serviceTable">
									<thead>
										<tr>
											<th scope="col" class="text-center" data-sortable="true">Fecha de Cita</th>
											<th scope="col" class="text-center" data-sortable="true">Técnico</th>
											<th scope="col" class="text-center" style="display:none" data-sortable="true">Día</th>
											<th scope="col" class="text-center" style="display:none" data-sortable="true">Cupos Totales</th>
											<th scope="col" class="text-center" style="display:none" data-sortable="true">Disponibilidad</th>
											<th scope="col" class="text-center" data-sortable="true">Distrito</th>
											<th scope="col" class="text-center" data-sortable="true">Tipo de Servicio</th>
											<th scope="col" class="text-center" data-sortable="true">Código</th>
											<th scope="col" class="text-center" data-sortable="true">Nombre Cliente</th>
											<th scope="col" class="text-center" data-sortable="true">Teléfono</th>
											<th scope="col" class="text-center" style="display:none" data-sortable="true">Fecha de Registro</th>									
											<th scope="col" class="text-center" data-sortable="true">Status</th>
											<th scope="col" class="text-center" style="display:none" data-sortable="true">Comentarios</th>
											<th scope="col"> </th>
											<th scope="col"> </th>
										</tr>
									</thead>
									<tbody>
										<?php foreach ($info as $item_info) { ?>
											<tr>
												<td class="text-center"><?= $item_info->service_date ?> </td>
												<td class="text-center"><?= $item_info->technical ?> </td>
												<td class="text-center" style="display:none"><?= $item_info->day ?> </td>
												<td class="text-center" style="display:none"><?= $item_info->total_job ?> </td>
												<td class="text-center" style="display:none"><?= $item_info->available_job ?> </td>
												<td class="text-center"><?= $item_info->district ?> </td>
												<td class="text-center"><?= $item_info->service_type ?> </td>
												<td class="text-center"><?= $item_info->service_code ?> </td>
												<td class="text-center"><?= $item_info->client_name ?> </td>
												<td class="text-center"><?= $item_info->mobile_number ?> </td>
												<td class="text-center" style="display:none"><?= $item_info->register_date ?> </td>										
												<td class="text-center">
													<?php if ($item_info->status === 'Registered') { ?>
														<span class="badge border border-secondary border-1 text-secondary"><i class="bi bi-info-circle me-1"></i>Registrado</span>
													<?php } elseif ($item_info->status === 'Postponed') { ?>
														<span class="badge border border-danger border-1 text-danger"><i class="bi bi-exclamation-octagon me-1"></i>Postpuesto</span>
													<?php } elseif ($item_info->status === 'Finished') { ?>
														<span class="badge border border-success border-1 text-success"><i class="bi bi-check-circle me-1"></i>Finalizado</span>
													<?php } elseif ($item_info->status === 'Assigned') { ?>
														<span class="badge border border-primary border-1 text-primary"><i class="bi bi-check-all"></i>Asignado</span>
													<?php } ?>
												</td>
												<td class="text-center" style="display:none"><?= $item_info->service_comment ?> </td>	
												<td class="text-center" style="width: 80px;">
													<div class="text-end">
														<button class="btn btn-link edit-button" data-id="<?= $item_info->svc_control_id ?>" data-old-service-date="<?= $item_info->service_date ?>" data-old-service-type="<?= $item_info->service_type ?>">
															<i class="bi bi-pencil-fill"></i>
														</button>
													</div>
												</td>
												<td class="text-center" style="width: 80px;">
													<div class="text-end">
														<button class="btn btn-link delete-button" data-id="<?= $item_info->svc_control_id ?>" data-old-service-date="<?= $item_info->service_date ?>" data-old-service-type="<?= $item_info->service_type ?>">
															<i class="bi bi-trash3-fill text-danger"></i>
														</button>
													</div>
												</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>								
						
						</div>
						<div class="tab-pane fade" id="dashboard-justified" role="tabpanel" aria-labelledby="dashboard-tab">
						
							<div class="row g-3" style="margin-top: 10px;">

								<div class="col-md-2" style="display:none">
									<input type="date" class="form-control" style="display:none" id="citaFilterDinamic" name="citaFilterDinamic">
								</div>
								
								<div class="col-md-2">
									<select id="groupFilterDinamic" class="form-select" name="groupFilterDinamic">
										<option value="">Agrupar por:</option>
										<option>Servicio</option>
										<option>Distrito</option>
										<option>Tecnico</option>
									</select>
								</div>
								<div class="col-md-2" id="uniqueFilterContainer" style="display:none">
									<select id="uniqueFilterDinamic" class="form-select" name="uniqueFilterDinamic">
										<option value="">Choose...</option>
									</select>
								</div>
							</div>
							<br>
							<!--<a href="#" id="resumenSemana" class="view-table" style="font-size: 15px;" data-id="28">Resumen Semana</a>-->
							<button id="abrirResumenVentana" class="btn btn-outline-secondary btn-sm ms-2" style="font-size: 12px; display: none;">Ver en Ventana</button>
							<div id="resumenSemanaContainer" style="display: none;">
								<div style="overflow-y: auto; max-height: 800px;">
									<table class="table" id="resumenSemanaTable">
										<thead>
											<tr>
												<!--<th scope="col" class="text-center">Técnico</th>
												<th scope="col" class="text-center">Día</th>
												<th scope="col" class="text-center">Cupos Totales</th>
												<th scope="col" class="text-center">Disponibilidad</th>
												<th scope="col" class="text-center">Distrito</th>
												<th scope="col" class="text-center">Tipo de Servicio</th>
												<th scope="col" class="text-center">Código</th>
												<th scope="col" class="text-center">Nombre Cliente</th>
												<th scope="col" class="text-center">Teléfono</th>
												<th scope="col" class="text-center">Fecha de Registro</th>
												<th scope="col" class="text-center">Fecha de Cita</th>
												<th scope="col" class="text-center">Status</th>-->
											</tr>
										</thead>
										<tbody id="resumenSemanaTableBody"></tbody>
									</table>
								</div>
							</div> 								
						</div>
					</div>										
				</div>
			</div>
		</div>
	</div>	
</section>

<div class="modal fade" id="editModal_pre" tabindex="-1" aria-labelledby="editModalLabelPre" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabelPre">Editar Servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
			<form id="editForm">
				<div class="modal-body" style="max-height: 80vh; overflow-y: auto;">
					
						<div class="row mb-3">
							<label for="technical" class="col-sm-2 col-form-label">Técnico</label>
							<div class="col-5">
								<select class="form-select" id="technical" name="technical">
									<?php 
										$tecnicos_unicos = [];
										foreach ($info as $fila) {
											if (!in_array($fila->technical, $tecnicos_unicos) && $fila->technical !== NULL) {
												$tecnicos_unicos[] = $fila->technical;
												echo '<option value="' . $fila->technical . '">' . $fila->technical . '</option>';
											}
										}
									 ?>
								</select>
								<input type="text" class="form-control" id="technicalInput" name="technicalInput" style="display:none">
							</div>
							
							<div class="col-2 d-flex align-items-center">
							   <input type="checkbox" id="createTechnicalCheckbox" name="create_technical" class="file-checkbox">
							   <label for="createTechnicalCheckbox" class="ms-1">Add</label>
							</div>
													
						</div>
						<div class="row mb-3">
							<label for="register_date" class="col-sm-2 col-form-label">Fecha de Registro</label>
							<div class="col-sm-10">
								<input type="text" class="form-control bg-light" id="register_date" name="register_date" readonly>
							</div>
						</div>
						<div class="row mb-3">
							<label for="service_date" class="col-sm-2 col-form-label">Fecha de Cita</label>
							<div class="col-sm-5">
								<input type="date" class="form-control" id="service_date" name="service_date">
							</div>
						</div>
						<div class="row mb-3">
							<label for="day" class="col-sm-2 col-form-label">Día</label>
							<div class="col-sm-5">
								<input type="text" class="form-control bg-light" id="day" name="day" readonly>
							</div>
						</div>
						<div class="row mb-3">
							<label for="total_job" class="col-sm-2 col-form-label">Cupos Totales</label>
							<div class="col-sm-10">
								<input type="number" class="form-control bg-light" id="total_job" name="total_job" readonly>
							</div>
						</div>
						<div class="row mb-3">
							<label for="available_job" class="col-sm-2 col-form-label">Disponibilidad</label>
							<div class="col-sm-10">
								<input type="number" class="form-control bg-light" id="available_job" name="available_job" readonly>
							</div>
						</div>
						<div class="row mb-3">
							<label for="district" class="col-sm-2 col-form-label">Distrito</label>
							<div class="col-sm-5">
								<select id="district" class="form-select" name="district">
									<?php
									foreach ($state as $item_state) {
										?>
										<option><?= $item_state ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="row mb-3">
							<label for="service_type" class="col-sm-2 col-form-label">Tipo de Servicio</label>
							<div class="col-sm-5">
								<select id="service_type" class="form-select" name="service_type">
										<?php
										foreach ($service_type as $item_service_type) {
											?>
											<option><?= $item_service_type ?></option>
										<?php } ?>
								</select>
								
							</div>
						</div>
						<div class="row mb-3">
							<label for="service_code" class="col-sm-2 col-form-label">Código</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" id="service_code" name="service_code">
							</div>
						</div>
						<div class="row mb-3">
							<label for="client_name" class="col-sm-2 col-form-label">Nombre Cliente</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" id="client_name" name="client_name">
							</div>
						</div>
						<div class="row mb-3">
							<label for="mobile_number" class="col-sm-2 col-form-label">Teléfono</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" id="mobile_number" name="mobile_number">
							</div>
						</div>
						
						<div class="row mb-3">
							<label for="status" class="col-sm-2 col-form-label">Status</label>
							<div class="col-sm-5">
								<select class="form-select" id="status" name="status">
									<?php foreach ($status as $item_status) { ?>
											<option><?= $item_status ?></option>
									<?php }?>
								</select>
							</div>
						</div>
						<div class="row mb-3">
							<label for="service_comment" class="col-sm-2 col-form-label">Comentario</label>
							<div class="col-sm-5">
								<textarea class="form-control" id="service_comment" name="service_comment" style="height: 50px; width: 630px"></textarea>
							</div>
						 </div>
						
						
					<!--</form>-->
				</div>
				
				<div class="modal-footer">
						<input type="hidden" id="service_id" name="service_id">
						<button type="submit" class="btn btn-primary">Guardar Cambios</button>
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
				</div>
			
			</form>
        </div>
    </div>
</div>

<div class="modal fade" id="optionsModal" tabindex="-1" aria-labelledby="optionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="optionsModalLabel"><i class="bi bi-gear me-2"></i> Cambio Cupo Diario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="optionsForm">
                    <div class="mb-3">
                        <label for="appointmentDate" class="form-label"><i class="bi bi-calendar-event me-2"></i> Fecha de Cita:</label>
                        <input type="date" class="form-control" id="appointmentDate" name="appointmentDate" required>
                        <div class="form-text">Seleccione la fecha de cita a modificar.</div>
                    </div>
                    <div class="mb-3">
                        <label for="serviceTypeFilter" class="form-label"><i class="bi bi-list-ul me-2"></i> Tipo de Servicio:</label>
                        <select class="form-select" id="serviceTypeFilter" name="serviceTypeFilter" required>
                            <option value="">-- Seleccionar Tipo de Servicio --</option>
                        </select>
                        <div class="form-text">Seleccione el tipo de servicio.</div>
                    </div>
                    <div class="mb-3">
                        <label for="totalJobEdit" class="form-label"><i class="bi-pencil-square me-2"></i> Editar Cupo Total:</label>
                        <div class="input-group">
                            <span class="input-group-text" id="totalJobPrefix">Cupo Diario:</span>
                            <input type="number" class="form-control" id="totalJobEdit" name="totalJobEdit" aria-describedby="totalJobPrefix" min="0">
                        </div>
                        <div class="form-text">Ingrese el nuevo cupo diario para la fecha y servicio seleccionados.</div>
                    </div>
                    <div class="mb-3">
                        <label for="currentAvailableJob" class="form-label"><i class="bi bi-info-circle me-2"></i> Disponibilidad Actual:</label>
                        <input type="text" class="form-control" id="currentAvailableJob" value="N/A" readonly>
                        <div class="form-text">Este valor muestra la disponibilidad para la fecha seleccionada.</div>
                    </div>
                    <button type="button" class="btn btn-success" id="applyOptions"><i class="bi bi-check-circle me-2"></i> Aplicar</button>
                </form>
                <div id="optionsMessage" class="mt-3 alert alert-info d-none" role="alert">
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="uploadAvailable" tabindex="-1" aria-labelledby="optionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="optionsModalLabel">Select File Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="d-grid gap-3">
					<form id="form_svc_upload">
						<!--<button type="button" class="btn btn-primary btn-lg" id="uploadButton">-->
						<div class="input-group">
							<a class="btn btn-success" href="<?= base_url() ?>template/svc_control_template.xlsx" download="svc_control_template"><i class="bi bi-file-earmark-spreadsheet"></i> </a>
							
							<input class="form-control" type="file" name="attach">
							<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i></button>
							<!-- <i class="bi bi-upload me-2"></i> Upload Absenteeism -->
						</div>
						<!--</button>-->
					</form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Script configuracion de citas diarias
document.addEventListener('DOMContentLoaded', function() {
    const optionsModal = document.getElementById('optionsModal');
    const appointmentDateInput = document.getElementById('appointmentDate');
    const serviceTypeFilterSelect = document.getElementById('serviceTypeFilter');
    const totalJobEditInput = document.getElementById('totalJobEdit');
    const applyOptionsButton = document.getElementById('applyOptions');
    const optionsMessageDiv = document.getElementById('optionsMessage');
    const currentAvailableJobInput = document.getElementById('currentAvailableJob');

    function showMessage(type, message) {
        optionsMessageDiv.classList.remove('alert-info', 'alert-danger', 'alert-warning', 'd-none');
        optionsMessageDiv.classList.add(`alert-${type}`);
        optionsMessageDiv.textContent = message;
    }

    function obtenerYMostrarPendienteActual(date, type) {
		if (date && type) {
			fetch('svc_control_adm/obtener_pendientes', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded'
				},
				body: `service_date=${encodeURIComponent(date)}&service_type=${encodeURIComponent(type)}`
			})
			.then(response => response.json())
			.then(pendientes => {
				let latestPending = null;
				let latestAvailableJob = 'N/A';

				if (pendientes && pendientes.length > 0) {
					pendientes.forEach(p => {
						//if (!latestPending || new Date(p.register_date) > new Date(latestPending.register_date)) {
						if (!latestPending || p.available_job < latestPending.available_job) {
							latestPending = p;
						}
					});

					if (latestPending) {
						latestAvailableJob = latestPending.available_job;
					}
				} else {
					// No se encontraron pendientes, usar el valor del input Cupo Diario
					latestAvailableJob = totalJobEditInput.value || 'N/A';
				}
				currentAvailableJobInput.value = latestAvailableJob;
			})
			.catch(error => {
				console.error('Error al verificar registros pendientes:', error);
				showMessage('danger', 'Error al verificar registros pendientes.');
				currentAvailableJobInput.value = totalJobEditInput.value || 'N/A';
			});
		} else {
			currentAvailableJobInput.value = 'N/A';
		}
	}

    appointmentDateInput.addEventListener('change', function() {
        const selectedDate = this.value;
        serviceTypeFilterSelect.innerHTML = '<option value="">-- Seleccionar Tipo de Servicio --</option>';
        totalJobEditInput.value = '';
        currentAvailableJobInput.value = 'N/A';

        if (selectedDate) {
            fetch(`svc_control_adm/obtener_tipos_servicio_por_fecha?fecha=${selectedDate}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        data.forEach(type => {
                            const option = document.createElement('option');
                            option.value = type;
                            option.textContent = type;
                            serviceTypeFilterSelect.appendChild(option);
                        });
                    } else {
                        const option = document.createElement('option');
                        option.textContent = 'No hay servicios para esta fecha';
                        option.disabled = true;
                        serviceTypeFilterSelect.appendChild(option);
                    }
                })
                .catch(error => {
                    console.error('Error al cargar los tipos de servicio:', error);
                    showMessage('danger', 'Error al cargar los tipos de servicio.');
                });
        }
    });

    serviceTypeFilterSelect.addEventListener('change', function() {
        const selectedDate = appointmentDateInput.value;
        const selectedServiceType = this.value;

        totalJobEditInput.value = '';
        totalJobEditInput.dataset.currentTotalJob = 0;
        currentAvailableJobInput.value = 'N/A';

        if (selectedDate && selectedServiceType) {
            fetch(`svc_control_adm/obtener_cupo?fecha=${selectedDate}&tipo=${selectedServiceType}`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.total_job !== undefined) {
                        totalJobEditInput.value = data.total_job;
                        totalJobEditInput.dataset.currentTotalJob = data.total_job;
                    }
                    obtenerYMostrarPendienteActual(selectedDate, selectedServiceType);
                })
                .catch(error => {
                    console.error('Error al obtener el cupo:', error);
                    showMessage('danger', 'Error al obtener el cupo.');
                });
        }
    });

    applyOptionsButton.addEventListener('click', function() {
        const selectedDate = appointmentDateInput.value;
        const selectedServiceType = serviceTypeFilterSelect.value;
        const newTotalJob = parseInt(totalJobEditInput.value);
        const currentTotalJob = parseInt(totalJobEditInput.dataset.currentTotalJob || 0);
        const difference = newTotalJob - currentTotalJob;
		
        if (selectedDate && selectedServiceType && !isNaN(newTotalJob)) {
            fetch('svc_control_adm/obtener_pendientes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `service_date=${encodeURIComponent(selectedDate)}&service_type=${encodeURIComponent(selectedServiceType)}`
            })
            .then(response => response.json())
            .then(pendientes => {
                let latestPending = null;
                let disponibilidadCeroReciente = false;

                if (pendientes && pendientes.length > 0) {
                    pendientes.forEach(p => {
                        //if (!latestPending || new Date(p.register_date) > new Date(latestPending.register_date)) {
						if (!latestPending || p.available_job < latestPending.available_job) {
                            latestPending = p;
                        }
                    });

                    if (latestPending && parseInt(latestPending.available_job) == 0 && newTotalJob < currentTotalJob) {
                        disponibilidadCeroReciente = true;
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: 'No se puede disminuir el cupo. <br>La disponibilidad actual de un servicio pendiente es 0.',
                            confirmButtonText: 'Aceptar',
							timer: 5000
                        }).then(() => {
								window.location.reload(); // Refrescar la página después del SweetAlert
							});
                        return;
                    }
                }

                if (!disponibilidadCeroReciente) {
                    Swal.fire({
                        title: 'Advertencia',
                        html: `Se encontro ${pendientes.length} citas registradas en el dia ${selectedDate}.<br> ¿Desea actualizar el cupo y la disponibilidad de estos registros?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, actualizar pendientes',
                        cancelButtonText: 'Cancelar',
                        reverseButtons: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('svc_control_adm/actualizar_pendientes_cupo', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    serviceDate: selectedDate,
                                    serviceType: selectedServiceType,
                                    newTotalJob: newTotalJob,
                                    difference: difference
                                })
                            })
                            .then(response => response.json())
                            .then(updateData => {
                                if (updateData.success) {
									Swal.fire({
										icon: 'success',
										title: 'Información',
										text: 'Cupo y disponibilidad de servicios pendientes actualizados.',
										timer: 5000,
										showConfirmButton: true
									}).then(() => {
										window.location.reload(); // Refrescar la página después del SweetAlert
									});
									//showMessage('info', 'Cupo y disponibilidad de servicios pendientes actualizados.');
                                } else {
									Swal.fire({
										icon: 'error',
										title: 'Error',
										text: 'Error al actualizar el cupo de los servicios pendientes.',
										confirmButtonText: 'Aceptar'
									});
                                   //showMessage('danger', 'Error al actualizar el cupo de los servicios pendientes.');
                                }
                            })
                            .catch(error => {
								console.error('Error al verificar registros pendientes:', error);
								Swal.fire({
									icon: 'error',
									title: 'Error',
									text: 'Error al verificar registros pendientes.',
									confirmButtonText: 'Aceptar'
								});
                                // console.error('Error al actualizar el cupo de los pendientes:', error);
                                // showMessage('danger', 'Error al actualizar el cupo de los servicios pendientes.');
                            });
							actualizarCupoBase(selectedDate, selectedServiceType, newTotalJob);
							//window.location.reload();
                        }
                       //actualizatCupoBase 
                    });
                }
            })
            .catch(error => {
				Swal.fire({
					icon: 'warning',
					title: 'Advertencia',
					text: 'Por favor, seleccione la fecha de cita, el tipo de servicio e ingrese un cupo válido.',
					confirmButtonText: 'Aceptar'
				});
                // console.error('Error al verificar registros pendientes:', error);
                // showMessage('danger', 'Error al verificar registros pendientes.');
                currentAvailableJobInput.value = 'N/A';
            });
        } else {
            showMessage('warning', 'Por favor, seleccione la fecha de cita, el tipo de servicio e ingrese un cupo válido.');
            currentAvailableJobInput.value = 'N/A';
        }
    });

    function actualizarCupoBase(date, type, total) {
        fetch('svc_control_adm/guardar_opciones', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                appointmentDate: date,
                serviceType: type,
                totalJob: total
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {				
               showMessage('info', 'Cupo base actualizado.');
            } else {				
               showMessage('danger', 'Error al guardar el cupo base.');
            }
        })
        .catch(error => {
            console.error('Error al guardar las opciones base:', error);
            showMessage('danger', 'Error al guardar el cupo base.');
        });
    }

    totalJobEditInput.addEventListener('focus', function() {
        this.dataset.currentTotalJob = this.value;
    });
});
</script>

<script>
// Edit form, checkbox funcionalidad
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('createTechnicalCheckbox');
        const select = document.getElementById('technical');
        const input = document.getElementById('technicalInput');

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                select.style.display = 'none';
                input.style.display = 'block';
                input.addEventListener('input', capitalizeInput);
            } else {
                select.style.display = 'block';
                input.style.display = 'none';
                input.removeEventListener('input', capitalizeInput);
            }
        });

        function capitalizeInput() {
          let inputValue = input.value.toLowerCase();
          if (inputValue.length > 0) {
              input.value = inputValue.charAt(0).toUpperCase() + inputValue.slice(1);
          }
        }
    });
</script>

<script>
//Ordenamiento de cabeceras primera tabla
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('serviceTable');
        const headers = table.querySelectorAll('th[data-sortable="true"]');
        let currentSortColumn = null;
        let currentSortDirection = 'asc';

        headers.forEach((header, index) => {
            header.addEventListener('click', function() {
                if (currentSortColumn === index) {
                    currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSortColumn = index;
                    currentSortDirection = 'asc';
                }

                sortTable(table, index, currentSortDirection);
            });
        });

        function sortTable(table, columnIndex, direction) {
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((a, b) => {
                const aValue = a.querySelectorAll('td')[columnIndex].textContent.trim();
                const bValue = b.querySelectorAll('td')[columnIndex].textContent.trim();

                if (direction === 'asc') {
                    return aValue.localeCompare(bValue);
                } else {
                    return bValue.localeCompare(aValue);
                }
            });

            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        }
    });
</script>

<script>
// Actualizacion de input dia en ventana edit
    document.addEventListener('DOMContentLoaded', function() {
        const serviceDateInput = document.getElementById('service_date');
        const dayInput = document.getElementById('day');

        function updateDayOfWeek() {
            const selectedDate = serviceDateInput.value;
            if (selectedDate) {
                const date = new Date(selectedDate);
                const daysOfWeek = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                const dayOfWeek = daysOfWeek[date.getDay()];
                dayInput.value = dayOfWeek;
            } else {
                dayInput.value = ''; // Limpiar el campo si no hay fecha seleccionada
            }
        }

        serviceDateInput.addEventListener('change', updateDayOfWeek);

        // Opcional: Actualizar el día al cargar el modal si hay una fecha inicial
        if (serviceDateInput.value) {
            updateDayOfWeek();
        }
    });
</script>

<script>
// Generacion tabla dinamica y abrir en nueva ventana
document.addEventListener('DOMContentLoaded', function() {
	citaFilter
	const dateFilter = document.getElementById('citaFilter');
    //const dateFilter = document.getElementById('citaFilterDinamic');
    const groupFilter = document.getElementById('groupFilterDinamic');
    const uniqueFilter = document.getElementById('uniqueFilterDinamic');
    const resumenSemanaLink = document.getElementById('resumenSemana');
    const resumenSemanaContainer = document.getElementById('resumenSemanaContainer');
    const resumenSemanaTable = document.getElementById('resumenSemanaTable');
    const resumenSemanaTableBody = resumenSemanaTable.getElementsByTagName('tbody')[0];
    const abrirResumenVentanaBtn = document.getElementById('abrirResumenVentana');


    function updateResumenLinkText() {
        const selectedDate = dateFilter.value;
        const selectedGroup = groupFilter.value;

        let linkText = 'Resumen Semana';
        if (selectedDate && selectedGroup ) {
            linkText += ` (${selectedDate}) - ${selectedGroup}`;
        } else if (selectedDate) {
            linkText += ` (${selectedDate})`;
        } else if (selectedGroup) {
            linkText += ` - ${selectedGroup}`;
        }
        //resumenSemanaLink.textContent = linkText;
    }

    dateFilter.addEventListener('change', updateResumenLinkText);
    groupFilter.addEventListener('change', updateResumenLinkText);

    function getWeekRange(dateString) {
        const [year, month, day] = dateString.split('-').map(Number);
        const date = new Date(year, month - 1, day);
        const dayOfWeek = date.getDay();
        const startOfWeek = new Date(date);
        if (dayOfWeek === 0) {
            startOfWeek.setDate(date.getDate() - 6);
        } else {
            startOfWeek.setDate(date.getDate() - (dayOfWeek - 1));
        }
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 5);
        const startDateString = startOfWeek.toISOString().split('T')[0];
        const endDateString = endOfWeek.toISOString().split('T')[0];
        return { startDate: startDateString, endDate: endDateString };
    }

    function getDaysOfWeek(startDate, endDate) {
        const startDateObj = new Date(startDate);
        const endDateObj = new Date(endDate);
        const days = [];
        let currentDate = startDateObj;
        while (currentDate <= endDateObj) {
            days.push(currentDate.toISOString().split('T')[0]);
            currentDate.setDate(currentDate.getDate() + 1);
        }
        return days;
    }

    // Mostrar uniqueFilterContainer al cambiar groupFilterDinamic
    groupFilter.addEventListener('change', function() {
        const selectedGroup = groupFilter.value;
        if (selectedGroup) {
            document.getElementById('uniqueFilterContainer').style.display = 'block';
            // Cargar opciones del select uniqueFilterDinamic
            fetch('svc_control_adm/obtener_valores_unicos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `groupType=${encodeURIComponent(selectedGroup)}`,
            })
            .then(response => response.json())
            .then(data => {
                uniqueFilter.innerHTML = '<option value="">Choose...</option>';
                data.forEach(value => {
                    const option = document.createElement('option');
                    option.value = value;
                    option.textContent = value;
                    uniqueFilter.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
        } else {
            document.getElementById('uniqueFilterContainer').style.display = 'none';
        }
    });

    let tablaResumenHTML = ''; // Variable para almacenar el HTML de la tabla

    uniqueFilter.addEventListener('change', function(event) {
        event.preventDefault();

        const selectedDate = dateFilter.value;
        const selectedGroup = groupFilter.value;
        const selectedUnique = uniqueFilter.value;

        if (selectedDate && selectedGroup && selectedUnique) {
            const weekRange = getWeekRange(selectedDate);
            const daysOfWeek = getDaysOfWeek(weekRange.startDate, weekRange.endDate);

            fetch('svc_control_adm/resumen_semana', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `service_date=${encodeURIComponent(selectedDate)}&group_by=${encodeURIComponent(selectedGroup.toLowerCase())}&selected_unique=${encodeURIComponent(selectedUnique)}`,
            })
            .then(response => response.json())
            .then(data => {
                resumenSemanaTableBody.innerHTML = '';
                const filteredData = data.filter(item =>
                    item.service_date >= weekRange.startDate &&
                    item.service_date <= weekRange.endDate
                );

                if (filteredData.length > 0) {
                    const groupedData = {};
                    filteredData.forEach(item => {
                        const groupKey = item[selectedGroup.toLowerCase()];
                        if (!groupedData[groupKey]) {
                            groupedData[groupKey] = {};
                        }
                        if (!groupedData[groupKey][item.service_date]) {
                            groupedData[groupKey][item.service_date] = [];
                        }
                        groupedData[groupKey][item.service_date].push(item);
                    });

                    // Update table headers and store HTML
                    let headerRow = resumenSemanaTable.querySelector('thead tr');
                    headerRow.innerHTML = '';
					//let headers = ['Día', 'Cupos Totales', 'Disponibilidad', 'Tipo de Servicio', 'Tecnico', 'Distrito', 'Código', 'Nombre Cliente', 'Teléfono', 'Fecha de Registro', 'Fecha de Cita', 'Status']; //Con register date
                    let headers = ['Día', 'Fecha de Cita', 'Cupos Totales', 'Disponibilidad', 'Tipo de Servicio', 'Tecnico', 'Distrito', 'Código', 'Nombre Cliente', 'Teléfono', 'Status'];

                    if (selectedGroup.toLowerCase() === 'servicio') {
                        headers.unshift('Tipo de Servicio');
                        headers.splice(headers.indexOf('Tipo de Servicio', 1), 1);
                    } else if (selectedGroup.toLowerCase() === 'distrito') {
                        headers.unshift('Distrito');
                        headers.splice(headers.indexOf('Distrito', 1), 1);
						headers = headers.filter(header => header !== 'Cupos Totales' && header !== 'Disponibilidad');
                    } else if (selectedGroup.toLowerCase() === 'tecnico') {
                        headers.unshift('Tecnico');
                        headers.splice(headers.indexOf('Tecnico', 1), 1);
						// Eliminar "Cupos Totales" y "Disponibilidad" usando filter()
						headers = headers.filter(header => header !== 'Cupos Totales' && header !== 'Disponibilidad');
                    }

                    let theadHTML = '<tr>';
                    headers.forEach(header => {
                        theadHTML += `<th scope="col" class="text-center">${header}</th>`;
                    });
                    theadHTML += '</tr>';
                    headerRow.innerHTML = theadHTML;

                    let tbodyHTML = '';
                    Object.keys(groupedData).forEach(groupKey => {
                        daysOfWeek.forEach(day => {
                            if (groupedData[groupKey][day]) {
                                const dayItems = groupedData[groupKey][day];
                                let latestItem = dayItems[0];
                                dayItems.forEach(item => {
                                    if (item.available_job < latestItem.available_job) {
                                        latestItem = item;
                                    }
                                });
								
								if (selectedGroup.toLowerCase() === 'tecnico' || selectedGroup.toLowerCase() === 'distrito'){
									tbodyHTML += `<tr class="table-success">
										<td class="text-center">${selectedUnique}</td>
										<td class="text-center">${latestItem.day}</td>
										<td class="text-center">${latestItem.service_date}</td>
										<td colspan="6"></td>
									</tr>`;
								}
								else{

									tbodyHTML += `<tr class="table-success">
										<td class="text-center">${selectedUnique}</td>
										<td class="text-center">${latestItem.day}</td>
										<td class="text-center">${latestItem.service_date}</td>
										<td class="text-center">${latestItem.total_job}</td>
										<td class="text-center">${latestItem.available_job}</td>
										<td colspan="7"></td>
									</tr>`;
								}

                                dayItems.forEach(item => {
                                    let statusBadge = '';
                                    if (item.status === 'Registered') statusBadge = '<span class="badge border border-secondary border-1 text-secondary"><i class="bi bi-info-circle me-1"></i>Registrado</span>';
                                    else if (item.status === 'Postponed') statusBadge = '<span class="badge border border-danger border-1 text-danger"><i class="bi bi-exclamation-octagon me-1"></i>Postpuesto</span>';
                                    else if (item.status === 'Finished') statusBadge = '<span class="badge border border-success border-1 text-success"><i class="bi bi-check-circle me-1"></i>Finalizado</span>';
                                    else if (item.status === 'Assigned') statusBadge = '<span class="badge border border-primary border-1 text-primary"><i class="bi bi-check-all m-1"></i>Asignado</span>';

                                    let detailRowContent = '';
                                    if (selectedGroup.toLowerCase() === 'servicio') {
                                        detailRowContent = `<td colspan="5"></td>
                                            <td class="text-center">${item.technical}</td>
                                            <td class="text-center">${item.district}</td>
                                            <td class="text-center">${item.service_code}</td>
                                            <td class="text-center">${item.client_name}</td>
                                            <td class="text-center">${item.mobile_number}</td>
                                            <td class="text-center" style="display:none;">${item.register_date}</td>
                                            <!--<td class="text-center">${item.service_date}</td>-->
                                            <td class="text-center">${statusBadge}</td>`;
                                    } else if (selectedGroup.toLowerCase() === 'distrito') {
                                        detailRowContent = `<td colspan="3"></td>
                                            <td class="text-center">${item.service_type}</td>
                                            <td class="text-center">${item.technical}</td>
                                            <td class="text-center">${item.service_code}</td>
                                            <td class="text-center">${item.client_name}</td>
                                            <td class="text-center">${item.mobile_number}</td>
                                            <td class="text-center" style="display:none;">${item.register_date}</td>
                                            <!--<td class="text-center">${item.service_date}</td>-->
                                            <td class="text-center">${statusBadge}</td>`;
                                    } else if (selectedGroup.toLowerCase() === 'tecnico') {
                                        detailRowContent = `<td colspan="3"></td>
                                            <td class="text-center">${item.service_type}</td>
                                            <td class="text-center">${item.district}</td>
                                            <td class="text-center">${item.service_code}</td>
                                            <td class="text-center">${item.client_name}</td>
                                            <td class="text-center">${item.mobile_number}</td>
                                            <td class="text-center" style="display:none;">${item.register_date}</td>
                                            <!--<td class="text-center">${item.service_date}</td>-->
                                            <td class="text-center">${statusBadge}</td>`;
                                    }
                                    tbodyHTML += `<tr>${detailRowContent}</tr>`;
                                });
                            }
                        });
                    });
                    resumenSemanaTableBody.innerHTML = tbodyHTML;
                    tablaResumenHTML = `<table class="table table-bordered table-striped"><thead>${theadHTML}</thead><tbody>${tbodyHTML}</tbody></table>`;
                    //abrirResumenVentanaBtn.style.display = 'inline-block';
                } else {
                    resumenSemanaTableBody.innerHTML = '<tr><td colspan="12">No hay datos para la semana y selección.</td></tr>';
                   // abrirResumenVentanaBtn.style.display = 'none';
                }

                resumenSemanaContainer.style.display = 'block';
            })
            .catch(error => console.error('Error:', error));
        } else {
            alert('Por favor, selecciona una fecha y una opción de agrupación.');
            //abrirResumenVentanaBtn.style.display = 'none';
        }
    });

    abrirResumenVentanaBtn.addEventListener('click', function() {
        if (tablaResumenHTML) {
            const nuevaVentana = window.open('', '_blank');
            nuevaVentana.document.write('<html><head><title>Resumen Semana</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head><body>');
            nuevaVentana.document.write('<div class="container mt-3">' + tablaResumenHTML + '</div>');
            nuevaVentana.document.write('</body></html>');
            nuevaVentana.document.close();
        } else {
            alert('Por favor, genera el resumen de semana primero.');
        }
    });
});
</script>

<script>
// Filtro primera tabla (service_date, tecnicos, status y distritos)
    document.addEventListener('DOMContentLoaded', function() {
        const dateFilter = document.getElementById('citaFilter');
        const technicianFilter = document.getElementById('technicianFilter');
		const statusFilter = document.getElementById('statusFilter');
		const serviceFilter = document.getElementById('serviceFilter');
		const districtFilter  = document.getElementById('districtFilter');
        const availabilityInput = document.getElementById('availabilityValue');
        const table = document.getElementById('serviceTable');
        const tbody = table.getElementsByTagName('tbody')[0];
        const rows = tbody.getElementsByTagName('tr');
		
		
        function filterTable() {
            const selectedDate = dateFilter.value;
            const selectedTechnician = technicianFilter.value;
			const selectedStatus = statusFilter.value;
			const serviceStatus = serviceFilter.value;
			const districtStatus = districtFilter.value;
			
            for (let i = 0; i < rows.length; i++) {
                const serviceDateCell = rows[i].getElementsByTagName('td')[0];
                const technicianCell = rows[i].getElementsByTagName('td')[1];
				const serviceCell = rows[i].getElementsByTagName('td')[6];
				let statusCell = rows[i].getElementsByTagName('td')[11];
				const districtCell = rows[i].getElementsByTagName('td')[5];
				
                if (serviceDateCell && technicianCell && statusCell && serviceCell && districtCell) {
                    const serviceDate = serviceDateCell.textContent.trim();
                    const technician = technicianCell.textContent.trim();
					const service_type = serviceCell.textContent.trim();
					const district = districtCell.textContent.trim();
					let status = statusCell.textContent.trim();
					if(status == 'Asignado'){
						status = 'Assigned';
					}
					else if(status == 'Postpuesto'){
						status = 'Postponed';
					}
					else if(status == 'Registrado'){
						status = 'Registered';
					}
					else if(status == 'Finalizado'){
						status = 'Finished';
					}

                    const dateMatch = selectedDate === '' || serviceDate === selectedDate;
                    const technicianMatch = selectedTechnician === '' || technician === selectedTechnician;
					const statusMatch = selectedStatus === '' || status === selectedStatus;
					const serviceMatch = serviceStatus === '' || service_type === serviceStatus;
					const districtMatch = districtStatus === '' || district === districtStatus;
					
                    if (dateMatch && technicianMatch && statusMatch && serviceMatch && districtMatch) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
			
            if (selectedDate) {
                fetch('svc_control_adm/obtener_disponibilidad', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `service_date=${encodeURIComponent(selectedDate)}`,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.available_jobs !== null) {
                        availabilityInput.value = 'Disponibilidad: ' + data.available_jobs; // Actualizar el valor del input
                    } else {
                        availabilityInput.value = 'Disponibilidad: Not Found'; // Actualizar el valor del input
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    availabilityInput.textContent = 'Error';
                });
            } else {
                availabilityInput.textContent = 'Not Found';
            }
        }

        dateFilter.addEventListener('change', filterTable);
        technicianFilter.addEventListener('change', filterTable);
		statusFilter.addEventListener('change', filterTable);
		serviceFilter.addEventListener('change', filterTable);
		districtFilter.addEventListener('change', filterTable);
    });
</script>

<script>
// script delete registers
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-button');
			
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const idToDelete = this.dataset.id;
            const oldServiceDate = this.dataset.oldServiceDate; // Puedes usarlo para lógica adicional si es necesario
			const oldServiceType = this.dataset.oldServiceType;
			// const oldServiceDate = clickedEditButton ? clickedEditButton.dataset.oldServiceDate : '';
			// const oldServiceType = clickedEditButton ? clickedEditButton.dataset.oldServiceType : '';
			
			// console.log('oldServiceDate: ', oldServiceDate);
			// console.log('oldServiceType: ', oldServiceType); return;
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción eliminará el registro permanentemente.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('svc_control_adm/delete_register', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id=${encodeURIComponent(idToDelete)}&old_service_date=${encodeURIComponent(oldServiceDate)}&oldServiceType=${encodeURIComponent(oldServiceType)}` // Envía también oldServiceDate si lo necesitas en el controlador
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire(
                                '¡Eliminado!',
                                data.message,
                                'success'
                            ).then(() => {
                                // Opcional: Recargar la tabla o eliminar la fila del DOM
                                window.location.reload(); // Recargar la página
                                // Si prefieres eliminar la fila sin recargar:
                                // const rowToDelete = this.closest('tr');
                                // rowToDelete.remove();
                            });
                        } else {
                            Swal.fire(
                                '¡Error!',
                                data.message,
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error al eliminar el registro:', error);
                        Swal.fire(
                            '¡Error!',
                            'Hubo un problema al intentar eliminar el registro.',
                            'error'
                        );
                    });
                }
            });
        });
    });
});
</script>

<script>
let clickedEditButton = null; // Variable para almacenar el botón clickeado
//Rellenar inputs de ventana edit y luego guardar los cambios
    document.addEventListener('DOMContentLoaded', function() {
        const editButtons = document.querySelectorAll('.edit-button');
        const editModal = new bootstrap.Modal(document.getElementById('editModal_pre'));
        const editForm = document.getElementById('editForm');
        const technicalSelect = document.getElementById('technical');
        const technicalInput = document.getElementById('technicalInput');
        const createTechnicalCheckbox = document.getElementById('createTechnicalCheckbox');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const cells = row.querySelectorAll('td');
				clickedEditButton = this; // Guarda el botón clickeado
                const oldServiceDate = cells[0].textContent.trim();
				const oldServiceType = cells[6].textContent.trim();
				
				//console.log(oldServiceDate);
                this.dataset.oldServiceDate = oldServiceDate; // Guardar la fecha antigua en el botón
				this.dataset.oldServiceType = oldServiceType; // Guardar el tipo de servicio antiguo en el botón
				//console.log('data-old-service-date establecido:', this.dataset.oldServiceDate);
                document.getElementById('service_date').value = oldServiceDate;
                document.getElementById('day').value = cells[2].textContent.trim();
                document.getElementById('total_job').value = cells[3].textContent.trim();
                document.getElementById('available_job').value = cells[4].textContent.trim();
                document.getElementById('district').value = cells[5].textContent.trim();
                document.getElementById('service_type').value = oldServiceType;
                document.getElementById('service_code').value = cells[7].textContent.trim();
                document.getElementById('client_name').value = cells[8].textContent.trim();
                document.getElementById('mobile_number').value = cells[9].textContent.trim();
                document.getElementById('register_date').value = cells[10].textContent.trim();
				document.getElementById('service_comment').value = cells[12].textContent.trim();

                // Manejar el técnico según el checkbox
                if (cells[1].dataset.isNew === 'true') { // Suponiendo que tienes un data-is-new en la celda
                    createTechnicalCheckbox.checked = true;
                    technicalSelect.style.display = 'none';
                    technicalInput.style.display = 'block';
                    technicalInput.value = cells[1].textContent.trim(); // Obtener el valor del input
                } else {
                    createTechnicalCheckbox.checked = false;
                    technicalSelect.style.display = 'block';
                    technicalInput.style.display = 'none';
                    technicalSelect.value = cells[1].textContent.trim(); // Obtener el valor del select
                }

                // Para los selects, asigna el valor seleccionado
                document.getElementById('technical').value = cells[1].textContent.trim(); // Técnico
                if(cells[11].textContent.trim()=='Asignado'){
                    document.getElementById('status').value = 'Assigned';
                }
                else if(cells[11].textContent.trim()=='Postpuesto'){
                    document.getElementById('status').value = 'Postponed';
                }
                else if(cells[11].textContent.trim()=='Registrado'){
                    document.getElementById('status').value = 'Registered';
                }
                else if(cells[11].textContent.trim()=='Finalizado'){
                    document.getElementById('status').value = 'Finished';
                }
                //document.getElementById('status').value = cells[11].textContent.trim(); // Status

                document.getElementById('service_id').value = this.dataset.id;
                console.log('service_id:', this.dataset.id);

                editModal.show();
            });
        });

        editForm.addEventListener('submit', function(event) {
            event.preventDefault();

            // Obtener la old_service_date del atributo data del botón
			//console.log('service_id en submit:', document.getElementById('service_id').value);
			//const editButton = document.querySelector('.edit-button');
			//console.log('editButton con selector simple:', editButton);
            // const editButton = document.querySelector('.edit-button[data-bs-target="#editModal_pre"][data-id="' + document.getElementById('service_id').value + '"]');
			// console.log('editButton encontrado en submit:', editButton);
			const oldServiceDate = clickedEditButton ? clickedEditButton.dataset.oldServiceDate : '';
           // const oldServiceDate = editButton ? editButton.dataset.oldServiceDate : '';
			const oldServiceType = clickedEditButton ? clickedEditButton.dataset.oldServiceType : '';
			const id = clickedEditButton ? clickedEditButton.dataset.id : '';
			
            const formData = new FormData(editForm);
            formData.append('old_service_date', oldServiceDate); // Agregar la old_service_date al FormData
			formData.append('old_service_type', oldServiceType); // Agregar la old_service_type al FormData
			formData.append('id', id)
			
            fetch('svc_control_adm/update_register', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                editModal.hide();
                if (data && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Cambios Guardados',
                        text: 'Los cambios se han guardado correctamente.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.reload(); // Refrescar la página después del SweetAlert
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al Guardar',
                        text: data && data.message ? data.message : 'Hubo un problema al guardar los cambios.',
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Inesperado',
                    text: 'Ocurrió un error durante la comunicación con el servidor.',
                    confirmButtonText: 'Aceptar'
                });
            });
        });
    });
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_svc_upload").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "module/svc_control_adm/upload", "Do you want to upload stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/svc_control_adm");
		});
	});
});
</script>