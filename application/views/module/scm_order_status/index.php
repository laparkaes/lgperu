<section class="section">
	<div class="row">
		<div class="col-12">
			<div class="d-flex justify-content-between align-items-start">
				<div class="pagetitle">
					<h1>SCM - Order Status</h1>
					<nav>
						<ol class="breadcrumb">
							<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
							<li class="breadcrumb-item active">SCM - Order Status</li>
						</ol>
					</nav>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Order List</h5>
					<?php print_r($sales[0]); ?>
					<div><?= number_format(count($sales)) ?> records</div>
					<table class="table">
						<thead>
							<tr>
								<th scope="col">Order</th>
								<th scope="col">Line</th>
								<th scope="col">Customer</th>
								<th scope="col">Model</th>
								<th scope="col">Qty</th>
								<th scope="col">Flags</th>
								<th scope="col">Status</th>
								<th scope="col">OM Status</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($sales as $item){ /* if (!in_array($item->so_status, ["AWAITING_FULFILLMENT"])){ */ ?>
							<tr>
								<td><?= $item->order_no ?></td>
								<td><?= $item->line_no ?></td>
								<td><?= $item->bill_to_name ?></td>
								<td><?= $item->model ?></td>
								<td><?= $item->ordered_qty ?></td>
								<td><?= $item->ordered_qty ?></td>
								<td><?= $item->so_status ?></td>
								<td>
									<select class="form-select" name="line_status_detail" sales_order_id="<?= $item->sales_order_id ?>">
										<option value="">---</option>
										<?php foreach($line_status_detail_list as $op){ ?>
										<option value="<?= $op ?>" <?= trim($item->line_status_detail) === $op ? "selected" : "" ?>><?= $op ?></option>
										<?php } ?>
									</select>
								</td>
							</tr>
							<?php /* } */ } ?>
						</tbody>
					</table>
					
					
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload").submit(function(e) {
		e.preventDefault();
		$("#form_upload .sys_msg").html("");
		ajax_form_warning(this, "module/scm_sku_management/upload", "Do you upload data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/scm_sku_management");
		});
	});
});
</script>