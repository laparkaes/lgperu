<div class="card mt-3">
	<div class="card-body">
		<div class="d-flex justify-content-between align-items-center">
			<h5 class="card-title">Access Detail <?= $period ?></h5>
			<button type="button" class="btn btn-danger" onclick="window.close();"><i class="bi bi-x-lg"></i></button>
		</div>
		<table class="table align-middle" style="font-size: 0.8rem;">
			<thead class="sticky-top" style="z-index: 10;">
				<tr>
					<th scope="col">Employee</th>
					<?php foreach($dates as $d => $item){ ?>
					<th scope="col" class="text-center">
						<?= date("d", strtotime($d)) ?>
					</th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
				<tr class="row_emp">
					<td>
						<div><strong><?= $employee->name ?></strong></div>
						<div><?= $employee->employee_number ?></div>
						<div><?= $employee->subsidiary ?> > <?= $employee->organization ?> > <?= $employee->department ?></div>
					</td>
					<?php foreach($dates as $d => $times){ ?>
					<td class="text-center align-top">
						<?php $times = array_unique($times); foreach($times as $t){ ?>
						<div><?= $t ?></div>
						<?php } ?>
					</td>
					<?php } ?>
				</tr>
			</tbody>
		</table>
	</div>
</div>
			
<div class="d-none" id="bl_export_result"></div>

<input type="hidden" id="ip_period" value="<?= $period ?>">

<script>
function apply_filter(dom){
	var criteria = $(dom).val().toUpperCase();
	
	if (criteria == ""){
		$(".row_emp").show();
	}else{
		$(".row_emp").each(function(index, elem) {
			if ($(elem).find(".search_criteria").html().toUpperCase().includes(criteria)) $(elem).show();
			else $(elem).hide();
		});	
	}
}

document.addEventListener("DOMContentLoaded", () => {
	
	$("#form_exp_report").submit(function(e) {
		e.preventDefault();
		$("#form_exp_report .sys_msg").html("");
		ajax_form_warning(this, "page/lgepr_punctuality/export_monthly_report", "Do you want to export monthly punctuality report?").done(function(res) {
			swal_redirection(res.type, res.msg, "page/lgepr_punctuality");
		});
	});
	
	$("#sl_period").change(function(e) {
		window.location.href = "/llamasys/page/lgepr_punctuality?p=" + $(this).val();
	});
	
	$("#sl_dept").change(function(e) {
		$("#ip_search").val('');
		apply_filter(this);
	});
	
	$("#ip_search").change(function(e) {
		$("#sl_dept").val('');
		apply_filter(this);
	});
	
	ajax_simple({p: $("#ip_period").val()}, "page/lgepr_punctuality/export").done(function(res) {
		$("#btn_export").removeClass("d-none");
		$("#btn_export").attr("href", res.url);
	});
	
});
</script>

