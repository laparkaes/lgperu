<html>
<head>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<?php
$is_production = false;
$js_niubiz_test = "https://pocpaymentserve.s3.amazonaws.com/payform.min.js";
$js_niubiz_production = "https://static-content.vnforapps.com/elements/v1/payform.min.js";
?>
<script src="<?= $is_production ? $js_niubiz_production : $js_niubiz_test ?>"></script>

</head>
<body>
<div class="buy-summary-area__secondary"><div></div><div class="buy-summary-area__payment buy-sticky-no"><div class="cart-price-total"><div class="c-product-price-sticky c-product-price-sticky--m-bottom-fixed c-product-price-sticky--fold-toggle"><div class="c-product-price-information web close"><div class="c-product-price-information__inner"><div class="button c-product-price-information__btn-toggle"><button type="button" class="cmp-button" aria-controls="productPriceDetailInformation" aria-expanded="false"><span class="cmp-button__text sr-only">open/close</span></button></div><div class="c-product-total-price"><div class="c-product-total-price__box" id="grand_total"><div class="text c-product-total-price__text"><div class="cmp-text font-w-normal-32 font-m-semibold-20"><p>Total (incl. IGV)</p></div></div><div class="text c-product-total-price__num"><span class="cmp-text font-w-semibold-32 font-m-semibold-20"><strong>S/&nbsp;14,797.01</strong></span></div></div></div><div class="buy-total__container"><div class="text c-price-box font-w-normal-24 font-m-normal-16" id="productPriceDetailInformation"><div class="c-price-box__item c-price-box__item--list"><div class="c-price-info c-price-info__subject font-semibold "><div class="c-text-contents c-price-info__title"><div class="text     "><p class="cmp-text " role="status" aria-live="polite"><span>Subtotal (incl. IGV)</span></p></div></div><div class="c-price-info__price cmp-text font-w-normal-20 font-m-semibold-14 "><p>S/&nbsp;14,797.01</p></div></div><div class="c-price-info-detail "><div class="c-price-info-detail__name cmp-name font-w-normal-16 font-m-normal-12"><span>WK14BS6.APBGLGP</span></div><div class="c-price-info-detail__price cmp-price font-w-semibold-16 font-m-semibold-14"><span>S/&nbsp;6,599.00</span></div></div><div class="c-price-info-detail "><div class="c-price-info-detail__name cmp-name font-w-normal-16 font-m-normal-12"><span>GS66SXN.APZGLPR</span></div><div class="c-price-info-detail__price cmp-price font-w-semibold-16 font-m-semibold-14"><span>S/&nbsp;5,899.00</span></div></div><div class="c-price-info-detail "><div class="c-price-info-detail__name cmp-name font-w-normal-16 font-m-normal-12"><span>65UR8750PSA.AWF</span></div><div class="c-price-info-detail__price cmp-price font-w-semibold-16 font-m-semibold-14"><span>S/&nbsp;2,299.01</span></div></div><div class="c-price-info c-price-info__subject font-semibold "><div class="c-text-contents c-price-info__title"><div class="text     "><p class="cmp-text " role="status" aria-live="polite"><span>Gastos de envío</span></p></div></div><div class="c-price-info__price cmp-text font-w-normal-20 font-m-semibold-14 "><p>S/&nbsp;0.00</p></div></div><div class="c-price-info-detail "><div class="c-price-info-detail__name cmp-name font-w-normal-16 font-m-normal-12"><span>WK14BS6.APBGLGP</span></div><div class="c-price-info-detail__price cmp-price font-w-semibold-16 font-m-semibold-14"><span>S/&nbsp;0.00</span></div></div><div class="c-price-info-detail "><div class="c-price-info-detail__name cmp-name font-w-normal-16 font-m-normal-12"><span>GS66SXN.APZGLPR</span></div><div class="c-price-info-detail__price cmp-price font-w-semibold-16 font-m-semibold-14"><span>S/&nbsp;0.00</span></div></div><div class="c-price-info-detail "><div class="c-price-info-detail__name cmp-name font-w-normal-16 font-m-normal-12"><span>65UR8750PSA.AWF</span></div><div class="c-price-info-detail__price cmp-price font-w-semibold-16 font-m-semibold-14"><span>S/&nbsp;0.00</span></div></div><div class="c-price-info c-price-info__subject font-semibold "><div class="c-text-contents c-price-info__title"><div class="text     "><p class="cmp-text " role="status" aria-live="polite"><span>Cargo de servicio</span></p></div></div><div class="c-price-info__price cmp-text font-w-normal-20 font-m-semibold-14 "><p>S/&nbsp;0.00</p></div></div><div class="c-price-info-detail "><div class="c-price-info-detail__name cmp-name font-w-normal-16 font-m-normal-12"><span>Instalación Premium</span></div><div class="c-price-info-detail__price cmp-price font-w-semibold-16 font-m-semibold-14"><span>S/&nbsp;0.00</span></div></div></div></div></div><div class="c-product-pay-cta button c-cta my-button__full"><button disabled="" class="cmp-button c-button c-button--default highlight m-medium w-large" type="button" aria-describedby="checkout-aria"><span class="cmp-button__text c-button__text">Emitir pedido</span></button></div><div class="c-product-card-info text"><div class="text   c-product-card-info__text font-w-normal-14 font-m-normal-14"><p class="cmp-text " role="status" aria-live="polite">Puedes pagar con</p></div><ul class="c-product-card-info__image"><li><span class="image c-image"><div class="cmp-image"><img class="cmp-image__image c-image__img" src="/etc.clientlibs/lge/clientlibs/clientlib-site/resources/images/payicons/icon-visa.png" alt="visa" loading="lazy"></div></span></li><li><span class="image c-image"><div class="cmp-image"><img class="cmp-image__image c-image__img" src="/etc.clientlibs/lge/clientlibs/clientlib-site/resources/images/payicons/icon-master.png" alt="master" loading="lazy"></div></span></li><li><span class="image c-image"><div class="cmp-image"><img class="cmp-image__image c-image__img" src="/etc.clientlibs/lge/clientlibs/clientlib-site/resources/images/payicons/icon-americanExpress.png" alt="americanExpress" loading="lazy"></div></span></li><li><span class="image c-image"><div class="cmp-image"><img class="cmp-image__image c-image__img" src="/etc.clientlibs/lge/clientlibs/clientlib-site/resources/images/payicons/icon-dinersclub-pe.png" alt="dinersclub-pe" loading="lazy"></div></span></li><li><span class="image c-image"><div class="cmp-image"><img class="cmp-image__image c-image__img" src="/etc.clientlibs/lge/clientlibs/clientlib-site/resources/images/payicons/icon-creditcard-cl-pe.png" alt="creditcard-cl-pe" loading="lazy"></div></span></li><li><span class="image c-image"><div class="cmp-image"><img class="cmp-image__image c-image__img" src="/etc.clientlibs/lge/clientlibs/clientlib-site/resources/images/payicons/icon-mercadopago-cl-pe.png" alt="mercadopago-cl-pe" loading="lazy"></div></span></li></ul></div><div class="rootKaspi"><style>.component>.fluid-container{position:relative;z-index:1;}</style></div></div></div><div class="CT000F"><div class="otp-check"><div class="c-pop-msg medium" id="otpCheck" role="dialog" aria-modal="true"><div class="c-pop-msg__container "><div class="c-pop-msg__header "><div class="c-text-contents"><div class="title c-text-contents__headline" id="otpCheck-headline"><strong class="cmp-title font-w-light-36 font-m-light-24" data-cmp-data-layer="">cc_otpSendToPhoneNoTxt </strong></div></div></div><div class="full-container"><div class="otp-check-box"><div class="inner-box"><div class="my-input"><div style="display: flex; align-items: center;"><div class="empty-label"><div class="c-input-item"><input type="text" autocomplete="off" aria-label="Please enter OTP character 1" style="width: 100%; text-align: center;" inputmode="text" value=""></div></div><div class="empty-label"><div class="c-input-item"><input type="text" autocomplete="off" aria-label="Please enter OTP character 2" style="width: 100%; text-align: center;" inputmode="text" value=""></div></div><div class="empty-label"><div class="c-input-item"><input type="text" autocomplete="off" aria-label="Please enter OTP character 3" style="width: 100%; text-align: center;" inputmode="text" value=""></div></div><div class="empty-label"><div class="c-input-item"><input type="text" autocomplete="off" aria-label="Please enter OTP character 4" style="width: 100%; text-align: center;" inputmode="text" value=""></div></div><div class="empty-label"><div class="c-input-item"><input type="text" autocomplete="off" aria-label="Please enter OTP character 5" style="width: 100%; text-align: center;" inputmode="text" value=""></div></div><div class="empty-label"><div class="c-input-item"><input type="text" autocomplete="off" aria-label="Please enter OTP character 6" style="width: 100%; text-align: center;" inputmode="text" value=""></div></div></div></div><p class="c-pop-msg__text font-w-normal-14 font-m-normal-12">cc_otpCondition</p></div></div></div><div class="toast-aria" role="alert" aria-live="assertive" aria-atomic="true"><ul class="toast-popup"></ul></div><div class="button c-pop-msg__button-wrap center-align"><button class="cmp-button c-button c-button--default highlight m-medium w-medium" type="button" data-cmp-data-layer="" disabled=""><span class="cmp-button__text c-button__text"> cc_validateTxt </span></button></div></div><div class="c-pop-msg__dimmed"></div></div></div></div></div></div></div></div>

<style>
label{
	display: block;
}
</style>

<div style="padding: 30px; width: 30%; margin: 0 auto; border: solid 1px black;">
	<form id="form_make_session_token" style="margin: 0;">
		<h4 style="margin-top: 0;">Envío / Shipping</h4>
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
		</div>
	</form>
</div>



	<div class="CT0303">
		<div class="c-checkout-step  ">
			<span class="sr-only">Input completed</span>
			<h4 class="c-checkout-step__title">Pago</h4>
			<div class="c-checkout-step__box">
				<span class="c-checkout-step__txt">Paso</span><span class="c-checkout-step__current">3</span><span class="c-checkout-step__total">/ 3</span>
			</div>
		</div>
		<div class="c-checkout-step03-payment">
			<form id="form-checkout-niubiz">
				<div class="c-creditCard--col01">
					<div class="c-creditCard__box">
						<div class="c-input-item">
							<label class="font-w-normal-16 font-m-normal-14" for="form-checkout__cardNumber">Información de la tarjeta / Card Information</label>
							<input id="form-checkout__cardNumber" placeholder="Card number" class="my-warn">
						</div>
					</div>
				</div>
				<div class="c-creditCard--col03">
					<div class="c-creditCard__box">
						<div class="c-select-item">
							<label class="font-w-normal-16 font-m-normal-14" for="form-checkout__expirationDate">Mes De Expiración / Expiration Month</label>
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
							<label class="font-w-normal-16 font-m-normal-14" for="expired-year">Año De Expiración / Expiration Year</label>
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
							<label class="font-w-normal-16 font-m-normal-14" for="card-name">Nombre Del Titular / Owner Name</label>
							<input type="text" id="card-name" placeholder="" name="cardName">
						</div>
					</div>
					<div class="c-creditCard__box">
						<div class="c-input-item">
							<label class="font-w-normal-16 font-m-normal-14" for="card-last-name">Apellido Del Titular / Owner Last Name</label>
							<input type="text" id="card-last-name" placeholder="" name="cardLastName">
						</div>
					</div>
				</div>
				<div class="c-creditCard--col02">
					<div class="c-creditCard__box">
						<div class="c-select-item">
							<label class="font-w-normal-16 font-m-normal-14" for="identification-types">Tipo De Documento / Document Type</label>
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
							<label class="font-w-normal-16 font-m-normal-14" for="identification">Documento / Document</label>
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
							<label class="font-w-normal-16 font-m-normal-14" for="cardIssuer">Medio de Pago / Payment Method</label>
							<select id="cardIssuer" name="cardIssuer">
								<option value="12354">BBVA</option>
							</select>
						</div>
					</div>
				</div>
				<div class="c-creditCard--col01">
					<div class="c-creditCard__box">
						<div class="c-select-item">
							<label class="font-w-normal-16 font-m-normal-14" for="installments">Cuotas Mensuales / Installment</label>
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