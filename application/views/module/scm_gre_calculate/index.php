<div class="pagetitle">
	<h1>SCM GRE Calculate</h1>
	<nav>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Dashboard</a></li>
			<li class="breadcrumb-item active">Scm GRE Calculate</li>
		</ol>
	</nav>
</div>

<section class="section">
	<div class="col">
		<div class="col-lg-6 col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title text-center">Upload Excel File</h5>
					<form class="row g-3" id="form_guide_upload" href="<?= base_url() ?>" enctype="multipart/form-data">
						<div class="col-md-12">
							<label for="attach_file" class="form-label">Select File</label>
							<input class="form-control" type="file" id="attach_file" name="attach" accept=".xls,.xlsx" required>
							<div class="form-text">Only .xls and .xlsx files are allowed.</div>
						</div>
						<div class="mt-2">
							<a href="<?= base_url() ?>template/scm_gre_calculate.xlsx" download="scm_gre_calculate.xlsx">
								GRE template
							</a>
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
document.addEventListener("DOMContentLoaded", () => {
    // Script for uploading Excel
    $("#form_guide_upload").submit(function(e) {
        e.preventDefault();
        
        // This will show a confirmation dialog first
        ajax_form_warning(this, "module/scm_gre_calculate/upload", "Do you want to upload this data?").done(function(res) {
            swal_redirection(res.type, res.msg, "module/scm_gre_calculate");

            loadExportFiles();
        }).fail(function(jqXHR, textStatus, errorThrown) {
            let errorMessage = 'An error occurred while communicating with the server.';
            if (jqXHR.responseJSON && jqXHR.responseJSON.msg) {
                errorMessage = jqXHR.responseJSON.msg;
            } else if (errorThrown) {
                errorMessage = errorThrown;
            }
            swal_redirection('error', errorMessage, null);
        });
    });
    
    function loadExportFiles() {
        $.ajax({
            url: "<?= base_url('module/scm_gre_calculate/get_uploaded_files') ?>",
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
                    content += `
                        <div class="text-center pt-3">
                            <button id="exportButton" class="btn btn-success" style="display:none;">
                                <i class="bi bi-download"></i> Export
                            </button>
                        </div>`;
                } else {
                    content = "<p>No files available for export.</p>";
                }
                $("#file_list").html(content);
                attachCheckboxListener();
                updateExportButtonVisibility();
            },
            error: function() {
                $("#file_list").html("<p>Error loading files.</p>");
            }
        });
    }
    
    // Function to show/hide the Export button based on checked checkboxes
    function attachCheckboxListener() {
        $(".file-checkbox").off("change").on("change", function() { // Use .off() to prevent duplicate handlers
            updateExportButtonVisibility();
        });
    }

    // Helper function to update export button visibility
    function updateExportButtonVisibility() {
        let checked = $(".file-checkbox:checked").length > 0;
        $("#exportButton").toggle(checked);
    }
    
    // Initial load of files when the page is ready
    loadExportFiles();
    
    $(document).on("click", "#exportButton", function() {
        let selectedFiles = [];

        $(".file-checkbox:checked").each(function() {
            selectedFiles.push($(this).val());
        });

        if (selectedFiles.length === 0) {
            Swal.fire(
                'Warning',
                'Please select a file to export.',
                'warning'
            );
            return;
        }

        Swal.fire({
            title: 'Generating Report...',
            html: 'Please wait, your Excel file is being prepared.',
            icon: 'info',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        fetch('<?= base_url('module/scm_gre_calculate/export_excel') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                //'X-CSRF-TOKEN': 'your-csrf-token-here' 
            },
            body: new URLSearchParams({
                files: JSON.stringify(selectedFiles) // Send the array of files as JSON string
            })
        })
        .then(response => {
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                return response.json(); // Parse the response as JSON
            } else {
                throw new Error("Server response was not JSON. Check server logs for errors.");
            }
        })
        .then(data => {
            Swal.close();

            if (data.type === 'success') {
                const downloadLink = document.createElement('a');
                downloadLink.href = data.downloadUrl;
                downloadLink.download = data.fileNameOnServer || 'report.xlsx'; // Use server-provided filename
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);

                Swal.fire({
                    title: 'Download Generated!',
                    text: 'Your Excel file has been generated and the download should start now.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
						if (data.fileNameOnServer) {
                            fetch('<?= base_url('module/scm_gre_calculate/delete_temp_excel_file') ?>', {
                                method: 'POST',
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                    // 'X-CSRF-TOKEN': 'your-csrf-token-here' 
                                },
                                body: new URLSearchParams({
                                    fileName: data.fileNameOnServer
                                })
                            })
                            .then(deleteResponse => deleteResponse.json())
                            .then(deleteData => {
                                if (deleteData.type === 'error') {
                                    console.error('Error deleting temp file:', deleteData.msg);
                                    // Opcional: mostrar un SweetAlert de error de borrado
                                    Swal.fire('Deletion Error', deleteData.msg, 'error');
                                } else {
                                    console.log('Temp file deletion successful or file not found:', deleteData.msg);
                                }
                            })
                            .catch(deleteError => {
                                console.error('Network error during temp file deletion:', deleteError);
                                Swal.fire('Network Error', 'Could not reach server to delete temp file.', 'error');
                            })
                            .finally(() => {
                                window.location.reload();
                            });
                        } else {
                            window.location.reload();
                        }
                    }
                });
            } else {
                Swal.fire(
                    'Error',
                    data.msg || 'An unknown error occurred.',
                    'error'
                );
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Export Excel Error:', error);
            Swal.fire(
                'Error',
                'There was an issue generating your report. Please try again or contact support. ' + error.message,
                'error'
            );
        });
    });
});
</script>
