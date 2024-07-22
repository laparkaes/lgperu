<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>ISM - Activity Management</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">ISM - Activity Management</li>
			</ol>
		</nav>
	</div>
	<div>
		<a type="button" class="btn btn-success" href="<?= base_url() ?>module/ism_activity_management/create">
			<i class="bi bi-plus-lg"></i>
		</a>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Activity List</h5>
					<div class="table-responsive">
						<table class="table datatable align-middle">
							<thead>
								<tr>
									<th scope="col">Status</th>
									<th scope="col">Title</th>
									<th scope="col">Approval</th>
									<th scope="col">Period</th>
									<th scope="col">Registered</th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($activities as $item){ ?>
								<tr>
									<td><?= $item->activity_status ?></td>
									<td><?= $item->title ?></td>
									<td><?= $item->approval_no.($item->approval_status ? " / ".$item->approval_status : "") ?></td>
									<td><?= $item->period_from.($item->period_to ? " ~ ".$item->period_to : "") ?></td>
									<td><?= $item->registered ?></td>
									<td>
										<div class="text-end">
											<a class="btn btn-primary btn-sm" href="<?= base_url() ?>module/ism_activity_management/edit/<?= $item->activity_id ?>">
												<i class="bi bi-pencil"></i>
											</a>
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
<script>
document.addEventListener("DOMContentLoaded", () => {
	
});
</script>