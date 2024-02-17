<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Vacation</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Home</a></li>
				<li class="breadcrumb-item active">Vacation</li>
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
				<?php print_r($vacations); ?>
					<h5 class="card-title">List</h5>
					<div class="table-responsive">
						<table class="table align-middle">
							<thead>
								<tr>
									<th scope="col" style="width: 80px;">#</th>
									<th scope="col">Organization</th>
									<th scope="col">Emp. Num.</th>
									<th scope="col">Name</th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>
								<?php $base_i = ($page-1) * 30; foreach($vacations as $i => $emp){ ?>
								<tr>
									<td><?= number_format($base_i + $i + 1) ?></td>
									<td><?= $emp->subsidiary ?><br/><?= $emp->organization  ?></td>
									<td><?= $emp->employee_number  ?></td>
									<td><?= $emp->name  ?></td>
									<td class="text-end">
										<button type="button" class="btn btn-link">
											<i class="bi bi-file-earmark-fill"></i>
										</button>
									</td>
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
					<h5 class="modal-title">Upload from file</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div>
						<a href="<?= base_url() ?>form_file/vacation.xlsx" download="vacation_form.xlsx">1. Download upload form</a>
					</div>
					<div>
						<a href="<?= base_url() ?>upload/vacation.xlsx" download="vacation_upload_result.xlsx">2. Last file upload result</a>
					</div>
					<br/>
					<form class="row g-3" id="form_uff">
						<div class="col-12">
							<label for="md_uff_file" class="form-label">File</label>
							<input type="file" class="form-control" id="md_uff_file" name="md_uff_file" accept=".xls,.xlsx">
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
	$("#form_uff").submit(function(e) {
		e.preventDefault();
		$("#form_uff .sys_msg").html("");
		ajax_form_warning(this, "hr/vacation/upload_from_file", "Do you want to load data from attachment?").done(function(res) {
			swal_redirection(res.type, res.msg, "hr/vacation");
		});
	});
	
	/*
	//cancel purchase
	$("#btn_delete_payment").click(function() {
		ajax_simple_warning({id: $(this).val()}, "commerce/purchase/delete_payment", "wm_payment_delete").done(function(res) {
			swal_redirection(res.type, res.msg, window.location.href);
		});
	});
	*/
});
</script>