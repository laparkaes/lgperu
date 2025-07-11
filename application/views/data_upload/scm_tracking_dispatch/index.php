<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Tracking Dispatch</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">SCM Tracking Dispatch</li>
			</ol>
		</nav>
	</div>
	<!--<div>
		<a href="../user_manual/data_upload/scm_tracking_dispatch/scm_tracking_dispatch_en.pptx" class="text-primary">User Manual</a>
	</div>-->
</div>
<section class="section">
	<div class="row justify-content-center">
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">KLO Tracking Dispatch</h5>
					<form id="form_klo_update">
						<div class="input-group">
							<input class="form-control" type="file" name="attach">
							<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
						</div>
					</form>
					<div class="mt-3">
						<a href="<?= base_url() ?>template/scm_tracking_dispatch_klo_template.xlsx" download="scm_tracking_dispatch_klo_template">
							KLO Tracking Dispatch template
						</a>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">APM Tracking Dispatch</h5>
					<form id="form_apm_update">
						<div class="input-group">
							<input class="form-control" type="file" name="attach">
							<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
						</div>
					</form>
					<div class="mt-3">
						<a href="<?= base_url() ?>template/scm_tracking_dispatch_apm_template.xlsx" download="scm_tracking_dispatch_apm_template">
							APM Tracking Dispatch template
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title"><?= $count_tracking ?> records</h5>					
					</div>

					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">3PL</th>
								<th scope="col">Date</th>
								<th scope="col">Transport</th>
								<th scope="col">Customer</th>
								<th scope="col">Pick Order</th>
								<th scope="col">Guia</th>
								<th scope="col">Model</th>
								<th scope="col">Qty</th>
								<th scope="col">Cbm</th>
								<th scope="col">Status</th>
								<th scope="col">Cita Cliente</th>
								<th scope="col">Cita To</th>
								<th scope="col">H. LLegada</th>
								<th scope="col">H. Descarga</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($tracking as $item){ ?>
							<tr>
								<td><?= $item->_3pl?></td>
								<td><?= $item->date?></td>
								<td><?= $item->transport?></td>
								<td><?= $item->customer?></td>
								<td><?= $item->pick_order?></td>
								<td><?= $item->guide?></td>
								<td><?= $item->model?></td>
								<td><?= $item->qty?></td>
								<td><?= $item->cbm?></td>
								<td><?= $item->status?></td>
								<td><?= $item->client_appointment?></td>
								<td><?= $item->to_appointment?></td>
								<td><?= $item->arrival_time?></td>
								<td><?= $item->download_time?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>

<div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable"> <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dataModalLabel">Comparison</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modal-data-content">
                    <p class="text-center">Load data... <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_klo_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/scm_tracking_dispatch/upload_tracking_klo", "Do you want to upload stock KLO data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/scm_tracking_dispatch");
		});
	});
	
	$("#form_apm_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/scm_tracking_dispatch/upload_tracking_apm", "Do you want to upload stock APM data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/scm_tracking_dispatch");
		});
	});
});
</script>

<script>
// En tu vista principal (donde está el modal y tus otros scripts)
document.addEventListener("DOMContentLoaded", () => {
    var dataModal = document.getElementById('dataModal');

    if (dataModal) {
        dataModal.addEventListener('show.bs.modal', function (event) {
            var modalDataContent = document.getElementById('modal-data-content');
            modalDataContent.innerHTML = '<p class="text-center">Cargando datos... <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></p>';

            fetch('<?php echo base_url("data_upload/lgepr_warehouse_stock/data_comparision"); ?>')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    // 'data' ahora es tu array 'final_result'
                    let tableHtml = '<div class="table-responsive">';
                    tableHtml += '<table class="table table-striped table-hover table-bordered table-sm">';
                    tableHtml += '<thead class="table-dark">';
                    tableHtml += '<tr>';
                    tableHtml += '<th>Warehouse</th>';
                    tableHtml += '<th>Model</th>';
                    tableHtml += '<th>Sub Inventory</th>';
                    tableHtml += '<th class="text-end">LG Stock</th>';
                    tableHtml += '<th class="text-end">Warehouse Stock</th>';
                    tableHtml += '<th class="text-end">Diff</th>';
                    tableHtml += '</tr>';
                    tableHtml += '</thead>';
                    tableHtml += '<tbody>';

                    if (data.length === 0) {
                        tableHtml += '<tr><td colspan="6" class="text-center">No se encontraron datos para mostrar.</td></tr>';
                    } else {
                        data.forEach(row => {
                            let diffClass = '';
                            if (row.diff > 0) {
                                diffClass = 'text-success fw-bold';
                            } else if (row.diff < 0) {
                                diffClass = 'text-danger fw-bold';
                            }
                            tableHtml += '<tr>';
                            tableHtml += `<td>${escapeHtml(row.warehouse)}</td>`; // Usar escapeHtml para seguridad
                            tableHtml += `<td>${escapeHtml(row.model)}</td>`;
                            tableHtml += `<td>${escapeHtml(row.sub_inventory)}</td>`;
                            tableHtml += `<td class="text-end">${escapeHtml(row.lg_stock)}</td>`;
                            tableHtml += `<td class="text-end">${escapeHtml(row.w_stock)}</td>`;
                            tableHtml += `<td class="text-end ${diffClass}">${escapeHtml(row.diff)}</td>`;
                            tableHtml += '</tr>';
                        });
                    }

                    tableHtml += '</tbody>';
                    tableHtml += '</table>';
                    tableHtml += '</div>';

                    modalDataContent.innerHTML = tableHtml; // Inyecta el HTML generado
                })
                .catch(error => {
                    modalDataContent.innerHTML = '<p class="text-danger">Error al cargar los datos: ' + error.message + '</p>';
                    console.error("Fetch error: ", error);
                });
        });

        dataModal.addEventListener('hidden.bs.modal', function (e) {
            document.getElementById('modal-data-content').innerHTML = '';
        });
    } else {
        console.warn("Modal con ID 'dataModal' no encontrado. Asegúrate de que el HTML esté cargado correctamente.");
    }
});

// Función de escape HTML para prevenir XSS al construir HTML con datos del servidor
function escapeHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
</script>