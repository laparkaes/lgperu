<div class="pagetitle">
	<h1> SCM Purchase Orders </h1>
	<nav>
		<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
			<li class="breadcrumb-item active">SCM Purchase Orders</li>
		</ol>
	</nav>
</div>

<section class="section">
	<div class="row">
		<div class="col-md-6">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title text-center">Extract Orders</h5> 
					<form class="row g-9" id="form_extract_pdf" action="<?= base_url('module/scm_purchase_orders/upload') ?>" method="POST" enctype="multipart/form-data">
					
						<div class="col-md-12 mb-3"> 
                            <label for="client_select" class="form-label">Customer</label>
							<select class="form-select" id="client_select" name="client" required>
								<option value="">Choose customer...</option>
								<?php foreach ($stores as $store) { ?>
									<option value="<?php echo htmlspecialchars($store); ?>"><?php echo htmlspecialchars($store); ?></option>
								<?php } ?>
							</select>
						</div>
						
						<div class="col-md-12 mb-3" id="pdf_upload_section">
						    <label for="pdf_file" class="form-label">Select PDF File</label>
						    <input class="form-control" type="file" name="attach" id="pdf_file" disabled>
						</div>
						
						<div class="col-md-12 mb-3" id="txt_upload_section" style="display: none;"> 
                            <label for="txt_file1" class="form-label">Select EOC TXT File</label>
                            <input class="form-control mb-2" type="file" name="attach_txt1" id="txt_file1" disabled>
                            <label for="txt_file2" class="form-label">Select EOD TXT File</label>
                            <input class="form-control" type="file" name="attach_txt2" id="txt_file2" disabled>
                        </div>

						<div class="text-center pt-3">
						    <button type="submit" class="btn btn-primary" id="upload_button" disabled>
							<i class="bi bi-download"></i> Export
						    </button>
						</div>
					</form>	
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clientSelect = document.getElementById('client_select');
    
    // Elementos del PDF
    const pdfUploadSection = document.getElementById('pdf_upload_section');
    const pdfFileInput = document.getElementById('pdf_file');
    
    // Elementos de los TXT
    const txtUploadSection = document.getElementById('txt_upload_section');
    const txtFileInput1 = document.getElementById('txt_file1');
    const txtFileInput2 = document.getElementById('txt_file2');
    
    const uploadButton = document.getElementById('upload_button');
    const formExtractPdf = document.getElementById('form_extract_pdf');

    // Define el nombre del cliente que requiere dos TXT
    const specialClient = 'SAGA FALABELLA S.A.'; 

    // Función para actualizar el estado de los campos de archivo y el botón de carga
    function updateFormState() {
        const isClientSelected = clientSelect.value !== '';
        const selectedClient = clientSelect.value;
        let isFileSelected = false; 

        if (selectedClient === specialClient) {
            pdfUploadSection.style.display = 'none';
            pdfFileInput.disabled = true;
            pdfFileInput.removeAttribute('required');
            pdfFileInput.value = ''; 

            txtUploadSection.style.display = 'block';
            txtFileInput1.disabled = !isClientSelected; 
            txtFileInput2.disabled = !isClientSelected; 
            txtFileInput1.setAttribute('required', 'required');
            txtFileInput2.setAttribute('required', 'required');

            isFileSelected = txtFileInput1.files.length > 0 && txtFileInput2.files.length > 0;

        } else {
            // Otros clientes: mostrar PDF, ocultar TXT
            pdfUploadSection.style.display = 'block';
            pdfFileInput.disabled = !isClientSelected; // Habilitar solo si hay cliente
            pdfFileInput.setAttribute('required', 'required');

            txtUploadSection.style.display = 'none';
            txtFileInput1.disabled = true;
            txtFileInput2.disabled = true;
            txtFileInput1.removeAttribute('required');
            txtFileInput2.removeAttribute('required');
            txtFileInput1.value = ''; // Limpiar selección previa
            txtFileInput2.value = ''; // Limpiar selección previa

            isFileSelected = pdfFileInput.files.length > 0;
        }
        
        uploadButton.disabled = !(isClientSelected && isFileSelected);
    }

    // Event Listeners para habilitar/deshabilitar elementos
    clientSelect.addEventListener('change', updateFormState);
    pdfFileInput.addEventListener('change', updateFormState);
    txtFileInput1.addEventListener('change', updateFormState);
    txtFileInput2.addEventListener('change', updateFormState);

    // Inicializar el estado al cargar la página
    updateFormState();

    // Manejar el envío del formulario con SweetAlert
    formExtractPdf.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevenir el envío normal del formulario

        // Mostrar SweetAlert de carga
        Swal.fire({
            title: 'Processing your files...',
            text: 'Please wait while we extract data and generate your Excel file.',
            icon: 'info',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Verificar si la respuesta es un archivo (Excel) o JSON
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json(); // Es JSON, probablemente un error
            } else if (contentType && contentType.includes('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')) {
                // Es un archivo Excel, manejar la descarga
                return response.blob().then(blob => {
                    // Obtener el nombre del archivo del encabezado Content-Disposition
                    const contentDisposition = response.headers.get('Content-Disposition');
                    let filename = 'download.xlsx'; // Nombre por defecto
                    if (contentDisposition) {
                        const filenameMatch = contentDisposition.match(/filename="([^"]+)"/);
                        if (filenameMatch && filenameMatch[1]) {
                            filename = filenameMatch[1];
                        }
                    }

                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                    return { type: 'success', msg: 'Excel file generated and downloaded successfully!' };
                });
            } else {
                // Tipo de contenido inesperado
                throw new Error('Unexpected server response type or no Content-Disposition header.');
            }
        })
        .then(data => {
            Swal.close(); // Cerrar el SweetAlert de carga

            if (data.type === 'success') {
                Swal.fire(
                    'Success!',
                    data.msg,
                    'success'
                );
                formExtractPdf.reset();
                updateFormState(); // Restablecer el estado de los botones y campos
            } else {
                Swal.fire(
                    'Error!',
                    data.msg || 'An unknown error occurred during processing.',
                    'error'
                );
            }
        })
        .catch(error => {
            Swal.close(); // Asegurarse de cerrar el SweetAlert de carga
            console.error('Fetch error:', error);
            Swal.fire(
                'Error!',
                'Failed to connect to the server or process the file. Please try again.',
                'error'
            );
        });
    });
});
</script>