<!DOCTYPE html>
<html lang="es">

<div class="pagetitle">
  <h1><br>PI - Listening to you</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
      <li class="breadcrumb-item active">Listening to you</li>
    </ol>
  </nav>
</div>

<div class="card overflow-scroll" style="height: 97vh;">
<section class="section">

  <div class="row">
  
    <div class="col-12">
		
      <div class="card">
        <div class="card-body">
          <!-- <h5 class="card-title">Voices</h5> -->
		  <h5 class="card-title">Voices <br> <a href="#" data-bs-toggle="modal" data-bs-target="#addProblemModal">Add Issue</a></h5>
          <table class="table align-middle" id="dataTable" style="table-layout: fixed;">
			
            <thead>
              <tr>
                <th scope="col" style="width: 30px;">Num</th>
                <th scope="col" style="width: 60px;">Updated</th>
                <th scope="col" style="width: 50px;">For</th>
                <th scope="col" style="width: 400px;">Issue</th>
                <th scope="col" style="width: 300px;">Progress</th>

              </tr>
            </thead>
            <tbody id="tableBody">
              <!-- Los datos se generarán dinámicamente con JavaScript -->
				<?php foreach($records as $i => $item){ ?>
					<tr>
						<th scope="row"><?= $i + 1 ?></th>
							<td id="date-<?= $item->listening_id ?>">
								<?php
									// Mostrar la fecha registrada del problema (por defecto)
									$displayDate = $item->registered;

									// Filtrar los comentarios que pertenecen a este listening_id
									$commentsForListening = array_filter($records_comment, function($comment) use ($item) {
										return $comment->listening_id === $item->listening_id;
									});

									// Si hay comentarios, obtener la fecha del último comentario
									if (!empty($commentsForListening)) {
										// Ordenar los comentarios por la fecha 'updated' en orden descendente (más reciente primero)
										usort($commentsForListening, function($a, $b) {
											return strtotime($b->updated) - strtotime($a->updated);
										});

										// Mostrar la fecha del último comentario (más reciente)
										$displayDate = $commentsForListening[0]->updated;
									}
									// Formatear la fecha para mostrarla sin los segundos (YYYY-MM-DD HH:MM)
									$displayDate = date('Y-m-d H:i', strtotime($displayDate));
									// Imprimir la fecha a mostrar
									echo $displayDate;
									?>
									
								<div class="d-flex justify-content-start align-items-center mt-1">
									
										<?php
											if ($item->status === 'Approved'){?>
												<span class="badge bg-success">
												<i class="bi bi-check-circle me-1"></i>
												<?= $item->status ?>
												</span> 
										<?php	} ?>
										
										<?php
											if ($item->status === 'Refused'){?>
												<span class="badge bg-danger">
												<i class="bi bi-exclamation-octagon me-1"></i>
												<?= $item->status ?>
												</span> 
										<?php	} ?>

										<?php
											if ($item->status === 'Registered'){?>
												<span class="badge rounded-pill bg-dark">
												<i class="bi bi-check-circle me-1"></i>
												<?= $item->status ?>
												</span> 
										<?php	} ?>
										
										<?php
											if ($item->status === 'In progress'){?>
												<span class="badge bg-warning text-dark">
												<i class="bi bi-exclamation-triangle me-1"></i>
												<?= $item->status ?>
												</span> 
										<?php	} ?>
										<!-- Success -->
									
									<!-- <div class="border rounded p-1 bg-light" style="width: 100px; height: 40px;">
										<?= $item->status ?>
									</div>  -->
								</div>

								<!-- <br> -->
								<!-- <label class="text-muted">Status:</label> -->
								<!--<select class="form-select status-select" data-listening-id="<?= $item->listening_id ?>">
									<!--<option value="Registered" <?= $item->status == "Registered" ? "selected" : "" ?>>Registered</option>
									<option value="Approved" <?= $item->status == "Approved" ? "selected" : "" ?>>Approved</option>
									<option value="Refused" <?= $item->status == "Refused" ? "selected" : "" ?>>Refused</option>
									<option value="In progress" <?= $item->status == "In progress" ? "selected" : "" ?>>In progress</option> 
								</select> -->
							</td>
						<td><?= $item->dptTo ?></td>
						<td>
							<?= $item->issue ?>
							
							<br>
								<strong>Proposal </strong>
								<div class="border rounded p-2 bg-light mt-1"><?= $item->solution ?>
						</td>
						<!-- <td><?= $item->status?></td> -->
						<td>
							<a href="#" class="add-comment" data-id="<?= $item->listening_id ?>">Add comment</a>  
						<div class="d-flex flex-column align-items-start" style="width: 600px; ">
						
						
						  
							<div class="border rounded p-2 bg-light ">
								<strong>Last comment:</strong>
																						
								<p id="latest-comment-<?= $item->listening_id ?>">
									<?php
									$latestComment = "";
									$latestCommentDate = "";
									if (!empty($commentsForListening)) {
										usort($commentsForListening, function($a, $b) {
											return strtotime($b->updated) - strtotime($a->updated);
										});
										$latestComment = $commentsForListening[0]->comment;
										$latestCommentDate = $commentsForListening[0]->updated;
									}
									echo $latestComment ?: "No comment.";
									?>
								</p>
							</div>
						</div>
						
						<div id="comment-list-<?= $item->listening_id ?>" class="comment-list d-none">
							<?php foreach ($commentsForListening as $comment) { ?>
								<div class='border p-2 mt-1'>
									<strong style="font-size: 0.8em;"><?= $comment->pr_user ?>:</strong> <!-- Aquí mostramos el nombre del usuario -->
									<br>
									<?= $comment->comment ?> 
									<span class='small text-muted'> - <?= $comment->updated ?></span>
								</div>
							<?php } ?>
						</div>	
							
							
						<a href="#" class="view-more" data-id="<?= $item->listening_id ?>">View more</a>
						<div id="comment-list-<?= $item->listening_id ?>" class="comment-list d-none">
							<?php
							foreach ($commentsForListening as $comment) {
								echo "<div class='border p-2 mt-1'>{$comment->comment} <span class='small text-muted'> - {$comment->updated}</span> </div>";
							}
							?>
						</div>
						</td>
						
					</tr>
				<?php } ?>
				
            </tbody>
          </table>
		  <div class="d-flex justify-content-center mt-3">
				<nav aria-label="Page navigation example">
					<ul class="pagination" id="pagination"></ul>
				</nav>
			</div>
			<!--
		  <div class="d-flex justify-content-center mt-3">
			<div id="pagination"></div>	
		  </div> -->

        </div>
      </div>
    </div>
  </div>

	<!-- Modal para agregar comentarios -->
	<!-- Modal -->
	<div class="modal fade" id="addProblemModal" tabindex="-1" aria-labelledby="addProblemModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addProblemModalLabel">Add Issue</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" id="modalContent">
				<!-- View del modal -->
					<div class="row" >
						<div class="col-md-12 mx-auto pt-3">
							<?php
							$type = $msg = null;
							if ($this->session->flashdata('success_msg')){
								$type = "success";
								$msg = $this->session->flashdata('success_msg');
							}elseif ($this->session->flashdata('error_msg')){
								$type = "danger";
								$msg = $this->session->flashdata('error_msg');
							}
							
							if ($msg){
							?>
							<div class="alert alert-<?= $type ?> fade show" role="alert">
								<?= $msg ?>
							</div>
							<?php } ?>
							<div class="card overflow-hidden">
								<div class="card-body">
									<h5 class="card-title" style = "font-size:25px">PI - LISTENING TO YOU !!!</h5>
									<form class="row g-3" method="POST" action="<?= base_url() ?>page/pi_listening_user/cpilistening">
										<!-- div class="col-md-6">
											<label for="dptFrom" class="form-label">From (Department code provided by PI)</label>
											<input type="text" class="form-control" id="dptFrom" name="dptFrom" value="<?= $this->session->flashdata('dptFrom') ?>" required>
										</div-->
										<div class="col-md-12">
											<label for="dptTo" class="fw-bold" class="form-label">Departamento al que desea presentar la propuesta</label>
											<select id="dptTo" name="dptTo" class="form-select" required>
												<option value="" selected="">Escoger...</option>
												<?php foreach($dpts as $key => $item){ ?>
												<option value="<?= $key ?>" <?= $this->session->flashdata('dptTo') === $key ? "selected" : "" ?>><?= $item ?></option>
												<?php } ?>
											</select>
										</div>
										
										<div class="col-md-6">
											<label for="issue" class="fw-bold" class="form-label">Descripción del Problema</label>
											<label for="issue" class="form-label">Proporcione una descripción detallada del problema</label>
											<textarea class="form-control" id="issue" name="issue" style="height: 300px" required><?= $this->session->flashdata('issue') ?></textarea>
										</div>
										<div class="col-md-6">
											<label for="solution" class="fw-bold" class="form-label">Propuesta de solución</label>
											<label for="solution" class="form-label">Proporcione una idea detallada para abordar el problema</label>
											<textarea class="form-control" id="solution" name="solution" style="height: 300px" required><?= $this->session->flashdata('solution') ?></textarea>
										</div>
										
										<div class="text-center pt-3">
											<button type="submit" class="btn btn-primary">Enviar formulario</button>
										</div>
										
										
								  </form>
								</div>
							</div>
						</div>
					</div>
				
				
				
				
				
				
					<!-- El contenido se cargará aquí -->
				</div>
				<div class="modal-footer justify-content-center mt-3">
					<!--<button type="button" class="btn btn-primary">Submit</button> -->
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>				
				</div>
			</div>
		</div>
	</div>
	

</section>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const rowsPerPage = 5; // Cantidad de filas por página
    const tableBody = document.getElementById("tableBody");
    const rows = Array.from(tableBody.getElementsByTagName("tr"));
    const paginationContainer = document.getElementById("pagination");
    
    // let currentPage = 1;
    // const totalPages = Math.ceil(rows.length / rowsPerPage);
	

    let currentPage = 1;
    const totalPages = Math.ceil(rows.length / rowsPerPage);
	
    function showPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        
        rows.forEach((row, index) => {
            row.style.display = (index >= start && index < end) ? "" : "none";
        });

        updatePaginationButtons();
    }

    function updatePaginationButtons() {
        paginationContainer.innerHTML = "";

        // Botón de "Anterior"
        const prevBtn = document.createElement("li");
        prevBtn.className = `page-item ${currentPage === 1 ? "disabled" : ""}`;
        prevBtn.innerHTML = `
            <a class="page-link" href="#" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        `;
        prevBtn.onclick = function (e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                showPage(currentPage);
            }
        };
        paginationContainer.appendChild(prevBtn);

        // Botones de Páginas
        for (let i = 1; i <= totalPages; i++) {
            const pageBtn = document.createElement("li");
            pageBtn.className = `page-item ${i === currentPage ? "active" : ""}`;
            pageBtn.innerHTML = `
                <a class="page-link" href="#">${i}</a>
            `;
            pageBtn.onclick = function (e) {
                e.preventDefault();
                currentPage = i;
                showPage(i);
            };
            paginationContainer.appendChild(pageBtn);
        }

        // Botón de "Siguiente"
        const nextBtn = document.createElement("li");
        nextBtn.className = `page-item ${currentPage === totalPages ? "disabled" : ""}`;
        nextBtn.innerHTML = `
            <a class="page-link" href="#" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        `;
        nextBtn.onclick = function (e) {
            e.preventDefault();
            if (currentPage < totalPages) {
                currentPage++;
                showPage(currentPage);
            }
        };
        paginationContainer.appendChild(nextBtn);
    }

    // Inicializar la paginación
    if (rows.length > 0) {
        showPage(1);
    }
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {

    // Mostrar u ocultar comentarios
    document.querySelectorAll(".view-more").forEach(btn => {
        btn.addEventListener("click", function () {
            let commentList = document.getElementById(`comment-list-${this.getAttribute("data-id")}`);
            commentList.classList.toggle("d-none");
            this.textContent = commentList.classList.contains("d-none") ? "View more" : "View less";
        });
    });
});
</script>



