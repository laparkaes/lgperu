<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Attendance</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Home</a></li>
				<li class="breadcrumb-item active">Attendance</li>
			</ol>
		</nav>
	</div>
	<div>
		<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#md_uff">
			<i class="bi bi-upload"></i>
		</button>
		<a href="#" type="button" class="btn btn-success">
			<i class="bi bi-search"></i>
		</a>
		<a href="#" type="button" class="btn btn-success">
			<i class="bi bi-plus-lg"></i>
		</a>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title"><?= $month ?></h5>
					<div class="table-responsive">
						<table class="table align-middle">
							<thead>
								<tr>
									<th scope="col" style="width: 80px;">#</th>
									<th scope="col">Employee</th>
									<th scope="col">Falta</th>
									<th scope="col">Tardiness</th>
									<th scope="col">vacation</th>
									<?php foreach($headers as $h){ ?>
									<th scope="col" class="text-<?= $h["color"] ?>"><?= $h["day"] ?><br/><?= $h["w_day"] ?></th>
									<?php } ?>
								</tr>
							</thead>
							<tbody>
								<?php foreach($employees as $i => $emp){ ?>
								<tr>
									<td><?= number_format($i + 1) ?></td>
									<td>
										<div><?= $emp->employee_number  ?></div>
										<div style="overflow: hidden; max-width: 150px; text-overflow: ellipsis;" class="text-nowrap"><?= $emp->name  ?></div>
									</td>
									<td><?= number_format($mapping[$emp->employee_id]["absence_qty"]) ?></td>
									<td><?= number_format($mapping[$emp->employee_id]["tardiness_qty"]) ?></td>
									<td><?= number_format($mapping[$emp->employee_id]["vacation_qty"]) ?></td>
									<?php foreach($dates as $d){ $aux = $mapping[$emp->employee_id][$d]; ?>
									<td>
										<?php if (array_key_exists("time", $aux["enter"])){ ?>
										<div><?= date("H:i", strtotime($aux["enter"]["time"])) ?></div>
										<div><?= date("H:i", strtotime($aux["leave"]["time"])) ?></div>
										<?php } ?>
									</td>
									<?php } ?>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
					<div class="btn-group" role="group" aria-label="paging">
						<?php 
						$f_url = $this->input->get();
						foreach($paging as $p){
						$f_url["page"] = $p[0]; ?>
						<a href="<?= base_url() ?>hr/employee?<?= http_build_query($f_url) ?>" class="btn btn-<?= $p[2] ?>">
							<?= $p[1] ?>
						</a>
						<?php } ?>
					</div>
				</div>
			</div>
		</div> 
	</div>
</section>
<div>
	<div class="modal fade" id="md_uff" tabindex="-1" style="display: none;" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Upload Device Check-in</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="my-3">
						<span>You need to upload excel file exported from access device.</span>
					</div>
					<form class="row g-3" id="form_uff_attendance">
						<div class="col-12">
							<label class="form-label">Device File</label>
							<input type="file" class="form-control" name="md_uff_file" accept=".xls,.xlsx">
						</div>
						<div class="text-end pt-3">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary">Upload</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_uff_attendance").submit(function(e) {
		e.preventDefault();
		$("#form_uff_attendance .sys_msg").html("");
		ajax_form_warning(this, "hr/attendance/upload_device_file", "Do you want to upload device check-in data from selected file?").done(function(res) {
			swal_redirection(res.type, res.msg, "hr/attendance");
		});
	});
});
</script>