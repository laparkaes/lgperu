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
		#po-table {
			/* Fuerza al navegador a usar la anchura de las columnas de la primera fila/thead */
			table-layout: fixed; 
			/* Opcional: Esto ayuda a que no se desborden */
			width: 100%; 
		}
		#summary-table thead th {
			position: sticky;
			top: 0;
			z-index: 10;
		}
	</style>
</header>
<div class="container-fluid mt-5">
	<div class="row">
		<div>
			<a href="../user_manual/page/po_management/po_register_en.pptx" class="text-primary p-3">User Manual</a>
		</div>
		<div class="row-12 col-md-6 mx-auto" id="form-column">
			<div class="card p-4 mx-auto">
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
							<div class="d-flex align-items-center">
								<div class="form-check me-3">
									<input class="form-check-input" style="display:None;" type="checkbox" id="filter_ac_checkbox">
									<label class="form-check-label" style="display:None;" for="filter_ac_checkbox">
										AC
									</label>
								</div>
								
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="add_customer_checkbox">
									<label class="form-check-label" for="add_customer_checkbox">
										Add
									</label>
								</div>
							</div>
						</label>
						
						<div id="customer_select_container">
							<select class="form-select" id="customer_name" name="customer_name" required>
								<option value="">Choose customer...</option>
								<?php foreach ($order_customers as $customer) { ?>
									<option value="<?php echo htmlspecialchars($customer); ?>"><?php echo htmlspecialchars($customer); ?></option>
								<?php } ?>
							</select>
						</div>				
						<div id="customer_input_container" style="display: none;">
							<input type="text" class="form-control" id="customer_name_input" name="customer_name" placeholder="Customer..." required disabled>
						</div>
					</div>
					<div class="col-md-6">
						<label class="form-label">
							CC Email 
							<span class="text-muted small">(Optional)</span>
						</label>
						
						<div class="form-control"> 
							
							<div class="d-flex flex-row justify-content-start align-items-center">
								
								<div class="form-check me-5">
									<input class="form-check-input" type="checkbox" value="HS" id="cc_email_hs" name="cc_emails[]">
									<label class="form-check-label" for="cc_email_hs">
										HS
									</label>
								</div>
								<div class="form-check me-5">
									<input class="form-check-input" type="checkbox" value="MS" id="cc_email_ms" name="cc_emails[]">
									<label class="form-check-label" for="cc_email_ms">
										MS
									</label>
								</div>
								<div class="form-check me-5">
									<input class="form-check-input" type="checkbox" value="IT" id="cc_email_it" name="cc_emails[]">
									<label class="form-check-label" for="cc_email_it">
										IT
									</label>
								</div>
								<div class="form-check">
									<input class="form-check-input" type="checkbox" value="ES" id="cc_email_es" name="cc_emails[]">
									<label class="form-check-label" for="cc_email_es">
										ES
									</label>
								</div>
							</div>
						</div>
					</div>
					<!--<div class="col-md-12">
						<label for="remark" class="form-label">Remark</label>
						<textarea class="form-control" id="remark" name="remark" placeholder="remark"></textarea>
					</div>-->
					<div class="form-group ac-po-source-group" style="display: none;">
						<label for="po_source_ac">PO Numbers</label>
						<textarea class="form-control" id="po_source_ac" name="po_source_ac" rows="2" placeholder="Ingrese los números de PO aquí, uno por línea o separados por comas."></textarea>
					</div>

					<div class="form-group">
						<label for="remark">Remark <span class="text-muted small">(Optional)</span></label>
						<textarea class="form-control" id="remark" name="remark" rows="2" placeholder="Comentarios adicionales para el registro..."></textarea>
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
		<div class="row-md-12" id="table-column">
			<div class="card p-4 h-100">
				<h5 class="card-title text-center">Purchase Order History</h5>
				<div class="d-flex justify-content-between align-items-center">
					 <button class="btn btn-outline-secondary btn-sm me-2" id="toggle-form-btn">
						<i class="bi bi-eye-slash"></i>
						Hide Form
					</button>
					<div class="d-flex justify-content-end">
						<input type="text" class="form-control me-1" id="registrator-search" placeholder="Search KAM..." style="width: 200px;">
						<input type="text" class="form-control me-1" id="po-search" placeholder="Search PO..." style="width: 200px;">
						<select class="form-select me-1" id="sl_period" style="width: 200px;">
							<option value="">Customer --</option>
							<?php foreach($order_customers as $customer){  ?>
							<option><?= $customer ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div style="max-height: 700px; overflow-y: auto;">
					<table id="po-table" class="table table-hover table-bordered mt-3">
						<thead>
							<tr>
								<th class="text-center" style="width: 3%;">#</th>
								<th class="text-center" style="width: 8%;">PO Number</th>
								<th style="width: 16%;">Customer</th>
								<th class="text-center">KAM</th>
								<th class="text-center">PO Created</th>
								<th class="text-center" style="width: 3%;">Line</th>
								<th class="text-center" style="width: 10%;">Model</th>
								<th class="text-center" style="width: 4%;">Qty</th>
								<th class="text-center" style="width: 8%;">Amount USD</th>
								<th class="text-center">Status</th>
								<th class="text-center">Registered GERP</th>
								<th class="text-center">Appointment Requested</th>
								<th class="text-center">Appointment Confirmed</th>
								<th class="text-center">Remark</th>
							</tr>
						</thead>
						<tbody>
							<?php if (!empty($history)): ?>
								<?php $counter = 1; ?>
								<?php foreach ($history as $key => $records): ?>
									<?php $firstRow = true; ?>
									<?php $total_records = count($records); ?>
									<?php $rowId = "po-{$key}"; ?>
									
									<?php foreach ($records as $item): ?>
										
										<?php 
											$row_classes = ($firstRow) ? "po-master-row" : "po-detail-row {$rowId} d-none"; 
										?>
										
										<tr class="<?= $row_classes ?>" data-po-id="<?= $rowId ?>">
											
											<?php if ($firstRow): ?>
												<td class="text-center po-span-cell" data-real-rowspan="<?php echo $total_records; ?>" rowspan="1"><?php echo $counter++; ?></td>
												<td class="po-number-cell po-span-cell" data-real-rowspan="<?php echo $total_records; ?>" rowspan="1">
													<i class="bi bi-caret-down-fill po-toggle-icon me-2" role="button" data-po-id="<?= $rowId ?>"></i>
													<?php echo $key; ?>
												</td>
												<td class="customer-cell po-span-cell" data-real-rowspan="<?php echo $total_records; ?>" rowspan="1"><?php echo $item->customer_name; ?></td>
												<td class="registrator-cell text-center po-span-cell" data-real-rowspan="<?php echo $total_records; ?>" rowspan="1"><?php echo $item->registrator; ?></td>
												<td class="text-center created-cell po-span-cell" data-real-rowspan="<?php echo $total_records; ?>" rowspan="1"><?php echo $item->created; ?></td>
												<?php $firstRow = false; ?>
											<?php endif; ?>
											
											<td class="text-center line-cell"><?php echo $item->line_no; ?></td>
											<td class="text-center model-cell"><?php echo $item->model; ?></td>
											<td class="text-center qty-cell"><?php echo $item->qty; ?></td>
											<td class="text-center amount-cell"><?php echo $item->amount_usd; ?></td>
											<td class="text-center status-cell"><?php echo $item->status; ?></td>
											<td class="text-center">
												<?php echo (!empty($item->gerp)) ? $item->gerp : '<input type="checkbox" name="gerp" required disabled/>'; ?>
											</td>
											<td class="text-center">
												<?php echo (!empty($item->appointment_request)) ? $item->appointment_request : '<input type="checkbox" name="requested" required disabled/>'; ?>
											</td>
											<td class="text-center">
												<?php echo (!empty($item->appointment_confirmed)) ? $item->appointment_confirmed : '<input type="checkbox" name="confirmed" required disabled/>'; ?>
											</td>
											<td class="text-center remark-cell">
												<?php echo ($item->remark_appointment !== null) ? $item->remark_appointment : '-'; ?>
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
				<!--<div id="pagination-controls" class="d-flex justify-content-center mt-3"></div>-->
			</div>
		</div>
	</div>
</div>

<script> // Customers list values
    const ALL_CUSTOMERS = <?php echo json_encode($order_customers); ?>;
    const AC_CUSTOMERS = <?php echo json_encode($customers_ac); ?>;
</script>

<script> // Add, AC buttons and special customers
document.addEventListener('DOMContentLoaded', function () {
    const customerSelect = document.getElementById('customer_name');
    const customerInput = document.getElementById('customer_name_input'); 
    const poSourceGroup = document.querySelector('.ac-po-source-group');
    const poSourceAc = document.getElementById('po_source_ac');
    const addCustomerCheckbox = document.getElementById('add_customer_checkbox'); 
    const filterACCheckbox = document.getElementById('filter_ac_checkbox');
    const inputContainer = document.getElementById('customer_input_container');
    const selectContainer = document.getElementById('customer_select_container');
    
    // Special Customers
    const specialCustomers = [
        'SAGA FALABELLA S.A.', 
        'HIPERMERCADOS TOTTUS S.A.', 
        'TIENDAS DEL MEJORAMIENTO DEL HOGAR S.A. - [SODIMAC]', 
        'TIENDAS POR DEPARTAMENTO RIPLEY S.A.C.'
    ];

    function updateCustomerList(customersArray) {
        customerSelect.innerHTML = '<option value="">Choose customer...</option>';
        customersArray.forEach(customer => {
            const option = document.createElement('option');
            option.value = customer; 
            option.textContent = customer;
            customerSelect.appendChild(option);
        });

        try {
            $('#customer_name').select2('destroy');
        } catch (e) {
            // Ignorar error si no estaba inicializado
        }
        initSelect2();
        toggleAcPoSource();
    }

    // Init Select2
    function initSelect2() {
        $('#customer_name').select2({
            placeholder: "Choose Customer...", 
            allowClear: true,
            theme: "bootstrap-5",
			width: 'resolve'
        });
        
        $('#customer_name').on('change', toggleAcPoSource);
    }
    
	// Add new customer
    function getSelectedCustomerName() {
        if (addCustomerCheckbox && addCustomerCheckbox.checked) {
            return customerInput ? customerInput.value.trim().toUpperCase() : '';
        } else {
            return customerSelect ? customerSelect.value.trim().toUpperCase() : '';
        }
    }

    function toggleAcPoSource() {
        const customerValue = getSelectedCustomerName();
        const isAC = specialCustomers.includes(customerValue); 

        if (isAC) {
            if (poSourceGroup) poSourceGroup.style.display = 'block';
            if (poSourceAc) poSourceAc.required = true;
        } else {
            if (poSourceGroup) poSourceGroup.style.display = 'none';
            if (poSourceAc) {
                poSourceAc.required = false;
                poSourceAc.value = ''; 
            }
        }
    }

    // AC Checkbox
    filterACCheckbox.addEventListener('change', function () {
        if (this.checked) {
            if (addCustomerCheckbox.checked) {
                addCustomerCheckbox.checked = false;
            }
            updateCustomerList(AC_CUSTOMERS);        
        } else {
            updateCustomerList(ALL_CUSTOMERS);
        }
    });

    // Add Customer
    addCustomerCheckbox.addEventListener('change', function() {
        if (this.checked) {
            try {
                 $('#customer_name').select2('destroy'); 
            } catch (e) { }

            if (filterACCheckbox.checked) {
                filterACCheckbox.checked = false;
                updateCustomerList(ALL_CUSTOMERS);
            }
            
            selectContainer.style.display = 'none';
            inputContainer.style.display = 'block';
            customerInput.required = true;
            customerInput.disabled = false;
            customerSelect.required = false;
            customerInput.value = '';
            customerInput.addEventListener('input', toggleAcPoSource);

        } else {
            selectContainer.style.display = 'block';
            inputContainer.style.display = 'none';
            customerInput.required = false;
            customerInput.disabled = true;
            customerSelect.required = true;
            customerInput.value = '';
            initSelect2();
        }
        toggleAcPoSource();
    });
    
    initSelect2(); 
    toggleAcPoSource();
    
    if (addCustomerCheckbox && addCustomerCheckbox.checked) {
        addCustomerCheckbox.dispatchEvent(new Event('change'));
    }
});
</script>

<script> // Expand po numbers rows
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('po-table');
	
    if (table) {
        table.style.tableLayout = 'fixed';
    }

    table.addEventListener('click', function(e) {
        if (e.target.classList.contains('po-toggle-icon')) {
            const icon = e.target;
            const poId = icon.getAttribute('data-po-id');
            const detailRows = document.querySelectorAll(`.po-detail-row.${poId}`);
            const masterRow = icon.closest('tr');
            const spanCells = masterRow.querySelectorAll('.po-span-cell');
            
            // Expanse or contracted
            const is_collapsed = icon.classList.contains('bi-caret-down-fill');

            if (is_collapsed) {
                detailRows.forEach(row => {
                    row.classList.remove('d-none');
                });
                
                spanCells.forEach(cell => {
                    const realRowspan = cell.getAttribute('data-real-rowspan');
                    cell.setAttribute('rowspan', realRowspan);
                });

                icon.classList.remove('bi-caret-down-fill');
                icon.classList.add('bi-caret-up-fill');
                
            } else {
                detailRows.forEach(row => {
                    row.classList.add('d-none');
                });
                
                spanCells.forEach(cell => {
                    cell.setAttribute('rowspan', '1');
                });

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
	
	// Special Customers
	const SPECIAL_CUSTOMERS = [
        'SAGA FALABELLA S.A.', 
        'HIPERMERCADOS TOTTUS S.A.', 
        'TIENDAS DEL MEJORAMIENTO DEL HOGAR S.A. - [SODIMAC]', 
        'TIENDAS POR DEPARTAMENTO RIPLEY S.A.C.'
    ];
    let originalEmlFiles = [];
	
	function getSelectedCustomerName() {
		if (addCustomerCheckbox.checked) {
			return inputElement.value.trim();
		} else {
			return selectElement.value;
		}
	}
	
	function isSpecialCustomerSelected() {
        const customerName = getSelectedCustomerName().toUpperCase();
        return SPECIAL_CUSTOMERS.includes(customerName);
    }
	
    attachmentInput.addEventListener('change', function () {
        const files = this.files;
        tableBody.innerHTML = '';
        originalEmlFiles = [];
		
		const customerName = getSelectedCustomerName();
		
		if (isSpecialCustomerSelected()) {
             if (fileSummaryContainer) {
                 fileSummaryContainer.style.display = 'none';
             }

             return;
        }
			
        if (files.length > 0) {
            fileSummaryContainer.style.display = 'block';
            
            const formData = new FormData();
			formData.append('customer_name', customerName);
            Array.from(files).forEach(file => {
                formData.append('attachment[]', file);
				if (file.name.toLowerCase().endsWith('.eml')) {
					originalEmlFiles.push(file.name);
				}
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
                            if (response.files_data.length === 0 && originalEmlFiles.length > 0) {
                                originalEmlFiles.forEach(emlName => {
                                    const row = createRow({ name: emlName }, null);
                                    fragment.appendChild(row);
                                });
                            } else {
                                response.files_data.forEach(item => {
                                    const row = createRow({ name: item.name }, item.po_number);
                                    fragment.appendChild(row);
                                });
                            }
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
		
		if (!isSpecialCustomerSelected()) {
            
            // Revisa si hay archivos adjuntos y la tabla de resumen está vacía.
            // Esto solo aplica a clientes normales, forzando la entrada manual si la extracción falla.
            if (attachmentInput.files.length > 0 && poInputs.length === 0) {
                allFieldsFilled = false;
            }
            
            // Revisa que cada input de PO tenga valor.
            poInputs.forEach(input => {
                if (!input.value.trim()) {
                    allFieldsFilled = false;
                    input.classList.add('is-invalid');
                } else {
                    input.classList.remove('is-invalid');
                }
            });
        }
		
        // if (attachmentInput.files.length > 0 && poInputs.length === 0) {
            // allFieldsFilled = false;
        // }
        // poInputs.forEach(input => {
            // if (!input.value.trim()) {
                // allFieldsFilled = false;
                // input.classList.add('is-invalid');
            // } else {
                // input.classList.remove('is-invalid');
            // }
        // });

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
                            sendDataToServer();
                        }
                    });
                } else {
                    sendDataToServer();
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

		const formData = new FormData(form);
		const attachmentInput = document.getElementById('attachment');
		
		Array.from(attachmentInput.files).forEach(file => {
			formData.append('attachment[]', file);
		});

		formData.delete('attachment[]');
		
		Array.from(attachmentInput.files).forEach(file => {
			formData.append('attachment[]', file);
		});
		// Adjunta los arrays de POs y nombres de archivos de la tabla
		poNumbersForm.forEach(po => formData.append('po_numbers_form[]', po));
		fileNamesForm.forEach(name => formData.append('file_names_form[]', name));

		originalEmlFiles.forEach(emlName => {
			formData.append('original_eml_files[]', emlName);
		});

		// Muestra el contenido del objeto FormData antes de enviarlo
		console.log('Contenido final del FormData:');
		for (let pair of formData.entries()) {
			console.log(pair[0] + ': ' + (pair[1] instanceof File ? pair[1].name : pair[1]));
		}
		
		Swal.fire({
			title: 'Processing Data...',
			text: 'Please wait while the files are being registered and the email is sent.',
			allowOutsideClick: false, // Evita que el usuario haga clic fuera
			didOpen: () => {
				Swal.showLoading();
			},
			imageUrl: 'https://cdn.jsdelivr.net/gh/t4t5/sweetalert/images/loading.gif' 
		});
		
		
		const xhr = new XMLHttpRequest();
		const url = '<?php echo site_url("page/po_register/register_data"); ?>';

		xhr.open('POST', url, true);
		xhr.onload = function() {
			Swal.close();
			
			if (xhr.status === 200) {
				try {
					const response = JSON.parse(xhr.responseText);
					if (response.status === 'success' || response.status === 'warning') {
						Swal.fire(
							(response.status === 'success' ? 'Success!' : 'Warning!'), 
							response.message, 
							response.status
						).then(() => {
							location.reload();
						});
					} else {
						Swal.fire('Error!', response.message, 'error');
					}
				} catch (e) {
					console.error('Parsing error. Server response:', xhr.responseText, e);
					Swal.fire('Error!', 'An unexpected error occurred. Please try again later. (Parsing Error)', 'error');
				}
			} else {
				Swal.fire('Error!', 'Something went wrong with the server request (HTTP Status: ' + xhr.status + ').', 'error');
			}
		};
		
		xhr.onerror = function() {
			Swal.close();
			Swal.fire('Error!', 'Network error. Could not connect to the server.', 'error');
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
            selectContainer.style.display = 'none';
            inputContainer.style.display = 'block';
            selectElement.required = false;
            selectElement.disabled = true;
            selectElement.value = "";

            inputElement.required = true;
            inputElement.disabled = false;
        } else {
            selectContainer.style.display = 'block';
            inputContainer.style.display = 'none';
            inputElement.required = false;
            inputElement.disabled = true;
            inputElement.value = "";

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
            tableColumn.classList.remove('row-md-12');
            tableColumn.classList.add('row-md-9');
            toggleFormBtn.innerHTML = '<i class="bi bi-eye-slash"></i> Hide Form';
        } else {
            // Si el formulario está visible, lo oculta y expande el ancho de la tabla
            formColumn.classList.add('d-none');
            tableColumn.classList.remove('row-md-9');
            tableColumn.classList.add('row-md-12');
            toggleFormBtn.innerHTML = '<i class="bi bi-eye"></i> Show Form';
        }
    });
});
</script>

<script> // Filter Scripts and pagination 
document.addEventListener('DOMContentLoaded', function() {
    const poSearchInput = document.getElementById('po-search');
    const customerSelect = document.getElementById('sl_period');
    const tableBody = document.querySelector('#po-table tbody');
    const registratorSearchInput = document.getElementById('registrator-search');
    const allRows = tableBody ? Array.from(tableBody.querySelectorAll('tr')) : [];
    
    function filterTable() {
        if (!tableBody) return;
        const poTerm = poSearchInput ? poSearchInput.value.toLowerCase() : '';
        const registratorTerm = registratorSearchInput ? registratorSearchInput.value.toLowerCase() : '';
        const customerFilter = customerSelect ? customerSelect.value.toLowerCase() : '';
        
        let foundMatch = false;

        allRows.forEach(row => row.style.display = 'none');

        allRows.forEach(row => {
            const poNumberCell = row.querySelector('.po-number-cell');
            
            if (poNumberCell) {
                const poNumber = poNumberCell.textContent.toLowerCase();
                const customerCell = row.querySelector('.customer-cell');
                const customerName = customerCell ? customerCell.textContent.toLowerCase() : '';
                const registratorCell = row.querySelector('.registrator-cell'); 
                const registratorName = registratorCell ? registratorCell.textContent.toLowerCase() : '';

                // Filters
                const poMatch = poTerm === '' || poNumber.includes(poTerm);
                const customerMatch = customerFilter === '' || customerName.includes(customerFilter);
                const registratorMatch = registratorTerm === '' || registratorName.includes(registratorTerm);

                if (poMatch && customerMatch && registratorMatch) {                   
                    row.style.display = '';
                    foundMatch = true;
            
                    let nextRow = row.nextElementSibling;
                    while (nextRow && !nextRow.querySelector('.po-number-cell')) {
                        nextRow.style.display = '';
                        nextRow = nextRow.nextElementSibling;
                    }
                }
            }
        });
        updateNoResultsMessage(!foundMatch);
    }
    
    function updateNoResultsMessage(show) {
        let noResultsRow = document.getElementById('no_results_row');
        
        if (!noResultsRow && tableBody) {
            const table = tableBody.closest('table');
            const colspanCount = table ? table.querySelectorAll('thead th').length : 5;
            noResultsRow = tableBody.insertRow();
            noResultsRow.id = 'no_results_row';
            const cell = noResultsRow.insertCell();
            cell.colSpan = colspanCount; 
            cell.className = 'text-center';
            cell.textContent = 'No se encontraron órdenes que coincidan con los filtros.';
        }

        if (noResultsRow) {
            noResultsRow.style.display = show ? '' : 'none';
        }
    }
    if (poSearchInput) poSearchInput.addEventListener('input', filterTable);
    if (registratorSearchInput) registratorSearchInput.addEventListener('input', filterTable);
    if (customerSelect) customerSelect.addEventListener('change', filterTable);
    filterTable();
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
							if (lineIsNull) {
								currentRow.querySelector('.line-cell').textContent = '1';
							}
							
							location.reload();
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
	
    // Save remark
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