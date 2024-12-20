<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Create Activity</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item">ISM - Activity Management</li>
				<li class="breadcrumb-item active">Create</li>
			</ol>
		</nav>
	</div>
	<div>
		<a type="button" class="btn btn-success" href="<?= base_url() ?>module/ism_activity_management">
			<i class="bi bi-arrow-left"></i>
		</a>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">New Activity</h5>
					<form class="row" id="form_new_activity">
						<div class="col-md-8">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Activity Information</h5>
									<div class="row g-3">
										<div class="col-md-12">
											<label class="form-label">Title</label>
											<input class="form-control" name="title" required>
										</div>
										<div class="col-md-4">
											<label class="form-label">PR PIC</label>
											<?php $list = ["HSAD", "LG"]; sort($list); ?>
											<select class="form-select" name="pr_pic">
												<option value="" selected="">Choose...</option>
												<?php foreach($list as $item){ ?>
												<option value="<?= $item ?>"><?= $item ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="col-md-4">
											<label class="form-label">PR Number</label>
											<input class="form-control" name="pr_number">
										</div>
										<div class="col-md-4">
											<label class="form-label">PR Buyer</label>
											<?php $list = ["Andy", "Jeter"]; sort($list); ?>
											<select class="form-select" name="pr_buyer">
												<option value="" selected="">Choose...</option>
												<?php foreach($list as $item){ ?>
												<option value="<?= $item ?>"><?= $item ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="col-md-4">
											<label class="form-label">Approval N°</label>
											<input class="form-control" name="approval_no">
										</div>
										<div class="col-md-4">
											<label class="form-label">Retail</label>
											<?php $list = ["Hiraoka", "Saga", "Ripley", "Oechsle", "Conecta", "Plaza Vea", "Tottus", "Metro", "Wong", "Promart", "Sodimac", "Credivargas", "Estilos", "Rubi"]; sort($list); $list[] = "Almacen"; $list[] = "Varios"; ?>
											<select class="form-select" name="retail">
												<option value="" selected="">Choose...</option>
												<?php foreach($list as $item){ ?>
												<option value="<?= $item ?>"><?= $item ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="col-md-4">
											<label class="form-label">Category</label>
											<?php $list = ["TV", "AV", "REF", "WM", "Cooking", "MWO", "HA", "HE"]; $list[] = "Common"; ?>
											<select class="form-select" name="category">
												<option value="" selected="">Choose...</option>
												<?php foreach($list as $item){ ?>
												<option value="<?= $item ?>"><?= $item ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="col-md-4">
											<label class="form-label">Project Type</label>
											<?php $list = ["0 Other", "1 Mantenimiento", "2 Transportation", "3 Graphic", "4 Branding", "5 POP", "7 Production", "8 Installation", "9 Remodelacion /Actualization"]; ?>
											<select class="form-select" name="project_type">
												<option value="" selected="">Choose...</option>
												<?php foreach($list as $item){ ?>
												<option value="<?= $item ?>"><?= $item ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="col-md-4">
											<label class="form-label">Currency</label>
											<?php $list = ["PEN", "USD"]; sort($list); ?>
											<select class="form-select" name="currency">
												<?php foreach($list as $item){ ?>
												<option value="<?= $item ?>"><?= $item ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="col-md-4">
											<label class="form-label">Amount</label>
											<input class="form-control" name="amount" value="0.00">
										</div>
										<div class="col-md-8">
											<label class="form-label">Period</label>
											<div class="input-group">
												<input type="date" class="form-control" name="period_from">
												<span class="input-group-text">~</span>
												<input type="date" class="form-control" name="period_to">
											</div>
										</div>
										<div class="col-md-4">
											<label class="form-label">Activity Status</label>
											<?php $list = ["En proceso", "Aprobado", "Finalizado", "Cancelado"]; ?>
											<select class="form-select" name="activity_status">
												<?php foreach($list as $item){ ?>
												<option value="<?= $item ?>"><?= $item ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card">
								<div class="card-body">
									<h5 class="card-title">Invoice</h5>
									<div class="row g-3">
										<div class="col-md-6">
											<label class="form-label">Invoice Number</label>
											<input class="form-control" name="invoice_number">
										</div>
										<div class="col-md-6">
											<label class="form-label">Invoice Date</label>
											<input type="date" class="form-control" name="invoice_date">
										</div>
										<div class="col-md-6">
											<label class="form-label">Invoice Status</label>
											<?php $list = ["En proceso", "Aprobado", "Cancelado"]; ?>
											<select class="form-select" name="invoice_status">
												<?php foreach($list as $item){ ?>
												<option value="<?= $item ?>"><?= $item ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="col-md-6">
											<label class="form-label">Vendor</label>
											<?php $list = ["ACTION VISUAL NOW E.I.R.L.", "ACTIVA GROUP S.A.C.", "ARK INSIDE S.R.L", "CONSORCIO DE NEGOCIOS Y PROYECTOS S.A.C.", "HS AD LATIN AMERICA S.A. SUCURSAL DEL PERU", "INDESIGN PROJECTS PERU S.A.C", "INTEGRACION LOGISTICA INLOG S.A.C", "LB FABRICANTES", "METAGRAF S.A.C.", "MORRIS PERU S.A.C.", "METRICA COMUNICACION ESTRATEGICA S.A.C.", "QUALITY ZONE", "PEVISO INGENIEROS S.A.C", "SAR AMBIENTAL SA", "RISING SUN BUSINESS GROUP S.A.C", "SISTEMA DE IMPRESIONES S.A.", "SURPACK S.A", "URBANA COMUNICACION VISUAL S.A.C.", "TBS ARQUITECTURA S.A.C.", "America Móvil Perú S.A.C", "DHL EXPRESS PERU SAC", "DONG IL DESIGN CO.,LTD.", "OHSUNG ELECTRONICS U.S.A., INC.", "LINE GNC CO., LTD", "MAERSK LOGISTICS & SERVICES PERU S.A", "PALACIOS Y ASOCIADOS AGENTES DE ADUANA SA"]; sort($list); ?>
											<select class="form-select" name="vendor">
												<option value="" selected="">Choose...</option>
												<?php foreach($list as $item){ ?>
												<option value="<?= $item ?>"><?= $item ?></option>
												<?php } ?>
											</select>
										</div>
										<div class="col-md-12">
											<label class="form-label">Invoice Description</label>
											<input class="form-control" name="invoice_description">
										</div>
										<div class="col-md-12">
											<label class="form-label">Detail</label>
											<textarea class="form-control" name="detail"></textarea>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="text-center pt-3">
							<button type="submit" class="btn btn-primary">Submit</button>
							<button type="reset" class="btn btn-secondary">Reset</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_new_activity").submit(function(e) {
		e.preventDefault();
		$("#form_new_activity .sys_msg").html("");
		ajax_form_warning(this, "module/ism_activity_management/add_activity", "Do you want to add new activity?").done(function(res) {
			swal_redirection(res.type, res.msg, res.url);
		});
	});
});
</script>