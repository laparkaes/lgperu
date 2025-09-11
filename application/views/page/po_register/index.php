<header>
	<style>
		.clickable-icon {
		  cursor: pointer;
		  /* Otros estilos que hayas añadido, como font-size o hover */
		}
		#po-table thead th {
			position: sticky;
			top: 0;
			z-index: 10;
		}
	</style>
</header>

<div class="container-fluid mt-5">
	<div class="row">
		<div class="col-md-3" id="form-column">
			<div class="card p-4">
			<h5 class="card-title text-center">Submission Form</h5>
				<form class="row g-3" action="<?php echo site_url('page/po_register/register_data'); ?>" method="post" enctype="multipart/form-data">
					<div class="col-md-6">
						<label for="registrator" class="form-label">Registrator</label>
						<input type="text" class="form-control" id="registrator" name="registrator" placeholder="your name" required>
					</div>
					<div class="col-md-6">
						<label for="ep_mail" class="form-label">Email</label>
						<div class="input-group">
							<input type="text" class="form-control" id="ep_mail" name="ep_mail" placeholder="ep-mail" required>
							<span class="input-group-text text-muted">@lge.com</span>
						</div>
						<!--<div class="form-text">The @lge.com suffix is automatically added.</div>-->
					</div>
					<div class="col-md-6">
						<label for="customer_name" class="form-label d-flex align-items-center justify-content-between">
							<span>Customer</span>
							<div class="form-check">
								<input class="form-check-input" type="checkbox" id="add_customer_checkbox">
								<label class="form-check-label" for="add_customer_checkbox">
									Add
								</label>
							</div>
						</label>
						
						<div id="customer_select_container">
							<select class="form-select" id="customer_name" name="customer_name" required>
								<option value="">Choose customer...</option>
								<?php foreach ($customers as $customer) { ?>
									<option value="<?php echo htmlspecialchars($customer); ?>"><?php echo htmlspecialchars($customer); ?></option>
								<?php } ?>
							</select>
						</div>
						
						<div id="customer_input_container" style="display: none;">
							<input type="text" class="form-control" id="customer_name_input" name="customer_name" placeholder="Customer..." required disabled>
						</div>
					</div>
					<div class="col-md-6">
						<label for="po_number" class="form-label">PO Number</label>
						<input type="text" class="form-control" id="po_number" name="po_number" placeholder="po number" required>
					</div>					
					<div class="col-md-12">
						<label for="remark" class="form-label">Remark</label>
						<textarea class="form-control" id="remark" name="remark" placeholder="remark"></textarea>
					</div>
					<div class="mb-3">
						<label for="attachment" class="form-label">Attach Files</label>
						<input class="form-control" type="file" id="attachment" name="attachment" multiple>
					</div>
					<button type="submit" class="btn btn-primary w-100">Send</button>
				</form>
			</div>
		</div>
		<div class="col-md-9" id="table-column">
			<div class="card p-4 h-100">
				<h5 class="card-title text-center">Purchase Order History</h5>
					<div class="d-flex justify-content-between align-items-center">
						 <button class="btn btn-outline-secondary btn-sm me-2" id="toggle-form-btn">
							<i class="bi bi-eye-slash"></i>
							Hide Form
						</button>
						<div class="d-flex justify-content-end">
							<select class="form-select me-1" id="sl_period" style="width: 200px;">
								<option value="">Customer --</option>
								<?php foreach($customers as $customer){  ?>
								<option><?= $customer ?></option>
								<?php } ?>
							</select>
							<select class="form-select me-1" id="sl_dept" style="width: 150px;">
								<option value="">Status --</option>
								<?php foreach($status as $item){  ?>
								<option><?= $item ?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div style="max-height: 600px; overflow-y: auto;">
						<table id="po-table" class="table table-hover">
							<thead>
							  <tr>
								<th></th>
								<th>PO Number</th>
								<th class="text-center">Line</th>
								<th class="text-center">Customer</th>
								<th class="text-center">Status</th>
								<th class="text-center">Registrator</th>
								<th class="text-center">Created</th>
								<th class="text-center">GERP</th>
								<th class="text-center">Requested</th>
								<th class="text-center">Confirmed</th>
								<th class="text-center">Remark</th>								
							  </tr>
							</thead>
							<tbody>
								<?php if (!empty($history)): ?>
									<?php $po_line = []?>
									<?php foreach ($history as $record): ?>
										<?php $po_line[$record->po_number] = $record->line?>
									<?php endforeach; ?>
									<?php foreach ($history as $record): ?>
										<tr data-id="<?php echo $record->id; ?>">
											<td> 
												<i class="bi bi-x-square text-danger remove-line-btn clickable-icon fs-5" data-po-number="<?php echo $record->po_number; ?>" data-record-id="<?php echo $record->id; ?>"></i>
											</td>
											<td class="text-center po-number-cell">
												<div class="d-flex align-items-center justify-content-between w-100">
													<span><?php echo $record->po_number; ?></span>
													<?php if ($record->line === null || $record->line >= $po_line[$record->po_number]) { ?>
														<i class="bi bi-plus-square text-primary add-line-btn clickable-icon fs-5" data-po-number="<?php echo $record->po_number; ?>" data-record-id="<?php echo $record->id; ?>"></i>
													<?php } else { ?>
													<?php } ?>
												</div>
											</td>
											<td class="text-center line-cell"><?php echo $record->line; ?></td>
											<td class="customer-cell"><?php echo $record->customer_name; ?></td>
											<td class="text-center status-cell"><?php echo $record->status; ?></td>
											<td class="text-center"><?php echo $record->registrator; ?></td>
											<td class="text-center created-cell"><?php echo $record->created; ?></td>
											<td class="text-center">
												<?php if (!empty($record->gerp)) { ?>
													<?php echo $record->gerp; ?> 
													<i class="bi bi-calendar-x text-danger m-2 remove-gerp-date-btn clickable-icon fs-8" data-po-number="<?php echo $record->po_number; ?>" data-record-id="<?php echo $record->id; ?>"></i>
												<?php } else { ?>
													<input type="checkbox" name="gerp"/>
												<?php } ?>
											</td>
											<td class="text-center">
												<?php if (!empty($record->appointment_request)) { ?>
													<?php echo $record->appointment_request; ?>
													<i class="bi bi-calendar-x text-danger m-2 remove-requested-date-btn clickable-icon fs-8" data-po-number="<?php echo $record->po_number; ?>" data-record-id="<?php echo $record->id; ?>"></i>
												<?php } else { ?>
													<input type="checkbox" name="requested"/>
												<?php } ?>
											</td>
											<td class="text-center">
												<?php if (!empty($record->appointment_confirmed)) { ?>
													<?php echo $record->appointment_confirmed; ?>
													<i class="bi bi-calendar-x text-danger m-2 remove-confirmed-date-btn clickable-icon fs-8" data-po-number="<?php echo $record->po_number; ?>" data-record-id="<?php echo $record->id; ?>"></i>
												<?php } else { ?>
													<input type="checkbox" name="confirmed"/>
												<?php } ?>
											</td>
											<td class="text-center remark-cell" data-record-id="<?php echo $record->id; ?>">
												<?php if ($record->remark_appointment !== null) { ?>
													<div class="remark-display d-flex align-items-center justify-content-between">
														<span><?php echo htmlspecialchars($record->remark_appointment); ?></span>
														<div class="d-flex flex-column align-items-end">
															<button class="btn btn-sm btn-outline-primary edit-remark-btn ms-2" data-record-id="<?php echo $record->id; ?>">
																<i class="bi bi-pencil"></i>
															</button>
															<button class="btn btn-sm btn-outline-danger delete-remark-btn ms-2 mt-1" data-record-id="<?php echo $record->id; ?>">
																<i class="bi bi-trash"></i>
															</button>
														</div>
													</div>
												<?php } else { ?>
													<div class="d-flex align-items-center">
														<textarea class="form-control remark-input flex-grow-1 me-2" data-record-id="<?php echo $record->id; ?>" name="remark_appointment" placeholder="Add a remark"></textarea>
														<button class="btn btn-outline-primary btn-sm save-remark-btn" data-record-id="<?php echo $record->id; ?>">
															<i class="bi bi-send"></i>
														</button>
													</div>
												<?php } ?>
											</td>
											
										</tr>
									<?php endforeach; ?>
								<?php else: ?>
									<tr>
										<td colspan="12" class="text-center">No Data.</td>
									</tr>
								<?php endif; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script> // Add new Customer
document.addEventListener('DOMContentLoaded', function() {
    const addCustomerCheckbox = document.getElementById('add_customer_checkbox');
    const selectContainer = document.getElementById('customer_select_container');
    const inputContainer = document.getElementById('customer_input_container');
    const selectElement = document.getElementById('customer_name');
    const inputElement = document.getElementById('customer_name_input');

    addCustomerCheckbox.addEventListener('change', function() {
        if (this.checked) {
            // Ocultar el select y mostrar el input
            selectContainer.style.display = 'none';
            inputContainer.style.display = 'block';

            // Deshabilitar el select y habilitar el input
            selectElement.required = false;
            selectElement.disabled = true;
            selectElement.value = ""; // Limpiar el valor del select

            inputElement.required = true;
            inputElement.disabled = false;
        } else {
            // Mostrar el select y ocultar el input
            selectContainer.style.display = 'block';
            inputContainer.style.display = 'none';
            
            // Deshabilitar el input y habilitar el select
            inputElement.required = false;
            inputElement.disabled = true;
            inputElement.value = ""; // Limpiar el valor del input

            selectElement.required = true;
            selectElement.disabled = false;
        }
    });
});
</script>

<script> // script about show|hide form (change icons)
document.addEventListener('DOMContentLoaded', function() {
    const toggleFormBtn = document.getElementById('toggle-form-btn');
    const formColumn = document.getElementById('form-column');
    const tableColumn = document.getElementById('table-column');

    toggleFormBtn.addEventListener('click', function() {
        if (formColumn.classList.contains('d-none')) {
            // Si el formulario está oculto, lo muestra y restaura el ancho de la tabla
            formColumn.classList.remove('d-none');
            tableColumn.classList.remove('col-md-12');
            tableColumn.classList.add('col-md-9');
            toggleFormBtn.innerHTML = '<i class="bi bi-eye-slash"></i> Hide Form';
        } else {
            // Si el formulario está visible, lo oculta y expande el ancho de la tabla
            formColumn.classList.add('d-none');
            tableColumn.classList.remove('col-md-9');
            tableColumn.classList.add('col-md-12');
            toggleFormBtn.innerHTML = '<i class="bi bi-eye"></i> Show Form';
        }
    });
});
</script>

<script>
// Filter Scripts
document.addEventListener('DOMContentLoaded', function () {
    const customerSelect = document.getElementById('sl_period');
    const statusSelect = document.getElementById('sl_dept');
    const tableRows = document.querySelectorAll('#po-table tbody tr');

    // Función que se encarga de aplicar todos los filtros
    function filterTable() {
        const selectedCustomer = customerSelect.value;
        const selectedStatus = statusSelect.value;

        tableRows.forEach(row => {
            const customerCell = row.querySelector('.customer-cell');
            const statusCell = row.querySelector('.status-cell');

            if (!customerCell || !statusCell) {
                return; // Ignora si las celdas no existen
            }

            const customerText = customerCell.textContent.trim();
            const statusText = statusCell.textContent.trim();

            const customerMatch = (selectedCustomer === '' || customerText === selectedCustomer);
            const statusMatch = (selectedStatus === '' || statusText === selectedStatus);

            // Si la fila coincide con ambos filtros, la muestra, de lo contrario, la oculta.
            if (customerMatch && statusMatch) {
                row.style.display = ''; // Muestra la fila
            } else {
                row.style.display = 'none'; // Oculta la fila
            }
        });
    }

    // Escucha los cambios en ambos selects para activar el filtro
    customerSelect.addEventListener('change', filterTable);
    statusSelect.addEventListener('change', filterTable);
});
</script>

<script> // Delete row
document.addEventListener('DOMContentLoaded', function () {
    // Escuchar clics en el contenedor de la tabla
    document.querySelector('table').addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-line-btn')) {
            const button = event.target;
            const recordId = button.dataset.recordId;
			const poNumber = button.dataset.poNumber;
            const currentRow = button.closest('tr');
            
			
			Swal.fire({
                    title: 'Are you sure?',
                    text: "You will remove the current row",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'OK',
                    cancelButtonText: 'Cancel'
			}).then((result) => {
				if (result.isConfirmed) {
					// Preparar y enviar la solicitud AJAX
					const xhr = new XMLHttpRequest();
					const url = '<?php echo site_url("page/po_register/delete_row"); ?>';
					const params = `po_number=${poNumber}&record_id=${recordId}`;
					
					xhr.open('POST', url, true);
					xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
					
					xhr.onload = function() {
						if (xhr.status === 200) {
							const response = JSON.parse(xhr.responseText);
							if (response.status === 'success') {
								Swal.fire('Confirmed!', response.message, 'success')
								.then(() => {
                                    // Recarga la página
                                    location.reload();
                                });
							} else {
								Swal.fire('Error!', response.message, 'error');
							}
						} else {
							Swal.fire('Error!', 'Something went wrong with the server request.', 'error');
						}
					};						
					xhr.send(params);
				}
			});			
		}
	});	
});
</script>

<script> // Remove checkbox dates
document.addEventListener('DOMContentLoaded', function () {
	const state_date = 0;
    // Escuchar clics en el contenedor de la tabla
    document.querySelector('table').addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-gerp-date-btn')) {
            const button = event.target;
            const poNumber = button.dataset.poNumber;
            const recordId = button.dataset.recordId;
            const currentRow = button.closest('tr');
            const poNumberCell = currentRow.querySelector('.po-number-cell');
			
			const state_date = 1;
			//console.log(state_date);

            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to remove this date.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Si el usuario confirma, procede con la llamada AJAX
                    const xhr = new XMLHttpRequest();
                    const url = '<?php echo site_url("page/po_register/remove_dates"); ?>';
                    const params = `record_id=${recordId}&state_date=${state_date}&state_date=${state_date}`;

                    xhr.open('POST', url, true);
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === 'success') {
                                // Muestra un mensaje de éxito y recarga la página
                                Swal.fire({
                                    title: 'Confirmed!',
                                    text: 'The date has been removed.',
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        } else {
                            Swal.fire('Error', 'Something went wrong with the server request.', 'error');
                        }
                    };
                    xhr.send(params);
                }
			});
		}
		
		if (event.target.classList.contains('remove-requested-date-btn')) {
            const button = event.target;
            const poNumber = button.dataset.poNumber;
            const recordId = button.dataset.recordId;
            const currentRow = button.closest('tr');
            const poNumberCell = currentRow.querySelector('.po-number-cell');
			
			const state_date = 2;
			//console.log(state_date);

            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to remove this date.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Si el usuario confirma, procede con la llamada AJAX
                    const xhr = new XMLHttpRequest();
                    const url = '<?php echo site_url("page/po_register/remove_dates"); ?>';
                    const params = `record_id=${recordId}&state_date=${state_date}&state_date=${state_date}`;

                    xhr.open('POST', url, true);
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === 'success') {
                                // Muestra un mensaje de éxito y recarga la página
                                Swal.fire({
                                    title: 'Confirmed!',
                                    text: 'The date has been removed.',
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        } else {
                            Swal.fire('Error', 'Something went wrong with the server request.', 'error');
                        }
                    };
                    xhr.send(params);
                }
			});
		}
		
		if (event.target.classList.contains('remove-confirmed-date-btn')) {
            const button = event.target;
            const poNumber = button.dataset.poNumber;
            const recordId = button.dataset.recordId;
            const currentRow = button.closest('tr');
            const poNumberCell = currentRow.querySelector('.po-number-cell');
			
			const state_date = 3;
			//console.log(state_date);

            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to remove this date.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'OK',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Si el usuario confirma, procede con la llamada AJAX
                    const xhr = new XMLHttpRequest();
                    const url = '<?php echo site_url("page/po_register/remove_dates"); ?>';
                    const params = `record_id=${recordId}&state_date=${state_date}&state_date=${state_date}`;

                    xhr.open('POST', url, true);
                    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

                    xhr.onload = function () {
                        if (xhr.status === 200) {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status === 'success') {
                                // Muestra un mensaje de éxito y recarga la página
                                Swal.fire({
                                    title: 'Confirmed!',
                                    text: 'The date has been removed.',
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        } else {
                            Swal.fire('Error', 'Something went wrong with the server request.', 'error');
                        }
                    };
                    xhr.send(params);
                }
			});
		}
    });
});
</script>

<script> // Add new line
document.addEventListener('DOMContentLoaded', function () {
    // Escuchar clics en el contenedor de la tabla
    document.querySelector('table').addEventListener('click', function (event) {
        if (event.target.classList.contains('add-line-btn')) {
            const button = event.target;
            const poNumber = button.dataset.poNumber;
            const recordId = button.dataset.recordId;
            const currentRow = button.closest('tr');
            const poNumberCell = currentRow.querySelector('.po-number-cell');
			
			// Verifica si la línea original era nula
            const lineIsNull = currentRow.dataset.lineIsNull === 'true';

            // 1. Eliminar el botón de la fila actual (la que se hizo clic)
            button.remove();

            // Preparar y enviar la solicitud AJAX
            const xhr = new XMLHttpRequest();
            const url = '<?php echo site_url("page/po_register/add_new_line"); ?>';
            const params = `po_number=${poNumber}&record_id=${recordId}`;

            xhr.open('POST', url, true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

            xhr.onload = function () {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        // Actualizar la celda de la línea en la fila original
                        if (lineIsNull) {
                            currentRow.querySelector('.line-cell').textContent = '1';
						}
						
						location.reload();
                        // 2. Crear el HTML para la nueva fila con el botón
                        const newRowHtml = `
                            <tr data-id="${response.new_record.id}">
                                <td class="text-center po-number-cell">
                                    <div class="d-flex align-items-center justify-content-between w-100">
                                        <span>${response.new_record.po_number}</span>
										<i class="bi bi-plus-square text-primary add-line-btn clickable-icon fs-5" data-po-number="${response.new_record.po_number}" data-record-id="${response.new_record.id}"></i>
                                    </div>
                                </td>
                                <td class="text-center line-cell">${response.new_record.line}</td>
                                <td class="text-center">${response.new_record.customer_name}</td>
                                <td class="text-center status-cell">${response.new_record.status}</td>
                                <td class="text-center">${response.new_record.registrator}</td>
                                <td class="text-center">${response.new_record.created}</td>
                                <td class="text-center"><input type="checkbox" name="gerp" /></td>
                                <td class="text-center"><input type="checkbox" name="requested" /></td>
                                <td class="text-center"><input type="checkbox" name="confirmed" /></td>
                            </tr>
                        `;
                        // Insertar la nueva fila después de la fila actual
                        currentRow.insertAdjacentHTML('afterend', newRowHtml);
                    } else {
                        // Re-mostrar el botón si hubo un error en la base de datos
                        poNumberCell.appendChild(button);
                        Swal.fire('Error', 'Failed to add a new line.', 'error');
                    }
                }
            };
            xhr.send(params);
        }
    });
});
</script>

<script> // Update status
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            if (this.checked) {
                // Obtener el ID del registro desde el atributo data-id de la fila
                const recordId = this.closest('tr').dataset.id;
                const field = this.name;
                const row = this.closest('tr');
                const checkboxCell = this.closest('td');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You will register the current date",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'OK',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const xhr = new XMLHttpRequest();
                        const url = '<?php echo site_url("page/po_register/update_status"); ?>';
                        // Enviar el ID y el nombre del campo en la solicitud AJAX
                        const params = `record_id=${recordId}&field=${field}`;

                        xhr.open('POST', url, true);
                        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                const response = JSON.parse(xhr.responseText);
                                if (response.status === 'success') {
                                    // Actualizar la celda con la fecha y hora
                                    checkboxCell.innerHTML = `${response.timestamp}`;

                                    const statusCell = row.querySelector('.status-cell');
                                    if (field === 'gerp') {
                                        statusCell.textContent = 'Registered';
                                    } else if (field === 'requested') {
                                        statusCell.textContent = 'Requested';
                                    } else if (field === 'confirmed') {
                                        statusCell.textContent = 'Confirmed';
                                    }

                                    Swal.fire({
										title: 'Confirmed!',
										text: response.message,
										icon: 'success'
									}).then(() => {
										location.reload();
									});
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            } else {
                                Swal.fire('Error!', 'Something went wrong with the server request.', 'error');
                            }
                        };
                        xhr.send(params);
                    } else {
                        this.checked = false;
                    }
                });
            }
        });
    });
});
</script>

<script> // Register data
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const addCustomerCheckbox = document.getElementById('add_customer_checkbox');
    const selectElement = document.getElementById('customer_name');
    const inputElement = document.getElementById('customer_name_input');
	
    // Escucha el evento de envío del formulario
    form.addEventListener('submit', function (event) {
        
        // Evita el envío automático del formulario para hacer la validación manual
        event.preventDefault();
        
        // Excluye los campos opcionales del array de validación
        const requiredFields = ['registrator', 'ep_mail', 'po_number'];
        let allFieldsFilled = true;
        
        // Itera sobre los campos obligatorios para verificar si están vacíos
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                allFieldsFilled = false;
                field.classList.add('is-invalid'); // Añade una clase para indicar un error
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
		let customerField;
        if (addCustomerCheckbox.checked) {
            customerField = inputElement;
        } else {
            customerField = selectElement;
        }

        if (!customerField.value.trim()) {
            allFieldsFilled = false;
            customerField.classList.add('is-invalid');
        } else {
            customerField.classList.remove('is-invalid');
        }
		
        // Si no todos los campos están llenos, detén el proceso y muestra un error
        if (!allFieldsFilled) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please fill out all required fields.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Verifica si se adjuntó un archivo
        const attachmentInput = document.getElementById('attachment');
        const fileAttached = attachmentInput.files.length > 0;
        
        let confirmMessage = 'Are you sure you want to register this data?';

        if (!fileAttached) {
            confirmMessage += '<br><br><strong>Note:</strong> No file is being attached.';
        }
        
        // Muestra la alerta de confirmación con SweetAlert
        Swal.fire({
            title: 'Confirm Submission',
            html: confirmMessage,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ok',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario confirma, procede con la validación del PO
                const poNumber = document.getElementById('po_number').value.trim();
                
                // 3. Llamada AJAX para verificar si el PO ya existe
                const checkXhr = new XMLHttpRequest();
                const checkUrl = '<?php echo site_url("page/po_register/check_po_exists"); ?>';
                const checkParams = `po_number=${poNumber}`;

                checkXhr.open('POST', checkUrl, true);
                checkXhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

                checkXhr.onload = function() {
                    if (checkXhr.status === 200) {
                        const checkResponse = JSON.parse(checkXhr.responseText);

                        if (checkResponse.exists) {
                            // Si el PO existe, muestra la alerta de confirmación
                            Swal.fire({
                                title: 'PO already exists!',
                                text: 'Are you sure you want to proceed with an existing PO number?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, register it!',
                                cancelButtonText: 'Cancel'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Si el usuario confirma, procede con la llamada AJAX original
                                    sendDataToServer();
                                }
                            });
                        } else {
                            // Si el PO no existe, envía los datos directamente
                            sendDataToServer();
                        }
                    }
                };
                checkXhr.send(checkParams);
            }
        });
    });
	
	function sendDataToServer() {
        const xhr = new XMLHttpRequest();
        const url = form.action;
        const formData = new FormData(form);
        
        xhr.open('POST', url, true);

        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.status === 'success') {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('Error!', 'An unexpected error occurred. Please try again later.', 'error');
                    console.error('Parsing error:', e);
                }
            } else {
                Swal.fire('Error!', 'Something went wrong with the server request.', 'error');
            }
        };
        xhr.send(formData);
    }
});
</script>

<script> // Remove remark appointment
document.addEventListener('DOMContentLoaded', function() {
    // Escucha clics en los botones de "Guardar"
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('delete-remark-btn')) {
            const button = event.target;
            const recordId = button.dataset.recordId;

            // Alerta de confirmación previa a la acción
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ok'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Si el usuario confirma, llama a la función para borrar
                    removeRemark(recordId);
                }
            });
        }
    });
	
	function removeRemark(recordId) {
        const xhr = new XMLHttpRequest();
        const url = '<?php echo site_url("page/po_register/remove_table_remark"); ?>';
        const params = `record_id=${recordId}`;

        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    // Muestra el mensaje de éxito y recarga la página
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: true,
                        confirmButtonText: 'OK'
                    })
                    .then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            } else {
                Swal.fire('Error', 'Server error.', 'error');
            }
        };
        xhr.send(params);
    }
});	
</script>

<script> // Save button
document.addEventListener('DOMContentLoaded', function() {
    // Escucha clics en los botones de "Guardar"
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('save-remark-btn')) {
            const button = event.target;
            const recordId = button.dataset.recordId;
            const remarkTextarea = button.parentNode.querySelector('.remark-input');
            const remark = remarkTextarea.value;
            
            saveRemark(recordId, remark, remarkTextarea, button);
        }
    });
    
	document.addEventListener('click', function(event) {
        if (event.target.classList.contains('edit-remark-btn')) {
            const button = event.target;
            const recordId = button.dataset.recordId;
            const remarkCell = button.closest('.remark-cell');
            const currentRemark = remarkCell.querySelector('.remark-display span').textContent;

            // Reemplaza el contenido de la celda con el textarea y el botón de guardar
            remarkCell.innerHTML = `
                <div class="d-flex align-items-center">
                    <textarea class="form-control remark-input flex-grow-1 me-2" data-record-id="${recordId}" name="remark_appointment">${currentRemark}</textarea>
                    <button class="btn btn-primary btn-sm save-remark-btn" data-record-id="${recordId}">Save</button>
                </div>
            `;
        }
    });
	
    // Función para guardar el comentario
    function saveRemark(recordId, remark, textareaElement = null, buttonElement = null) {
        const xhr = new XMLHttpRequest();
        const url = '<?php echo site_url("page/po_register/save_remark"); ?>';
        const params = `record_id=${recordId}&remark=${encodeURIComponent(remark)}`;
        
        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
					Swal.fire({
						icon: 'success',
						title: 'Success!',
						text: response.message,
						timer: 2000,
						showConfirmButton: true,
						confirmButtonText: 'OK'
					})
					.then((result) => {
						if (result.isConfirmed) {
							location.reload();
						}
					});
                    // const remarkCell = document.querySelector(`tr[data-id="${recordId}"] .remark-cell`);
                    // if (remarkCell) {
                        // remarkCell.innerHTML = `
                            // <div class="remark-display">
                                // ${remark}
                                // <button class="btn btn-sm btn-outline-primary edit-remark-btn" data-record-id="${recordId}">
                                    // <i class="bi bi-pencil"></i>
                                // </button>
                            // </div>
                        // `;
                    // }
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            } else {
                Swal.fire('Error', 'Server error.', 'error');
            }
        };
        xhr.send(params);
    }

	


});
</script>