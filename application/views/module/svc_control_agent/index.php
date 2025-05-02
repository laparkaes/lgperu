<head>
	<style>
		#serviceTable thead tr {
		  position: sticky;
		  top: 0;
		  background-color: #d1e7dd; /* El mismo color de fondo que la clase table-success */
		  z-index: 100; /* Asegura que la cabecera esté por encima del contenido */
		}

		/* Opcional: Para mejorar la visualización, puedes agregar un borde inferior */
		#serviceTable thead th {
		  border-bottom: 2px solid black;
		}
	</style>
</head>
<div class="pagetitle">
    <h1>Service Control Agent </h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
            <li class="breadcrumb-item active">Service Control Agent</li>
        </ol>
    </nav>
</div>
<section class="section">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title">Registro de Citas</h5>
                        <button id="toggleFormButton" class="btn btn-primary" style="display:None;">
                            Form
                        </button>
                    </div>
                    <div id="formContainer">
					
                        <form class="row g-3" id="form_data_client" method="post">
							<div class="col-md-12">
								<div class="row">
									<div class="col-2">
										<label for="inputCita" class="form-label">Fecha de Cita</label>
										<!--<input type="date" class="form-control" id="inputCita" name="inputCita">-->
										<input type="date" class="form-control" id="inputCita" name="inputCita" min="<?php echo date('Y-m-d'); ?>">
									</div>
									<div class="col-md-2">
										<label for="inputService" class="form-label">Tipo de Servicio</label>
										<select id="inputService" class="form-select" name="inputService">
											<option selected="">Choose...</option>
											<?php
											foreach ($service_type as $item_service_type) {
												?>
												<option><?= $item_service_type ?></option>
											<?php } ?>
										</select>
									</div>
									
									<div class="col-3">
										<label for="inputTotalJobs" class="form-label">Cupo Diario</label>
										<input type="text" class="form-control bg-light" id="inputTotalJobs" name="inputTotalJobs" readonly>
									</div>
									<div class="col-3">
										<label for="inputAvailable" class="form-label">Disponibilidad</label>
										<input type="text" class="form-control bg-light" id="inputAvailable" name="inputAvailable" readonly>
									</div>
									<!--<div class="col-md-4">
										<label for="TechnicianInput" class="form-label">Técnico</label>
										<select id="TechnicianInput" class="form-select" name="TechnicianInput">
											<option value="">Seleccionar Técnico</option>
											<?php
											$tecnicos_unicos = [];
											foreach ($technical as $tecnico) {
												echo '<option>' . $tecnico . '</option>';
											}
											?>
										</select>
									</div>-->							                      
									<div class="col-md-2">
										<label for="inputDay" class="form-label">Día</label>
										<input type="text" class="form-control bg-light" id="inputDay" name="inputDay" readonly>
									</div>
								</div>	
							</div>
							<div class="col-md-12">
								<div class="row">
									
									
									
									<div class="col-md-2">
										<label for="inputServiceCode" class="form-label">Código de Servicio</label>
										<input type="text" class="form-control" id="inputServiceCode" name="inputServiceCode">
									</div>
									<div class="col-md-2">
										<label for="inputMobile" class="form-label">Teléfono</label>
										<input type="text" class="form-control" id="inputMobile" name="inputMobile" placeholder="Mobile Number">
									</div>
									<div class="col-md-3">
											<label for="inputClientName" class="form-label">Nombre Cliente</label>
											<input type="text" class="form-control" id="inputClientName" name="inputClientName">
									</div>
									
									<div class="col-md-3">
										<label for="inputCity" class="form-label">Ciudad</label>
										<input type="text" class="form-control" id="inputCity" name="inputCity" placeholder="Lima">
									</div>
									
									<div class="col-md-2">
										<label for="inputDistrict" class="form-label">Distrito</label>
										<select id="inputDistrict" class="form-select" name="inputDistrict">
											<option selected="">Choose...</option>
											<?php
											foreach ($state as $item_state) {
												?>
												<option><?= $item_state ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>
							
							<div class="col-md-12 ">
								<label for="inputComment" class="form-label">Comentarios</label>
								<textarea class="form-control" id="inputComment" name="inputComment" style="height: 80px" placeholder="(Opcional)"></textarea>
								
							</div>
							<br>
							<div class="text-center">
								<button type="submit" id="submitButton" class="btn btn-primary">Submit</button>
								<button type="reset" class="btn btn-secondary">Reset</button>
							</div>
						</form>
					</div><br>
						
					<div style="overflow-y: auto; max-height: 550px;">
						<table class="table" id="serviceTable">
							<thead>
								<tr class="table-primary" style="border: 2px solid black;">
									<th scope="col" class="text-center">Día</th>
									<th scope="col" class="text-center">Tipo de Servicio</th>
									<th scope="col" class="text-center">Cupo Diario</th>
									<th scope="col" class="text-center">Disponibilidad</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
					
				</div>			
			</div>
		</div>			     
	</div>
	
</section>

<script>
// Script generar tabla resumen y llenado de inputs del form
document.addEventListener('DOMContentLoaded', function() {
    const inputCita = document.getElementById('inputCita');
    const inputDay = document.getElementById('inputDay');
    const inputService = document.getElementById('inputService');

    function generarTabla(selectedDate) {
        const dayOfWeek = selectedDate.getDay();
        let monday = new Date(selectedDate);
        const daysToMonday = (dayOfWeek === 0 ? 6 : dayOfWeek - 1);
        monday.setDate(selectedDate.getDate() - daysToMonday);

        const tableBody = document.querySelector('#serviceTable tbody');
        tableBody.innerHTML = '';

        const serviceTypes = <?php echo json_encode($service_type); ?>;

        for (let i = 0; i < 6; i++) {
            const currentDate = new Date(monday);
            const formattedDate = currentDate.toISOString().split('T')[0];
            const dayName = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'][i];
            const displayDate = currentDate.toLocaleDateString('es-ES');

            // Fila para el día
            const dayRow = document.createElement('tr');
            dayRow.innerHTML = `
                <td class="text-center">${dayName} (${displayDate})</td>
                <td class="text-center"></td>
                <td class="text-center"></td>
                <td class="text-center"></td>
            `;
            tableBody.appendChild(dayRow);

            // Resaltar y aplicar bordes externos a la fila del día seleccionado
            if (selectedDate && currentDate.toDateString() === selectedDate.toDateString()) {
                dayRow.classList.add('table-success');
                dayRow.querySelectorAll('td').forEach((cell, index, cells) => {
                    cell.style.fontWeight = 'bold';
                    if (index === 0) {
                        cell.style.borderLeft = '2px solid black';
                        cell.style.borderTop = '2px solid black';
                        cell.style.borderBottom = '2px solid black';
                    } else if (index === cells.length - 1) {
                        cell.style.borderRight = '2px solid black';
                        cell.style.borderTop = '2px solid black';
                        cell.style.borderBottom = '2px solid black';
                    } else {
                        cell.style.borderTop = '2px solid black';
                        cell.style.borderBottom = '2px solid black';
                    }
                });

                // Añadir bordes externos a las filas de los tipos de servicio para el día seleccionado
                serviceTypes.forEach(service => {
                    const serviceRow = document.createElement('tr');
                    serviceRow.innerHTML = `
                        <td class="text-center"></td>
                        <td class="text-center">${service}</td>
                        <td class="text-center">Cargando...</td>
                        <td class="text-center">Cargando...</td>
                    `;
                    tableBody.appendChild(serviceRow);

                    serviceRow.querySelectorAll('td').forEach((cell, index, cells) => {
                        if (index === 0) {
                            cell.style.borderLeft = '2px solid black';
                        } else if (index === cells.length - 1) {
                            cell.style.borderRight = '2px solid black';
                        }
                        cell.style.borderTop = '2px solid black';
                        cell.style.borderBottom = '2px solid black';
                    });

                    fetch('<?php echo base_url('module/svc_control_agent/check_availability'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `date=${encodeURIComponent(formattedDate)}&service=${encodeURIComponent(service)}`,
                    })
                    .then(response => response.json())
                    .then(data => {
                        const totalJobCell = serviceRow.cells[2];
                        const availableJobCell = serviceRow.cells[3];

                        if (data.available === true) {
                            totalJobCell.textContent = data.total_job;
                            availableJobCell.textContent = data.available_job;
                        } else {
                            totalJobCell.textContent = '0';
                            availableJobCell.textContent = '0';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        totalJobCell.textContent = 'Error';
                        availableJobCell.textContent = 'Error';
                    });
                });
            } else {
                // Filas para los tipos de servicio (sin resaltar ni bordes específicos)
                serviceTypes.forEach(service => {
                    const serviceRow = document.createElement('tr');
                    serviceRow.innerHTML = `
                        <td class="text-center"></td>
                        <td class="text-center">${service}</td>
                        <td class="text-center">Cargando...</td>
                        <td class="text-center">Cargando...</td>
                    `;
                    tableBody.appendChild(serviceRow);

                    fetch('<?php echo base_url('module/svc_control_agent/check_availability'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `date=${encodeURIComponent(formattedDate)}&service=${encodeURIComponent(service)}`,
                    })
                    .then(response => response.json())
                    .then(data => {
                        const totalJobCell = serviceRow.cells[2];
                        const availableJobCell = serviceRow.cells[3];

                        if (data.available === true) {
                            totalJobCell.textContent = data.total_job;
                            availableJobCell.textContent = data.available_job;
                        } else {
                            totalJobCell.textContent = '0';
                            availableJobCell.textContent = '0';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        totalJobCell.textContent = 'Error';
                        availableJobCell.textContent = 'Error';
                    });
                });
            }

            monday.setDate(monday.getDate() + 1);
        }
    }

    function updateAvailability() {
        const selectedDate = inputCita.value;
        const selectedService = inputService.value;

        if (selectedDate && selectedService && selectedService !== 'Choose...') {
            fetch('<?php echo base_url('module/svc_control_agent/check_availability'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `date=${encodeURIComponent(selectedDate)}&service=${encodeURIComponent(selectedService)}`,
            })
            .then(response => response.json())
            .then(data => {
                if (data.available === true) {
                    document.getElementById('inputTotalJobs').value = data.total_job;
                    document.getElementById('inputAvailable').value = data.available_job;
                } else {
                    document.getElementById('inputTotalJobs').value = 'Data Not Found';
                    document.getElementById('inputAvailable').value = 'Data Not Found';
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    }

    function actualizarDia(dateInputValue) {
        const selectedDate = new Date(dateInputValue + 'T00:00:00'); // Asegurar formato ISO para new Date
        const dayOfWeek = selectedDate.getDay();
        const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        inputDay.value = days[dayOfWeek];
    }

    // Generar la tabla con la fecha actual al cargar la página
    const fechaActual = new Date();
    const formattedFechaActual = fechaActual.toISOString().split('T')[0];
    inputCita.value = formattedFechaActual; // Establecer la fecha actual en el input

    // Actualizar el inputDay con la fecha actual al cargar la página
    actualizarDia(inputCita.value);
    updateAvailability(); // También actualizar la disponibilidad inicial

    // Generar la tabla con la fecha actual al cargar la página
    generarTabla(fechaActual);

    // Evento para regenerar la tabla al cambiar la fecha de cita
    inputCita.addEventListener('change', function() {
        const dateString = this.value;
        const selectedDate = new Date(dateString + 'T00:00:00');
        generarTabla(selectedDate);

        actualizarDia(this.value); // Pasar el valor directamente
        updateAvailability();
    });
    inputService.addEventListener('change', updateAvailability);
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_mdms_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "module/svc_control_agent/update", "Do you want to upload stock data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/svc_control_agent");
		});
	});
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const submitButton = document.getElementById('submitButton');
    const form = document.getElementById('form_data_client');
    const inputAvailable = document.getElementById('inputAvailable');

    submitButton.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent the default form submission

        const disponibilidad = parseInt(inputAvailable.value);

        if (isNaN(disponibilidad) || disponibilidad <= 0) {
            // Show a message indicating full availability
            Swal.fire({
                icon: 'warning',
                title: 'Availability Sold Out',
                text: 'There are no more slots available for this service.',
                confirmButtonText: 'Ok',
                confirmButtonColor: '#dc3545' // Red to indicate danger
            });
            return; // Stop form submission
        }

        var formData = new FormData(form);

        // Print FormData to the console (for debugging)
        for (var pair of formData.entries()) {
            console.log(pair[0] + ', ' + pair[1]);
        }

        fetch('<?php echo base_url('module/svc_control_agent/upload_svc_data'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Successful Registration',
                    text: 'The appointment has been registered successfully.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    form.reset();
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Registration Error',
                    text: 'There was a problem registering the appointment: ' + data.message,
                    confirmButtonText: 'Accept'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Unexpected Error',
                text: 'An error occurred during submission.',
                confirmButtonText: 'Accept'
            });
        });
    });
});
</script>

