<div class="row">
	<div class="col-12">
		<div class="card mt-4">
			<div class="card-body py-0">
				<div class="d-flex justify-content-between align-items-center">
					<div class="d-flex justify-content-start align-items-center">
						<a type="button" class="btn btn-primary me-3 d-none" href="<?= base_url() ?>lgepr/reports"><i class="bi bi-arrow-left"></i></a>
						<h5 class="card-title py-3 my-0">LGEPR Container Plan</h5>
					</div>
					<div class="d-flex justify-content-end align-items-center">
						<form class="input-group">
							<select class="form-select" name="f_company" style="width: 200px;">
								<option value="">All Companies</option>
								<option value="HS" <?= $f_com === "HS" ? "selected" : "" ?>>HS</option>
								<option value="MS" <?= $f_com === "MS" ? "selected" : "" ?>>MS</option>
								<option value="ES" <?= $f_com === "ES" ? "selected" : "" ?>>ES</option>
							</select>
							<select class="form-select" name="f_ctn_step" style="width: 200px;">
								<option value="">All Steps</option>
								<option value="port" <?= $f_step === "port" ? "selected" : "" ?>>Arrived to port</option>
								<option value="temp_wh" <?= $f_step === "temp_wh" ? "selected" : "" ?>>Temporary WH</option>
								<option value="3pl" <?= $f_step === "3pl" ? "selected" : "" ?>>Entered to 3PL</option>
							</select>
							<input type="text" class="form-control" placeholder="Container" name="f_container" style="width: 200px;" value="<?= $f_ctn ?>">
							<button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-12">
		<div class="card">
			<div class="card-body pt-3">
				<table class="table table-bordered text-center">
					<thead id="thead_container">
						<tr class="align-middle">
							<th scope="col" rowspan="2">GERP</th>
							<th scope="col" rowspan="2">Container</th>
							<th scope="col" rowspan="2">Company</th>
							<th scope="col" rowspan="2">Division</th>
							<th scope="col" rowspan="2">Model</th>
							<th scope="col" rowspan="2">Qty</th>
							<th scope="col" colspan="3">Plan</th>
							<th scope="col" colspan="3">Real</th>
							<th scope="col" rowspan="2">WH Temp</th>
							<th scope="col" rowspan="2">Destination</th>
							<th scope="col" rowspan="2">ORG</th>
							<th scope="col" rowspan="2">Type</th>
							<th scope="col" rowspan="2">Updated</th>
						</tr>
						<tr>
							<th scope="col">ETA</th>
							<th scope="col">Pick up</th>
							<th scope="col">Warehouse</th>
							<th scope="col">ATA</th>
							<th scope="col">Pick up</th>
							<th scope="col">warehouse</th>
						</tr>
					</thead>
					<tbody class="table-group-divider">
						<?php foreach($containers as $ctn){ ?>
						<tr>
							<td><?= $ctn->is_received ? "Received" : "Intransit" ?></td>
							<td><?= $ctn->container ?></td>
							<td><?= $ctn->company ?></td>
							<td><?= $ctn->division ?></td>
							<td><?= $ctn->model ?></td>
							<td><?= $ctn->qty ?></td>
							<td><?= $ctn->eta ?></td>
							<td><?= $ctn->picked_up_plan ?></td>
							<td><?= $ctn->wh_arrival_plan ?></td>
							<td><?= $ctn->ata ?></td>
							<td><?= $ctn->picked_up ?></td>
							<td><?= $ctn->wh_arrival ?></td>
							<td><?= $ctn->wh_temp ?></td>
							<td><?= $ctn->destination ?></td>
							<td><?= $ctn->organization ?></td>
							<td><?= $ctn->ctn_type ?></td>
							<td><?= $ctn->updated_at ?></td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script>
function table_header_sticky(){
	const thead = $('#thead_container');
	const scrollThreshold = 100; // 스크롤이 100px 내려갔을 때

	$("#content_canva").on('scroll', function() {
		// 현재 스크롤 위치 가져오기
		const scrollY = $("#content_canva").scrollTop();

		if (scrollY > scrollThreshold) {
			// 스크롤이 100px 이상 내려갔을 때 sticky-top 클래스 추가
			if (!thead.hasClass('sticky-top')) {
				thead.addClass('sticky-top');
				thead.addClass('sticky-header-active'); // 추가 스타일 (선택 사항)
			}
		} else {
			// 스크롤이 100px 미만일 때 sticky-top 클래스 제거
			if (thead.hasClass('sticky-top')) {
				thead.removeClass('sticky-top');
				thead.removeClass('sticky-header-active'); // 추가 스타일 제거
			}
		}
	});
}

document.addEventListener("DOMContentLoaded", () => {
	table_header_sticky();
});
</script>