<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Employee</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Home</a></li>
				<li class="breadcrumb-item active">Employee</li>
			</ol>
		</nav>
	</div>
	<div>
		<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#md_uff">
			<i class="bi bi-upload"></i>
		</button>
		<button type="button" class="btn btn-primary" id="btn_category_admin">
			<i class="bi bi-tags-fill"></i>
		</button>
		<a href="#" type="button" class="btn btn-success">
			<i class="bi bi-plus-lg"></i>
		</a>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">List</h5>
					<?php print_r($employees); ?>
				</div>
			</div>
		</div>
	</div>
</section>
<div>
	<div class="modal fade" id="md_uff" tabindex="-1" style="display: none;" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Upload from file</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p>
						<a href="<?= base_url() ?>form_file/employee.xlsx" download="employee_form.xlsx">Download Form</a>
					</p>
					<form class="row g-3" id="form_uff">
						<div class="col-12">
							<label for="md_uff_file" class="form-label">File</label>
							<input type="file" class="form-control" id="md_uff_file" name="md_uff_file" accept=".xls,.xlsx">
						</div>
						<div class="text-end pt-3">
							<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary">Upload</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_uff").submit(function(e) {
		e.preventDefault();
		$("#form_uff .sys_msg").html("");
		ajax_form_warning(this, "hr/employee/upload_from_file", "Do you want to load data from attachment?").done(function(res) {
			swal_redirection(res.type, res.msg, "hr/employee");
		});
	});
	
	/*
	//cancel purchase
	$("#btn_delete_payment").click(function() {
		ajax_simple_warning({id: $(this).val()}, "commerce/purchase/delete_payment", "wm_payment_delete").done(function(res) {
			swal_redirection(res.type, res.msg, window.location.href);
		});
	});
	
	
	//provider
	$("#form_save_provider").submit(function(e) {
		e.preventDefault();
		$("#form_edit_provider .sys_msg").html("");
		ajax_form(this, "commerce/purchase/save_provider").done(function(res) {
			swal(res.type, res.msg);
		});
	});
	
	$("#btn_search_provider").click(function() {
		ajax_simple({tax_id: $("#prov_ruc").val()}, "ajax_f/search_company").done(function(res) {
			swal(res.type, res.msg);
			if (res.type == "success"){
				$("#prov_name").val(res.company.name);
				$("#prov_web").val(res.company.web);
				$("#prov_person").val(res.company.person);
				$("#prov_tel").val(res.company.tel);
				$("#prov_email").val(res.company.email);
				$("#prov_address").val(res.company.address);
				$("#prov_remark").val(res.company.remark);
			}
		});
	});
	*/
});
</script>