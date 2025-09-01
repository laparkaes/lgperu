<div class="pagetitle">
	<h1> SA Promotion Calculate</h1>
	<nav>
		<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
			<li class="breadcrumb-item active">Sa Promotion</li>
		</ol>
	</nav>
	
	<!--<a href="../user_manual/module/sa_promotion_calculate/sa_promotion_calculate_en.pptx" class="text-primary">User Manual</a>-->
</div>

<section class="section">
  <div class="col">
    <!-- Columna Única que contiene ambos formularios uno encima del otro -->
    <div class="col-md-6">

      <!-- Formulario para Subir Excel -->
		<div class="card">
			<div class="card-body">
			 
			  <h5 class="card-title text-center">Upload Excel File</h5>			  
				<form class="row g-3" id="form_ar_promotion_update" href="<?= base_url() ?>" enctype="multipart/form-data">
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
	</div>
	<div class="col-md-6">
		<div class="card">
			<div class="card-body" id="export_report_content">
				<h5 class="card-title text-center">Export Report</h5>
				<div id="file_list" class="text-center">
					<p>Loading files...</p>
				</div>
			</div>
		</div>
	</div>
  </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // Cargar archivos disponibles en la sección "Export Report"
    function loadExportFiles() {
        $.ajax({
            url: "<?= base_url('module/sa_sell_in_out_promotion/get_uploaded_files') ?>",
            type: "GET",
            dataType: "json",
            success: function(files) {
                let content = "";
                if (files.length > 0) {
                    files.forEach(function(file) {
                        content += `
                            <div class="form-check">
                                <input class="form-check-input file-checkbox" type="checkbox" name="selected_files[]" value="${file}">
                                <label class="form-check-label">${file}</label>
                            </div>`;
                    });
                    // Add Export button (hidden by default)
                    content += `
                        <div class="text-center pt-3">
                            <button id="exportButton" class="btn btn-success" style="display:none;">
                                <i class="bi bi-download"></i> Export
                            </button>
                        </div>`;
                } else {
                    content = "<p>No files available.</p>";
                }
                $("#file_list").html(content);
                attachCheckboxListener(); // Add event to checkboxes
            },
            error: function() {
                $("#file_list").html("<p>Error loading files.</p>");
            }
        });
    }

    // Function to show/hide the Export button
    function attachCheckboxListener() {
        $(".file-checkbox").on("change", function() {
            let checked = $(".file-checkbox:checked").length > 0;
            $("#exportButton").toggle(checked);
        });
    }

    // Call the function on page load
    loadExportFiles();

    // Handle file upload
    $("#form_ar_promotion_update").submit(function(e) {
        e.preventDefault();
        // Assuming ajax_form_warning and swal_redirection are global functions
        ajax_form_warning(this, "module/sa_sell_in_out_promotion/upload", "Do you want to generate promotion calculation report?")
        .done(function(res) {
            swal_redirection(res.type, res.msg, "module/sa_sell_in_out_promotion");
            loadExportFiles(); // Reload files after upload
        })
        .fail(function() {
            Swal.fire('Error', 'An error occurred during file upload. Please try again.', 'error');
        });
    });

    // --- Handle the Export button click with SweetAlert and Fetch API ---
    $(document).on("click", "#exportButton", function() {
        let selectedFiles = $(".file-checkbox:checked").map(function() {
            return $(this).val();
        }).get();

        if (selectedFiles.length > 0) {
            // Show SweetAlert loading message BEFORE sending the AJAX request
            Swal.fire({
                title: 'Generating Report...',
                text: 'Please wait while your Excel file is being prepared on the server.',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading(); // Show SweetAlert spinner
                }
            });

            // Prepare data for the AJAX request
            const formData = new FormData();
            formData.append('files', JSON.stringify(selectedFiles)); // Send as JSON string

            // Send AJAX request to your server to GENERATE the Excel file
            fetch("<?= base_url('module/sa_sell_in_out_promotion/generate_excel') ?>", {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Check if the HTTP response was successful (2xx status code)
                if (!response.ok) {
                    // If not successful, try to read an error message from JSON (if available)
                    return response.json().then(errorData => {
                        throw new Error(errorData.msg || `Server responded with status ${response.status}.`);
                    }).catch(() => {
                        // If no JSON or malformed, throw a generic error
                        throw new Error(`Network error or unexpected response from server (status: ${response.status}).`);
                    });
                }
                return response.json(); // If response is OK, expect JSON from the server
            })
            .then(data => {
                // Close the loading SweetAlert ONLY WHEN the server responds
                Swal.close(); 

                // If the server indicates success and provides a download URL
                if (data.type === 'success' && data.downloadUrl) {
                    Swal.fire(
                        'Success!',
                        'Your Excel file is ready to download! Click OK to start the download.',
                        'success'
                    ).then(() => {
                        // Once the user closes the success SweetAlert, initiate the download
                        // This redirects the browser to the URL provided by the server.
                        window.location.href = data.downloadUrl;
                        
						setTimeout(() => {
							if (data.fileNameToDelete) {
								const deleteFormData = new FormData();
                                deleteFormData.append('fileName', data.fileNameToDelete); // Envía el nombre del archivo al PHP

                                // Llama a la nueva función PHP para borrar el archivo
                                fetch("<?= base_url('module/sa_sell_in_out_promotion/delete_temp_excel_file')?>", {
                                    method: 'POST',
                                    body: deleteFormData,
									headers: {
										'X-Requested-With': 'XMLHttpRequest' // Para que CodeIgniter reconozca como AJAX
										// 'Content-Type' no es necesario si usas FormData, el navegador lo establecerá
									}
                                })
                                .then(deleteResponse => deleteResponse.json())
                                .then(deleteData => {
                                    console.log('Delete response:', deleteData); // Para depuración
                                    // Opcional: Podrías mostrar otro SweetAlert aquí si el borrado es crítico
                                    // Swal.fire(deleteData.type, deleteData.msg, deleteData.type);
                                })
                                .catch(deleteError => {
                                    console.error('Error deleting file:', deleteError);
                                    // Manejar errores de borrado sin afectar la experiencia principal
                                });
                            }
							// Limpia la UI y recarga la página DESPUÉS del borrado (o de intentar borrar)
                            $(".file-checkbox").prop("checked", false);
                            $("#exportButton").hide();
                            window.location.reload(); 

                        }, 1000); // 1 segundo de retraso para el borrado y la recarga
                        // Optional: Deselect checkboxes and hide button after export
                        // $(".file-checkbox").prop("checked", false);
                        // $("#exportButton").hide();
                        
                        // Reload the page after the download link is clicked (or immediately)
                        // Reload after a short delay to allow browser to register download:
                        //setTimeout(() => window.location.reload(), 500); 
                    });
                } else {
                    // If the response is JSON but indicates a server-side error
                    Swal.fire(
                        'Error!',
                        data.msg || 'An unknown error occurred during report generation. No download URL provided.',
                        'error'
                    );
                }
            })
            .catch(error => {
                // Catch network errors or errors thrown in the .then() block
                // Ensure loading SweetAlert is closed in case of error
                Swal.close(); 
                console.error('Fetch error:', error); // Log the error for debugging
                Swal.fire(
                    'Error!',
                    error.message || 'Failed to connect to the server or process the report. Please try again.',
                    'error'
                );
            });

        } else {
            // If no file selected for export, show a warning with SweetAlert
            Swal.fire(
                'Warning',
                'Please select at least one file to export.',
                'warning'
            );
        }
    });

});
</script>