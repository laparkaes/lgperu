<div class="row">
	<div class="col-md-6 mx-auto pt-3">
		<?php
		$type = $msg = null;
		if ($this->session->flashdata('success_msg')){
			$type = "success";
			$msg = $this->session->flashdata('success_msg');
		}elseif ($this->session->flashdata('error_msg')){
			$type = "danger";
			$msg = $this->session->flashdata('error_msg');
		}
		
		if ($msg){
		?>
		<div class="alert alert-<?= $type ?> fade show" role="alert">
			<?= $msg ?>
		</div>
		<?php } ?>
		<div class="card overflow-hidden">
			<div class="card-body">
				<h5 class="card-title" style = "font-size:25px">PI - LISTENING TO YOU !!!</h5>
				<form class="row g-3" method="POST" action="<?= base_url() ?>page/pi_listening_request/cpilistening">
					<!-- div class="col-md-6">
						<label for="dptFrom" class="form-label">From (Department code provided by PI)</label>
						<input type="text" class="form-control" id="dptFrom" name="dptFrom" value="<?= $this->session->flashdata('dptFrom') ?>" required>
					</div-->
					<div class="col-md-12">
						<label for="dptTo" class="fw-bold" class="form-label">Departamento al que desea presentar la propuesta</label>
						<select id="dptTo" name="dptTo" class="form-select" required>
							<option value="" selected="">Escoger...</option>
							<?php foreach($dpts as $key => $item){ ?>
							<option value="<?= $key ?>" <?= $this->session->flashdata('dptTo') === $key ? "selected" : "" ?>><?= $item ?></option>
							<?php } ?>
						</select>
					</div>
					
					<div class="col-md-6">
						<label for="issue" class="fw-bold" class="form-label">Descripción del Problema</label>
						<label for="issue" class="form-label">Proporcione una descripción detallada del problema</label>
						<textarea class="form-control" id="issue" name="issue" style="height: 300px" required><?= $this->session->flashdata('issue') ?></textarea>
					</div>
					<div class="col-md-6">
						<label for="solution" class="fw-bold" class="form-label">Propuesta de solución</label>
						<label for="solution" class="form-label">Proporcione una idea detallada para abordar el problema</label>
						<textarea class="form-control" id="solution" name="solution" style="height: 300px" required><?= $this->session->flashdata('solution') ?></textarea>
					</div>
					
					<div class="text-center pt-3">
						<button type="submit" class="btn btn-primary">Enviar formulario</button>
						<button type="button" class="btn btn-outline-primary" onclick="location.href='<?= base_url("page/lgepr_listening") ?>'">
							Seguimiento
						</button>
					</div>
					
					
              </form>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
	
});
</script>