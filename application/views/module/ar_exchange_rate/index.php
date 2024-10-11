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
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title">USD > PEN</h5>
						<button type="button" class="btn btn-primary btn-sm" id="btn_load_pen">Load E.R.</button>
					</div>
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">Date</th>
								<th scope="col">Apply</th>
								<th scope="col">Buy</th>
								<th scope="col">Sell</th>
								<th scope="col">Avg</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($er_pen as $item){ ?>
							<tr>
								<td><?= $item->date ?></td>
								<td><?= $item->date_apply ?></td>
								<td><?= $item->buy ?></td>
								<td><?= $item->sell ?></td>
								<td><?= $item->avg ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card">
				<div class="card-body">
					<div class="d-flex justify-content-between align-items-center">
						<h5 class="card-title">USD > PYG</h5>
						<button type="button" class="btn btn-primary btn-sm" id="btn_load_pyg">Load E.R.</button>
					</div>
					<table class="table datatable">
						<thead>
							<tr>
								<th scope="col">Date</th>
								<th scope="col">Apply</th>
								<th scope="col">Buy</th>
								<th scope="col">Sell</th>
								<th scope="col">Avg</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($er_pyg as $item){ ?>
							<tr>
								<td><?= $item->date ?></td>
								<td><?= $item->date_apply ?></td>
								<td><?= number_format($item->buy, 2) ?></td>
								<td><?= number_format($item->sell, 2) ?></td>
								<td><?= number_format($item->avg, 2) ?></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>
<div class="d-none" id="bl_er_pyg"></div>
<script>
function mesEnNumero(mes) {
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
		"setiembre": 9,
        "octubre": 10,
        "noviembre": 11,
        "diciembre": 12
    };

    return meses[mes.toLowerCase()] || 0;
}

document.addEventListener("DOMContentLoaded", () => {
	
	$("#btn_load_pyg").on("click", function() {
		$("#btn_load_pyg").html('Cargando...');
		$("#btn_load_pyg").attr("disabled", true);
		
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
			
			ajax_simple({data: data}, "module/ar_exchange_rate/upload_pyg").done(function(res) {
				swal_redirection(res.type, res.msg, "module/ar_exchange_rate");
			});
		});
	});
	
	$("#btn_load_pen").on("click", function() {
		$("#btn_load_pen").html('Cargando...');
		$("#btn_load_pen").attr("disabled", true);
		
		ajax_simple({}, "module/ar_exchange_rate/upload_pen").done(function(res) {
			swal_redirection(res.type, res.msg, "module/ar_exchange_rate");
		});
	});
	
});
</script>