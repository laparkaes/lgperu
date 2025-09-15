<div class="d-flex justify-content-between align-items-start">
	<div class="pagetitle">
		<h1>AR Cash Back</h1>
		<nav>
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
				<li class="breadcrumb-item active">AR Cash Back</li>
			</ol>
		</nav>
	</div>
	<div>
		<a href="../user_manual/data_upload/ar_bank_code/ar_bank_code_en.pptx" class="text-primary">User Manual</a>
	</div>
</div>
<section class="section">
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-center">Upload Cash Back</h5>
                    <form class="row g-3" id="form_cash_back_update" href="<?= base_url() ?>" enctype="multipart/form-data">
                        <div class="col-md-12">
                            <label class="form-label">Select Period</label>
                            <select class="form-control" name="date_period" id="date_period_select">
                                <option value="">Select a period</option>
                                <?php foreach ($dates as $date): ?>
                                    <option value="<?= $date['period_name'] ?>"><?= $date['period_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Select File (raw data)</label>
                            <input class="form-control" type="file" name="attach" id="file_attach" disabled>
                        </div>
                        <div class="text-center pt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-upload"></i> Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_cash_back_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/ar_cash_back/upload", "Do you want to update Daily Book data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/ar_cash_back");
		});
	});
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dateSelect = document.getElementById('date_period_select');
    const fileInput = document.getElementById('file_attach');

    dateSelect.addEventListener('change', function() {
        if (this.value !== "") {
            fileInput.disabled = false;
        } else {
            fileInput.disabled = true;
        }
    });
});
</script>