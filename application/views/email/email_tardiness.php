<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerta tardanza</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fa;
        }
        .email-container {
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #f0ece4;
            color: #333;
            padding: 15px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            font-size: 24px;
            margin: 0;
        }
        .header img {
            width: 50px;
            margin-bottom: 10px;
        }
        .content {
            margin-top: 20px;
            font-size: 16px;
            color: #333;
        }
        .content p {
            line-height: 1.6;
        }
        .highlight {
            color: #007BFF;
            font-weight: bold;
        }
        .table-container {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .table-container th, .table-container td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        .table-container th {
            background-color: #f2f2f2;
            color: #333;
        }
        .table-container tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer a {
            color: #007BFF;
            text-decoration: none;
        }
        .icon {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            vertical-align: middle;
        }
    </style>
</head>

<body>

    <div class="email-container">
        <div class="header">
            <img src="https://www.lg.com/content/dam/lge/common/logo/logo-lg-100-44.svg" alt="Logo">
            <h1>Notificacion de tardanza</h1>
        </div>

        <div class="content">
            <!--<p>Estimado/a <?= $info_pr['name']?>,</p>-->
			
			<p class="text-center">Informamos que ud. registro una tardanza el dia de hoy <?=$current_day_format?> con hora de entrada <?=$info_pr['first_access']?> y un retraso de <?= $delay ?>.</p>
           
		    <p class="text-center">Si has tenido un inconveniente, recuerda justificar la tardanza mediante un approval, considerando la siguiente linea de aprobacion:</p>
		   
			<p>
				<div style="margin-bottom: 5px;">
					<strong>Solicitante - Team Leader - CEO o CFO</strong> (de acuerdo al area)
				</div>
				<div>
					<strong>CC:</strong> carlos.mego, hans.beuermann
				</div>
			</p>
			<p class="text-center"> <strong>Nota:</strong> dejar sin efecto la presente, si al momento de recibir la notificacion a justificado la tardanza.</p>
			
            <p>A continuacion, se detalla la informacion:</p>

                <table class="table-container">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Hora de entrada</th>
                            <th>Marcacion</th>
							<th>Retraso</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php //foreach ($info_pr as $info): ?>
						<tr>
							<td><?= $current_day ?></td>
							<td><?= $info_pr['name'] ?></td>
							<td><?= $info_pr['work_start'] ?></td>
							<td><?= $info_pr['first_access'] ?></td>							
							<td><?= $delay ?></td>
						</tr>
                        <?php //endforeach; ?>
                    </tbody>
                </table>


            <!--<p>Saludos cordiales,</p>-->
             <!--<p><strong>Process Innovation Team</strong></p>-->
        </div>

        <div class="footer">
            <p>Este es un mensaje generado automaticamente. Por favor, no respondas a este correo.</p>
        </div>
    </div>

</body>
</html>