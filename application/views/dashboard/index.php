<section class="section dashboard">
	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Reports</h5>
					<ul class="list-group list-group-flush">
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>report/obs_nsp" target="_blank">OBS - NSP</a>
					</ul>
				</div>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Modules</h5>
					<ul class="list-group list-group-flush">
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/gerp_sales_order">GERP - Sales Order</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/hr_attendance">HR - Attendance</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/hr_employee">HR - Employee</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/ism_activity_management">ISM - Activity Management</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/obs_gerp">OBS - GERP Sales Order</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/obs_magento">OBS - Magento</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/obs_most_likely">OBS - Most Likely (ML)</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/obs_report">OBS - Report</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/sa_promotion">SA - Promotion</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/sa_sell_inout">SA - Sell In/Out Report</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/sa_sell_out">SA - Sell Out</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/scm_purchase_order">SCM - Purchase Order</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/tax_invoice_comparison">Tax - Invoice Comparison</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/tax_paperless_document">Tax - Paperless Document</a>
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
						<a class="btn btn-primary" href="<?= base_url() ?>dashboard/update_exchange_rate" target="_blank">Exchange Rate Load</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>