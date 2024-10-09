<div class="pagetitle">
	<h1>Paperless Document</h1>
	<nav>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
			<li class="breadcrumb-item active">Exchange Rate</li>
		</ol>
	</nav>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-6">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">USD > PYG</h5>
				</div>
			</div>
		</div>
	</div>
</section>
<div id="bl_er_pyg"></div>
<script>
function mesEnNumero(mes) {
    // 스페인어 월 이름을 숫자로 매핑하는 객체
    const meses = {
        "enero": 1,
        "febrero": 2,
        "marzo": 3,
        "abril": 4,
        "mayo": 5,
        "junio": 6,
        "julio": 7,
        "agosto": 8,
        "septiembre": 9,
        "octubre": 10,
        "noviembre": 11,
        "diciembre": 12
    };

    // 입력한 월 이름을 소문자로 변환한 후 숫자로 변환
    return meses[mes.toLowerCase()] || 0;
}

document.addEventListener("DOMContentLoaded", () => {
	$.get('ar_exchange_rate/proxy_dnit', function(data) {
		$('#bl_er_pyg').html(data);
		
		var data = [];
		$(".journal-content-article").each(function(index, element) {
			var month_year = $(element).find(".section__midtitle").html().replace("Tipos de cambios del mes de ", "").split(" ");
			
			var td_aux;
			$(element).find("tr").each(function(index_day, row) {
				td_aux = $(row).find("td");
				if (isFinite($(td_aux[0]).html())){
					var rowdata = [
						month_year[1], //year
						mesEnNumero(month_year[0]), //month
						$(td_aux[0]).html(), //day
						$(td_aux[1]).html().replace(/\./g, '').replace(',', '.'), //buy
						$(td_aux[2]).html().replace(/\./g, '').replace(',', '.'), //sell
					];
					
					data.push(rowdata);
				}
			});
		});
		
		console.log(data);
		$('#bl_er_pyg').remove();
		
	});
	
	/*
	$("#btn_show").on("click", function() {
		$("#bl_html").html($("#txt_html").val());
	});
	*/
});
</script>