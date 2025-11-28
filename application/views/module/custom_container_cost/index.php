<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>CUSTOM Container Cost</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">CUSTOM Container Cost</li>
			</ol>
		</nav>
	</div>
</div>
<section class="section">
	<div class="row justify-content-center">
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">GLAP Upload</h5>
					<form id="form_custom_cost">
						<div class="input-group">
							<input class="form-control" type="file" id="fileAttachPrimary" name="attach_primary" required>
							<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
						</div>
					</form>
					<div class="mt-3">
						<a href="<?= base_url() ?>template/custom_glab_template.xlsx" download="custom_glab_template">
							GLAP template
						</a>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Release Container Upload</h5>
					<form id="form_update_attach">
						<div class="input-group">
							<input class="form-control" type="file" id="fileAttachSecondary" name="attach_secondary">
							<button type="submit" class="btn btn-primary"><i class="bi bi-upload"></i> Upload</button>
						</div>
					</form>
					<div class="mt-3">
						<a href="<?= base_url() ?>template/custom_release_container_template.xlsx" download="custom_release_container_template">
							Release Container template
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="card shadow">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center mb-4">
						<h5 class="card-title mb-0">Container Cost Records</h5>
						<div class="d-flex align-items-center mt-3">
							<form id="form_export_filter" class="d-flex align-items-center me-3" method="GET" action="<?= base_url('module/custom_container_cost/export_excel') ?>">
								<div class="col-auto me-2">
									<label for="date_range_selector" class="form-label visually-hidden">Date Range</label>
									<select id="date_range_selector" name="range" class="form-select form-select">
										<option value="" selected>-- Select Filter Option --</option>
										<option value="3_months">Last 3 Months</option>
										<option value="6_months">Last 6 Months</option>
										<option value="12_months">Last 12 Months</option>
										<option value="all_records">All Records</option>
										<option value="custom">Custom Range</option>
									</select>
								</div>

								<div class="col-auto date-custom-field" style="display: none;">
									<label class="form-label visually-hidden">Custom Date Range</label>
									
									<div class="input-group input-group">
										
										<span class="input-group-text">From</span>
										<input type="date" id="start_date" name="start" class="form-control" placeholder="YYYY-MM-DD">
										
										<span class="input-group-text border-0 bg-transparent px-1">~</span>
										
										<span class="input-group-text">To</span>
										<input type="date" id="end_date" name="end" class="form-control" placeholder="YYYY-MM-DD">
									</div>
								</div>
								
								<button type="submit" id="export_submit_btn" class="btn btn-success btn" title="Please select a filter option first." disabled>
									<i class="bi bi-file-earmark-excel"></i> Export Data
								</button>
							</form>
						</div>
					</div>
	
					<div class="table-responsive">
						<table class="table datatable">
							<thead>
								<tr>
									<th scope="col">Invoice Num</th>
									<th scope="col">House BL No</th>
									<th scope="col">Currency</th>
									<th scope="col">Status</th>
									<th scope="col">Container No</th>
									<th scope="col">Shipping Date</th>
									<th scope="col">Invoice Date</th>
									<th scope="col">Confirm Date</th>
									<th scope="col">Last Updated</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach($container_cost as $item){ ?>
								<tr>
									<td><?= $item->invoice_num ?></td>
									<td><?= $item->house_bl_no ?></td>
									<td><?= $item->currency ?></td>
									<td><?= $item->status ?></td>
									<td><?= $item->container_no ?></td>
									<td><?= $item->shipping_date ?></td>
									<td><?= $item->invoice_date ?></td>
									<td><?= $item->confirm_date ?></td>
									<td><?= $item->last_updated ?></td>
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
    const $dateRangeSelector = $("#date_range_selector");
    const $startDate = $("#start_date");
    const $endDate = $("#end_date");
    const $exportSubmitBtn = $("#export_submit_btn");
    
    function updateExportButtonState() {
        if ($dateRangeSelector.val() === '') {
            $exportSubmitBtn.prop('disabled', true).attr('title', 'Please select a filter option first.');
        } else {
            $exportSubmitBtn.prop('disabled', false).attr('title', 'Download data based on the selected filter.');
        }
    }
    
    updateExportButtonState();

	$("#form_custom_cost").submit(function(e) {
		e.preventDefault();
		
		if (!document.getElementById('fileAttachPrimary').value) {
			Swal.fire('Warning', 'Please select a file to upload.', 'warning');
			return;
		}
        
		ajax_form_warning(this, "module/custom_container_cost/upload", "Do you want to upload data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/custom_container_cost");
		});
	});

	$("#form_update_attach").submit(function(e) {
		e.preventDefault();
		
		if (!document.getElementById('fileAttachSecondary').value) {
			Swal.fire('Warning', 'Please select a file to upload.', 'warning');
			return;
		}
        
		ajax_form_warning(this, "module/custom_container_cost/upload_release_container", "Do you want to upload data?").done(function(res) {
			swal_redirection(res.type, res.msg, "module/custom_container_cost");
		});
	});
    

    $dateRangeSelector.on('change', function() {
        const selectedValue = $(this).val();

        if (selectedValue === 'custom') {
            $(".date-custom-field").slideDown();
        } else {
            $(".date-custom-field").slideUp();
            $startDate.val('');
            $endDate.val('');
        }
        updateExportButtonState();
    });

    $("#form_export_filter").submit(function(e) {
        if ($dateRangeSelector.val() === 'custom' && (!$startDate.val() || !$endDate.val())) {
            e.preventDefault();
            Swal.fire('Warning', 'Please select both start and end dates for a custom range before exporting.', 'warning');
            return false;
        }
    });
    
});
</script>