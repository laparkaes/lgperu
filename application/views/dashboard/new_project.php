<div class="pagetitle">
	<h1>New Project</h1>
	<nav>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
			<li class="breadcrumb-item active">New Project</li>
		</ol>
	</nav>
</div>
<section class="section">
	<div class="row">
		<div class="col">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Project Information</h5>
					<div class="row">
						<div class="col-md-4">
							<div class="row">							
								<div class="col-md-12">
									<label class="form-label">Employee List</label>
									<select class="form-select" id="sl_employees" style="height: 500px;" multiple required>
										<?php foreach($employees as $item){ ?>
										<option value="<?= $item->employee_id ?>"><?= $item->subsidiary ?>_<?= $item->organization ?>_<?= $item->department ?>] <?= $item->name ?> <?= $item->employee_number ?></option>
										<?php } ?>
									</select>
								</div>
							</div>
						</div>
						<div class="col-md-8">
							<form class="row g-3">
								<div class="col-md-12">
									<label class="form-label">Members</label>
									<div class="d-flex justify-content-start align-items-start">
										<button type="button" class="btn btn-success" id="btn_add_emp"><i class="bi bi-arrow-right"></i></button>
										<div class="px-3" id="bl_selected_employees"></div>
									</div>
								</div>
								<div class="col-md-12">
									<label class="form-label">Name</label>
									<input type="text" class="form-control" name="name">
								</div>
								<div class="col-md-12">
									<label class="form-label">Description</label>
									<textarea class="form-control"name="description" rows="5"></textarea>
								</div>
								<div class="text-center pt-3">
									<button type="submit" class="btn btn-primary">Submit</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div> 
	</div>
	
	
</section>
<script>
document.addEventListener("DOMContentLoaded", () => {
	
	$("#btn_add_emp").click(function() {
		$('#sl_employees option:selected').each(function() {
            const value = $(this).val();
            const text = $(this).text();
			
			const cl = "op_emp_" + value;
			
			if ($('#bl_selected_employees .' + cl).length == 0) {
				$("#bl_selected_employees").append('<div class="row_emp mb-1" id="row_emp_' + value + '"><input type="hidden" name="employee_ids[]" class="' + cl + '" value="' + value + '"><button type="button" class="btn btn-outline-primary btn-sm btn_remove_emp ' + cl + '" value="' + value + '">[X] ' + text + '</button></div>');
			}
			
            //console.log('Value:', value);
            //console.log('Text:', text);
        });
		
		//sort rows
		const rows = $('#bl_selected_employees .row_emp').toArray();
		
		rows.sort(function(a, b) {
			return $(a).text().localeCompare($(b).text());
		});
		
		$('#bl_selected_employees').empty().append(rows);
		
		//add remove event
		$(".btn_remove_emp").click(function() { 
			$("#row_emp_" + $(this).val()).remove();
		});
		
		//console.log(rows);
	});
	
	/*
	$("#form_uff_w_hour").submit(function(e) {
		e.preventDefault();
		$("#form_uff_w_hour .sys_msg").html("");
		ajax_form_warning(this, "module/employee/upload_w_hour_from_file", "Do you want to upload working hours data from selected file?").done(function(res) {
			if (res.type == "success") window.location.href = base_url + "upload/working_hour.xlsx";
			swal_redirection(res.type, res.msg, "module/employee");
		});
	});
	
	//cancel purchase
	$("#btn_delete_payment").click(function() {
		ajax_simple_warning({id: $(this).val()}, "commerce/purchase/delete_payment", "wm_payment_delete").done(function(res) {
			swal_redirection(res.type, res.msg, window.location.href);
		});
	});
	*/
});
</script>