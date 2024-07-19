<html>
<head>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<?php
$is_production = false;
$js_niubiz_test = "https://pocpaymentserve.s3.amazonaws.com/payform.min.js";
$js_niubiz_production = "https://static-content.vnforapps.com/elements/v1/payform.min.js";
?>
<script src="<?= $is_production ? $js_niubiz_production : $js_niubiz_test ?>"></script>

<style>
label{
	display: block;
}

table, input, select{
	width: 100%;
}
</style>

</head>
<body>

<table>
	<tr>
		<td style="width: 33.33%; vertical-align: top;">
			<div style="padding: 30px; width: 80%; margin: 0 auto; border: solid 1px black;">
				<form id="form_confirm_shipping_infomation" style="margin: 0;">
					<h4 style="margin-top: 0;">1. Envío / Shipping</h4>
					<div style="margin-bottom: 15px;">
						<label>Total Amount (need to be hidden)</label>
						<input type="text" name="totalAmount" value="14797.01">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Nombre / First Name</label>
						<input type="text" id="firstName" name="firstName" placeholder="Nombre" value="Hoon Woo">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Apellido / Last Name <span style="color: red;">(* New!!)</span></label>
						<input type="text" id="lastName" name="lastName" placeholder="Apellido" value="Kim">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Teléfono móvil / Mobile Phone</label>
						<input type="text" id="telephone" name="telephone" placeholder="Teléfono móvil" value="992533096">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Otro teléfono / Other Phone</label>
						<input type="text" id="additionalTelephone" name="additionalTelephone" placeholder="Otro teléfono" value="993322119">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Tipo de Documento / Document Type</label>
						<select id="documentType" name="documentType">
							<option value="">Por favor, seleccione tipo de documento</option>
							<option value="DNI">DNI</option>
							<option value="CE" selected>CE</option>
							<option value="RUC">RUC</option>
							<option value="Otro">Otro</option>
						</select>
						<br/>
						<small>Debe seleccionar RUC si desea recibir Factura</small>
					</div>
					<div style="margin-bottom: 15px;">
						<label>Número de Documento / Document Number</label>
						<input type="text" id="documentNumber" name="documentNumber" placeholder="Número de Documento" value="000765823">
						<br/>
						<small>Revisar bien. No realizamos cambio de Comprobantes emitidos.</small>
					</div>
					<div style="margin-bottom: 15px;">
						<label>Dirección de Envío / Shipping Address</label>
						<input type="text" id="streetAddress" name="streetAddress" placeholder="Dirección de Envío" value="Av. Republica de Panama 4077 Dpto 2305">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Departamento / Department</label>
						<select id="region" name="region">
							<option value="3165" selected>Lima</option>
						</select>
					</div>
					<div style="margin-bottom: 15px;">
						<label>Provincia / Province</label>
						<select id="city" name="city">
							<option value="37956" selected>Lima</option>
						</select>
					</div>
					<div style="margin-bottom: 15px;">
						<label>Distrito / District</label>
						<select id="district" title="" class="" name="district" data-gtm-form-interact-field-id="2">
							<option value="540285" selected>Surquillo</option>
						</select>
					</div>
					<div style="margin-bottom: 15px;">
						<label>Código Postal (Ubigeo) / Zipcode (Geo-Location)</label>
						<select id="postcode" name="postcode">
							<option value="150141">150141</option>
						</select>
					</div>
					<div style="margin-bottom: 15px;">
						<label>Tipo de Residencia / Residence Type</label>
						<select id="typeOfResidence" name="typeOfResidence">
							<option value="Departamento con ascensor" selected>Departamento con ascensor</option>
						</select>
					</div>
					<div style="margin-bottom: 15px;">
						<label for="references">Referencias / Reference</label>
						<input type="text" id="references" name="references" placeholder="Referencias" value="cruce entre Av Tomas Marsano y Av Republica de Panama">
					</div>
					<div style="margin-bottom: 15px;">
						<label for="country">País</label>
						<input type="text" id="country" name="country" placeholder="País" value="PE">
					</div>
					<div style="padding-top: 40px;">
						<button type="submit" style="width: 100%;">Guardar dirección / Save Address</button>
						<div>Steps: 1a ~ 1c & 2a ~ 2c</div>
					</div>
				</form>
			</div>
		</td>
		<td style="width: 33.33%; vertical-align: top;">
			<div style="padding: 30px; width: 80%; margin: 0 auto; border: solid 1px black;">
				<form id="form_confirm_card_information" style="margin: 0;">
					<h4 style="margin-top: 0;">2. Pago / Payment</h4>
					<div style="margin-bottom: 15px;">
						<label>Información de la tarjeta / Card Information</label>
						<input type="text" id="cardNumber" name="cardNumber" placeholder="Card number" value="4551708161768059">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Mes De Expiración / Expiration Month</label>
						<select name="expiredMonth" id="expiredMonth">
							<option id="0" value="">Mes</option>
							<option id="1" value="01">01 - Enero</option>
							<option id="2" value="02">02 - Febrero</option>
							<option id="3" value="03" selected>03 - Marzo</option>
							<option id="4" value="04">04 - Abril</option>
							<option id="5" value="05">05 - Mayo</option>
							<option id="6" value="06">06 - Junio</option>
							<option id="7" value="07">07 - Julio</option>
							<option id="8" value="08">08 - Agosto</option>
							<option id="9" value="09">09 - Aeptiembre</option>
							<option id="10" value="10">10 - Octubre</option>
							<option id="11" value="11">11 - Noviembre</option>
							<option id="12" value="12">12 - Diciembre</option>
						</select>
					</div>
					<div style="margin-bottom: 15px;">
						<label>Año De Expiración / Expiration Year</label>
						<select name="expiredYear" id="expiredYear">
							<option id="2023" value="2023">2023</option>
							<option id="2024" value="2024">2024</option>
							<option id="2025" value="2025">2025</option>
							<option id="2026" value="2026">2026</option>
							<option id="2027" value="2027">2027</option>
							<option id="2028" value="2028" selected>2028</option>
							<option id="2029" value="2029">2029</option>
							<option id="2030" value="2030">2030</option>
							<option id="2031" value="2031">2031</option>
							<option id="2032" value="2032">2032</option>
							<option id="2033" value="2033">2033</option>
						</select>
					</div>
					<div style="margin-bottom: 15px;">
						<label>CVV</label>
						<input type="text" id="cardCVV" name="cardCVV" placeholder="CVV" value="111">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Nombre Del Titular / Owner Name</label>
						<input type="text" id="cardName" name="cardName" placeholder="Nombre Del Titular" value="Jeong Woo">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Apellido Del Titular / Owner Last Name</label>
						<input type="text" id="cardLastName" name="cardLastName" placeholder="Apellido Del Titular" value="Park">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Tipo De Documento / Document Type</label>
						<select id="identificationType" name="identificationType">
							<option value="DNI">DNI</option>
							<option value="C.E" selected>C.E</option>
							<option value="RUC">RUC</option>
							<option value="Otro">Otro</option>
						</select>
					</div>
					<div style="margin-bottom: 15px;">
						<label>Documento / Document</label>
						<input type="text" id="identification" name="identification" placeholder="Documento" value="000765838">
					</div>
					<div style="margin-bottom: 15px;">
						<label>E-mail</label>
						<input type="text" id="email" name="email" placeholder="E-mail" value="myemail@example.com">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Medio de Pago / Payment Method</label>
						<select id="cardIssuer" name="cardIssuer">
							<option value="12354">BBVA</option>
						</select>
					</div>
					<div style="margin-bottom: 15px;">
						<label>Cuotas Mensuales / Installment</label>
						<select id="installments">
							<option value="1" selected>1 cuota</option>
							<option value="3">3 cuotas</option>
							<option value="6">6 cuotas</option>
							<option value="9">9 cuotas</option>
							<option value="12">12 cuotas</option>
						</select>
					</div>
					<hr>
					<div style="margin-bottom: 15px; color: blue;">
					Result of Step 1: Confirm shipping information
					</div>
					<div style="margin-bottom: 15px;">
						<label>Access Token</label>
						<input type="text" id="accessToken" name="accessToken" placeholder="Access Token" value="">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Session Token</label>
						<input type="text" id="sessionToken" name="sessionToken" placeholder="Session Token" value="">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Expiration Time</label>
						<input type="text" id="expirationTime" name="expirationTime" placeholder="Expiration Time" value="">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Merchant ID</label>
						<input type="text" id="merchantId" name="merchantId" placeholder="merchant ID" value="">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Order Number</label>
						<input type="text" id="orderNo" name="orderNo" placeholder="Order Number" value="">
					</div>
					
					
					<div style="padding-top: 40px;">
						<button type="submit" style="width: 100%;">Siguiente / Next</button>
					</div>
				</form>
			</div>
		</td>
		<td style="width: 33.33%; vertical-align: top;">
			<div style="padding: 30px; width: 80%; margin: 0 auto; border: solid 1px black;">
				<form id="form_issue_order" style="margin: 0;">
					<h4 style="margin-top: 0;">3. Total Amount</h4>
					<div style="margin-bottom: 15px;">
						<label>Total Amount</label>
						<input type="text" id="totalAmount" name="totalAmount" value="14797.01">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Session Token</label>
						<input type="text" id="totalAmount" name="totalAmount" value="">
					</div>
				
					<div style="padding-top: 40px;">
						<button type="submit" style="width: 100%;">Emitir Pedido / Issue Order</button>
					</div>
				</form>
			</div>
		</td>
	</tr>
</table>


<div style="width: 100%;">
	<h4>Niubiz Process Map</h4>
	<div>https://desarrolladores.niubiz.com.pe/docs/formulario-desacoplado</div>
	<div><img src="https://files.readme.io/530b7ca-Formulario-Desacoplado-V3.jpg" style="width: 50%;"></div>
</div>		




<script>
//https://desarrolladores.niubiz.com.pe/docs/formulario-desacoplado

function get_transaction_token() {//in manual this function name is sendPayment()

	if ($("#sessionToken").val() == ""){
		alert("Session token required.")
		return;
	}

	var cardNumber = $("#cardNumber").val();
	var cardExpiry = $("#expiredMonth").val() + '/' + $("#expiredYear").val();
	var cardCvc = $("#cardCVV").val();
	
	if (cardNumber == ""){
		alert("Enter card number.")
		return;
	}
	
	if (cardExpiry == ""){
		alert("Enter card expiration.")
		return;
	}
	
	if (cardCvc == ""){
		alert("Enter CVC.")
		return;
	}

	//setting basic configuration
	var configuration = {
		callbackurl: 'obs_niubiz/payment_result/' + $("#orderNo").val(),//URL to redirect after payment. 
		sessionToken: $("#sessionToken").val(), //Generated sessionToken in step 1
		channel: 'web',//Default value
		merchantid: $("#merchantId").val(),//Niubiz client code.
		purchasenumber: $("#orderNo").val(),//Order number obtained from step 1
		amount: $("#totalAmount").val(),//Transaction amount is in stel 3
		language: 'es',
		font: 'https://fonts.googleapis.com/css?family=Montserrat:400&display=swap',
		//recurrencemaxamount: '8.5'//use in case of recurrent payment
	};
	console.log(configuration);

	payform.setConfiguration(configuration);
	
	//Transaction token creation
	var data = {
		name: $("#cardName").val(),
		lastName: $("#cardLastName").val(),
		email: $("#email").val(),
		installment: $("#installments").val(),
		cardExpiry: cardExpiry
	};
	console.log(data);
	
	
	payform.createToken([cardNumber,cardExpiry,cardCvc], data).then(function(response){
		console.log(response);
	}).catch(function(error){
		console.log(error);
	});

}

document.addEventListener("DOMContentLoaded", () => {
	$("#form_confirm_shipping_infomation").submit(function(e) {
		e.preventDefault();
		$.ajax({
			url: "/llamasys/module/obs_niubiz/get_session_key",
			type: "POST",
			data: new FormData(this),
			contentType: false,
			processData:false,
			success:function(res){
				console.log(res);
				alert(res.msg);
				$("#accessToken").val(res.accessToken);
				$("#sessionToken").val(res.sessionToken);
				$("#expirationTime").val(res.expirationTime);
				$("#merchantId").val(res.merchantId);
				$("#orderNo").val(res.orderNo);
			}
		});
	});
	
	$("#form_confirm_card_information").submit(function(e) {
		e.preventDefault();
		get_transaction_token();
		
		/*
		$.ajax({
			url: "/llamasys/module/obs_niubiz/get_session_key",
			type: "POST",
			data: new FormData(this),
			contentType: false,
			processData:false,
			success:function(res){
				console.log(res);
				alert(res.msg);
				$("#sessionToken").val(res.sessionToken);
				$("#expirationTime").val(res.expirationTime);
			}
		});*/
	});
	
	$("#form-checkout-niubiz").submit(function(e) {
		e.preventDefault();
		sendPayment();
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
	});
});
</script>
</body>
</html>