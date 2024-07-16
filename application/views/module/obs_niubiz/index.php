<?php
$is_production = false;
$js_niubiz_test = "https://pocpaymentserve.s3.amazonaws.com/payform.min.js";
$js_niubiz_production = "https://static-content.vnforapps.com/elements/v1/payform.min.js";
?>

<html>
<head>

<link rel="stylesheet" type="text/css" href="https://www.lg.com/etc.clientlibs/lge/clientlibs/clientlib-site-ltr.lc-ec8494069537f2236f9f306596cf5a60-lc.min.css">
<style>
.CT000C .CT0303 .c-checkout-step03-payment .c-creditCard--col03 {
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    gap: 0 3.125rem;
}
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="<?= $is_production ? $js_niubiz_production : $js_niubiz_test ?>"></script>

</head>
<body>

<div class="CT000C" style="width: 800px; margin: 0 auto; padding: 30px;">
	<div class="CT0303">
		<div class="c-checkout-step03-payment">
			<form id="form-checkout-niubiz">
				<div class="c-creditCard--col01">
					<div class="c-creditCard__box">
						<div class="c-input-item">
							<label class="font-w-normal-16 font-m-normal-14" for="form-checkout__cardNumber">Información de la tarjeta</label>
							<input id="form-checkout__cardNumber" placeholder="Card number" class="my-warn">
						</div>
						<div class="cmp-image">
							<img class="cmp-image__image c-image__img " src="/etc.clientlibs/lge/clientlibs/clientlib-site/resources/images/icon/icon-payment-credit-card-mx-192-15.svg" alt="icon payment credit card" loading="lazy">
						</div>
					</div>
				</div>
				<div class="c-creditCard--col03">
					<div class="c-creditCard__box">
						<div class="c-select-item">
							<label class="font-w-normal-16 font-m-normal-14" for="form-checkout__expirationDate">Mes De Expiración</label>
							<select id="expired-month">
								<option id="0" value="0">Mes</option>
								<option id="1" value="1">01 - Enero</option>
								<option id="2" value="2">02 - Febrero</option>
								<option id="3" value="3">03 - Marzo</option>
								<option id="4" value="4">04 - Abril</option>
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
					</div>
					<div class="c-creditCard__box">
						<div class="c-select-item">
							<label class="font-w-normal-16 font-m-normal-14" for="expired-year">Año De Expiración</label>
							<select id="expired-year">
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
					</div>
					<div class="c-creditCard__box">
						<div class="c-input-item">
							<label class="font-w-normal-16 font-m-normal-14" for="card-cvv">CVV</label>
							<input type="text" id="card-cvv" name="cardCVV" placeholder="CVV" value="">
						</div>
					</div>
				</div>
				<div class="c-creditCard--col02">
					<div class="c-creditCard__box">
						<div class="c-input-item">
							<label class="font-w-normal-16 font-m-normal-14" for="card-name">Nombre Del Titular</label>
							<input type="text" id="card-name" placeholder="" name="cardName">
						</div>
					</div>
					<div class="c-creditCard__box">
						<div class="c-input-item">
							<label class="font-w-normal-16 font-m-normal-14" for="card-last-name">Apellido Del Titular</label>
							<input type="text" id="card-last-name" placeholder="" name="cardLastName">
						</div>
					</div>
				</div>
				<div class="c-creditCard--col02">
					<div class="c-creditCard__box">
						<div class="c-select-item">
							<label class="font-w-normal-16 font-m-normal-14" for="identification-types">Tipo De Documento</label>
							<select id="identification-types" name="identificationType">
								<option value="DNI">DNI</option>
								<option value="C.E">C.E</option>
								<option value="RUC">RUC</option>
								<option value="Otro">Otro</option>
							</select>
						</div>
					</div>
					<div class="c-creditCard__box">
						<div class="c-input-item">
							<label class="font-w-normal-16 font-m-normal-14" for="identification">Documento</label>
							<input type="text" id="identification" placeholder="" name="identification">
						</div>
					</div>
				</div>
				<div class="c-creditCard--col01">
					<div class="c-creditCard__box">
						<div class="c-input-item">
							<label class="font-w-normal-16 font-m-normal-14" for="identification">E-mail</label>
							<input type="text" id="email" placeholder="" name="email">
						</div>
					</div>
				</div>
				<div class="c-creditCard--col01">
					<div class="c-creditCard__box">
						<div class="c-select-item">
							<label class="font-w-normal-16 font-m-normal-14" for="cardIssuer">Medio de Pago</label>
							<select id="cardIssuer" name="cardIssuer">
								<option value="12354">BBVA</option>
							</select>
						</div>
					</div>
				</div>
				<div class="c-creditCard--col01">
					<div class="c-creditCard__box">
						<div class="c-select-item">
							<label class="font-w-normal-16 font-m-normal-14" for="installments">Cuotas Mensuales</label>
							<select id="installments">
								<option value="1">1 cuota</option>
								<option value="3">3 cuotas</option>
								<option value="6">6 cuotas</option>
								<option value="9">9 cuotas</option>
								<option value="12">12 cuotas</option>
							</select>
						</div>
					</div>
					<ul class="c-creditCard-list">
						<li class="font-w-normal-16 font-m-normal-14 type02">El beneficio de cuotas sin intereses sólo está disponible con tarjetas de Crédito BBVA, Interbank, Diners y BCP Visa.</li>
						<li class="font-w-normal-16 font-m-normal-14 type02">El número de cuotas sin intereses se aplicara según el monto total del carrito de compras. Solo aplican 12 cuotas para compras mayores a S/4000, 9 cuotas mas de S/2000, 6 cuotas mas de S/1000 y 3 cuotas mas de S/300.</li><li class="font-w-normal-16 font-m-normal-14 type02">Si selecciono DNI/CE se emitirá Boleta. Debe seleccionar RUC si desea recibir Factura.</li>
						<li class="font-w-normal-16 font-m-normal-14 type02">Si desea, puede hacer clic en ‘Atrás’ para revisar su información de compra y cambiar algún dato de ser necesario.</li>
					</ul>
					<p class="c-creditCard__text font-w-normal-16 font-m-normal-16">Pago procesado por Mercado Pago</p>
				</div>
				<input id="token" name="token" type="hidden">
				<div class="button align-text-center">
					<button class="cmp-button c-button  highlight next m-medium w-medium    c-button--default " type="submit" id="">
						<span class="cmp-button__text c-button__text ">Siguiente</span>
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
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
	$("#form-checkout-niubiz").submit(function(e) {
		e.preventDefault();
		sendPayment();
		/*
		ajax_form_warning(this, "module/ism_activity_management/update_activity", "Do you want to updated this activity?").done(function(res) {
			swal_redirection(res.type, res.msg, res.url);
		});
		*/
	});
});
</script>
</body>
</html>