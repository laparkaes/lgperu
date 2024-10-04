<div class="pagetitle">
	<h1>PI - Listening to you</h1>
	<nav>
		<ol class="breadcrumb">
			<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
			<li class="breadcrumb-item active">Listening to you</li>
		</ol>
	</nav>
</div>
<section class="section">
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Filter</h5>
					<form class="row g-3">
						<div class="col-md-6">
							<label for="dptFrom" class="form-label">From</label>
							<select id="dptFrom" name="dptFrom" class="form-select">
								<option value="" selected="">Choose...</option>
								<?php foreach($dptsFrom as $item){ ?>
								<option value="<?= $item->dptFrom ?>" <?= $this->input->get("dptFrom") === $item->dptFrom ? "selected" : "" ?>><?= $item->dptFrom ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-6">
							<label for="dptTo" class="form-label">To</label>
							<select id="dptTo" name="dptTo" class="form-select">
								<option value="" selected="">Choose...</option>
								<?php foreach($dptsTo as $item){ ?>
								<option value="<?= $item->dptTo ?>" <?= $this->input->get("dptTo") === $item->dptTo ? "selected" : "" ?>><?= $item->dptTo ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-12 pt-3 text-center">
							<button type="submit" class="btn btn-primary">Show</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-12">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Voices</h5>
					<table class="table align-middle">
						<thead>
							<tr>
								<th scope="col">Num</th>
								<th scope="col" style="width: 200px;">Status</th>
								<th scope="col" style="width: 200px;">From</th>
								<th scope="col" style="width: 200px;">To</th>
								<th scope="col">Issue</th>
								<th scope="col">Propose</th>
								<th scope="col">Response</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($records as $i => $item){ ?>
							<tr>
								<th scope="col"><?= $i + 1 ?></th>
								<td>
									<select class="form-select sl_status" listening_id="<?= $item->listening_id ?>">
										<option value="" selected="">Choose...</option>
										<option value="Registered" <?= "Registered" === $item->status ? "selected" : "" ?>>Registered</option>
										<option value="Accepted" <?= "Accepted" === $item->status ? "selected" : "" ?>>Accepted</option>
										<option value="Rejected" <?= "Rejected" === $item->status ? "selected" : "" ?>>Rejected</option>
										<option value="Processing" <?= "Processing" === $item->status ? "selected" : "" ?>>Processing</option>
										<option value="Finished" <?= "Finished" === $item->status ? "selected" : "" ?>>Finished</option>
									</select>
								</td>
								<td><?= $item->dptFrom ?></td>
								<td><?= $item->dptTo ?></td>
								<td><textarea class="form-control ta_issue" style="height: 250px;" listening_id="<?= $item->listening_id ?>"><?= $item->issue ?></textarea></td>
								<td><textarea class="form-control ta_solution" style="height: 250px;" listening_id="<?= $item->listening_id ?>"><?= $item->solution ?></textarea></td>
								<td><textarea class="form-control ta_response" style="height: 250px;" listening_id="<?= $item->listening_id ?>"><?= $item->response ?></textarea></td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>
<script>
function update_voice(data){
	ajax_simple(data, "module/pi_listening/update").done(function(res) {
		//swal(res.type, res.msg);
		console.log(res.msg);
	});
}

document.addEventListener("DOMContentLoaded", () => {
	$(".sl_status").on("change", function() {
		update_voice({listening_id: $(this).attr("listening_id"), status: $(this).val()});
	});
	
	$(".ta_issue").on("focusout", function() {
		update_voice({listening_id: $(this).attr("listening_id"), issue: $(this).val()});
	});
	
	$(".ta_solution").on("focusout", function() {
		update_voice({listening_id: $(this).attr("listening_id"), solution: $(this).val()});
	});
	
	$(".ta_response").on("focusout", function() {
		update_voice({listening_id: $(this).attr("listening_id"), response: $(this).val()});
	});
});
</script>