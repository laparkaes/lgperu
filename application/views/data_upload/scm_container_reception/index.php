<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Container Reception</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">SCM Container Reception</li>
			</ol>
		</nav>
	</div>
	<!--<div>
		<a href="../user_manual/data_upload/scm_container_status/scm_container_status_en.pptx" class="text-primary">User Manual</a>
	</div>-->
</div>
<section class="section">	
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title"><?= $count_tracking ?> records</h5>	

						<form id="form_container_update">
							<div class="input-group">
								<a class="btn btn-success" href="<?= base_url() ?>template/scm_container_reception_template.xlsx" download="scm_container_reception_template"><i class="bi bi-file-earmark-spreadsheet"></i></a>
								<input class="form-control" type="file" name="attach">
								<button type="submit" class="btn btn-primary me-2"><i class="bi bi-upload"></i></button>
							</div>
						</form>
					</div>
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">3PL</th>
								<th scope="col">Contenedor Recepcionado</th>
								<th scope="col">Cliente</th>
								<th scope="col">Destino</th>
								<th scope="col">Lugar de Descarga</th>
								<th scope="col">Fecha de Llegada</th>
								<th scope="col">Hora</th>
								<th scope="col">Orden</th>
								<th scope="col">Line</th>
								<th scope="col">Terminal Devolución</th>
								<th scope="col">Vacio Entregado</th>
								<th scope="col">Placa</th>
								<th scope="col">Updated</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($tracking as $item){ ?>
							<tr>
								<td><?= $item->_3pl?></td>
								<td><?= $item->received_container?></td>
								<td><?= $item->customer?></td>
								<td><?= $item->destination?></td>
								<td><?= $item->discharge_location?></td>
								<td><?= $item->arrival_date?></td>
								<td><?= $item->arrival_time?></td>
								<td><?= $item->order_no?></td>
								<td><?= $item->line?></td>
								<td><?= $item->return_terminal?></td>
								<td><?= $item->empty_delivered?></td>
								<td><?= $item->placa?></td>
								<td><?= $item->updated?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {	
	$("#form_container_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/scm_container_reception/upload_container", "Do you want to upload Container Reception data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/scm_container_reception");
		});
	});
});
</script>