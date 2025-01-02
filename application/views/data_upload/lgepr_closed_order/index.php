<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>LGEPR Closed Order Update</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">LGEPR Closed Order Update </li>
			</ol>
		</nav>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title">Closed Orders (<?= number_format(count($closed_orders)) ?> records)</h5>
						<select class="form-select" id="sl_period" style="width:150px;">
							<?php $current = $last; while ($current >= $first) { $str = date('Y-m', $current); ?>
							<option value="<?= $str ?>" <?= $str === $d ? "selected" : "" ?>><?= $str ?></option>
							<?php $current = strtotime('-1 month', $current); } ?>
						</select>
						
						<form id="form_closed_order_upload">
							<div class="input-group">
								<a class="btn btn-success" href="<?= base_url() ?>template/lgepr_closed_order_template.xls" download="lgepr_closed_order_template"><i class="bi bi-file-earmark-spreadsheet"></i></a>
								<input class="form-control" type="file" name="attach">
								<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i></button>
							</div>
						</form>
					</div>
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">Type</th>
								<th scope="col">Ordered</th>
								<th scope="col">Closed</th>
								<th scope="col">Order</th>
								<th scope="col">Line</th>
								<th scope="col">Dept.</th>
								<th scope="col">Inventory</th>
								<th scope="col">Sub</th>
								<th scope="col">USD</th>
								<th scope="col">Qty</th>
								<th scope="col">Model</th>
								<th scope="col">Division</th>
								<th scope="col">Category</th>
								<th scope="col">Level 1</th>
								<th scope="col">Level 2</th>
								<th scope="col">Level 3</th>
								<th scope="col">Level 4</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($closed_orders as $item){ ?>
							<tr>
								<td><?= $item->category ?></td>
								<td><?= $item->order_date ?></td>
								<td><?= $item->closed_date ?></td>
								<td><?= $item->order_no ?></td>
								<td><?= $item->line_no ?></td>
								<td><?= $item->customer_department ?></td>
								<td><?= $item->inventory_org ?></td>
								<td><?= $item->sub_inventory ?></td>
								<td><?= number_format($item->order_amount_usd, 2) ?></td>
								<td><?= $item->order_qty ?></td>
								<td><?= $item->model ?></td>
								<td><?= $item->dash_division ?></td>
								<td><?= $item->dash_category ?></td>
								<td><?= $item->product_level1_name ?></td>
								<td><?= $item->product_level2_name ?></td>
								<td><?= $item->product_level3_name ?></td>
								<td><?= $item->product_level4_name ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	
	 $('#sl_period').on('change', function () {
        const selectedValue = $(this).val(); // 선택된 값 (URL)
        if (selectedValue) {
            window.location.href = base_url + "data_upload/lgepr_closed_order?d=" + selectedValue; // URL로 이동
        }
    });
	
	$("#form_closed_order_upload").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/lgepr_closed_order/upload", "Do you want to update closed order data?").done(function(res) {
			//swal_redirection(res.type, res.msg, "data_upload/lgepr_closed_order");
			swal_open_tab(res.type, res.msg, "lgepr_closed_order/process");
		});
	});
});
</script>