<div class="card mt-3">
	<div class="card-body">
		<h5 class="card-title">Product Creation for Internal Sale - LGEPR</h5>
		
		<form action="<?= base_url() ?>page/lgepr_internal_sale/insert" enctype="multipart/form-data" method="post" accept-charset="utf-8">

    <label for="category">Category:</label>
    <input type="text" name="category" value=""><br>

    <label for="model">Model:</label>
    <input type="text" name="model" value=""><br>

    <label for="grade">Grade:</label>
    <select name="grade">
		<option value="">Select</option>
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
    </select><br>

    <label for="model">List Price:</label>
    <input type="text" name="price_list" value=""><br>
	
    <label for="model">Offer Price:</label>
    <input type="text" name="price_offer" value=""><br>
	
    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" value=""><br>

    <label for="image_1">Image 1:</label>
    <input type="file" name="images[]"><br>

    <label for="image_2">Image 2:</label>
    <input type="file" name="images[]"><br>

    <label for="image_3">Image 3:</label>
    <input type="file" name="images[]"><br>

    <label for="image_4">Image 4:</label>
    <input type="file" name="images[]"><br>
	
    <label for="image_5">Image 5:</label>
    <input type="file" name="images[]"><br>
	
    <button type="submit">Register Product</button>
    </form>
		
	</div>
</div>

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