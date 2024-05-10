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
											<i class="bi bi-box-seam"></i>
										</div>
										<div class="ps-3">
											<a href="<?= base_url() ?>som/product"><h6>Product</h6></a>
											<span class="text-muted small pt-2">Admin product's information</span>
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
		<div class="col-md-4">
			<div class="card">
				<div class="card-body pb-0">
					<h5 class="card-title">SA - Sale Admin</h5>
					<div class="row">
						<div class="col-md-12">
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
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
											<i class="bi bi-folder-plus"></i>
										</div>
										<div class="ps-3">
											<a href="<?= base_url() ?>scm/espr_file"><h6>ESPR File</h6></a>
											<span class="text-muted small pt-2">Merge COI, SOI 1 and SOI 2 to insert Dashboard SQL.</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body pb-0">
					<h5 class="card-title">Tax & Account - Invoice comparison</h5>
					<div class="row">
						<div class="col-md-12">
							<div class="card info-card sales-card pb-0">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
											<i class="bi bi-list-columns"></i>
										</div>
										<div class="ps-3">
											<a href="<?= base_url() ?>tax/invoice_comparison"><h6>Invoice Comparison</h6></a>
											<span class="text-muted small pt-2">Compare invoices in GERP and Paperless</span>
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
					<h5 class="card-title">HR - Human Resource</h5>
					<div class="row">
						<div class="col-md-6">
							<div class="card info-card sales-card pb-0">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
											<i class="bi bi-person-badge"></i>
										</div>
										<div class="ps-3">
											<a href="<?= base_url() ?>hr/employee"><h6>Employee</h6></a>
											<span class="text-muted small pt-2">Admin employees information</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6">
							<div class="card info-card revenue-card pb-0">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
											<i class="bi bi-door-open"></i>
										</div>
										<div class="ps-3">
											<a href="<?= base_url() ?>hr/attendance"><h6>Attendance</h6></a>
											<span class="text-muted small pt-2">Manage diary attendance of employees</span>
										</div>
									</div>
								</div>
							</div>
						</div>			
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-4">
			<div class="card">
				<div class="card-body pb-0">
					<h5 class="card-title">AR - Account to Receive</h5>
					<div class="row">
						<div class="col-md-12">
							<div class="card info-card sales-card pb-0">
								<div class="card-body p-3">
									<div class="d-flex align-items-center">
										<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
											<i class="bi bi-window-split"></i>
										</div>
										<div class="ps-3">
											<a href="<?= base_url() ?>ar/aging"><h6>Aging Report</h6></a>
											<span class="text-muted small pt-2">Make aging report by period</span>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>