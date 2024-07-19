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

input, select{
	width: 100%;
}
</style>

</head>
<body>

<table style="width: 100%;">
	<tr>
		<td style="width: 33.33%; vertical-align: top;">
			<div style="padding: 30px; width: 80%; margin: 0 auto; border: solid 1px black;">
				<form id="form_session_token" style="margin: 0;">
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
						<label>Apellido / Last Name</label>
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
						<div>
						This will create Niubiz session token
						</div>
					</div>
				</form>
			</div>
		</td>
		<td style="width: 33.33%; vertical-align: top;">
			<div style="padding: 30px; width: 80%; margin: 0 auto; border: solid 1px black;">
				<form id="form_save_card_information" style="margin: 0;">
					<h4 style="margin-top: 0;">2. Pago / Payment</h4>
					<div style="margin-bottom: 15px;">
						<label>Información de la tarjeta / Card Information</label>
						<input type="text" id="cardNumber" name="cardNumber" placeholder="Card number">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Mes De Expiración / Expiration Month</label>
						<select name="expiredMonth" id="expiredMonth">
							<option id="0" value="0">Mes</option>
							<option id="1" value="1">01 - Enero</option>
							<option id="2" value="2">02 - Febrero</option>
							<option id="3" value="3">03 - Marzo</option>
							<option id="4" value="4" selected>04 - Abril</option>
							<option id="5" value="5">05 - Mayo</option>
							<option id="6" value="6">06 - Junio</option>
							<option id="7" value="7">07 - Julio</option>
							<option id="8" value="8">08 - Agosto</option>
							<option id="9" value="9">09 - Aeptiembre</option>
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
							<option id="2028" value="2028">2028</option>
							<option id="2029" value="2029">2029</option>
							<option id="2030" value="2030">2030</option>
							<option id="2031" value="2031">2031</option>
							<option id="2032" value="2032">2032</option>
							<option id="2033" value="2033">2033</option>
						</select>
					</div>
					<div style="margin-bottom: 15px;">
						<label>CVV</label>
						<input type="text" id="cardCVV" name="cardCVV" placeholder="CVV" value="123">
					</div>
					<div style="margin-bottom: 15px;">
						<label>Nombre Del Titular / Owner Name</label>
						<input type="text" id="cardName" name="cardName" placeholder="Nombre Del Titular" value="Jeong Woo Park">
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
						<input type="text" id="identification" name="identification" placeholder="Documento" value="000765808">
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





<script>
//https://desarrolladores.niubiz.com.pe/docs/formulario-desacoplado

function sendPayment() {
	//general configuration
	
	//load from backend
	
	var configuration = {
		callbackurl: 'paginaRespuesta',//URL to redirect later
		sessionkey: '67cf73735f83590eabf1382ff49e5e08b261976326c6897cb764fd160a15a8ca', //Generated sessionKey
		channel: 'web',//Default
		merchantid: '341198210',//Niubiz client code
		purchasenumber: 2020100901,//Put here orderNumber or magentoId
		amount: 10.5,//Transaction amount
		language: 'es',
		font: 'https://fonts.googleapis.com/css?family=Montserrat:400&display=swap',
		//recurrencemaxamount: '8.5'//use in case of recurrent payment
	};

	payform.setConfiguration(configuration);
	
	//validation code required here before transactionToken generation
	var data = {
		name: $("#card-name").val(),
		lastName: $("#card-last-name").val(),
		email: $("#email").val()
	};
	
	var cardNumber = $("#form-checkout__cardNumber").val();
	var cardExpiry = $("#expired-month").val() + "/" + $("#expired-year").val();
	var cardCvc = $("#card-cvv").val();
	
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
	
	payform.createToken([cardNumber,cardExpiry,cardCvc], data).then(function(response){
		console.log(response);
	}).catch(function(error){
		console.log(error);
	});

}

document.addEventListener("DOMContentLoaded", () => {
	$("#form_session_token").submit(function(e) {
		e.preventDefault();
		$.ajax({
			url: "/llamasys/module/obs_niubiz/get_session_key",
			type: "POST",
			data: new FormData(this),
			contentType: false,
			processData:false,
			success:function(res){
				console.log(res);
			}
		});
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