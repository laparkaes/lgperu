const base_url = "/llamasys/";

function ajax_form(dom, url){
	var btn_html = $(dom).find('button').html();
	alert(btn_html);
	
	$(dom).find('button').addClass("d-none");
	$(dom).append('<div class="text-center ajax_spinner"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
	var deferred = $.Deferred();
	$.ajax({
		url: base_url + url,
		type: "POST",
		data: new FormData(dom),
		contentType: false,
		processData:false,
		success:function(res){
			$(".ajax_spinner").remove();
			$(dom).find('button').removeClass("d-none");
			deferred.resolve(res);
		}
	});
	
	return deferred.promise();
}

function ajax_simple(data, url){
	var deferred = $.Deferred();
	$.ajax({
		url: base_url + url,
		type: "POST",
		data: data,
		success:function(res){
			deferred.resolve(res);
		}
	});
	
	return deferred.promise();
}

function ajax_form_warning(dom, url, msg){
	var deferred = $.Deferred();
	Swal.fire({
		title: "Warning !!!",
		icon: 'warning',
		html: msg,
		showCancelButton: true,
		confirmButtonText: "Confirm",
		cancelButtonText: "Cancel",
	}).then((result) => {
		if (result.isConfirmed) ajax_form(dom, url).done(function(res) {
			deferred.resolve(res);
		});
	});
	
	return deferred.promise();
}

function ajax_simple_warning(data, url, msg){
	var deferred = $.Deferred();
	Swal.fire({
		title: "Warning !!!",
		icon: 'warning',
		html: msg,
		showCancelButton: true,
		confirmButtonText: "Confirm",
		cancelButtonText: "Cancel",
	}).then((result) => {
		if (result.isConfirmed) ajax_simple(data, url).done(function(res) {
			deferred.resolve(res);
		});
	});
	
	return deferred.promise();
}

function swal(type, msg){
	if (msg != ""){
		Swal.fire({
			title: type.toUpperCase() + " !!!",
			icon: type,
			html: msg,
			confirmButtonText: "Confirm",
			cancelButtonText: "Cancel",
		});
	}
}

function swal_redirection(type, msg, move_to){
	if (msg != ""){
		Swal.fire({
			title: type.toUpperCase() + " !!!",
			icon: type,
			html: msg,
			confirmButtonText: "Confirm",
			cancelButtonText: "Cancel",
		}).then((result) => {
			if (result.isConfirmed) if (type == "success") location.href = base_url + move_to;
		});	
	}
}

function swal_open_tab(type, msg, url){
	if (msg != ""){
		Swal.fire({
			title: type.toUpperCase() + " !!!",
			icon: type,
			html: msg,
			confirmButtonText: "Confirm",
			cancelButtonText: "Cancel",
		}).then((result) => {
			if (result.isConfirmed) if (type == "success") window.open(url, '_blank');
		});	
	}
}

function swal_warning_redirect(msg, move_to){
	if (msg != ""){
		Swal.fire({
			title: "Warning !!!",
			icon: "warning",
			html: msg,
			confirmButtonText: "Confirm",
			cancelButtonText: "Cancel",
		}).then((result) => {
			if (result.isConfirmed) location.href = base_url + move_to;
		});	
	}
}













const default_lang = "sp";

const alert_words = {
	sp: {
		error: "Error",
		success: "Éxito",
		warning: "Advertencia",
		confirm: "Confirmar",
		cancel: "Cancelar",
	},
}

const error_msg = {
	sp: {
		limited_payment: "Debe pagar igual o menos que total.",
		qty_no_zero: "Cantidad debe ser mayor que cero.",
	},
}

const warning_msg = {
	sp: {
		add_module: "¿Desea agregar nuevo módulo?",
		delete_module: "¿Desea eliminar módulo?",
		add_access: "¿Desea agregar nuevo acceso?",
		delete_access: "¿Desea eliminar acceso?",
		add_role: "¿Desea agregar nuevo rol?",
		update_role: "¿Desea actualizar rol?",
		delete_role: "¿Desea eliminar rol?",
		add_account: "¿Desea agregar nuevo usuario?",
		update_account: "¿Desea actualizar usuario?",
		update_password: "¿Desea actualizar contraseña?",
		deactivate_account: "¿Desea desactivar usuario?",
		activate_account: "¿Desea activar usuario?",
		add_category: "¿Desea agregar nueva categoría?",
		delete_category: "¿Desea eliminar categoría?",
		move_category: "¿Desea mover todos los productos de categoría?",
		add_product: "¿Desea agregar nuevo producto?",
		update_product: "¿Desea actualizar producto?",
		add_option: "¿Desea agregar nueva opción?",
		update_option: "¿Desea actualizar opción?",
		delete_option: "¿Desea eliminar opción?",
		add_image: "¿Desea agregar nueva imagen?",
		set_main_image: "¿Desea configurar como imagen principal?",
		delete_image: "¿Desea eliminar imagen?",
		add_sale: "¿Desea agregar nueva venta?",
		cancel_sale: "¿Desea anular la venta?",
		add_payment: "¿Desea agregar pago?",
		delete_payment: "¿Desea eliminar pago?",
		add_proforma: "¿Desea agregar nueva proforma?",
		update_proforma: "¿Desea actualizar la proforma?",
		void_proforma: "¿Desea anular la proforma?",
		issue_invoice: "¿Desea emitir comprobante?",
		send_invoice: "¿Desea enviar comprobante a Sunat?",
		void_invoice: "¿Desea anular comprobante?",
		add_client: "¿Desea agregar nuevo cliente?",
		update_client: "¿Desea actualizar cliente?",
		add_provider: "¿Desea agregar nuevo proveedor?",
		update_provider: "¿Desea actualizar proveedor?",
		no_product_option_selected: "Se creará una opción general por no elegir ninguna.",
		add_purchase: "¿Desea agregar nueva compra?",
		cancel_purchase: "¿Desea cancelar compra?",
		add_note: "¿Desea agregar nueva nota?",
		delete_note: "¿Desea eliminar nota?",
		add_file: "¿Desea subir archivo?",
		delete_file: "¿Desea eliminar archivo?",
		add_person: "¿Desea agregar persona?",
		delete_person: "¿Desea eliminar persona?",
		add_in_outcome: "¿Desea registrar datos ingresados?",
		delete_in_outcome: "¿Desea eliminar registro?",
	},
}

//disable form enter submit
$('form input').each(function(index, element) {
	if (!$(element).hasClass("enter_on")) $(element).on('keydown', function(event) {
		//enter key code
		if (event.keyCode === 13) event.preventDefault();
	});
});

//form valid, invalid msg reset
$('form input, form select, form textarea').on('change', function(event) {
	$(this).removeClass("is-invalid").removeClass("is-valid");
});

//move top & to
function move_top(){$("html, body").animate({ scrollTop: 0 }, "slow");}
function move_to(dom){$('html, body').animate({ scrollTop: $(dom).offset().top - 100 }, "slow");}

//number format 1,000,000.00
function nf(num){return parseFloat(num).toLocaleString('es-US', {maximumFractionDigits: 2, minimumFractionDigits: 2});}

//number format 1,000,000
function nf_int(num){return parseInt(num).toLocaleString('es-US');}

//number format without comma
function nf_reverse(num){return num.replace(/,/g, '');}

function reset_form(form_id){
	$(form_id)[0].reset();
	$(form_id + " [name]").removeClass("is-invalid").removeClass("is-valid");
}

function set_msgs(form_id, msgs){
	$(form_id + " [name]").removeClass("is-invalid").removeClass("is-valid");
	$(msgs).each(function (index, element) {
		let dom = $(form_id).find("[name='" + element.name + "']");
		dom.addClass(element.class);
		//dom.next().html(element.msg);
		dom.parent().find(".invalid-feedback").html(element.msg);
	});
}



function toastr_(type, msg){
	if (msg != ""){
		toastr.remove();
		switch (type) {
			case "success": toastr.success(msg, "¡ " + alert_words[default_lang]["success"] + " !"); break;
			case "error": toastr.success(msg, "¡ " + alert_words[default_lang]["error"] + " !"); break;
			case "warning":  toastr.warning(msg, "¡ " + alert_words[default_lang]["warning"] + " !"); break;
		}	
	}
}

function ob_from_form(dom_id){
	// Serialize form data into an array
	var formDataArray = $(dom_id).serializeArray();

	// Convert the array into an object
	var formDataObject = {};
	$.each(formDataArray, function(i, field){
		formDataObject[field.name] = field.value;
	});
	
	return formDataObject;
}

function set_date(dom_date, min){
	/*
	min = moment() // select from today
	min = null // all dates selection
	*/
	var op = {
		locale: 'es',
		allowInputToggle: true,
		showClose: true,
		showClear: true,
		format: "YYYY-MM-DD",
		widgetPositioning: {
            horizontal: 'left',
            vertical: 'bottom',
        },
		icons: {
			previous: 'bi bi-chevron-left',
			next: 'bi bi-chevron-right',
			today: 'bi bi-calendar-event',
			clear: 'bi bi-eraser',
			close: 'bi bi-x-lg',
		},
	}
	if (min != null) op.minDate = moment();
	
	$(dom_date).datetimepicker(op);
}

function set_dates_between(dom_from, dom_to){
	$(dom_from).datetimepicker({
		locale: 'es',
		allowInputToggle: true,
		showClose: true,
		showClear: true,
		format: "YYYY-MM-DD",
		widgetPositioning: {
            horizontal: 'left',
            vertical: 'bottom',
        },
		icons: {
			previous: 'bi bi-chevron-left',
			next: 'bi bi-chevron-right',
			today: 'bi bi-calendar-event',
			clear: 'bi bi-eraser',
			close: 'bi bi-x-lg',
		},
	});
	
	$(dom_to).datetimepicker({
		locale: 'es',
		allowInputToggle: true,
		showClose: true,
		showClear: true,
		format: "YYYY-MM-DD",
		widgetPositioning: {
            horizontal: 'left',
            vertical: 'bottom',
        },
		icons: {
			previous: 'bi bi-chevron-left',
			next: 'bi bi-chevron-right',
			today: 'bi bi-calendar-event',
			clear: 'bi bi-eraser',
			close: 'bi bi-x-lg',
		},
	});
	
	$(dom_from).on("dp.change", function (e) {
		$(dom_to).data("DateTimePicker").minDate(e.date);
	});
	$(dom_to).on("dp.change", function (e) {
		$(dom_from).data("DateTimePicker").maxDate(e.date);
	});
}

/* search provider functions
doms: #btn_search_person, #doc_type_id, #doc_number, #provider_name */
function set_search_provider_ajax(){
	function search_provider(){
		var data = {doc_type_id: $("#doc_type_id").val(), doc_number: $("#doc_number").val()};
		ajax_simple(data, "stock/provider/search_provider_ajax").done(function(res) {
			swal(res.type, res.msg);
			if (res.type == "success") $("#provider_name").val(res.person.name);
			else $("#provider_name").val("");
		});
	}
	
	$("#doc_type_id").on('change',(function(e) {
		$("#doc_number, #provider_name").val("");
		if ($("#doc_type_id option:selected").val() == 1){
			$("#doc_number").prop("disabled", true);
			$("#btn_search_person").prop("disabled", true);
			$("#provider_name").prop("disabled", true);
		}else{
			$("#doc_number").prop("disabled", false);
			$("#btn_search_person").prop("disabled", false);
			$("#provider_name").prop("disabled", false);
		}
	}));

	$("#doc_number").on('keyup',(function(e) {
		if (e.key === "Enter") search_provider();
		else $("#provider_name").prop("disabled", false);
	}));

	$("#btn_search_person").on('click',(function(e) {
		search_provider();
	}));
}

/* search client functions
doms: #btn_search_person, #doc_type_id, #doc_number, #client_name */
function set_search_client_ajax(){
	function search_client(){
		var data = {doc_type_id: $("#doc_type_id").val(), doc_number: $("#doc_number").val()};
		ajax_simple(data, "commerce/client/search_client_ajax").done(function(res) {
			swal(res.type, res.msg);
			if (res.type == "success") $("#client_name").val(res.person.name);
			else $("#client_name").val("");
		});
	}
	
	$("#doc_type_id").on('change',(function(e) {
		$("#doc_number, #client_name").val("");
		if ($("#doc_type_id option:selected").val() == 1){
			$("#doc_number").prop("disabled", true);
			$("#btn_search_person").prop("disabled", true);
			$("#client_name").prop("disabled", true);
		}else{
			$("#doc_number").prop("disabled", false);
			$("#btn_search_person").prop("disabled", false);
			$("#client_name").prop("disabled", false);
		}
	}));

	$("#doc_number").on('keyup',(function(e) {
		if (e.key === "Enter") search_client();
		else $("#client_name").prop("disabled", false);
	}));

	$("#btn_search_person").on('click',(function(e) {
		search_client();
	}));
}

/* control btn and card visibility */
function btn_card_control(btn_open, btn_close, card, color){
	$(btn_open).on('click',(function(e) {
		if ($(card).hasClass("d-none")){
			$(card).removeClass("d-none");
			$(btn_open).removeClass("btn-" + color);
			$(btn_open).addClass("btn-outline-" + color);
			move_to(card);
		}else{
			$(card).addClass("d-none");
			$(btn_open).removeClass("btn-outline-" + color);
			$(btn_open).addClass("btn-" + color);
		}
	}));

	$(btn_close).on('click',(function(e) {
		$(card).addClass("d-none");
		$(btn_open).removeClass("btn-outline-" + color);
		$(btn_open).addClass("btn-" + color);
		move_top();
	}));
}

/* control search card and button at index page */
btn_card_control("#btn_search_index", "#btn_close_search_index", "#bl_search_index", "primary");