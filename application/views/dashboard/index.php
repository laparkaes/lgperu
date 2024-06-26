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
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/dash_order_inquiry">Dashboard - Order Inquiry</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/employee">Employee</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/invoice">Invoice</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/obs_gerp">OBS - GERP Sales Order</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/obs_magento">OBS - Magento</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/obs_most_likely">OBS - Most Likely (ML)</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/obs_report">OBS - Report</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/product">Product</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/promotion">Promotion</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/purchase_order">Purchase Order</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/sell_inout">Sell In/Out Report</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/sell_out">Sell Out</a>
					</ul>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Anything here</h5>
					<div>
						Will be developed
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-2">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Functions</h5>
					<div class="d-grid gap-2 mt-3">
						<a class="btn btn-primary" href="<?= base_url() ?>/dashboard/update_exchange_rate" target="_blank">Exchange Rate Load</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>