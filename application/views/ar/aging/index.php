<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Account to Receive</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item">AR</li>
				<li class="breadcrumb-item active">Aging Report</li>
			</ol>
		</nav>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Data</h5>
					<form class="row g-3" id="form_upload_data">
						<div class="col-md-10">
							<label class="form-label">File</label>
							<input class="form-control" type="file" name="datafile">
						</div>
						<div class="col-md-2 flex-fill align-self-end">
							<div class="d-grid gap-2">
								<button type="submit" class="btn btn-primary">Submit</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-12">
			<div class="card d-none" id="cd_charts">
				<div class="card-body">
					<h5 class="card-title">Charts</h5>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload_data").submit(function(e) {
		e.preventDefault();
		$("#form_upload_data .sys_msg").html("");
		ajax_form_warning(this, "ar/aging/upload_data", "Do you want to upload data file and make charts?").done(function(res) {
			if (res.type == "success"){
				swal_open_tab(res.type, res.msg, res.url);
				//make chart
			}else swal(res.type, res.msg);
		});
	});
	
	/*
	if ($(".bl_move").length > 0){
		var height_n = Math.max($(".bl_move")[0].clientHeight, $(".bl_move")[1].clientHeight, $(".bl_move")[2].clientHeight);
		$(".bl_move").height(height_n);
	}
	
	$('#sl_lz').change(function(){
		$("#sl_li").val(""); $('#sl_li option.sl_li').addClass('d-none'); $('#sl_li option.sl_lz_' + $(this).val()).removeClass('d-none');
		$("#sl_lii").val(""); $('#sl_lii option.sl_lii').addClass('d-none');
		$("#sl_liii").val(""); $('#sl_liii option.sl_liii').addClass('d-none');
		$("#sl_liv").val(""); $('#sl_liv option.sl_liv').addClass('d-none');
		$("#sl_prd").val(""); $('#sl_prd option.sl_prd').addClass('d-none'); $('#sl_prd option.prl_' + $(this).val()).removeClass('d-none');
    });
	
	$('#sl_li').change(function(){
		$("#sl_lii").val(""); $('#sl_lii option.sl_lii').addClass('d-none'); $('#sl_lii option.sl_li_' + $(this).val()).removeClass('d-none');
		$("#sl_liii").val(""); $('#sl_liii option.sl_liii').addClass('d-none');
		$("#sl_liv").val(""); $('#sl_liv option.sl_liv').addClass('d-none');
		$("#sl_prd").val(""); $('#sl_prd option.sl_prd').addClass('d-none'); $('#sl_prd option.prl_' + $(this).val()).removeClass('d-none');
    });
	
	$('#sl_lii').change(function(){
		$("#sl_liii").val(""); $('#sl_liii option.sl_liii').addClass('d-none'); $('#sl_liii option.sl_lii_' + $(this).val()).removeClass('d-none');
		$("#sl_liv").val(""); $('#sl_liv option.sl_liv').addClass('d-none');
		$("#sl_prd").val(""); $('#sl_prd option.sl_prd').addClass('d-none'); $('#sl_prd option.prl_' + $(this).val()).removeClass('d-none');
    });
	
	$('#sl_liii').change(function(){
		$("#sl_liv").val(""); $('#sl_liv option.sl_liv').addClass('d-none'); $('#sl_liv option.sl_liii_' + $(this).val()).removeClass('d-none');
		$("#sl_prd").val(""); $('#sl_prd option.sl_prd').addClass('d-none'); $('#sl_prd option.prl_' + $(this).val()).removeClass('d-none');
    });
	
	$('#sl_liv').change(function(){
		$("#sl_prd").val(""); $('#sl_prd option.sl_prd').addClass('d-none'); $('#sl_prd option.prl_' + $(this).val()).removeClass('d-none');
    });
	
	$('#sl_lz_report').change(function(){
		$("#sl_li_report").val(""); $('#sl_li_report option.sl_li').addClass('d-none'); $('#sl_li_report option.sl_lz_' + $(this).val()).removeClass('d-none');
    });
	
	$('.ctrl_inv').click(function(){
		var ln_i = $(this).attr("id").replace("ctrl_", "");
		
		$(".ln_inv").addClass("d-none");
		if ($(this).hasClass("bi-caret-down-square")){//open list
			$(".ctrl_inv").removeClass("bi-caret-up-square");
			$(".ctrl_inv").addClass("bi-caret-down-square");
		
			$(".ln_inv_" + ln_i).removeClass("d-none");
			$(this).removeClass("bi-caret-down-square");
			$(this).addClass("bi-caret-up-square");
		}else{//close list
			$(this).removeClass("bi-caret-up-square");
			$(this).addClass("bi-caret-down-square");
		}
    });
	
	
	$("#form_upload_sell_inout").submit(function(e) {
		e.preventDefault();
		$("#form_upload_sell_inout .sys_msg").html("");
		ajax_form_warning(this, "sa/sell_inout/upload_sell_inout_file", "Do you upload data?").done(function(res) {
			swal_open_tab(res.type, res.msg, res.url);
		});
	});
	
	
	*/
});
</script>