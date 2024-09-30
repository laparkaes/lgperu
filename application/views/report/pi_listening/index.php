<div class="row">
	<div class="col-md-10 mx-auto pt-3">
		<?php
		$type = $msg = null;
		if ($this->session->flashdata('success_msg')){
			$type = "success";
			$msg = $this->session->flashdata('success_msg');
		}elseif ($this->session->flashdata('error_msg')){
			$type = "danger";
			$msg = $this->session->flashdata('error_msg');
		}
		
		if ($msg){
		?>
		<div class="alert alert-<?= $type ?> fade show" role="alert">
			<?= $msg ?>
		</div>
		<?php } ?>
		<div class="card overflow-hidden">
			<div class="card-body">
				<h5 class="card-title">PI - LISTENING TO YOU !!!</h5>
				<form class="row g-3" method="POST" action="<?= base_url() ?>report/pi_listening/cpilistening">
					<div class="col-md-6">
						<label for="dptFrom" class="form-label">From (Department code provided by PI)</label>
						<input type="text" class="form-control" id="dptFrom" name="dptFrom" value="<?= $this->session->flashdata('dptFrom') ?>" required>
					</div>
					<div class="col-md-6">
						<label for="dptTo" class="form-label">To</label>
						<select id="dptTo" name="dptTo" class="form-select" required>
							<option value="" selected="">Choose...</option>
							<option value="CFO_PI" <?= $this->session->flashdata('dptTo') === "CFO_PI" ? "selected" : "" ?>>Process Innovation</option>
						</select>
					</div>
					<div class="col-md-6">
						<label for="issue" class="form-label">Issue</label>
						<textarea class="form-control" id="issue" name="issue" style="height: 300px" required><?= $this->session->flashdata('issue') ?></textarea>
					</div>
					<div class="col-md-6">
						<label for="solution" class="form-label">Solution</label>
						<textarea class="form-control" id="solution" name="solution" style="height: 300px" required><?= $this->session->flashdata('solution') ?></textarea>
					</div>				
					<div class="text-center pt-5">
						<button type="submit" class="btn btn-primary">Submit</button>
						<button type="reset" class="btn btn-secondary">Reset</button>
					</div>
              </form>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
	var max_day = $("#val_max_day").val();
	
	$("#chk_bill_to").on("change", function() {
		var is_checked = $(this).is(":checked");
		if ($(this).is(":checked")) $(".rows_bill_to").removeClass("d-none"); else $(".rows_bill_to").addClass("d-none");
	});
	
	$(".btn_hide_models").on("click", function() {
		var selected_div = $(this).val();
		
		$(".td_chart").html("");
		
		$(".tb_" + selected_div + "_models").addClass("d-none");
		
		$("#btn_show_models_" + selected_div).removeClass("d-none");
		$(this).addClass("d-none");
	});
	
	$(".btn_view_models").on("click", function() {
		var selected_div = $(this).val();
		
		$(".tb_" + selected_div + "_models").removeClass("d-none");
		
		$("#btn_hide_models_" + selected_div).removeClass("d-none");
		$(this).addClass("d-none");
		
		$(".nsp_summary_" + selected_div).each(function (index, item) {
			var vals = $(item).html().split(",");
			if (vals.length > 1){
				var avg = vals.shift();//nsp avg
				var vals_min = Math.max(...vals);//select max value to get minimum
				var colors = [];
				
				for (let i = 0; i < vals.length; i++) {
					vals[i] = parseFloat(vals[i]);
				
					if ((vals_min > vals[i]) && (vals[i] > 0)) {
						vals_min = vals[i];
					}
					
					if (vals[i] > 0) color = (vals[i] >= (avg * 0.95)) ? "green" : "red";
					else color = "#e2e3e5";
					
					colors.push(color);
				}
				
				var reduce = vals_min * 0.9;
				
				if (vals_min > 0){
					for (let i = 0; i < vals.length; i++) {
						if (vals[i] > 0) vals[i] = vals[i] - reduce;
					}
				}
				
				var td_id = "#" + $(item).attr("id") + "_values";
				var chart_id = $(item).attr("id") + "_chart";
				var val_str = vals.join(",");
				 
				$(td_id).append('<span class="d-none" id="' + chart_id + '">' + val_str + '</span>');
				
				$("#" + chart_id).peity("bar", {
					fill: colors,
					width: (6 * vals.length),
					height: 70,
				});
				
				$("rect").attr("width", "5");
				
				$(td_id).append('<div class="d-flex justify-content-between fw-light"><small>1</small><small>15</small><small>' + max_day + '</small></div>');
			}
			 
		});
	});
	
	
	$(".nsp_summary").each(function (index, item) {
		var vals = $(item).html().split(",");
		if (vals.length > 1){
			var avg = vals.shift();//nsp avg
			var vals_min = Math.max(...vals);//select max value to get minimum
			var colors = [];
			
			for (let i = 0; i < vals.length; i++) {
				vals[i] = parseFloat(vals[i]);
				
				if ((vals_min > vals[i]) && (vals[i] > 0)) {
					vals_min = vals[i];
				}
				
				if (vals[i] > 0) color = (vals[i] >= (avg * 0.95)) ? "green" : "red";
				else color = "#e2e3e5";
				
				colors.push(color);
			}
			
			var reduce = vals_min * 0.9;
			
			if (vals_min > 0){
				for (let i = 0; i < vals.length; i++) {
					if (vals[i] > 0) vals[i] = vals[i] - reduce;
				}
			}
			
			var td_id = "#" + $(item).attr("id") + "_values";
			var chart_id = $(item).attr("id") + "_chart";
			var val_str = vals.join(",");
			
			$(td_id).append('<span class="d-none" id="' + chart_id + '">' + val_str + '</span>');
			
			$("#" + chart_id).peity("bar", {
				fill: function(value) {
					return "#0000007a";
				},
				width: (6 * vals.length),
				height: 70,
			});
			
			$("rect").attr("width", "5");
			
			$(td_id).append('<div class="d-flex justify-content-between fw-light"><small>1</small><small>15</small><small>' + max_day + '</small></div>');
		}
		 
	});
	
});
</script>