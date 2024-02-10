<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title>LG Peru</title>
	<link href="<?= base_url() ?>assets/img/favicon-32x32.png" rel="icon">
	<link href="<?= base_url() ?>assets/img/apple-touch-icon.png" rel="apple-touch-icon">
	<link href="<?= base_url() ?>assets/vendor/bootstrap-5.3.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
	<?php $this->load->view($main); ?>
	
	<script src="<?= base_url() ?>assets/vendor/bootstrap-5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>