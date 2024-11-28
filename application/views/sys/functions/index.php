<div class="pagetitle">
	<h1>System Functions</h1>
	<nav>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
			<li class="breadcrumb-item active">System Functions</li>
		</ol>
	</nav>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-4">
			<?php if ($this->session->flashdata('errors')) foreach($this->session->flashdata('errors') as $error){ ?>
			<div class="alert alert-danger fade show" role="alert">
				<?= $error ?>
			</div>
			<?php } if ($this->session->flashdata('success')){ ?>
			<div class="alert alert-success fade show" role="alert">
				<?= $this->session->flashdata('success') ?>
			</div>
			<?php } ?>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Create</h5>
					<form class="row g-3" action="<?= base_url() ?>sys/functions/create" method="post">
						<div class="col-md-6">
							<label class="form-label">Type</label>
							<select class="form-select" name="type">
								<option value="module" <?= $this->session->flashdata('type') === "module" ? "selected" : "" ?>>Module</option>
								<option value="data_upload" <?= $this->session->flashdata('type') === "data_upload" ? "selected" : "" ?>>Data Upload</option>
								<option value="page" <?= $this->session->flashdata('type') === "page" ? "selected" : "" ?>>Page</option>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Path</label>
							<input class="form-control" type="text" name="path" value="<?= $this->session->flashdata('path') ?>">
						</div>
						<div class="col-md-12">
							<label class="form-label">Title</label>
							<input class="form-control" type="text" name="title" value="<?= $this->session->flashdata('title') ?>">
						</div>
						<div class="text-center pt-3">
							<button type="submit" class="btn btn-primary">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">List</h5>
					<div class="table-responsive">
						<table class="table align-middle">
							<thead>
								<tr>
									<th scope="col">Title</th>
									<th scope="col">Type</th>
									<th scope="col">Path</th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($funcs as $i => $item){ ?>
								<tr>
									<td><?= $item->title  ?></td>
									<td><?= $item->type  ?></td>
									<td><?= $item->path  ?></td>
									<td>
										<div class="form-check form-switch d-flex justify-content-end">
											<input class="form-check-input chk_active" type="checkbox" value="33" <?= $item->valid ? "checked" : "" ?>>
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
	$(".chk_active").change(function() {
		alert($(this).val());
		
		return;
		
		var selectedValue = $(this).val(); 
		var currentUrl = window.location.href; 
		
		// 원하는 로직에 따라 URL을 변경합니다. 
		var newUrl = currentUrl.split('?')[0] + '?page=' + selectedValue; 
		window.location.href = newUrl;
	});
});
</script>