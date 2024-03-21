<div class="pagetitle">
	<h1 class="mb-3"><?= $title ?></h1>
</div>
<section class="section dashboard">
	<div class="row">
		<div class="col-md-4">
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
		<div class="col-md-4">
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
		<div class="col-md-4">
			<div class="card info-card customers-card pb-0">
				<div class="card-body p-3">
					<div class="d-flex align-items-center">
						<div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
							<i class="bi bi-cart"></i>
						</div>
						<div class="ps-3">
							<h6>145</h6>
							<span class="text-muted small pt-2">increase</span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>