<div class="pagetitle">
	<h1> Tax Daily Book </h1>
	<nav>
		<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
			<li class="breadcrumb-item active">Tax Daily Book</li>
		</ol>
	</nav>
</div>

<section class="section">
  <div class="row">
    <!-- Columna Única que contiene ambos formularios uno encima del otro -->
    <div class="col-md-6">

      <!-- Formulario para Subir Excel -->
      <div class="card">
        <div class="card-body">
          <h5 class="card-title text-center">Upload Daily Book</h5>
		  
		  <form class="row g-3" id="form_tax_update" href="<?= base_url() ?>" enctype="multipart/form-data">
                <div class="col-md-12">
                  <label class="form-label">Select File</label>
                  <input class="form-control" type="file" name="attach">
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
	  <div class="col-md-6">

      <!-- Formulario para Exportar Reporte Excel -->
      <div class="card">
        <div class="card-body">
			<h5 class="card-title text-center">Export Daily Book</h5>		  
		  <form class="row g-3" id="export_report_form" action="<?= base_url('data_upload/tax_daily_book/export_to_excel')?>" method="POST">
                <div class="col-md-6">
                  <label class="form-label">From</label>
                  <input type="date" class="form-control" id="effective_from" name="effective_from" required>
                </div>
                
				<div class="col-md-6">
                  <label class="form-label">To</label>
                  <input type="date" class="form-control" id="effective_to" name="effective_to" required>
                </div>
				
                <div class="text-center pt-3">
                  <button type="submit" class="btn btn-primary">
					<i class="bi bi-file-earmark-arrow-down"></i> Export				
				  </button>
                </div>
              </form>



			  
        </div>
      </div>
    </div>
  </div>
  
  <table class="table datatable">
		<thead>
			<h5 class="card-title">Last 5000 records </h5>
			<tr>
				<th scope="col">Period Name</th>
				<th scope="col">Effective Date</th>
				<th scope="col">Posted Date</th>
				<th scope="col">Accounting Unit</th>
				<th scope="col">Department Name</th>
				<th scope="col">Currency</th>
				<th scope="col">Net Entered Debit</th>
				<th scope="col">Entered Debit</th>
				<th scope="col">Entered Credit</th>
				<th scope="col">Net Accounted Debit</th>
				<th scope="col">Accounted Debit</th>
				<th scope="col">Accounted Credit</th>
				<th scope="col">Transaction Date</th>
				<th scope="col">Created By</th>
				<th scope="col">Updated</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($tax as $item){ ?>
			<tr>
				<td><?= $item->period_name ?></td>
				<td><?= $item->effective_date ?></td>
				<td><?= $item->posted_date ?></td>
				<td><?= $item->accounting_unit ?></td>
				<td><?= $item->department_name ?></td>
				<td><?= $item->currency ?></td>
				<td><?= $item->net_entered_debit ?></td>
				<td><?= $item->entered_debit ?></td>
				<td><?= $item->entered_credit ?></td>
				<td><?= $item->net_accounted_debit ?></td>
				<td><?= $item->accounted_debit ?></td>
				<td><?= $item->accounted_credit ?></td>		
				<td><?= $item->transaction_date ?></td>
				<td><?= $item->created_by ?></td>
				<td><?= $item->updated ?></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
	$("#form_tax_update").submit(function(e) {
		e.preventDefault();
		ajax_form_warning(this, "data_upload/tax_daily_book/upload", "Do you want to update Tax Daily Book data?").done(function(res) {
			swal_redirection(res.type, res.msg, "data_upload/tax_daily_book");
		});
	});
});
</script>
