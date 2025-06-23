<div class="row">
	<div class="col-12">
		<div class="card mt-4">
			<div class="card-body py-0">
				<h5 class="card-title py-3 my-0">LGEPR Report List</h5>
			</div>
		</div>
	</div>
	<a href="<?= base_url() ?>lgepr/container_plan" class="col-md-3">
		<div class="card">
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center">
					<h5 class="card-title">Container Plan</h5>
					<button type="button" class="btn btn-primary"><i class="bi bi-bar-chart-steps"></i></button>
				</div>
				<div>The container movement plan from Callao port to the 3PL or customer warehouse</div>
			</div>
		</div>
	</a>
	<a href="<?= base_url() ?>lgepr/warehouse_cbm" class="col-md-3">
		<div class="card">
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center">
					<h5 class="card-title">Warehouse CBM Simulation</h5>
					<button type="button" class="btn btn-primary"><i class="bi bi-boxes"></i></button>
				</div>
				<div>3PL warehouse occupancy simulator in CBM based on container arrivals and LGEPR sales</div>
			</div>
		</div>
	</a>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
	
});
</script>