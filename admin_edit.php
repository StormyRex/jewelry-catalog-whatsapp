<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_upload.php");
    exit();
}

require_once 'db_connect.php';

$success = "";
$error   = "";

// Get product ID
$id = (int)$_GET['id'];

// Fetch existing product data
$result = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: admin_products.php");
    exit();
}

// Handle form submission
if (isset($_POST['submit'])) {
    $name        = mysqli_real_escape_string($conn, trim($_POST['name']));
    $price       = (int)$_POST['price'];
    $category    = mysqli_real_escape_string($conn, trim($_POST['category']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $in_stock    = isset($_POST['in_stock']) ? 1 : 0;
    $image_file  = $product['image_file']; // keep old image by default

    // Check if new image uploaded
    if ($_FILES['image']['size'] > 0) {
        $allowed_types = array('image/jpeg', 'image/png', 'image/webp');
        $file_type     = $_FILES['image']['type'];
        $file_size     = $_FILES['image']['size'];

        if (!in_array($file_type, $allowed_types)) {
            $error = "Only JPG, PNG, WEBP allowed.";
        } elseif ($file_size > 2097152) {
            $error = "Image too large. Max 2MB.";
        } else {
            $ext         = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_name    = uniqid('product_') . '.' . $ext;
            $destination = 'images/' . $new_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                // Delete old image
                if (file_exists('images/' . $product['image_file'])) {
                    unlink('images/' . $product['image_file']);
                }
                $image_file = $new_name;
            } else {
                $error = "Image upload failed.";
            }
        }
    }

    if ($error == "") {
        $sql = "UPDATE products SET 
                name        = '$name',
                price       = $price,
                category    = '$category',
                description = '$description',
                image_file  = '$image_file',
                in_stock    = $in_stock
                WHERE id    = $id";

        if (mysqli_query($conn, $sql)) {
            $success = "Product updated successfully!";
            // Refresh product data to show updated values
            $result  = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
            $product = mysqli_fetch_assoc($result);
        } else {
            $error = "Database error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #111; color: #eee; min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .container { background: #1e1e1e; padding: 30px; border-radius: 12px; width: 100%; max-width: 500px; }
        h2 { color: #d4af37; margin-bottom: 20px; text-align: center; }
        label { display: block; margin: 12px 0 4px; font-size: 14px; color: #aaa; }
        input, textarea, select { width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; border-radius: 6px; color: #fff; font-size: 14px; }
        textarea { height: 80px; resize: vertical; }
        .current-img { width: 100%; height: 150px; object-fit: cover; border-radius: 6px; margin-top: 8px; }
        .checkbox-row { display: flex; align-items: center; gap: 10px; margin-top: 12px; }
        .checkbox-row input { width: auto; }
        button { width: 100%; margin-top: 20px; padding: 12px; background: #d4af37; color: #111; font-size: 16px; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; }
        .back { display: block; text-align: center; margin-top: 15px; color: #888; text-decoration: none; font-size: 13px; }
        .success { color: #4caf50; margin-top: 15px; text-align: center; }
        .error   { color: #f44336; margin-top: 15px; text-align: center; }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Product</h2>

    <form method="POST" enctype="multipart/form-data">
        <label>Product Name</label>
        <input type="text" name="name" required value="<?php echo $product['name']; ?>">

        <label>Price (₹)</label>
        <input type="number" name="price" required min="1" value="<?php echo $product['price']; ?>">

        <label>Category</label>
        <select name="category" required>
            <?php
            $categories = array('Necklace','Earrings','Bracelet','Ring','Bangles','Anklet');
            foreach ($categories as $cat) {
                $selected = ($product['category'] == $cat) ? "selected" : "";
                echo "<option value='$cat' $selected>$cat</option>";
            }
            ?>
        </select>

        <label>Description</label>
        <textarea name="description" required><?php echo $product['description']; ?></textarea>

        <label>Current Image</label>
        <img src="images/<?php echo $product['image_file']; ?>" class="current-img" alt="Current">

        <label>Replace Image (optional)</label>
        <input type="file" name="image" accept="image/*">

        <div class="checkbox-row">
            <input type="checkbox" name="in_stock" id="in_stock" <?php if($product['in_stock'] == 1) echo "checked"; ?>>
            <label for="in_stock" style="margin:0;">In Stock</label>
        </div>

        <button type="submit" name="submit">Update Product</button>
    </form>

    <?php if ($success != "") { echo "<p class='success'>$success</p>"; } ?>
    <?php if ($error != "")   { echo "<p class='error'>$error</p>"; } ?>

    <a href="admin_products.php" class="back">← Back to Manage Products</a>
</div>
</body>
</html>