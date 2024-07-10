<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Create Activity</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">ISM - Activity Management</li>
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
					<form class="row g-3" id="form_new_activity">
						<div class="col-md-6">
							<label class="form-label">Title</label>
							<input class="form-control" name="model" required>
						</div>
						<div class="col-md-2">
							<label class="form-label">PR PIC</label>
							<?php $list = ["HSAD", "LG"]; sort($list); ?>
							<select class="form-select" name="category_id">
								<option value="" selected="">Choose...</option>
								<?php foreach($list as $item){ ?>
								<option value="<?= $item ?>"><?= $item ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-2">
							<label class="form-label">PR Number</label>
							<input class="form-control" name="model">
						</div>
						<div class="col-md-2">
							<label class="form-label">PR Buyer</label>
							<?php $list = ["Andy", "Jetter"]; sort($list); ?>
							<select class="form-select" name="category_id">
								<option value="" selected="">Choose...</option>
								<?php foreach($list as $item){ ?>
								<option value="<?= $item ?>"><?= $item ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-2">
							<label class="form-label">Approval</label>
							<input class="form-control" name="model">
						</div>
						<div class="col-md-2">
							<label class="form-label">Retail</label>
							<?php $list = ["Hiraoka", "Saga", "Ripley", "Oechsle", "Conecta", "Plaza Vea", "Tottus", "Metro", "Wong", "Promart", "Sodimac", "Credivargas", "Estilos", "Rubi"]; sort($list); $list[] = "Almacen"; $list[] = "Varios"; ?>
							<select class="form-select" name="category_id">
								<option value="" selected="">Choose...</option>
								<?php foreach($list as $item){ ?>
								<option value="<?= $item ?>"><?= $item ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-2">
							<label class="form-label">Category</label>
							<?php $list = ["TV", "AV", "REF", "WM", "Cooking", "MWO", "HA", "HE", "Common"]; sort($list); ?>
							<select class="form-select" name="category_id">
								<option value="" selected="">Choose...</option>
								<?php foreach($list as $item){ ?>
								<option value="<?= $item ?>"><?= $item ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Días Emitidos</label>
							<input class="form-control" name="model">
						</div>
						<div class="col-md-6">
							<label class="form-label">Period</label>
							<input type="date" class="form-control" name="model">
						</div>
						<div class="col-md-6">
							<label class="form-label">Currency</label>
							<?php $list = ["PEN", "USD"]; sort($list); ?>
							<select class="form-select" name="category_id">
								<option value="" selected="">Choose...</option>
								<?php foreach($list as $item){ ?>
								<option value="<?= $item ?>"><?= $item ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Amount</label>
							<input class="form-control" name="model">
						</div>
						<div class="col-md-6">
							<label class="form-label">Approval Status</label>
							<?php $list = ["Aprobado", "En proceso", "Cancelado"]; sort($list); ?>
							<select class="form-select" name="category_id">
								<option value="" selected="">Choose...</option>
								<?php foreach($list as $item){ ?>
								<option value="<?= $item ?>"><?= $item ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Acitivty Status</label>
							<?php $list = ["En proceso", "Finalizado", "Cancelado"]; sort($list); ?>
							<select class="form-select" name="category_id">
								<option value="" selected="">Choose...</option>
								<?php foreach($list as $item){ ?>
								<option value="<?= $item ?>"><?= $item ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Vendor</label>
							<?php $list = ["ACTION VISUAL NOW E.I.R.L.", "ACTIVA GROUP S.A.C.", "ARK INSIDE S.R.L", "CONSORCIO DE NEGOCIOS Y PROYECTOS S.A.C.", "HS AD LATIN AMERICA S.A. SUCURSAL DEL PERU", "INDESIGN PROJECTS PERU S.A.C", "INTEGRACION LOGISTICA INLOG S.A.C", "LB FABRICANTES", "METAGRAF S.A.C.", "MORRIS PERU S.A.C.", "METRICA COMUNICACION ESTRATEGICA S.A.C.", "QUALITY ZONE", "PEVISO INGENIEROS S.A.C", "SAR AMBIENTAL SA", "RISING SUN BUSINESS GROUP S.A.C", "SISTEMA DE IMPRESIONES S.A.", "SURPACK S.A", "URBANA COMUNICACION VISUAL S.A.C.", "TBS ARQUITECTURA S.A.C.", "America Móvil Perú S.A.C", "DHL EXPRESS PERU SAC", "DONG IL DESIGN CO.,LTD.", "OHSUNG ELECTRONICS U.S.A., INC.", "LINE GNC CO., LTD", "MAERSK LOGISTICS & SERVICES PERU S.A", "PALACIOS Y ASOCIADOS AGENTES DE ADUANA SA"]; sort($list); ?>
							<select class="form-select" name="category_id">
								<option value="" selected="">Choose...</option>
								<?php foreach($list as $item){ ?>
								<option value="<?= $item ?>"><?= $item ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Glosa</label>
							<input class="form-control" name="model">
						</div>
						<div class="col-md-6">
							<label class="form-label">Emisión</label>
							<input class="form-control" name="model">
						</div>
						<div class="col-md-6">
							<label class="form-label">Factura</label>
							<input class="form-control" name="model">
						</div>
						<div class="col-md-6">
							<label class="form-label">Status 2</label>
							<input class="form-control" name="model">
						</div>
						<div class="col-md-6">
							<label class="form-label">Glosa</label>
							<input class="form-control" name="model">
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
	$('#sl_lvl0').change(function(){
		$("#sl_lvl1").val(""); $('#sl_lvl1 option').addClass('d-none'); $('#sl_lvl1 option.p_' + $(this).val()).removeClass('d-none');
		$("#sl_lvl2").val(""); $('#sl_lvl2 option').addClass('d-none');
		$("#sl_lvl3").val(""); $('#sl_lvl3 option').addClass('d-none');
		$("#sl_lvl4").val(""); $('#sl_lvl4 option').addClass('d-none');
    });
	
	$('#sl_lvl1').change(function(){
		$("#sl_lvl2").val(""); $('#sl_lvl2 option').addClass('d-none'); $('#sl_lvl2 option.p_' + $(this).val()).removeClass('d-none');
		$("#sl_lvl3").val(""); $('#sl_lvl3 option').addClass('d-none');
		$("#sl_lvl4").val(""); $('#sl_lvl4 option').addClass('d-none');
    });
	
	$('#sl_lvl2').change(function(){
		$("#sl_lvl3").val(""); $('#sl_lvl3 option').addClass('d-none'); $('#sl_lvl3 option.p_' + $(this).val()).removeClass('d-none');
		$("#sl_lvl4").val(""); $('#sl_lvl4 option').addClass('d-none');
    });
	
	$('#sl_lvl3').change(function(){
		$("#sl_lvl4").val(""); $('#sl_lvl4 option').addClass('d-none'); $('#sl_lvl4 option.p_' + $(this).val()).removeClass('d-none');
    });
});
</script>