<!DOCTYPE html>
<html>
<head>
    <title>Product Registration</title>
</head>
<body>
    <h1>Register a Product</h1>
    <?php print_r($errors); ?>
    
	<form action="<?= base_url() ?>page/lgepr_internal_sale/create" enctype="multipart/form-data" method="post" accept-charset="utf-8">

    <label for="category">Category:</label>
    <input type="text" name="category" value="<?php echo set_value('category'); ?>"><br>

    <label for="model">Model:</label>
    <input type="text" name="model" value="<?php echo set_value('model'); ?>"><br>

    <label for="grade">Grade:</label>
    <select name="grade">
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
    </select><br>

    <label for="model">List Price:</label>
    <input type="text" name="price_list" value="<?php echo set_value('price_list'); ?>"><br>
	
    <label for="model">Offer Price:</label>
    <input type="text" name="price_offer" value="<?php echo set_value('price_offer'); ?>"><br>
	
    <label for="created_at">Created At:</label>
    <input type="date" name="created_at" value="<?php echo set_value('created_at'); ?>"><br>

    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" value="<?php echo set_value('end_date'); ?>"><br>

    <label for="image_1">Image 1:</label>
    <input type="file" name="image_1"><br>

    <label for="image_2">Image 2:</label>
    <input type="file" name="image_2"><br>

    <label for="image_3">Image 3:</label>
    <input type="file" name="image_3"><br>

    <label for="image_3">Image 4:</label>
    <input type="file" name="image_3"><br>
	
    <label for="image_3">Image 5:</label>
    <input type="file" name="image_3"><br>
	
    <button type="submit">Register Product</button>
    </form>
</body>
</html>
