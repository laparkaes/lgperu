<section class="section dashboard">
	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Modules</h5>
					<div class="form-floating mb-3">
						<input type="text" class="form-control" id="searchModule" placeholder="Search Module">
						<label for="searchModule">Search Module</label>
					</div>
					<ul class="list-group list-group-flush">
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/aging">Aging Report</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/attendance">Attendance</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/employee">Employee</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/espr_file">ESPR File (COI, SOI 1, SOI 2)</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/invoice">Invoice</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/product">Product</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/promotion">Promotion</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/purchase_order">Purchase Order</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/sales_order">Sales Order</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/sell_inout">Sell In/Out Report</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/sell_out">Sell Out</a>
					</ul>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Anything here</h5>
					<div>
						Will be developed
					</div>
				</div>
			</div>
		</div>
	</div>
</section>