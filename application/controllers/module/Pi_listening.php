<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pi_listening extends CI_Controller {

	public function __construct(){
		parent::__construct();
		
		date_default_timezone_set('America/Lima');
		$this->load->model('general_model', 'gen_m');
	}
		
	public function index(){
		$sql = "
			SELECT pl.*, 
				   COALESCE(MAX(plc.comment_id), 0) AS latest_comment_id, 
				   COALESCE(MAX(plc.updated), pl.registered) AS latest_date
			FROM pi_listening pl
			LEFT JOIN pi_listening_comment plc 
				   ON pl.listening_id = plc.listening_id
			GROUP BY pl.listening_id
			ORDER BY latest_date DESC, pl.registered DESC
		";

		
		$records = $this->db->query($sql)->result();
		
		// Obtener los comentarios en orden descendente por updated
		$orders = [["updated", "DESC"]];
		$records_comment = $this->gen_m->filter("pi_listening_comment", false, null, null, null, $orders);
		$user_name = $this->session->userdata('name');
		$user = $this->gen_m->filter("hr_employee", false, ['name'=>$user_name]);
		//print_r($user[0]->ep_mail);
		
		$data = [
			"records" => $records,
			"records_comment" => $records_comment,
			"user" => $user[0]->ep_mail,
			"main" => "module/pi_listening/index",
		];

		$this->load->view('layout', $data);
	}

	
	public function update_status() {
		$listening_id = $this->input->post('listening_id');
		$status = $this->input->post('status');
		
		if (!$listening_id || !$status) {
			echo json_encode(["success" => false, "message" => "Datos insuficientes"]);
			return;
		}

		// Se actualiza la columna 'registered' con el nuevo estado seleccionado
		$data = ["status" => $status];

		$updated = $this->gen_m->update("pi_listening", ["listening_id" => $listening_id], $data);

		if ($updated) {
			echo json_encode(["success" => true, "message" => "Estado actualizado correctamente"]);
		} else {
			echo json_encode(["success" => false, "message" => "Error al actualizar"]);
		}
	
		exit;
	}

	public function update(){
		$type = "success"; $msg = "Voice updated.";
		
		$data = $this->input->post();
		$this->gen_m->update("pi_listening", ["listening_id" => $data["listening_id"]], $data);
		
		header('Content-Type: application/json');
		echo json_encode(["type" => $type, "msg" => $msg]);
	}
	
	// public function add() {
		// $listening_id = $this->input->post('listening_id');
		// $comment_es = $this->input->post('comment_es');
		// $pr_user_name = $this->session->userdata('name');
		// $pr_user = $this->gen_m->filter("hr_employee", false, ['name'=>$pr_user_name]);
		// if (!$listening_id || !$comment_es) {
			// echo json_encode(["success" => false]);
			// return;
		// }

		// $updated = date('Y-m-d H:i:s');
		// $data = [
			// "pr_user" => $pr_user[0]->ep_mail,
			// "listening_id" => $listening_id,
			// "comment_es" => $comment_es,
			// "updated" => $updated,
		// ];

		// $this->db->insert("pi_listening_comment", $data);
		// $comment_id = $this->db->insert_id(); // Obtener el último ID insertado

		// echo json_encode(["success" => true, "comment_id" => $comment_id, "updated" => $updated]);
	// }

	public function add() {
		$listening_id = $this->input->post('listening_id');
		$comment_es = $this->input->post('comment_es');
		$comment_en = $this->input->post('comment_en');
		$comment_kr = $this->input->post('comment_kr');
		$pr_user_name = $this->session->userdata('name');
		$pr_user = $this->gen_m->filter("hr_employee", false, ['name' => $pr_user_name]);

		if (!$listening_id || (!$comment_es && !$comment_en && !$comment_kr)) {
			echo json_encode(["success" => false]);
			return;
		}

		$updated = date('Y-m-d H:i:s');

		// Determinar qué comentario se está enviando
		$data = [
			"pr_user" => $pr_user[0]->ep_mail,
			"listening_id" => $listening_id,
			"updated" => $updated,
		];

		if (!empty($comment_es)) {
			$data["comment_es"] = $comment_es;
		}
		if (!empty($comment_en)) {
			$data["comment_en"] = $comment_en;
		}
		if (!empty($comment_kr)) {
			$data["comment_kr"] = $comment_kr;
		}

		$this->db->insert("pi_listening_comment", $data);
		$comment_id = $this->db->insert_id(); // Obtener el último ID insertado

		echo json_encode(["success" => true, "comment_id" => $comment_id, "updated" => $updated]);
	}


	
	// public function update_comment() {
		// $commentId = $this->input->post('comment_id');
		// $listeningId = $this->input->post('listening_id');
		// $pr_user_name = $this->session->userdata('name');
		// $pr_user = $this->gen_m->filter("hr_employee", false, ['name'=>$pr_user_name]);
		// $newComment = $this->input->post('comment_es');

		// if (!$commentId || !$listeningId || !$newComment) {
			// echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
			// return;
		// }

		// $data = [
			// 'comment_es' => $newComment,
			// 'pr_user' => $pr_user[0]->ep_mail,
			// 'updated' => date('Y-m-d H:i:s')
		// ];

		// $this->db->where('comment_id', $commentId);
		// $this->db->where('listening_id', $listeningId);
		// $update = $this->db->update('pi_listening_comment', $data);

		// if ($update) {
			// echo json_encode(['success' => true]);
		// } else {
			// echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el comentario.']);
		// }
	// }
	
	public function update_comment() {
		$commentId = $this->input->post('comment_id');
		$listeningId = $this->input->post('listening_id');
		$pr_user_name = $this->session->userdata('name');
		$pr_user = $this->gen_m->filter("hr_employee", false, ['name' => $pr_user_name]);

		// Obtener el comentario y el idioma seleccionado
		$comment = $this->input->post('comment');
		$language = $this->input->post('language'); // Idioma seleccionado ('es', 'en', 'ko')

		if (!$commentId || !$listeningId || !$comment || !$language) {
			echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
			return;
		}

		// Preparar los datos para actualizar
		$data = [
			'pr_user' => $pr_user[0]->ep_mail,
			'updated' => date('Y-m-d H:i:s')
		];

		// Solo actualizar el campo correspondiente al idioma seleccionado
		if ($language == 'es') {
			$data['comment_es'] = $comment;
		} elseif ($language == 'en') {
			$data['comment_en'] = $comment;
		} elseif ($language == 'ko') {
			$data['comment_kr'] = $comment;
		}

		// Realizar la actualización en la base de datos
		$this->db->where('comment_id', $commentId);
		$this->db->where('listening_id', $listeningId);
		$update = $this->db->update('pi_listening_comment', $data);
		
		if ($update) {
			echo json_encode(['success' => true]);
		} else {
			echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el comentario.']);
		}
	}

}
