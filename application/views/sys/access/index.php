<div class="pagetitle">
	<h1>System Access</h1>
	<nav>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
			<li class="breadcrumb-item active">System Access</li>
		</ol>
	</nav>
</div>
<section class="section">
	<?php
	$msgs = $this->session->flashdata('msgs');
	if ($msgs) foreach($msgs as $item){ if ($item[0] === "success") $color = "success"; else $color = "danger";
	?>
	<div class="alert alert-<?= $color ?> fade show" role="alert">
		<?= $item[1] ?>
	</div>	
	<?php } ?>
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title">List</h5>
						<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#md_create">
							<i class="bi bi-plus-lg"></i> Create
						</button>
					</div>
					<div class="table-responsive">
						<table class="table align-middle">
							<thead>
								<tr>
									<th scope="col">Status</th>
									<th scope="col">Employee</th>
									<th scope="col">EP</th>
									<th scope="col">PR</th>
									<th scope="col">Department</th>
									<th scope="col">Function</th>
									<th scope="col">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($access as $item){ ?>
								<tr>
									<td><?= $item->valid == 1 ? "Allowed" : "Requested" ?></td>
									<td><?= $item->emp_name ?></td>
									<td><?= $item->emp_ep ?></td>
									<td><?= $item->emp_pr ?></td>
									<td><?= $item->emp_dept ?></td>
									<td><?= $item->func ?></td>
									<td>
										<div class="text-end">
											<?php if ($item->valid == 0){ ?>
											<a href="<?= base_url() ?>sys/access/allow/<?= $item->access_id ?>" class="btn btn-success btn-sm">Allow</a>
											<?php } ?>
											<a href="<?= base_url() ?>sys/access/deny/<?= $item->access_id ?>" class="btn btn-outline-danger btn-sm">Deny</a>
										</div>
									</td>
								</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div> 
	</div>
</section>

<div class="modal fade" id="md_create" tabindex="-1" aria-hidden="true" style="display: none;">
	<div class="modal-dialog modal-xl modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Create Access</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<form class="row g-3" action="<?= base_url() ?>sys/access/create" method="post">
					<div class="col-md-6">
						<label class="form-label">Employee</label>
						<select class="form-select" name="employee_ids[]" style="height: 500px;" multiple required>
							<?php foreach($employees as $item){ ?>
							<option value="<?= $item->employee_id ?>">(<?= $item->subsidiary ?>_<?= $item->organization ?>_<?= $item->department ?>) <?= $item->name ?> <?= $item->employee_number ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-md-6">
						<label class="form-label">Function</label>
						<select class="form-select" name="function_ids[]" style="height: 500px;" multiple required>
							<?php foreach($funcs as $item){ ?>
							<option value="<?= $item->function_id ?>"><?= $item->type ?> _ <?= $item->title ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="text-center pt-3">
						<button type="submit" class="btn btn-primary">Submit</button>
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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