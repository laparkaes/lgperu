<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>Vacation</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">My LG</li>
				<li class="breadcrumb-item active">Vacation</li>
			</ol>
		</nav>
	</div>
</div>
<section class="section">
	<div class="row">
		<div class="col-md-3">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">New Request</h5>
					<form class="row g-3" id="form_vacation_request">
						<div class="col-md-12">
							<label class="form-label">Type</label>
							<select class="form-select" name="type">
								<option value="">Choose...</option>
								<?php foreach($type_rec as $item){ ?>
								<option value="<?= $item->lookup ?>"><?= $item->lookup ?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">From</label>
							<input type="date" class="form-control" name="d_from">
						</div>
						<div class="col-md-6">
							<label class="form-label">To</label>
							<input type="date" class="form-control" name="d_to">
						</div>
						<div class="col-md-12">
							<label class="form-label">1st Approver</label>
							<select class="form-select" name="approver_1">
								<option value="">Choose...</option>
								<?php foreach($approvers as $item){ ?>
								<option value="<?= $item->ep_mail ?>" <?= ($item->ep_mail === $last_rec->approver_1 ? "selected" : "") ?>>
									[<?= $item->ep_mail ?>] <?= $item->name ?> (<?= $item->department ?>)
								</option>
								<?php } ?>
								
							</select>
						</div>
						<div class="col-md-12">
							<label class="form-label">2nd Approver</label>
							<select class="form-select" name="approver_2">
								<option value="">Choose...</option>
								<?php foreach($approvers as $item){ ?>
								<option value="<?= $item->ep_mail ?>" <?= ($item->ep_mail === $last_rec->approver_2 ? "selected" : "") ?>>
									[<?= $item->ep_mail ?>] <?= $item->name ?> (<?= $item->department ?>)
								</option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-12">
							<label class="form-label">3rd Approver</label>
							<select class="form-select" name="approver_3">
								<option value="">Choose...</option>
								<?php foreach($approvers as $item){ ?>
								<option value="<?= $item->ep_mail ?>" <?= ($item->ep_mail === $last_rec->approver_3 ? "selected" : "") ?>>
									[<?= $item->ep_mail ?>] <?= $item->name ?> (<?= $item->department ?>)
								</option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-12">
							<label class="form-label">Remark (Optional)</label>
							<textarea class="form-control" name="remark" style="height: 100px"></textarea>
						</div>
						<div class="text-center pt-3">
							<button type="submit" class="btn btn-primary">Request</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-md-9">
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Records</h5>
					<table class="table align-middle">
						<thead>
							<tr>
								<th scope="col">Status</th>
								<th scope="col">Type</th>
								<th scope="col">Requested</th>
								<th scope="col">From</th>
								<th scope="col">To</th>
								<th scope="col">Days</th>
								<th scope="col">Approver</th>
								<th scope="col"></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($records as $item){ ?>
							<tr>
								<td><?= $item->status ?></td>
								<td><?= $item->type ?></td>
								<td><?= date("Y-m-d", strtotime($item->registed_at)) ?></td>
								<td><?= $item->d_from ?></td>
								<td><?= $item->d_to ?></td>
								<td><?= $item->qty_day ?></td>
								<td><?= $item->approver_now ?></td>
								<td class="text-end">
									<?php if ($item->status === "Requested"){ ?>
									<button type="button" class="btn btn-primary btn-sm btn_resend" value="<?= $item->request_id ?>">Resend</button>
									<?php } if ($item->status !== "Cancelled"){ ?>
									<button type="button" class="btn btn-outline-danger btn-sm btn_cancel" value="<?= $item->request_id ?>"><i class="bi bi-trash"></i></button>
									<?php } ?>
								</td>
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
document.addEventListener("DOMContentLoaded", () => {
	$("#form_vacation_request").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "my_lg/vacation/request", "Do you want to request new vacation?").done(function(res) {
			//console.log(res);
			swal_redirection(res.type, res.msg, "my_lg/vacation");
		});
	});
	
	$(".btn_resend").click(function(){
		ajax_simple_warning({"request_id" : $(this).val()}, "my_lg/vacation/resend", "Do you want to request again to actual approver?").done(function(res) {
			swal_redirection(res.type, res.msg, "my_lg/vacation");
		});
	});
	
	$(".btn_cancel").click(function(){
		ajax_simple_warning({"request_id" : $(this).val()}, "my_lg/vacation/cancel", "Do you want to cancel selected vacation plan?").done(function(res) {
			swal_redirection(res.type, res.msg, "my_lg/vacation");
		});
	});
});
</script>