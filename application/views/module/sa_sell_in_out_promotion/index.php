<div class="pagetitle">
	<h1> SA Promotion </h1>
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
                                <input class="form-check-input" type="checkbox" name="selected_files[]" value="${file}">
                                <label class="form-check-label">${file}</label>
                            </div>`;
                    });
                } else {
                    content = "<p>No files available.</p>";
                }
                $("#file_list").html(content);
            },
            error: function() {
                $("#file_list").html("<p>Error loading files.</p>");
            }
        });
    }

    // Llamar a la función al cargar la página
    loadExportFiles();
    
    // Manejar la carga del archivo
    $("#form_ar_promotion_update").submit(function(e) {
        e.preventDefault();
        ajax_form_warning(this, "module/sa_sell_in_out_promotion/upload", "Do you want to generate promotion calculation report?")
        .done(function(res) {
            swal_redirection(res.type, res.msg, "module/sa_sell_in_out_promotion");
            loadExportFiles(); // Recargar archivos después de subir
        });
    });
});
</script>

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
                    // Agregar botón Export (oculto por defecto)
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
                attachCheckboxListener(); // Agregar evento a los checkboxes
            },
            error: function() {
                $("#file_list").html("<p>Error loading files.</p>");
            }
        });
    }

    // Función para mostrar/ocultar el botón Export
    function attachCheckboxListener() {
        $(".file-checkbox").on("change", function() {
            let checked = $(".file-checkbox:checked").length > 0;
            $("#exportButton").toggle(checked);
        });
    }

    // Llamar a la función al cargar la página
    loadExportFiles();

    // Manejar la carga del archivo
    $("#form_ar_promotion_update").submit(function(e) {
        e.preventDefault();
        ajax_form_warning(this, "module/sa_sell_in_out_promotion/upload", "Do you want to generate promotion calculation report?")
        .done(function(res) {
            swal_redirection(res.type, res.msg, "module/sa_sell_in_out_promotion");
            loadExportFiles(); // Recargar archivos después de subir
        });
    });

    $(document).on("click", "#exportButton", function() {
		let selectedFiles = $(".file-checkbox:checked").map(function() {
			return $(this).val();
		}).get();

		if (selectedFiles.length > 0) {
			let form = $("<form>").attr({
				method: "POST",
				action: "<?= base_url('module/sa_sell_in_out_promotion/generate_excel') ?>"
			}).append(
				$("<input>").attr({ type: "hidden", name: "files", value: JSON.stringify(selectedFiles) })
			);

			$("body").append(form);
			form.submit();
			form.remove();
		} else {
			alert("Seleccione al menos un archivo para exportar.");
		}
	});

});
</script>