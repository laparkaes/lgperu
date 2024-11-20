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
					<h5 class="card-title">Voices</h5>
					<table class="table align-middle">
						<thead>
							<tr>
								<th scope="col">Num</th>
								<th scope="col" style="width: 200px;">Department</th>
								<th scope="col">Issue</th>
								<th scope="col">Received Proposal</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($records as $i => $item){ ?>
							<tr>
								<th scope="col"><?= $i + 1 ?></th>
								<td><?= $item->dptTo ?></td>
								<td style="white-space:pre;"><?= $item->issue ?></td>
								<td style="white-space:pre;"><?= $item->solution ?></td>
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
	/*
	ajax_simple(data, "module/pi_listening/update").done(function(res) {
		//swal(res.type, res.msg);
		console.log(res.msg);
	});
	*/
}

document.addEventListener("DOMContentLoaded", () => {
	/*
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
	*/
});
</script>