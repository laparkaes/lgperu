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

<div class="container-fluid mt-5">
	
    <div class="row">
        <div class="col-12 col-lg-9 mx-auto" id="form-column">
            <div class="card p-4 shadow-sm">
                <h5 class="card-title text-center">Document Export (SOVOS)</h5>
                
                <form class="row g-3 justify-content-center" action="<?php echo site_url('module/lgepr_sovos_invoice/process_request'); ?>" method="post">                  
                    <div class="col-12 col-md-6">
						<label class="form-label d-block">Document Type</label>
						
						<div style="max-height: 200px; overflow-y: auto; padding: 5px; border-radius: 5px;">
							<?php
							$is_first = true;
							foreach ($documents as $code => $name) { 
								$value = is_array($documents) ? $code : $name;
								$display = is_array($documents) ? $name : $name;
							?>
								<div class="form-check">
									<input 
										class="form-check-input" 
										type="radio" 
										name="document_type" 
										id="doc_type_<?php echo htmlspecialchars($value); ?>" 
										value="<?php echo htmlspecialchars($value); ?>" 
										required 
										<?php echo $is_first ? 'checked' : ''; ?>
									>
									<label 
										class="form-check-label" 
										for="doc_type_<?php echo htmlspecialchars($value); ?>"
									>
										<?php echo htmlspecialchars($display); ?>
									</label>
								</div>
							<?php 
								$is_first = false;
							} 
							?>
						</div>
					</div>

                    <div class="col-12 col-md-6">
						<label for="document_number" class="form-label">Document Number</label>
						<div class="input-group">
							<input type="text" class="form-control" id="document_number" name="document_number" placeholder="F001-00000001" required>
						</div>
					</div>

                    <div class="col-12 text-center mt-4">
						<button type="submit" class="btn btn-primary me-2" id="find-button">
							<i class="bi bi-search me-1"></i> Find Document
						</button>
						
						<a href="#" class="btn btn-danger disabled me-2" id="download-pdf-button" style="display: none;" target="_blank">
							<i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
						</a>

						<a href="#" class="btn btn-success disabled me-2" id="download-xml-button" style="display: none;" target="_blank">
							<i class="bi bi-filetype-xml me-1"></i> Download XML
						</a>

						<a href="<?= site_url('module/lgepr_sovos_invoice/index'); ?>" class="btn btn-outline-primary" id="clear-button" style="display: none;">
							<i class="bi bi-arrow-clockwise me-1"></i> Refresh
						</a>
					</div>
                </form>
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
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const findButton = document.getElementById('find-button');
    const downloadPdfButton = document.getElementById('download-pdf-button');
    const downloadXmlButton = document.getElementById('download-xml-button');
    const clearButton = document.getElementById('clear-button');
    
	const resultMessage = '<?php echo addslashes($result_message); ?>';
	const statusCode = '<?php echo addslashes($status_code); ?>';
	const pdfLink = '<?php echo addslashes($pdf_link); ?>';
	const xmlLink = '<?php echo addslashes($xml_link); ?>';
    
    
    // ----------------------------------------------------
    // LÓGICA DE VISIBILIDAD
    // ----------------------------------------------------
    function updateButtonVisibility(showClear = false, showPdf = false, showXml = false, showFind = true) {
        clearButton.style.display = showClear ? 'inline-block' : 'none';
        findButton.style.display = showFind ? 'inline-block' : 'none';
        
        downloadPdfButton.style.display = showPdf ? 'inline-block' : 'none';
        if (showPdf) downloadPdfButton.classList.remove('disabled');

        downloadXmlButton.style.display = showXml ? 'inline-block' : 'none';
        if (showXml) downloadXmlButton.classList.remove('disabled');
    }

    // ----------------------------------------------------
    // PANTALLA DE CARGA AL HACER SUBMIT
    // ----------------------------------------------------
    form.addEventListener('submit', function(e) {
        if (form.checkValidity()) {
            Swal.fire({
                title: 'Searching Documents',
                text: 'Performing query (PDF and XML), please wait...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    });
    
    
    // ----------------------------------------------------
    // PROCESAMIENTO DE RESPUESTA
    // ----------------------------------------------------
    if (statusCode && statusCode !== 'null') {
        Swal.close();
        
        // Verificar qué enlaces se recibieron
        let pdfReady = pdfLink && pdfLink !== 'null' && pdfLink.length > 0;
        let xmlReady = xmlLink && xmlLink !== 'null' && xmlLink.length > 0;
        
        updateButtonVisibility(true, false, false, false); // Mostrar solo Clear
        
        if (statusCode === '200' && (pdfReady || xmlReady)) {
            if (pdfReady) {
                downloadPdfButton.href = pdfLink;
            }
            if (xmlReady) {
                downloadXmlButton.href = xmlLink;
            }
            
            updateButtonVisibility(true, pdfReady, xmlReady, false);

            Swal.fire({
                icon: 'success',
                title: 'Document(s) Found!',
                text: 'The PDF and/or XML links are available for download.',
                confirmButtonText: 'Ok'
            });

        } else if (statusCode === '404' || (statusCode === '200' && !pdfReady && !xmlReady)) {
            Swal.fire({
                icon: 'warning',
                title: 'Operation Failed',
                text: resultMessage,
                footer: 'The document was not found by SOVOS in the requested format.',
                confirmButtonText: 'Close'
            });
            
        } else if (statusCode === '500' || statusCode === '503') {
            Swal.fire({
                icon: 'error',
                title: 'System Error',
                html: 'Failed to connect or process the SOAP response. <br> Details: <strong>' + resultMessage + '</strong>',
                confirmButtonText: 'OK'
            });
        }
    } else {
        updateButtonVisibility(false, false, false, true);
    }   
});
</script>