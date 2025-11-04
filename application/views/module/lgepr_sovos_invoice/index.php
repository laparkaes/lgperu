<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>SOVOS Documents</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">SOVOS Document</li>
			</ol>
		</nav>
	</div>
	
	<div>
		<a href="<?= base_url() ?>user_manual/module/lgepr_sovos_invoice/lgepr_sovos_invoice_en.pptx" class="btn btn-sm btn-outline-primary p-2">
            <i class="bi bi-file-earmark-text me-1"></i> User Manual
        </a>
	</div>
</div>

<div class="container-fluid">
    <div class="row g-4 justify-content-center">

        <div class="col-12 col-lg-5">
            <div class="card p-3 shadow-sm h-100">
                <h5 class="card-title text-center text-dark mb-3">Single Document Search</h5>
                
                <form class="row g-3" action="<?php echo site_url('module/lgepr_sovos_invoice/process_request'); ?>" method="post">
                    
                    <div class="col-12">
                        <label class="form-label d-block">Document Type</label>
                        <div style="max-height: 200px; overflow-y: auto; padding: 5px; border: 1px solid #dee2e6; border-radius: 5px;">
                            <?php
                            $is_first = true;
                            foreach ($documents as $code => $name) {
                                $value = is_array($documents) ? $code : $name;
                                $display = is_array($documents) ? $name : $name;
                            ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="document_type" id="doc_type_<?php echo htmlspecialchars($value); ?>" value="<?php echo htmlspecialchars($value); ?>" required
                                    <?php echo $is_first ? 'checked' : ''; ?> >
                                <label class="form-check-label" for="doc_type_<?php echo htmlspecialchars($value); ?>">
                                    <?php echo htmlspecialchars($display); ?>
                                </label>
                            </div>
                            <?php $is_first = false; } ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="document_number" class="form-label">Document Number</label>
                        <input type="text" class="form-control" id="document_number" name="document_number" placeholder="F001-00000001" required>
                    </div>
                    
                    <div class="col-12">
                        <hr class="my-3">
                        <label for="pdf_url_output" class="form-label">PDF URL</label>
                        <input type="text" class="form-control form-control-sm" id="pdf_url_output" readonly placeholder="PDF link will appear here">
                    </div>
                    <div class="col-12">
                        <label for="xml_url_output" class="form-label">XML URL</label>
                        <input type="text" class="form-control form-control-sm" id="xml_url_output" readonly placeholder="XML link will appear here">
                    </div>

                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-primary me-2" id="find-button">
                            <i class="bi bi-search me-1"></i> Find Document
                        </button>
                        
                        <a href="#" class="btn btn-danger me-2" id="download-pdf-button" style="display: none;" target="_blank">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
                        </a>

                        <a href="#" class="btn btn-success me-2" id="download-xml-button" style="display: none;" target="_blank">
                            <i class="bi bi-filetype-xml me-1"></i> Download XML
                        </a>

                        <a href="<?= site_url('module/lgepr_sovos_invoice/index'); ?>" class="btn btn-outline-primary" id="clear-button" style="display: none;">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                        </a>
                    </div>

                </form>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card p-3 shadow-sm h-100">
                <h5 class="card-title text-center text-dark mb-3">Massive Search</h5>

                <form class="row g-3" id="massive-search-form" action="<?php echo site_url('module/lgepr_sovos_invoice/process_massive_request'); ?>" method="post">
                    
                    <div class="col-12">
                        <label class="form-label d-block">Document Type</label>
                        <div style="max-height: 200px; overflow-y: auto; padding: 5px; border: 1px solid #dee2e6; border-radius: 5px;">
                            <?php
                            $is_first_massive = true;
                            foreach ($documents as $code => $name) {
                                $value = is_array($documents) ? $code : $name;
                                $display = is_array($documents) ? $name : $name;
                            ?>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="document_type_massive" id="doc_type_massive_<?php echo htmlspecialchars($value); ?>" value="<?php echo htmlspecialchars($value); ?>" required
                                    <?php echo $is_first_massive ? 'checked' : ''; ?> >
                                <label class="form-check-label" for="doc_type_massive_<?php echo htmlspecialchars($value); ?>">
                                    <?php echo htmlspecialchars($display); ?>
                                </label>
                            </div>
                            <?php $is_first_massive = false; } ?>
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Document Number Range</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text">From:</span>
                            <input type="text" class="form-control" id="document_number_start" name="document_number_start" placeholder="TM01-00053" required>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text">To:</span>
                            <input type="text" class="form-control" id="document_number_end" name="document_number_end" placeholder="TM01-00082" required>
                        </div>
                    </div>
                    
                    <div class="col-12" style="min-height: 120px;">
                        </div>

                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-primary me-2" id="massive-process-button">
                            <i class="bi bi-gear me-1"></i> Start Processing
                        </button>
                        <a href="<?= site_url('module/lgepr_sovos_invoice/index'); ?>" class="btn btn-outline-primary" id="clear-massive-button" style="display: none;">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                        </a>
                    </div>
                    
                </form>
                
                <div class="row mt-4 justify-content-center">
                    <div class="col-12" id="massive-results-container">
                        </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php if (isset($request_xml) && !empty($request_xml)): ?>
    <div class="container-fluid mt-5" style="display:None">
        <div class="card p-4 mx-auto">
            <h5 class="card-title text-center text-danger">Debugging Information (XML)</h5>
            <p><strong>Status:</strong> <?php echo $debug_message ?? 'N/A'; ?></p>
            
            <h6 class="mt-3">XML Request (Sent to SOVOS):</h6>
            <pre class="bg-light p-3 border rounded overflow-auto small"><code><?php echo htmlspecialchars($request_xml); ?></code></pre>

            <h6 class="mt-3">XML Response (Received from SOVOS):</h6>
            <pre class="bg-light p-3 border rounded overflow-auto small"><code><?php echo htmlspecialchars($response_xml); ?></code></pre>
        </div>
    </div>
<?php endif; ?>

<?php
$status_code = $status_code ?? null;
$pdf_link = $pdf_link ?? null;
$xml_link = $xml_link ?? null;
$result_message = $debug_message ?? "An unknown error occurred.";
@$debug_message = $debug_message ?? null;
@$massive_status_code = $massive_status_code ?? null;
@$massive_zip_link = $massive_zip_link ?? null;
@$massive_debug_message = $massive_debug_message ?? null;
@$massive_errors = json_encode($massive_errors ?? []);
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- REFERENCIAS INDIVIDUALES ---
    const findButton = document.getElementById('find-button');
    const downloadPdfButton = document.getElementById('download-pdf-button');
    const downloadXmlButton = document.getElementById('download-xml-button');
    const clearButton = document.getElementById('clear-button');
	const pdfUrlOutput = document.getElementById('pdf_url_output');
    const xmlUrlOutput = document.getElementById('xml_url_output');
    
    // Variables PHP(Individual)
    const statusCode = '<?php echo addslashes($status_code); ?>';
    const pdfLink = '<?php echo addslashes($pdf_link); ?>';
    const xmlLink = '<?php echo addslashes($xml_link); ?>';
    const debugMessage = '<?php echo addslashes($debug_message); ?>';

    function updateSingleButtonVisibility(showClear, showDownload) {
        if (clearButton) clearButton.style.display = showClear ? 'inline-block' : 'none';
        
        if (showDownload) {
			if (downloadPdfButton) downloadPdfButton.style.display = pdfLink ? 'inline-block' : 'none';
			if (downloadXmlButton) downloadXmlButton.style.display = xmlLink ? 'inline-block' : 'none';
		} else {
			if (downloadPdfButton) downloadPdfButton.style.display = 'none';
			if (downloadXmlButton) downloadXmlButton.style.display = 'none';
        }
    }
	
	function clearUrlOutputs() {
        if (pdfUrlOutput) pdfUrlOutput.value = '';
        if (xmlUrlOutput) xmlUrlOutput.value = '';
    }

    // ----------------------------------------------------
    // LÓGICA: BÚSQUEDA INDIVIDUAL
    // ----------------------------------------------------
    if (statusCode && statusCode !== 'null') {
        if (statusCode === '200') {
            updateSingleButtonVisibility(true, true);
			if (pdfUrlOutput) pdfUrlOutput.value = pdfLink;
            if (xmlUrlOutput) xmlUrlOutput.value = xmlLink;
            downloadPdfButton.href = pdfLink;
			downloadXmlButton.href = xmlLink;
            Swal.fire('Success', debugMessage, 'success');
        } else if (statusCode === '404') {
            updateSingleButtonVisibility(true, false);
			clearUrlOutputs();
			let title = (statusCode === '404') ? 'Not Found' : 'Error';
            let icon = (statusCode === '404') ? 'warning' : 'error';
            Swal.fire('Not Found', debugMessage, 'warning');
        } else {
            updateSingleButtonVisibility(true, false);
			clearUrlOutputs();
            Swal.fire('Error', debugMessage, 'error');
        }
        if (findButton) findButton.style.display = 'none';
    } else {
        updateSingleButtonVisibility(false, false);
        if (findButton) findButton.style.display = 'inline-block';
    }


    // ----------------------------------------------------
    // LÓGICA: BÚSQUEDA MASIVA
    // ----------------------------------------------------
    
    // ---  REFERENCIAS MASIVAS ---
    const massiveSearchForm = document.getElementById('massive-search-form');
    const massiveProcessButton = document.getElementById('massive-process-button');
    const clearMassiveButton = document.getElementById('clear-massive-button');
    const massiveButtonContainer = massiveProcessButton ? massiveProcessButton.parentNode : null; 
    const massiveResultsContainer = document.getElementById('massive-results-container');
    const massiveErrorsList = document.getElementById('massive-errors-list');
    const massiveStatusCode = '<?php echo addslashes($massive_status_code); ?>';
    const massiveZipLink = '<?php echo addslashes($massive_zip_link); ?>';
    const massiveDebugMessage = '<?php echo addslashes($massive_debug_message); ?>';
    const massiveErrors = JSON.parse('<?php echo $massive_errors; ?>'); 

    function updateMassiveButtonVisibility(showClear = false, showProcess = true) {
        if (clearMassiveButton) clearMassiveButton.style.display = showClear ? 'inline-block' : 'none';
        if (massiveProcessButton) massiveProcessButton.style.display = showProcess ? 'inline-block' : 'none';

        const oldDownloadButton = document.getElementById('download-zip-button');
        if (oldDownloadButton) oldDownloadButton.remove();
    }

    if (massiveSearchForm) {
        massiveSearchForm.addEventListener('submit', function(e) {
            const oldDownloadButton = document.getElementById('download-zip-button');
            if (oldDownloadButton) oldDownloadButton.remove();

            if (massiveSearchForm.checkValidity()) {
                if (massiveProcessButton) massiveProcessButton.classList.add('disabled');
                Swal.fire({
                    title: 'Processing Massive Search',
                    text: 'Analyzing document range and fetching files. This may take several minutes...',
                    icon: 'info',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }
        });
    }

    if (massiveStatusCode && massiveStatusCode !== 'null') {
        Swal.close();

        updateMassiveButtonVisibility(true, false); 
        
        if (massiveErrorsList) massiveErrorsList.innerHTML = ''; 
        if (massiveResultsContainer) massiveResultsContainer.innerHTML = '';

        if (massiveStatusCode === '200') {
            if (massiveButtonContainer && clearMassiveButton) {
                const downloadZipButton = document.createElement('a');
                downloadZipButton.href = massiveZipLink;
                downloadZipButton.className = 'btn btn-success me-2'; 
                downloadZipButton.id = 'download-zip-button';
                downloadZipButton.setAttribute('download', 'massive_documents.zip');
                downloadZipButton.innerHTML = '<i class="bi bi-download me-1"></i> Download ZIP';
                massiveButtonContainer.insertBefore(downloadZipButton, clearMassiveButton);
            }
            if (massiveResultsContainer) {
                massiveResultsContainer.innerHTML = `<div class="alert alert-success text-center">
                    ${massiveDebugMessage}
                </div>`;
            }

            if (massiveErrors && massiveErrors.length > 0) {
                let errorHtml = '<div class="alert alert-warning" role="alert">';
                errorHtml += `<h6> ${massiveErrors.length} Document(s) Not Found:</h6>`;
                errorHtml += '<ul class="list-group list-group-flush small">';
                
                const displayErrors = massiveErrors.slice(0, 10);
                displayErrors.forEach(docNumber => {
                    errorHtml += `<li class="list-group-item text-danger">${docNumber}</li>`;
                });
                
                if (massiveErrors.length > 10) {
                    errorHtml += `<li class="list-group-item text-muted">... and ${massiveErrors.length - 10} more documents.</li>`;
                }
                errorHtml += '</ul></div>';
                
                if (massiveErrorsList) massiveErrorsList.innerHTML = errorHtml;
            }

        } else if (massiveStatusCode === '404' || massiveStatusCode === '500') {
            let statusIcon = (massiveStatusCode === '404') ? 'warning' : 'error';
            let statusTitle = (massiveStatusCode === '404') ? 'No Documents Found' : 'Processing Error';
            
            Swal.fire({
                icon: statusIcon,
                title: statusTitle,
                html: `<p>${massiveDebugMessage}</p>`,
                confirmButtonText: 'OK'
            });
        }
        
    } else {
        updateMassiveButtonVisibility(false, true);
    }
    
    const urlHash = window.location.hash;
    const isMassiveTabActive = urlHash === '#massive-search-tab' || 
                               (massiveStatusCode && massiveStatusCode !== 'null');

    if (isMassiveTabActive) {
        const massiveTabTrigger = document.getElementById('massive-search-tab'); 
        if (massiveTabTrigger) {
            setTimeout(() => {
                 const tab = new bootstrap.Tab(massiveTabTrigger);
                 tab.show();
            }, 10);
        }
    }
});
</script>