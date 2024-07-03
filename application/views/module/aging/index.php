<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Aging Report</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">Aging Report</li>
			</ol>
		</nav>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-4">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Data</h5>
					<form class="row g-3" id="form_upload_data">
						<div class="col-md-6">
							<label class="form-label">Currency</label>
							<select class="form-select" name="curr">
								<option value="usd">USD</option>
								<option value="pen">PEN</option>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Exchange Rate</label>
							<input class="form-control" type="number" name="er" value="3.8" step="0.001">
						</div>
						<div class="col-md-12">
							<label class="form-label">File</label>
							<input class="form-control" type="file" name="datafile">
						</div>
						<div class="col-md-12 flex-fill align-self-end pt-3">
							<div class="d-grid gap-2">
								<button type="submit" class="btn btn-primary">Submit</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Summary</h5>
					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item" role="presentation">
							<button class="nav-link active" id="inv-tab" data-bs-toggle="tab" data-bs-target="#inv" type="button" role="tab" aria-controls="inv" aria-selected="true">Invoice</button>
						</li>
						<li class="nav-item" role="presentation">
							<button class="nav-link" id="cre-tab" data-bs-toggle="tab" data-bs-target="#cre" type="button" role="tab" aria-controls="cre" aria-selected="false" tabindex="-1">Credit Memo</button>
						</li>
						<li class="nav-item" role="presentation">
							<button class="nav-link" id="cha-tab" data-bs-toggle="tab" data-bs-target="#cha" type="button" role="tab" aria-controls="cha" aria-selected="false" tabindex="-1">Chargeback</button>
						</li>
					</ul>
					<div class="tab-content pt-2" id="myTabContent">
						<div class="tab-pane fade show active" id="inv" role="tabpanel" aria-labelledby="inv-tab">
							<table class="table">
								<thead>
									<tr>
										<th scope="col">Number</th>
										<th scope="col">Customer</th>
										<th scope="col">Current</th>
										<th scope="col">1~7 Days</th>
										<th scope="col">8~15 Days</th>
										<th scope="col">16~30 Days</th>
										<th scope="col">31~45 Days</th>
										<th scope="col">46~60 Days</th>
										<th scope="col">61~90 Days</th>
										<th scope="col">91~180 Days</th>
										<th scope="col">181~360 Days</th>
										<th scope="col">361+ Days</th>
									</tr>
								</thead>
								<tbody class="text-end" id="tb_inv"></tbody>
							</table>
						</div>
						<div class="tab-pane fade" id="cre" role="tabpanel" aria-labelledby="cre-tab">
							<table class="table">
								<thead>
									<tr>
										<th scope="col">Number</th>
										<th scope="col">Customer</th>
										<th scope="col">Current</th>
										<th scope="col">1~7 Days</th>
										<th scope="col">8~15 Days</th>
										<th scope="col">16~30 Days</th>
										<th scope="col">31~45 Days</th>
										<th scope="col">46~60 Days</th>
										<th scope="col">61~90 Days</th>
										<th scope="col">91~180 Days</th>
										<th scope="col">181~360 Days</th>
										<th scope="col">361+ Days</th>
									</tr>
								</thead>
								<tbody class="text-end" id="tb_cre"></tbody>
							</table>
						</div>
						<div class="tab-pane fade" id="cha" role="tabpanel" aria-labelledby="cha-tab">
							<table class="table">
								<thead>
									<tr>
										<th scope="col">Number</th>
										<th scope="col">Customer</th>
										<th scope="col">Current</th>
										<th scope="col">1~7 Days</th>
										<th scope="col">8~15 Days</th>
										<th scope="col">16~30 Days</th>
										<th scope="col">31~45 Days</th>
										<th scope="col">46~60 Days</th>
										<th scope="col">61~90 Days</th>
										<th scope="col">91~180 Days</th>
										<th scope="col">181~360 Days</th>
										<th scope="col">361+ Days</th>
									</tr>
								</thead>
								<tbody class="text-end" id="tb_cha"></tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_upload_data").submit(function(e) {
		e.preventDefault();
		$("#tb_inv").html("");
		$("#tb_cre").html("");
		$("#tb_cha").html("");
		
		$("#form_upload_data .sys_msg").html("");
		ajax_form_warning(this, "module/aging/upload_data", "Do you want to upload data file and make summary report?").done(function(res) {
			if (res.type == "success"){
				swal_open_tab(res.type, res.msg, res.data.url);
				
				//make tables
				var rows = res.data.rows;
				rows.forEach((item) => {
					$("#tb_inv").append('<tr><td class="text-start">' + item[0] + '</td><td class="text-start">' + item[1] + '</td><td>' + item[3] + '</td><td>' + item[4] + '</td><td>' + item[5] + '</td><td>' + item[6] + '</td><td>' + item[7] + '</td><td>' + item[8] + '</td><td>' + item[9] + '</td><td>' + item[10] + '</td><td>' + item[11] + '</td><td>' + item[12] + '</td></tr>');
					$("#tb_cre").append('<tr><td class="text-start">' + item[0] + '</td><td class="text-start">' + item[1] + '</td><td>' + item[14] + '</td><td>' + item[15] + '</td><td>' + item[16] + '</td><td>' + item[17] + '</td><td>' + item[18] + '</td><td>' + item[19] + '</td><td>' + item[20] + '</td><td>' + item[21] + '</td><td>' + item[22] + '</td><td>' + item[23] + '</td></tr>');
					$("#tb_cha").append('<tr><td class="text-start">' + item[0] + '</td><td class="text-start">' + item[1] + '</td><td>' + item[25] + '</td><td>' + item[26] + '</td><td>' + item[27] + '</td><td>' + item[28] + '</td><td>' + item[29] + '</td><td>' + item[30] + '</td><td>' + item[31] + '</td><td>' + item[32] + '</td><td>' + item[33] + '</td><td>' + item[34] + '</td></tr>');
				});
			}else swal(res.type, res.msg);
		});
	});
});
</script>