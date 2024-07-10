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
					<h5 class="card-title">Product List</h5>
					<div class="table-responsive">
						<table class="table datatable align-middle">
							<thead>
								<tr>
									<th scope="col">Category</th>
									<th scope="col">Division</th>
									<th scope="col">Level 1</th>
									<th scope="col">Level 2</th>
									<th scope="col">Level 3</th>
									<th scope="col">Level 4</th>
									<th scope="col">Model</th>
									<th scope="col">Updated</th>
									<th scope="col">Created</th>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>
								<?php 
								foreach($products as $prod){
									if ($prod->line_id){
										$lvl4 = $prod->line_id > 0 ? $lines_arr[$prod->line_id] : null;
										$lvl3 = $lvl4 ? $lines_arr[$lvl4->parent_id] : null;
										$lvl2 = $lvl3 ? $lines_arr[$lvl3->parent_id] : null;
										$lvl1 = $lvl2 ? $lines_arr[$lvl2->parent_id] : null;
										$lvl0 = $lvl1 ? $lines_arr[$lvl1->parent_id] : null;
										
										$prod->lvl0 = $lvl0 ? $lvl0->line : "";
										$prod->lvl1 = $lvl1 ? $lvl1->line : "";
										$prod->lvl2 = $lvl2 ? $lvl2->line : "";
										$prod->lvl3 = $lvl3 ? $lvl3->line : "";
										$prod->lvl4 = $lvl4 ? $lvl4->line : "";
									}else $prod->lvl0 = $prod->lvl1 = $prod->lvl2 = $prod->lvl3 = $prod->lvl4 = "";
								?>
								<tr>
									<td><?= $prod->category_id ? $categories_arr[$prod->category_id]->category : "" ?></td>
									<td><?= $prod->lvl0 ?></td>
									<td><?= $prod->lvl1 ?></td>
									<td><?= $prod->lvl2 ?></td>
									<td><?= $prod->lvl3 ?></td>
									<td><?= $prod->lvl4 ?></td>
									<td><?= $prod->model; ?></td>
									<td><?= $prod->updated ?></td>
									<td><?= $prod->registered ?></td>
									<td>
										<a class="btn btn-primary btn-sm" href="<?= base_url() ?>module/product/edit/<?= $prod->product_id ?>">
											<i class="bi bi-pencil"></i>
										</a>
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