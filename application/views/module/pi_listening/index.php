<!DOCTYPE html>
<head>
<style>
    .custom-select {
		appearance: none; /* Quita la flecha predeterminada */
		-webkit-appearance: none; /* Para navegadores basados en WebKit (Safari, Chrome) */
		-moz-appearance: none; /* Para Firefox */
		background-image: url('data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='currentColor' class='bi bi-caret-down' viewBox='0 0 16 16'%3E%3Cpath d='M3.204 5h9.592L8 10.481 3.204 5zm-.753.659 4.808 4.808a1 1 0 0 0 1.506 0l4.808-4.808A1 1 0 0 0 12.592 5H3.408a1 1 0 0 0-.753.659z'/%3E%3C/svg%3E'); /* Agrega una flecha personalizada */
		background-repeat: no-repeat;
		background-position: right 8px center; /* Ajusta la posición de la flecha */
		background-size: 8px; /* Ajusta el tamaño de la flecha */
		padding-right: 20px; /* Asegura espacio para la flecha */
	}
</style>
</head>
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
          <div class="d-flex justify-content-between align-items-center">
				<h5 class="card-title mb-0 me-2">Voices</h5>
				<div class="d-flex justify-content-end">					
					<!-- Campos de fecha para filtrar -->
					
					<input class="form-select me-1" type="date" id="fromDate" name="fromDate" >

					<input class="form-select me-1" type="date" id="toDate" name="toDate">

					
					<select class="form-select me-1" id="sl_status" style="width: 250px;">
						<option value="">Status --</option>
						<?php 
						$uniqueStatus = []; // Array para almacenar valores únicos
						foreach ($records as $item) { 
							if (!in_array($item->status, $uniqueStatus)) { 
								$uniqueStatus[] = $item->status; // Agregar al array de valores únicos
						?>
								<option value="<?= htmlspecialchars($item->status, ENT_QUOTES, 'UTF-8') ?>">
									<?= htmlspecialchars($item->status, ENT_QUOTES, 'UTF-8') ?>
								</option>
						<?php 
							} 
						} 
						?>
					</select>
					
					<select class="form-select me-1" id="sl_dept" style="width: 250px;">
						<option value="">All departments --</option>
						<?php 
						$uniqueDepartments = []; // Array para almacenar valores únicos
						foreach ($records as $item) { 
							if (!in_array($item->dptTo, $uniqueDepartments)) { 
								$uniqueDepartments[] = $item->dptTo; // Agregar al array de valores únicos
						?>
								<option value="<?= htmlspecialchars($item->dptTo, ENT_QUOTES, 'UTF-8') ?>">
									<?= htmlspecialchars($item->dptTo, ENT_QUOTES, 'UTF-8') ?>
								</option>
						<?php 
							} 
						} 
						?>
					</select>
					
					<input type="text" id="searchInput" class="form-control w-auto" style="max-width: 300px;" placeholder="Search...">
					
					<select class="form-select ms-1" style="width: auto; max-width: 150px;" id="global-language-selector">
					  <option value="es">ES</option>
					  <option value="en">EN</option>
					  <option value="kr">KR</option>
					</select>		   
				</div>
			</div>
		  
          <table class="table border table-striped align-middle" id="dataTable" style="table-layout: fixed;">
			
            <thead>
              <tr class="table-dark">
                <th scope="col" style="width: 20px;text-align: center;">Updated</th>
                <th scope="col" style="width: 20px;text-align: center;">Department</th>
                <th scope="col" style="width: 100px;text-align: center;">Issue</th>
                <th scope="col" style="width: 100px;text-align: center;">Progress</th>
				<th scope="col" style="width: 20px;text-align: center;"></th>
              </tr>
            </thead>
            <tbody id="tableBody">
              <!-- Los datos se generarán dinámicamente con JavaScript -->
				<?php foreach($records as $i => $item){ ?>
					<tr>
						<!--<th  scope="row"><?= $i + 1 ?></th>-->
							<td style="text-align: center;" id="date-<?= $item->listening_id ?>">
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
									//$displayHour = date('H:i', strtotime($displayDate));
									$displayDate = date('Y-m-d', strtotime($displayDate));
									//$displayDate = date('Y-m-d H:i', strtotime($displayDate));
									// Imprimir la fecha a mostrar
									echo $displayDate; echo '<br>'; 
									?>							
								<!-- <br> -->
								<!-- <label class="text-muted">Status:</label> -->
								<div class="text-center">
									<select class="form-select status-select custom-select" style="width: 110px; font-size: 14px; margin: 0 auto; display: block;" data-listening-id="<?= $item->listening_id ?>">
										<option value="Registered" <?= $item->status == "Registered" ? "selected" : "" ?>>Registered</option>
										<option value="Finished" <?= $item->status == "Finished" ? "selected" : "" ?>>Finished</option>
										<option value="Refused" <?= $item->status == "Refused" ? "selected" : "" ?>>Refused</option>
										<option value="In progress" <?= $item->status == "In progress" ? "selected" : "" ?>>In progress</option>
									</select>
								</div>
							</td>
						<td style="text-align: center;"><?= $item->dptTo ?></td>
						<td class="align-top">
							<!-- <div class="border rounded p-2 bg-light"><?= $item->issue ?> </div> -->
								<?php
								$maxLengthIssue = 100; // Longitud máxima para "issue"
								$fullTextIssue = $item->issue;
								$truncatedTextIssue = substr($fullTextIssue, 0, $maxLengthIssue);
								if (strlen($fullTextIssue) > $maxLengthIssue) {
									$truncatedTextIssue .= "...";
								}
								?>
								<div class="border rounded p-2 bg-light" style="display: inline-block;" id="issue-box-<?= $item->listening_id ?>">
									<span style="font-size: 15px;" id="truncated-issue-<?= $item->listening_id ?>"><?= $truncatedTextIssue ?></span>
									<?php if (strlen($fullTextIssue) > $maxLengthIssue): ?>
										<a href="#" class="read-more-issue" style="font-size: 14px;" data-id="<?= $item->listening_id ?>">read more</a>
										<span id="full-issue-<?= $item->listening_id ?>" style="display: none; font-size: 15px;"><?= $fullTextIssue ?> <a href="#" class="read-less-issue" style="font-size: 14px;" data-id="<?= $item->listening_id ?>">read less</a></span>
									<?php endif; ?>
								</div>
								<br>
								<strong>Proposal </strong><br>
								<?php
								$maxLengthProposal = 150; // Longitud máxima para "proposal"
								$fullTextProposal = $item->solution;
								$truncatedTextProposal = substr($fullTextProposal, 0, $maxLengthProposal);
								if (strlen($fullTextProposal) > $maxLengthProposal) {
									$truncatedTextProposal .= "...";
								}
								?>
								<div class="border rounded p-2 mt-1 bg-light" style="display: inline-block;" id="proposal-box-<?= $item->listening_id ?>">
									<span style="font-size: 15px;" id="truncated-proposal-<?= $item->listening_id ?>"><?= $truncatedTextProposal ?></span>
									<?php if (strlen($fullTextProposal) > $maxLengthProposal): ?>
										<a href="#" class="read-more-proposal" style="font-size: 14px;" data-id="<?= $item->listening_id ?>">read more</a>
										<span id="full-proposal-<?= $item->listening_id ?>" style="display: none; font-size: 15px;"><?= $fullTextProposal ?> <a href="#" class="read-less-proposal" style="font-size: 14px;" data-id="<?= $item->listening_id ?>">read less</a></span>
									<?php endif; ?>
								</div>
						</td>
						<!-- <td><?= $item->status?></td> -->
						<td class="align-top">
							<!--<a href="#" class="add-comment" data-id="<?= $item->listening_id ?>">Add comment</a>  -->
						<div class="text-start" style="padding-left: 10px;">					  
							<div id="latest-comment-<?= $item->listening_id ?>">									
									<?php
									
									$latestComment = "";
									$latestCommentDate = "";
									$latestCommentUser = "";
									$latestCommentId = null; 
									if (!empty($commentsForListening)) {
										usort($commentsForListening, function($a, $b) {
											return strtotime($b->updated) - strtotime($a->updated);
										});
										$latestComment = $commentsForListening[0]->comment_es;
										$latestCommentDate = $commentsForListening[0]->updated;
										$latestCommentUser = $commentsForListening[0]->pr_user;
										$latestCommentId = $commentsForListening[0]->comment_id;
									}?>
									<a class="list-group-item list-group-item-action border rounded  p-2 bg-light"> 
										<div class="d-flex w-100 justify-content-between">
											<h5 class="mb-1">												
												<strong><?= $latestCommentUser ?: "No user" ?>: </strong>
												<button type="button" class="btn btn-outline-primary edit-comment btn-sm" 
													
													data-comment-id='<?= $latestCommentId ?>'
													data-listening-id='<?= $item->listening_id ?>'
													data-comment='<?= htmlspecialchars($latestComment, ENT_QUOTES) ?>'
													style="padding: 2px 6px; font-size: 12px; line-height: 2;">
													<i class="bi bi-pencil"></i>													
												</button>
											</h5>
											<small class="text-muted"><?= $latestCommentDate ?></small>
										</div>
								
										<p class="mb-1"><?=nl2br(htmlspecialchars($latestComment)) ?: "No comment" ?></p>
										
										<?php if (!empty($latestComment)) : ?>
											
										<?php endif; ?>
									</a>
							</div>
						
						
						</div>
						
						 <!-- Lista de comentarios -->
						<div id="comment-list-<?= $item->listening_id ?>" class="comment-list d-none" style='padding-left: 10px;'>
							<?php
							// Excluir el comentario más reciente del "last comment"
							$commentsForViewing = array_filter($commentsForListening, function($comment) use ($latestComment) {
								return $comment->comment_es !== $latestComment;
							});

							// Ordenar los comentarios restantes de más reciente a más antiguo
							usort($commentsForViewing, function($a, $b) {
								return strtotime($b->updated) - strtotime($a->updated);
							});

							foreach ($commentsForViewing as $comment) {
								echo "<a href='#' class='list-group-item list-group-item-action border rounded bg-light p-2 mt-1' >
										<div class='d-flex w-100 justify-content-between'>
											<h5 class='mb-1'>{$comment->pr_user}:
												<button type='button' class='btn btn-outline-primary btn-sm edit-comment' 
												
												data-comment-id='{$comment->comment_id}'
												data-listening-id='{$item->listening_id}'
												data-comment='" . htmlspecialchars($comment->comment_es, ENT_QUOTES) . "'>
												<i class='bi bi-pencil'></i>
												</button>
												
											</h5>
											<small class='text-muted'>" . $comment->updated . "</small>
										</div>
										<p class='mb-1'>" . nl2br(htmlspecialchars($comment->comment_es)) . "</p>
										
									  </a>";
							}
							?>
						</div>
						
							<a style='padding-left: 20px;' href="#" class="view-more" data-id="<?= $item->listening_id ?>">View more</a>
						</td>
						<td class="align-top text-center">
							<button class='btn btn-sm btn-outline-primary add-comment' data-id="<?= $item->listening_id ?>"
							style="padding: 4px 10px; border-radius: 0px;">
									<strong>+</strong>
							</button>
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
        </div>
      </div>
    </div>
  </div>
	
	<!-- Modal agregar comentarios -->
	<div id="commentModal" class="modal fade" tabindex="-1" role="dialog">
	  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title">Add Comment</h5>
			<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
		  </div>
		  <div class="modal-body">
			<!-- Contenedor de 3 columnas para cada idioma -->
			<div class="row">
			  <!-- Español -->
			  <div class="col-md-4">
				<label for="commentEs" class="form-label">ES</label>
				<textarea id="commentEs" class="form-control" rows="4" style="height: 221px;" placeholder="Escribe en español..."></textarea>
			  </div>
			  <!-- Coreano -->
			  <div class="col-md-4">
				<label for="commentKo" class="form-label">KR</label>
				<textarea id="commentKo" class="form-control" rows="4" style="height: 221px;" placeholder="Escribe en coreano..."></textarea>
			  </div>
			  <!-- Inglés -->
			  <div class="col-md-4">
				<label for="commentEn" class="form-label">EN</label>
				<textarea id="commentEn" class="form-control" rows="4" style="height: 221px;" placeholder="Escribe en inglés..."></textarea>
			  </div>
			</div>
			<input type="hidden" id="currentListeningId">
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-primary" id="submitComment">Submit</button>
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
		  </div>
		</div>
	  </div>
	</div>



	<!-- Modal para editar comentario -->
	<div id="editCommentModal" class="modal fade" tabindex="-1" role="dialog">
	  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<h5 class="modal-title">Editar Comentario</h5>
			<button type="button" class="close" data-bs-dismiss="modal">&times;</button>
		  </div>
		  <div class="modal-body">
			<!-- Select para idioma -->
			<div class="mb-3">
			  <label for="editLanguageSelect" class="form-label">Selecciona el idioma</label>
			  <select id="editLanguageSelect" class="form-select">
				<option value="es">Español</option>
				<option value="ko">Coreano</option>
				<option value="en">Inglés</option>
			  </select>
			</div>

			<!-- Campo de texto para el comentario -->
			<textarea id="editCommentText" class="form-control" rows="4"></textarea>
			<input type="hidden" id="editCommentId">
			<input type="hidden" id="editListeningId">
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-primary" id="submitEditComment">Guardar</button>
			<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
    // Abrir el modal al hacer clic en "Agregar comentarios"
    document.querySelectorAll(".add-comment").forEach(btn => {
        btn.addEventListener("click", function () {
            let listeningId = this.getAttribute("data-id");
            document.getElementById("currentListeningId").value = listeningId;

            // Limpiar los campos de comentarios
            document.getElementById("commentEs").value = "";
            document.getElementById("commentKo").value = "";
            document.getElementById("commentEn").value = "";

            let modal = new bootstrap.Modal(document.getElementById("commentModal"));
            modal.show();
        });
    });

    // Enviar comentarios
    document.getElementById("submitComment").addEventListener("click", function () {
        let listeningId = document.getElementById("currentListeningId").value;
        let commentEs = document.getElementById("commentEs").value.trim();
        let commentKo = document.getElementById("commentKo").value.trim();
        let commentEn = document.getElementById("commentEn").value.trim();
        if (commentEs === "" && commentKo === "" && commentEn === "") {
            alert("Debes escribir al menos un comentario.");
            return;
        }

        // Crear el cuerpo de la petición con solo los campos que tienen valores
        let formData = `listening_id=${listeningId}`;
        if (commentEs) formData += `&comment_es=${encodeURIComponent(commentEs)}`;
        if (commentKo) formData += `&comment_kr=${encodeURIComponent(commentKo)}`;
        if (commentEn) formData += `&comment_en=${encodeURIComponent(commentEn)}`;

        // Enviar los datos por AJAX
        fetch("<?= base_url('module/pi_listening/add') ?>", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar la última fecha de actualización
                document.getElementById(`date-${listeningId}`).textContent = data.updated;

                // Actualizar la tabla con el último comentario registrado (priorizando idioma español)
                let latestComment = commentEs || commentKo || commentEn;
                document.getElementById(`latest-comment-${listeningId}`).textContent = latestComment;

                // Mover la fila al inicio de la tabla
                let row = document.querySelector(`[data-id="${listeningId}"]`).closest("tr");
                let tableBody = document.getElementById("tableBody");
                tableBody.prepend(row);

                // Limpiar y cerrar el modal
                document.getElementById("commentEs").value = "";
                document.getElementById("commentKo").value = "";
                document.getElementById("commentEn").value = "";
                bootstrap.Modal.getInstance(document.getElementById("commentModal")).hide();
				location.reload(); // Recargar la página para ver el nuevo comentario en la lista completa
            } else {
                alert("Error al agregar comentario.");
            }
        });
    });

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


<script>
// Cambio de combo en updated
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".status-select").forEach(select => {
        select.addEventListener("change", function () {
            let listeningId = this.getAttribute("data-listening-id");
            let newStatus = this.value;

            fetch("<?= base_url('./module/pi_listening/update_status') ?>", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `listening_id=${listeningId}&status=${newStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Estado actualizado correctamente.");
                } else {
                    alert("Error al actualizar el estado.");
                }
            })
			.catch(error => console.error("Error en la solicitud:", error));
        });
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {

  // Abrir modal de edición de comentario
  $(document).on('click', '.edit-comment', function(e) {
    e.preventDefault();

    let commentId = $(this).data('comment-id');
    let listeningId = $(this).data('listening-id');
    let comment = $(this).data('comment');

    // Mostrar modal de edición
    $('#editCommentModal').modal('show');
    
    // Asignar valores al formulario
    $('#editCommentId').val(commentId);
    $('#editListeningId').val(listeningId);
    $('#editCommentText').val(comment); // Asignar el comentario en español por defecto

    // Establecer idioma por defecto a español
    $('#editLanguageSelect').val('es');
  });

  // Mostrar comentario según el idioma seleccionado
  $('#editLanguageSelect').change(function() {
    let selectedLanguage = $(this).val();  // Idioma seleccionado
    let commentId = $('#editCommentId').val();  // ID del comentario
    let listeningId = $('#editListeningId').val();  // ID del listening

    // Buscar el comentario correspondiente según el idioma seleccionado
    let comment = null;
    
    // Aquí generamos un array con los comentarios
    let comments = <?php echo json_encode($records_comment); ?>;

    // Filtrar los comentarios para el listening_id actual
    let filteredComments = comments.filter(function(c) {
      return c.comment_id == commentId;
    });

    // Obtener el comentario del idioma seleccionado
    for (let i = 0; i < filteredComments.length; i++) {
      if (selectedLanguage === 'es') {
        comment = filteredComments[i].comment_es;
      } else if (selectedLanguage === 'en') {
        comment = filteredComments[i].comment_en;
      } else if (selectedLanguage === 'ko') {
        comment = filteredComments[i].comment_kr;
      }
    }

    // Actualizar el texto del textarea con el comentario en el idioma seleccionado
    $('#editCommentText').val(comment || '');  // Si no existe comentario, dejamos el campo vacío
  });

  // Enviar el comentario editado
  $('#submitEditComment').click(function() {
    let commentId = $('#editCommentId').val();
    let listeningId = $('#editListeningId').val();
    let commentText = $('#editCommentText').val();
    let language = $('#editLanguageSelect').val();

    // Lógica para actualizar el comentario en el backend
    $.ajax({
      url: '<?= base_url() ?>module/pi_listening/update_comment', // Ajusta la URL a la ruta de tu controlador
      method: 'POST',
      data: {
        comment_id: commentId,
        listening_id: listeningId,
        comment: commentText,
        language: language // Enviar el idioma seleccionado
      },
	 
		success: function(response) {
			console.log(response);  // Verifica la respuesta completa en la consola

			// Intentar convertir la respuesta en un objeto
			try {
				response = JSON.parse(response);
			} catch (e) {
				console.error("Error al parsear JSON:", e);
			}

			if (response && response.success) {
				// Cerrar el modal y mostrar un mensaje de éxito
				$('#editCommentModal').modal('hide');
				alert('Comentario actualizado correctamente.');
						// Refrescar la página después de un segundo
				setTimeout(function() {
					location.reload();
				}, 500);  // Espera 1 segundo antes de recargar la página
			} else {
				alert('Hubo un error al actualizar el comentario.');
			}
		},

    });
  });

});
</script>


<script>
document.getElementById("searchInput").addEventListener("keyup", function() {
    let input = this.value.toLowerCase();
    let rows = document.querySelectorAll("#dataTable tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
});
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const deptSelect = document.getElementById('sl_dept');
    const statusSelect = document.getElementById('sl_status');
    const searchInput = document.getElementById('searchInput'); // Suponiendo que el campo de búsqueda tiene id="search"
    const startDateInput = document.getElementById('fromDate'); // Suponiendo que hay un input de fecha de inicio
    const endDateInput = document.getElementById('toDate'); // Suponiendo que hay un input de fecha de fin
    const rows = document.querySelectorAll("#dataTable tbody tr");
    const itemsPerPage = 5;
    let currentPage = 1;

    // Función para actualizar los filtros
    function applyFilters() {
        const selectedDept = deptSelect.value.toLowerCase();
        const selectedStatus = statusSelect.value.toLowerCase();
        const searchTerm = searchInput.value.toLowerCase();
        const startDate = startDateInput.value ? convertDateToISO(startDateInput.value) : null;
        const endDate = endDateInput.value ? convertDateToISO(endDateInput.value) : null;

        // Filtrar filas según los criterios seleccionados
        const filteredRows = Array.from(rows).filter(row => {
            const deptCell = row.querySelector('td:nth-child(2)'); // Suponiendo que el depto está en la segunda columna
            const statusCell = row.querySelector('td:nth-child(1) select'); // Suponiendo que el status está en la segunda columna
            const dateCell = row.querySelector("td[id^='date-']"); // Suponiendo que la fecha está en la cuarta columna

            const deptText = deptCell ? deptCell.innerText.toLowerCase() : '';
            const statusText = statusCell ? statusCell.value.toLowerCase() : '';
            //const rowDate = dateCell ? new Date(dateCell.innerText) : new Date(0);
			const rowDateText  = dateCell ? dateCell.innerText.trim() : '';
			
			// Convertir la fecha de la fila a Date
            let rowDate = rowDateText ? convertDateToISO(rowDateText) : null;
			
            // Aplicar filtros: dept, status, búsqueda y fecha
            const matchesDept = selectedDept === '' || deptText.includes(selectedDept);
            const matchesStatus = selectedStatus === '' || statusText.includes(selectedStatus);
            const matchesSearch = searchTerm === '' || deptText.includes(searchTerm) || statusText.includes(searchTerm);
            const matchesDate = (!rowDate || (!startDate || rowDate >= startDate) && (!endDate || rowDate <= endDate));

            return matchesDept && matchesStatus && matchesSearch && matchesDate;
        });

        // Actualizar filas visibles
        updatePagination(filteredRows);
    }
	
	function convertDateToISO(dateString) {
        let parts = dateString.split('/'); // [mm, dd, yyyy]
        if (parts.length === 3) {
            return `${parts[2]}-${parts[0].padStart(2, '0')}-${parts[1].padStart(2, '0')}`;
        }
        return dateString; // Si no tiene el formato esperado, devolver la fecha sin cambios
    }
	
	function adjustProposalWidth(listeningId) {
        const issueBox = document.getElementById('issue-box-' + listeningId);
        const proposalBox = document.getElementById('proposal-box-' + listeningId);
		
		if (issueBox && proposalBox) {
			// Establecer el ancho de issueBox al máximo disponible
			issueBox.style.width = issueBox.parentElement.offsetWidth + 'px';

			// Establecer el ancho de proposalBox para que coincida con issueBox
			proposalBox.style.width = issueBox.offsetWidth + 'px';
		}
    }
	
    // Función para actualizar la paginación
    function updatePagination(filteredRows) {
        // Limpiar filas visibles
        rows.forEach(row => row.style.display = "none");

        // Mostrar las filas correspondientes a la página actual
        const startIdx = (currentPage - 1) * itemsPerPage;
        const endIdx = currentPage * itemsPerPage;

        filteredRows.slice(startIdx, endIdx).forEach(row => row.style.display = "");
		
		// Llamar a adjustProposalWidth para cada fila visible
        filteredRows.slice(startIdx, endIdx).forEach(row => {
            const listeningId = row.querySelector("td[id^='date-']").id.replace('date-', '');
            adjustProposalWidth(listeningId);
        });
		
        // Actualizar la paginación
        const totalPages = Math.ceil(filteredRows.length / itemsPerPage);
        updatePageControls(totalPages);
    }

    // Función para actualizar los controles de paginación
    function updatePageControls(totalPages) {
        const paginationContainer = document.getElementById('pagination');
        paginationContainer.innerHTML = ''; // Limpiar paginación

        const paginationList = document.createElement('ul');
        paginationList.classList.add('pagination'); // Añadir la clase de Bootstrap para estilo

        // Botón de "Primera página"
        const firstPageItem = document.createElement('li');
        firstPageItem.classList.add('page-item');
        const firstPageButton = document.createElement('a');
        firstPageButton.classList.add('page-link');
        firstPageButton.innerText = '«';
        firstPageButton.addEventListener('click', function () {
            currentPage = 1;
            applyFilters();
        });
        firstPageItem.appendChild(firstPageButton);
        paginationList.appendChild(firstPageItem);

        // Botón de "Página anterior"
        const prevPageItem = document.createElement('li');
        prevPageItem.classList.add('page-item');
        const prevPageButton = document.createElement('a');
        prevPageButton.classList.add('page-link');
        prevPageButton.innerText = '‹';
        prevPageButton.addEventListener('click', function () {
            if (currentPage > 1) {
                currentPage--;
                applyFilters();
            }
        });
        prevPageItem.appendChild(prevPageButton);
        paginationList.appendChild(prevPageItem);

        // Crear los botones de página numerados
        for (let i = 1; i <= totalPages; i++) {
            const pageItem = document.createElement('li');
            pageItem.classList.add('page-item');
            const pageButton = document.createElement('a');
            pageButton.classList.add('page-link');
            pageButton.innerText = i;
            pageButton.addEventListener('click', function () {
                currentPage = i;
                applyFilters();
            });
            pageItem.appendChild(pageButton);
            paginationList.appendChild(pageItem);
        }

        // Botón de "Página siguiente"
        const nextPageItem = document.createElement('li');
        nextPageItem.classList.add('page-item');
        const nextPageButton = document.createElement('a');
        nextPageButton.classList.add('page-link');
        nextPageButton.innerText = '›';
        nextPageButton.addEventListener('click', function () {
            if (currentPage < totalPages) {
                currentPage++;
                applyFilters();
            }
        });
        nextPageItem.appendChild(nextPageButton);
        paginationList.appendChild(nextPageItem);

        // Botón de "Última página"
        const lastPageItem = document.createElement('li');
        lastPageItem.classList.add('page-item');
        const lastPageButton = document.createElement('a');
        lastPageButton.classList.add('page-link');
        lastPageButton.innerText = '»';
        lastPageButton.addEventListener('click', function () {
            currentPage = totalPages;
            applyFilters();
        });
        lastPageItem.appendChild(lastPageButton);
        paginationList.appendChild(lastPageItem);

        paginationContainer.appendChild(paginationList);
    }
	

    // Escuchar cambios en los filtros
    deptSelect.addEventListener('change', applyFilters);
    statusSelect.addEventListener('change', applyFilters);
    searchInput.addEventListener('input', applyFilters);
    startDateInput.addEventListener('change', applyFilters);
    endDateInput.addEventListener('change', applyFilters);

    // Inicializar con los filtros aplicados
    applyFilters();
});
</script>

<script>
// document.addEventListener("DOMContentLoaded", function () {
    // document.querySelectorAll("[id^='languageSelect-']").forEach(select => {
        // select.addEventListener("change", function () {
            // const listeningId = this.id.split("-")[1]; // Extrae el ID del listening
            // const selectedLang = this.value; // Obtiene el idioma seleccionado
            // const commentBox = document.querySelector(`#latest-comment-${listeningId} p`); // Caja del comentario
            // const commentUser = document.querySelector(`#latest-comment-${listeningId} h5 strong`); // Usuario del último comentario
            // const commentList = document.querySelector(`#comment-list-${listeningId}`); // Lista de comentarios
            // const commentItems = commentList.querySelectorAll(".list-group-item p"); // Elementos de la lista

            // // Buscar los datos dentro de la tabla
            // const commentsForListening = <?= json_encode($records_comment) ?>;
            // const filteredComments = commentsForListening.filter(comment => comment.listening_id == listeningId);

            // if (filteredComments.length > 0) {
                // // Ordenar los comentarios por fecha
                // filteredComments.sort((a, b) => new Date(b.updated) - new Date(a.updated));

                // let latestComment = "";
                // let latestUser = filteredComments[0].pr_user || "No user";

                // // Seleccionar el comentario según el idioma
                // switch (selectedLang) {
                    // case "es":
                        // latestComment = filteredComments[0].comment_es;
                        // break;
                    // case "en":
                        // latestComment = filteredComments[0].comment_en;
                        // break;
                    // case "kr":
                        // latestComment = filteredComments[0].comment_kr;
                        // break;
                // }

                // // Actualizar el comentario principal
                // commentBox.textContent = latestComment ? latestComment : "No comment";
                // commentUser.textContent = `${latestUser}:`;

                // // Actualizar los comentarios en "View more"
                // commentItems.forEach((commentElement, index) => {
                    // if (filteredComments[index + 1]) { // Evitamos repetir el primer comentario
                        // let translatedComment = "";
                        // switch (selectedLang) {
                            // case "es":
                                // translatedComment = filteredComments[index + 1].comment_es;
                                // break;
                            // case "en":
                                // translatedComment = filteredComments[index + 1].comment_en;
                                // break;
                            // case "kr":
                                // translatedComment = filteredComments[index + 1].comment_kr;
                                // break;
                        // }
                        // commentElement.textContent = translatedComment ? translatedComment : "No comment";
                    // }
                // });
            // }
        // });
    // });
// });
</script>


<script>
document.addEventListener("DOMContentLoaded", function () {
    // Seleccionamos el selector de idioma global
    const languageSelector = document.getElementById('global-language-selector');

    languageSelector.addEventListener("change", function () {
        const selectedLang = this.value; // Idioma seleccionado

        // Obtener todos los listening_id presentes en la página
        document.querySelectorAll("[id^='latest-comment-']").forEach(commentBox => {
            const listeningId = commentBox.id.split("-")[2]; // Extrae el listening_id de la caja de comentario
            const commentList = document.querySelector(`#comment-list-${listeningId}`); // Lista de comentarios
            const commentItems = commentList.querySelectorAll(".list-group-item p"); // Elementos de la lista

            // Buscar los datos dentro de la tabla
            const commentsForListening = <?= json_encode($records_comment) ?>;
            const filteredComments = commentsForListening.filter(comment => comment.listening_id == listeningId);

            // Si hay comentarios para este listening_id
            if (filteredComments.length > 0) {
                // Ordenar los comentarios por fecha
                filteredComments.sort((a, b) => new Date(b.updated) - new Date(a.updated));

                // Actualizamos el comentario más reciente para el listening_id
                const commentBox = document.querySelector(`#latest-comment-${listeningId} p`); // Caja del comentario
                const commentUser = document.querySelector(`#latest-comment-${listeningId} h5 strong`); // Usuario del último comentario

                let latestComment = "";
                let latestUser = filteredComments[0].pr_user || "No user";

                // Seleccionar el comentario según el idioma
                switch (selectedLang) {
                    case "es":
                        latestComment = filteredComments[0].comment_es;
                        break;
                    case "en":
                        latestComment = filteredComments[0].comment_en;
                        break;
                    case "kr":
                        latestComment = filteredComments[0].comment_kr;
                        break;
                }

                // Actualizar el comentario principal
                commentBox.innerHTML = latestComment ? latestComment.replace(/\n/g, '<br>') : "No comment";
                commentUser.textContent = `${latestUser}:`;

                // Actualizar los comentarios en "View more"
                commentItems.forEach((commentElement, index) => {
                    if (filteredComments[index + 1]) { // Evitamos repetir el primer comentario
                        let translatedComment = "";
                        switch (selectedLang) {
                            case "es":
                                translatedComment = filteredComments[index + 1].comment_es;
                                break;
                            case "en":
                                translatedComment = filteredComments[index + 1].comment_en;
                                break;
                            case "kr":
                                translatedComment = filteredComments[index + 1].comment_kr;
                                break;
                        }
                        commentElement.innerHTML = translatedComment ? translatedComment.replace(/\n/g, '<br>') : "No comment";
                    }
                });
            }
        });
    });
});
</script>

<script>
document.getElementById("fromDate").addEventListener("focus", function () {
    this.showPicker(); // Abre el selector de fecha automáticamente
});

document.getElementById("toDate").addEventListener("focus", function () {
    this.showPicker(); // Abre el selector de fecha automáticamente
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lógica para "issue"
        document.querySelectorAll('.read-more-issue').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                const id = this.getAttribute('data-id');
                document.getElementById('truncated-issue-' + id).style.display = 'none';
                document.getElementById('full-issue-' + id).style.display = 'inline';
                this.style.display = 'none';
            });
        });

        document.querySelectorAll('.read-less-issue').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                const id = this.getAttribute('data-id');
                document.getElementById('full-issue-' + id).style.display = 'none';
                document.getElementById('truncated-issue-' + id).style.display = 'inline';
                document.querySelector('.read-more-issue[data-id="' + id + '"]').style.display = 'inline';
            });
        });

        // Lógica para "proposal"
        document.querySelectorAll('.read-more-proposal').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                const id = this.getAttribute('data-id');
                document.getElementById('truncated-proposal-' + id).style.display = 'none';
                document.getElementById('full-proposal-' + id).style.display = 'inline';
                this.style.display = 'none';
            });
        });

        document.querySelectorAll('.read-less-proposal').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault();
                const id = this.getAttribute('data-id');
                document.getElementById('full-proposal-' + id).style.display = 'none';
                document.getElementById('truncated-proposal-' + id).style.display = 'inline';
                document.querySelector('.read-more-proposal[data-id="' + id + '"]').style.display = 'inline';
            });
        });
    });
</script>