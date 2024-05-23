
							
<section class="section dashboard">
	<div class="row">
		<div class="col-md-4">
			<div class="card info-card">
				<div class="card-body">
					<h5 class="card-title">Modules</h5>
					<ul class="list-group list-group-flush">
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/aging">Aging Report</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/attendance">Attendance</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/employee">Employee</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/espr_file">ESPR File (COI, SOI 1, SOI 2)</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/invoice">Invoice</a>
						<a class="list-group-item list-group-item-action" href="<?= base_url() ?>module/product">Product</a>
						
						
						
						<button type="button" class="list-group-item list-group-item-action">A second item</button>
						<button type="button" class="list-group-item list-group-item-action">A third button item</button>
						<button type="button" class="list-group-item list-group-item-action">A fourth button item</button>
						<button type="button" class="list-group-item list-group-item-action" disabled="">A disabled button item</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<section class="section dashboard">
	<div class="row">
		<div class="col-md-8">
			<div class="card">
				<div class="card-body pb-0">
					<h5 class="card-title">SOM - Sales Order Management</h5>
					<div class="row">
						<div class="col-md-6">
							<div class="card info-card sales-card pb-0">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
											
										</div>
										<div class="ps-3">
											<a href=""><h6></h6></a>
											<span class="text-muted small pt-2"></span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card info-card sales-card pb-0">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
											<i class="bi bi-box-arrow-right"></i>
										</div>
										<div class="ps-3">
											<a href="<?= base_url() ?>som/sell_out_upload"><h6>Sell Out Upload</h6></a>
											<span class="text-muted small pt-2">Upload sell out information from customers</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="card">
				<div class="card-body pb-0">
					<h5 class="card-title">SA - Sale Admin</h5>
					<div class="row">
						<div class="col-md-6">
							<div class="card info-card sales-card pb-0">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
											<i class="bi bi-receipt"></i>
										</div>
										<div class="ps-3">
											<a href="<?= base_url() ?>sa/sell_inout"><h6>Sell In/Out Report</h6></a>
											<span class="text-muted small pt-2">Follow up customer stocks</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card info-card sales-card pb-0">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
											<i class="bi bi-box-arrow-in-down"></i>
										</div>
										<div class="ps-3">
											<a href="<?= base_url() ?>sa/promotion"><h6>Promotion</h6></a>
											<span class="text-muted small pt-2">Admin Sell-In promotions</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-12">
			<div class="card">
				<div class="card-body pb-0">
					<h5 class="card-title">SCM - Supply Chain Management</h5>
					<div class="row">
						<div class="col-md-4">
							<div class="card info-card sales-card pb-0">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
											<i class="bi bi-cart3"></i>
										</div>
										<div class="ps-3">
											<a href="<?= base_url() ?>scm/purchase_order"><h6>Purchase Order</h6></a>
											<span class="text-muted small pt-2">Convert PO to Excel</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card info-card sales-card pb-0">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
											<i class="bi bi-currency-dollar"></i>
										</div>
										<div class="ps-3">
											<a href="<?= base_url() ?>scm/sales_order"><h6>Sales Order</h6></a>
											<span class="text-muted small pt-2">Admin Sales Order in local database.</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="card info-card sales-card pb-0">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body pb-0">
					<h5 class="card-title">Tax & Account - Invoice</h5>
					<div class="row">
						<div class="col-md-12">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="card">
				<div class="card-body pb-0">
					<h5 class="card-title">HR - Human Resource</h5>
					<div class="row">
						<div class="col-md-6">
						</div>
						<div class="col-md-6">
						</div>			
					</div>
				</div>
			</div>
		</div>
	</div>
</section>