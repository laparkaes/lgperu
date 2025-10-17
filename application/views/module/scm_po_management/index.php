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
		#summary-table thead th {
			position: sticky;
			top: 0;
			z-index: 10;
		}
	</style>
</header>
<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1> PO Management </h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">PO Management</li>
			</ol>	
		</nav>
	</div>	
	<div>
		<a href="../user_manual/module/scm_po_management/scm_po_management_en.pptx" class="text-primary p-3">User Manual</a>
	</div>
</div>
<div class="container-fluid">
	<div class="row">
		<div class="col-md-3 d-none" id="form-column">
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
						<div class="col-md-12">
							<label for="remark" class="form-label">Remark</label>
							<textarea class="form-control" id="remark" name="remark" placeholder="remark"></textarea>
						</div>

						<div class="col-md-12">
							<div class="mb-3">
								<label for="attachment" class="form-label">Attach Files</label>
								<input class="form-control" type="file" id="attachment" name="attachment[]" multiple>
							</div>

							<div id="file-summary-container" class="mt-3" style="display: none;">
								<h6 class="text-center">File Summary</h6>
								<div style="max-height: 300px; overflow-y: auto;">
									<div class="table-responsive">
										<table id="summary-table" class="table table-bordered table-sm">
											<thead>
												<tr>
													<th style="width: 50%;">File Name</th>
													<th style="width: 30%;">PO Number</th>
													<th style="width: 10%;"></th>
												</tr>
											</thead>
											<tbody id="file-summary-table-body">
												</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>	
						<div class="col-12">
							<button type="submit" class="btn btn-primary w-100">Send</button>
						</div>
					</form>
				</div>
			
		</div>
		<div class="col-md-12" id="table-column">
			<div class="card p-4 h-100">
				<h5 class="card-title text-center">Purchase Order History</h5>
				<div class="d-flex justify-content-between align-items-center">
					 <button class="btn btn-outline-secondary btn-sm me-2" id="toggle-form-btn">
						<i class="bi bi-eye-slash"></i>
						Hide Form
					</button>
					<div class="d-flex justify-content-end">
						<input type="text" class="form-control me-1" id="po-search" placeholder="Search PO..." style="width: 200px;">
						<select class="form-select me-1" id="sl_period" style="width: 200px;">
							<option value="">Customer --</option>
							<?php foreach($customers as $customer){  ?>
							<option><?= $customer ?></option>
							<?php } ?>
						</select>
						<!--<select class="form-select me-1" id="sl_dept" style="width: 150px;">
							<option value="">Status --</option>
							<?php foreach($status as $item){  ?>
							<option><?= $item ?></option>
							<?php } ?>
						</select>-->
					</div>
				</div>
				<div style="max-height: 700px; overflow-y: auto;">
					<table id="po-table" class="table table-hover table-bordered mt-2">
						<thead>
						  <tr>
							<th class="text-center" style="width: 3%;">#</th>
							<th class="text-center" style="width: 8%;">PO Number</th>
							<th style="width: 16%;">Customer</th>
							<th class="text-center">Registrator</th>
							<th class="text-center">Created</th>
							<th class="text-center" style="width: 3%;">Line</th>
							<th class="text-center" style="width: 10%;">Model</th>
							<th class="text-center" style="width: 4%;">Qty</th>
							<th class="text-center">Status</th>
							<th class="text-center">GERP</th>
							<th class="text-center">Requested</th>
							<th class="text-center">Confirmed</th>
							<th class="text-center">Remark</th>
						  </tr>
						</thead>
						<tbody>
							<?php if (!empty($po_data)): ?>
								<?php $counter = 1; ?>
								<?php foreach ($po_data as $key => $records): ?>
									<?php $firstRow = true; ?>
									<?php $total_records = count($records); ?>
									<?php $rowId = "po-{$key}"; ?>
									
									<?php foreach ($records as $item): ?>
										
										<?php 
											$row_classes = ($firstRow) ? "po-master-row" : "po-detail-row {$rowId} d-none"; 
										?>
										
										<tr class="<?= $row_classes ?>" data-po-id="<?= $rowId ?>" data-id="<?php echo $item->id; ?>">
											
											<?php if ($firstRow): ?>
												<td class="text-center po-span-cell" data-real-rowspan="<?php echo $total_records; ?>" rowspan="1">													
													<?php echo $counter++; ?>
													<i class="bi bi-x-square text-danger remove-line-btn clickable-icon fs-5" data-po-number="<?php echo $item->po_number; ?>" data-record-id="<?php echo $item->id; ?>"></i>
												</td>
												<td class="po-number-cell po-span-cell" data-real-rowspan="<?php echo $total_records; ?>" rowspan="1">
													<i class="bi bi-caret-down-fill po-toggle-icon me-2" role="button" data-po-id="<?= $rowId ?>"></i>
													<?php echo $key; ?>
													<!--<i class="bi bi-plus-square text-primary add-line-btn clickable-icon fs-5" data-po-number="<?php echo $item->po_number; ?>" data-record-id="<?php echo $item->id; ?>"></i>-->
												</td>
												<td class="customer-cell po-span-cell" data-real-rowspan="<?php echo $total_records; ?>" rowspan="1"><?php echo $item->customer_name; ?></td>
												<td class="text-center po-span-cell" data-real-rowspan="<?php echo $total_records; ?>" rowspan="1"><?php echo $item->registrator; ?></td>
												<td class="text-center created-cell po-span-cell" data-real-rowspan="<?php echo $total_records; ?>" rowspan="1"><?php echo $item->created; ?></td>
												<?php $firstRow = false; ?>
											<?php endif; ?>
											
											<td class="text-center line-cell">
												<?php echo $item->line_no; ?>
												 
											</td>
											<td class="text-center model-cell"><?php echo $item->model; ?></td>
											<td class="text-center qty-cell"><?php echo $item->qty; ?></td>
											<td class="text-center status-cell"><?php echo $item->status; ?></td>
											<td class="text-center">
												<?php if (!empty($item->gerp)): ?>
													<?php echo $item->gerp; ?>
													<i class="bi bi-calendar-x text-danger m-2 remove-gerp-date-btn clickable-icon fs-8" data-po-number="<?php echo $item->po_number; ?>" data-record-id="<?php echo $item->id; ?>"></i>
												<?php else: ?>
													<input type="checkbox" name="gerp"/>
												<?php endif; ?>
											</td>
											<td class="text-center">
												<?php if (!empty($item->appointment_request)): ?>
													<?php echo $item->appointment_request; ?>
													<i class="bi bi-calendar-x text-danger m-2 remove-requested-date-btn clickable-icon fs-8" data-po-number="<?php echo $item->po_number; ?>" data-record-id="<?php echo $item->id; ?>"></i>
												<?php else: ?>
													<input type="checkbox" name="requested"/>
												<?php endif; ?>
											</td>
											<td class="text-center">
												<?php if (!empty($item->appointment_confirmed)): ?>
													<?php echo $item->appointment_confirmed; ?>
													<i class="bi bi-calendar-x text-danger m-2 remove-confirmed-date-btn clickable-icon fs-8" data-po-number="<?php echo $item->po_number; ?>" data-record-id="<?php echo $item->id; ?>"></i>
												<?php else: ?>
													<input type="checkbox" name="confirmed"/>
												<?php endif; ?>
											</td>
											<td class="text-center remark-cell">
												<?php if ($item->remark_appointment !== null):?>
													<?php //echo $item->remark_appointment; ?>
													<div class="remark-display d-flex align-items-center justify-content-between">
														<span><?php echo htmlspecialchars($item->remark_appointment); ?></span>
														<div class="d-flex flex-column align-items-end">
															<button class="btn btn-sm btn-outline-primary edit-remark-btn ms-2" data-record-id="<?php echo $item->id; ?>">
																<i class="bi bi-pencil"></i>
															</button>
															<button class="btn btn-sm btn-outline-danger delete-remark-btn ms-2 mt-1" data-record-id="<?php echo $item->id; ?>">
																<i class="bi bi-trash"></i>
															</button>
														</div>
													</div>
												<?php else: ?>
													<div class="d-flex align-items-center">
														<textarea class="form-control remark-input flex-grow-1 me-2" data-record-id="<?php echo $item->id; ?>" name="remark_appointment" placeholder="Add a remark"></textarea>
														<button class="btn btn-outline-primary btn-sm save-remark-btn" data-record-id="<?php echo $item->id; ?>">
															<i class="bi bi-send"></i>
														</button>
													</div>
												<?php endif; ?>
											</td>
										</tr>
									<?php endforeach; ?>
								<?php endforeach; ?>
							<?php else: ?>
								<tr>
									<td colspan="13" class="text-center">No Data.</td>
								</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
				<div id="pagination-controls" class="d-flex justify-content-center mt-3"></div>
			</div>
		</div>
	</div>
</div>

<script> // Expand po numbers rows
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('po-table');

    //  Asegúrate de que table-layout: fixed; esté aplicado
    if (table) {
        table.style.tableLayout = 'fixed';
    }

    table.addEventListener('click', function(e) {
        if (e.target.classList.contains('po-toggle-icon')) {
            const icon = e.target;
            const poId = icon.getAttribute('data-po-id');
            const detailRows = document.querySelectorAll(`.po-detail-row.${poId}`);
            
            // 1. Encontrar todas las celdas de la fila maestra que tienen rowspan
            const masterRow = icon.closest('tr');
            const spanCells = masterRow.querySelectorAll('.po-span-cell');
            
            // 2. Comprobar el estado (Expandido o Colapsado)
            const is_collapsed = icon.classList.contains('bi-caret-down-fill');

            if (is_collapsed) {
                // ESTADO: EXPANDIR
                // ------------------
                // Mostrar filas de detalle
                detailRows.forEach(row => {
                    row.classList.remove('d-none');
                });
                
                // Aplicar el rowspan real
                spanCells.forEach(cell => {
                    const realRowspan = cell.getAttribute('data-real-rowspan');
                    cell.setAttribute('rowspan', realRowspan);
                });

                // Cambiar icono
                icon.classList.remove('bi-caret-down-fill');
                icon.classList.add('bi-caret-up-fill');
                
            } else {
                // ESTADO: COLAPSAR
                // -----------------
                // Ocultar filas de detalle
                detailRows.forEach(row => {
                    row.classList.add('d-none');
                });
                
                // Resetear rowspan a 1 (para que solo ocupe 1 fila)
                spanCells.forEach(cell => {
                    cell.setAttribute('rowspan', '1');
                });

                // Cambiar icono
                icon.classList.remove('bi-caret-up-fill');
                icon.classList.add('bi-caret-down-fill');
            }
        }
    });
});
</script>

<script> // Validation empty submission form
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const addCustomerCheckbox = document.getElementById('add_customer_checkbox');
    const selectElement = document.getElementById('customer_name');
    const inputElement = document.getElementById('customer_name_input');
    
    const attachmentInput = document.getElementById('attachment');
    const fileSummaryContainer = document.getElementById('file-summary-container');
    const tableBody = document.getElementById('file-summary-table-body');
    
    // Función para manejar el cambio de archivos y poblar la tabla
    attachmentInput.addEventListener('change', function () {
        const files = this.files;
        tableBody.innerHTML = '';
        
        if (files.length > 0) {
            fileSummaryContainer.style.display = 'block';
            
            const formData = new FormData();
            Array.from(files).forEach(file => {
                formData.append('attachment[]', file);
            });

            const xhr = new XMLHttpRequest();
            const url = '<?php echo site_url("page/po_register/extract_po"); ?>';
            
            xhr.open('POST', url, true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.status === 'success') {
                            const fragment = document.createDocumentFragment();
                            response.files_data.forEach(item => {
                                const row = createRow({ name: item.name }, item.po_number);
                                fragment.appendChild(row);
                            });
                            tableBody.appendChild(fragment);
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    } catch (e) {
                        Swal.fire('Error!', 'An unexpected error occurred parsing the server response.', 'error');
                        console.error('Parsing error:', e);
                    }
                } else {
                    Swal.fire('Error!', 'Something went wrong with the server request.', 'error');
                }
            };
            xhr.send(formData);
        } else {
            fileSummaryContainer.style.display = 'none';
        }
    });
    
    function createRow(file, poNumber = null, isDuplicate = false) {
        const row = document.createElement('tr');
        const fileNameCell = document.createElement('td');
        const poNumberCell = document.createElement('td');
        const actionsCell = document.createElement('td');
        
        fileNameCell.textContent = file.name;
        
        const poInput = document.createElement('input');
        poInput.type = 'text';
        poInput.className = 'form-control form-control-sm po-input';
        poInput.placeholder = 'Enter PO...';
        poInput.name = 'po_numbers[]';
        poInput.required = true;
        if (poNumber) {
            poInput.value = poNumber;
        }
        poNumberCell.appendChild(poInput);
        
        const iconContainer = document.createElement('div');
        iconContainer.className = 'd-flex justify-content-end align-items-center h-100';

        const duplicateBtn = document.createElement('i');
        duplicateBtn.className = 'bi bi-plus-circle text-primary clickable-icon fs-5 me-2';
        duplicateBtn.addEventListener('click', () => {
            const newRow = createRow(file, poInput.value, true);
            row.insertAdjacentElement('afterend', newRow);
        });
        
        if (isDuplicate) {
            const removeBtn = document.createElement('i');
            removeBtn.className = 'bi bi-x-circle text-danger clickable-icon fs-5';
            
            removeBtn.addEventListener('click', () => {
                row.remove();
            });
            
            iconContainer.appendChild(duplicateBtn);
            iconContainer.appendChild(removeBtn);
        } else {
            iconContainer.appendChild(duplicateBtn);
        }

        actionsCell.appendChild(iconContainer);
        row.appendChild(fileNameCell);
        row.appendChild(poNumberCell);
        row.appendChild(actionsCell);

        return row;
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        
        if (validateForm()) {
            checkExistingPOs();
        }
    });

    function validateForm() {
        // Tu función de validación... (no necesita cambios)
        const requiredFields = ['registrator', 'ep_mail'];
        let allFieldsFilled = true;
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                allFieldsFilled = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });

        const customerField = addCustomerCheckbox.checked ? inputElement : selectElement;
        if (!customerField.value.trim()) {
            allFieldsFilled = false;
            customerField.classList.add('is-invalid');
        } else {
            customerField.classList.remove('is-invalid');
        }

        const poInputs = document.querySelectorAll('.po-input');
        if (attachmentInput.files.length > 0 && poInputs.length === 0) {
            allFieldsFilled = false;
        }
        poInputs.forEach(input => {
            if (!input.value.trim()) {
                allFieldsFilled = false;
                input.classList.add('is-invalid');
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (!allFieldsFilled) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Please fill out all required fields.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            return false;
        }
        return true;
    }

    function checkExistingPOs() {
        const poInputs = document.querySelectorAll('.po-input');
        
        if (poInputs.length === 0) {
            // No hay POs en la tabla, se asume que no hay archivos
            proceedWithSubmission();
            return;
        }

        const poNumbers = Array.from(poInputs).map(input => input.value.trim());

        const xhr = new XMLHttpRequest();
        const url = '<?php echo site_url("page/po_register/check_multiple_po_exists"); ?>';
        const params = `po_numbers=${JSON.stringify(poNumbers)}`;

        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.exists.length > 0) {
                    let poList = response.exists.map(po => `<strong>${po}</strong>`).join(', ');
                    Swal.fire({
                        title: 'PO(s) already exist!',
                        html: `The following POs already exist: ${poList}<br>Are you sure you want to proceed?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, register it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            sendDataToServer(); // Llama a la función sin argumentos
                        }
                    });
                } else {
                    sendDataToServer(); // Llama a la función sin argumentos
                }
            } else {
                Swal.fire('Error!', 'Something went wrong with the PO check.', 'error');
            }
        };
        xhr.send(params);
    }
    
    function proceedWithSubmission() {
        const fileAttached = attachmentInput.files.length > 0;
        let confirmMessage = 'Are you sure you want to register this data?';
        if (!fileAttached) {
            confirmMessage += '<br><br><strong>Note:</strong> No file is being attached.';
        }
        
        Swal.fire({
            title: 'Confirm Submission',
            html: confirmMessage,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'OK',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                sendDataToServer();
            }
        });
    }

    function sendDataToServer() {
		// Recopila los datos de la tabla de resumen
		const poNumbersForm = [];
		const fileNamesForm = [];
		
		const summaryRows = document.querySelectorAll('#file-summary-table-body tr');
		
		summaryRows.forEach(row => {
			const poInput = row.querySelector('.po-input');
			const fileNameCell = row.querySelector('td:first-child');

			if (poInput && fileNameCell) {
				poNumbersForm.push(poInput.value.trim());
				fileNamesForm.push(fileNameCell.textContent.trim());
			}
		});

		console.log('POs recopilados de la tabla:', poNumbersForm);
		console.log('Nombres de archivo recopilados de la tabla:', fileNamesForm);

		const formData = new FormData();
		
		// Adjunta los campos del formulario principal
		formData.append('registrator', document.getElementById('registrator').value);
		formData.append('ep_mail', document.getElementById('ep_mail').value);
		formData.append('customer_name', document.getElementById('customer_name').value);
		formData.append('remark', document.getElementById('remark').value);

		// Adjunta los archivos subidos
		const attachmentInput = document.getElementById('attachment');
		Array.from(attachmentInput.files).forEach(file => {
			formData.append('attachment[]', file);
		});

		// Adjunta los arrays de POs y nombres de archivos de la tabla
		poNumbersForm.forEach(po => formData.append('po_numbers_form[]', po));
		fileNamesForm.forEach(name => formData.append('file_names_form[]', name));
		
		// Muestra el contenido del objeto FormData antes de enviarlo
		console.log('Contenido final del FormData:');
		for (let pair of formData.entries()) {
			console.log(pair[0] + ': ' + pair[1]);
		}

		const xhr = new XMLHttpRequest();
		const url = '<?php echo site_url("page/po_register/register_data"); ?>';

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

    // Estado inicial: El botón muestra el formulario
    toggleFormBtn.innerHTML = '<i class="bi bi-eye"></i> Show Form';

    toggleFormBtn.addEventListener('click', function() {
        if (formColumn.classList.contains('d-none')) {
            // Si el formulario está oculto, lo muestra y reduce el ancho de la tabla
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

<script> // Filter Scripts
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('po-search');
    const customerSelect = document.getElementById('sl_period');
    const tableBody = document.querySelector('#po-table tbody');
    const paginationContainer = document.getElementById('pagination-controls');

    // Configuración de la paginación
    const rowsPerPage = 15;
    let currentPage = 1;

    // Función principal para filtrar y paginar la tabla
    function filterAndPaginate() {
        const searchTerm = searchInput.value.toLowerCase();
        const customerFilter = customerSelect.value.toLowerCase();
        const allRows = Array.from(tableBody.querySelectorAll('tr'));
        
        let visiblePoGroups = [];

        allRows.forEach(row => {
            const poNumberCell = row.querySelector('.po-number-cell');
            if (poNumberCell) {
                const poNumber = poNumberCell.textContent.toLowerCase();
                const customerCell = row.querySelector('.customer-cell');
                const customerName = customerCell.textContent.toLowerCase();
                const statusCell = row.querySelector('.status-cell');
                const statusName = statusCell.textContent.toLowerCase();
                
                const poMatch = poNumber.includes(searchTerm);
                const customerMatch = (customerFilter === '' || customerName.includes(customerFilter));
				
				if (poMatch && customerMatch) {
                    visiblePoGroups.push(row);
                }
            }
        });
        
        const totalPoGroups = visiblePoGroups.length;
        const totalPages = Math.ceil(totalPoGroups / rowsPerPage);

        const startGroup = (currentPage - 1) * rowsPerPage;
        const endGroup = startGroup + rowsPerPage;

        allRows.forEach(row => row.style.display = 'none');

        for (let i = startGroup; i < endGroup && i < totalPoGroups; i++) {
            const poGroupStartRow = visiblePoGroups[i];
            poGroupStartRow.style.display = '';

            let nextRow = poGroupStartRow.nextElementSibling;
            while (nextRow && !nextRow.querySelector('.po-number-cell')) {
                nextRow.style.display = '';
                nextRow = nextRow.nextElementSibling;
            }
        }

        renderPaginationControls(totalPages);
    }

    function renderPaginationControls(totalPages) {
        let paginationHtml = `<ul class="pagination justify-content-center">`;
        
        paginationHtml += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
        </li>`;

        for (let i = 1; i <= totalPages; i++) {
            paginationHtml += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        }

        paginationHtml += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
        </li>`;
        
        paginationHtml += `</ul>`;
        paginationContainer.innerHTML = paginationHtml;

        paginationContainer.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const newPage = parseInt(e.target.dataset.page, 10);
                if (newPage > 0 && newPage <= totalPages) {
                    currentPage = newPage;
                    filterAndPaginate();
                }
            });
        });
    }

    searchInput.addEventListener('input', () => {
        currentPage = 1;
        filterAndPaginate();
    });
    customerSelect.addEventListener('change', () => {
        currentPage = 1;
        filterAndPaginate();
    });
    filterAndPaginate();
});
</script>

<script> // Delete row
document.addEventListener('DOMContentLoaded', function () {
	const historyTable = document.getElementById('po-table');
	if (historyTable) {
        historyTable.addEventListener('click', function (event) {
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
	}
});
</script>

<script> // Remove checkbox dates
document.addEventListener('DOMContentLoaded', function () {
	const state_date = 0;
	const historyTable = document.getElementById('po-table');
	if (historyTable) {
        historyTable.addEventListener('click', function (event) {
			if (event.target.classList.contains('remove-gerp-date-btn')) {
				const button = event.target;
				const poNumber = button.dataset.poNumber;
				const recordId = button.dataset.recordId;
				const currentRow = button.closest('tr');
				const poNumberCell = currentRow.querySelector('.po-number-cell');
				
				const state_date = 1;
				
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
						const xhr = new XMLHttpRequest();
						const url = '<?php echo site_url("page/po_register/remove_dates"); ?>';
						const params = `record_id=${recordId}&state_date=${state_date}&state_date=${state_date}`;

						xhr.open('POST', url, true);
						xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

						xhr.onload = function () {
							if (xhr.status === 200) {
								const response = JSON.parse(xhr.responseText);
								if (response.status === 'success') {
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
    //});
	}
});
</script>

<script> // Add new line
document.addEventListener('DOMContentLoaded', function () {
	const historyTable = document.getElementById('po-table');
	if (historyTable) {
        historyTable.addEventListener('click', function (event) {
			if (event.target.classList.contains('add-line-btn')) {
				const button = event.target;
				const poNumber = button.dataset.poNumber;
				const recordId = button.dataset.recordId;
				const currentRow = button.closest('tr');
				const poNumberCell = currentRow.querySelector('.po-number-cell');
				const lineIsNull = currentRow.dataset.lineIsNull === 'true';

				button.remove();

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
							currentRow.insertAdjacentHTML('afterend', newRowHtml);
						} else {
							poNumberCell.appendChild(button);
							Swal.fire('Error', 'Failed to add a new line.', 'error');
						}
					}
				};
				xhr.send(params);
			}
		})
    };
});
</script>

<script> // Update status
document.addEventListener('DOMContentLoaded', function () {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            if (this.checked) {
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
                        const params = `record_id=${recordId}&field=${field}`;

                        xhr.open('POST', url, true);
                        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                const response = JSON.parse(xhr.responseText);
                                if (response.status === 'success') {
                                    checkboxCell.innerHTML = `${response.timestamp}
											<i class="bi bi-calendar-x text-danger m-2 remove-date-btn clickable-icon fs-8" 
                                           data-field="${field}" 
                                           data-record-id="${recordId}"></i>`;

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

<script> // Remove remark appointment
document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('delete-remark-btn')) {
            const button = event.target;
            const recordId = button.dataset.recordId;

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