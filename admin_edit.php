<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_upload.php");
    exit();
}
require_once 'db_connect.php';

$success = $error = "";
$id      = (int)$_GET['id'];
$result  = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: admin_products.php");
    exit();
}

if (isset($_POST['submit'])) {
    $name        = mysqli_real_escape_string($conn, trim($_POST['name']));
    $price       = (int)$_POST['price'];
    $category    = mysqli_real_escape_string($conn, trim($_POST['category']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));
    $in_stock    = isset($_POST['in_stock']) ? 1 : 0;
    $image_file  = $product['image_file'];

    if ($_FILES['image']['size'] > 0) {
        $allowed_types = array('image/jpeg', 'image/png', 'image/webp');
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $error = "Only JPG, PNG, WEBP allowed.";
        } elseif ($_FILES['image']['size'] > 2097152) {
            $error = "Image too large. Max 2MB.";
        } else {
            $ext         = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $new_name    = uniqid('product_') . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], 'images/' . $new_name)) {
                if (file_exists('images/' . $product['image_file'])) unlink('images/' . $product['image_file']);
                $image_file = $new_name;
            } else {
                $error = "Image upload failed.";
            }
        }
    }

    if ($error == "") {
        $sql = "UPDATE products SET name='$name', price=$price, category='$category', description='$description', image_file='$image_file', in_stock=$in_stock WHERE id=$id";
        if (mysqli_query($conn, $sql)) {
            $success = "Product updated successfully!";
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
    <title>Edit Product — TP Jewellery Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Lato', sans-serif; background: #FDF6F0; min-height: 100vh; overflow-x: hidden; }
        body::before { content: ''; position: fixed; width: 600px; height: 600px; background: radial-gradient(circle, rgba(232,180,184,0.3) 0%, transparent 70%); top: -150px; right: -150px; border-radius: 50%; pointer-events: none; z-index: 0; }
        body::after  { content: ''; position: fixed; width: 500px; height: 500px; background: radial-gradient(circle, rgba(201,169,110,0.18) 0%, transparent 70%); bottom: -100px; left: -100px; border-radius: 50%; pointer-events: none; z-index: 0; }
        nav { position: sticky; top: 0; z-index: 100; background: rgba(255,255,255,0.65); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-bottom: 1px solid rgba(201,169,110,0.2); padding: 14px 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; box-shadow: 0 4px 24px rgba(201,169,110,0.08); }
        .nav-brand { display: flex; align-items: center; gap: 12px; }
        .nav-brand img { height: 44px; border-radius: 6px; }
        .nav-brand span { font-family: 'Cormorant Garamond', serif; font-size: 18px; color: #C9A96E; letter-spacing: 2px; }
        .nav-links { display: flex; gap: 8px; flex-wrap: wrap; }
        .nav-links a { padding: 7px 16px; border-radius: 20px; text-decoration: none; font-size: 13px; font-weight: 700; }
        .btn-ghost { background: rgba(0,0,0,0.04); color: #999; border: 1px solid rgba(0,0,0,0.08); }
        .page-wrap { position: relative; z-index: 1; max-width: 560px; margin: 40px auto; padding: 0 20px 60px; }
        .page-title { font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #2C2C2C; margin-bottom: 24px; }
        .form-glass { background: rgba(255,255,255,0.6); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); border: 1px solid rgba(255,255,255,0.85); border-radius: 20px; padding: 32px; box-shadow: 0 8px 40px rgba(201,169,110,0.08); }
        label { display: block; font-size: 11px; color: #999; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 6px; margin-top: 18px; }
        label:first-child { margin-top: 0; }
        input[type="text"], input[type="number"], input[type="file"], textarea, select { width: 100%; padding: 11px 14px; background: rgba(255,255,255,0.75); border: 1px solid rgba(201,169,110,0.25); border-radius: 10px; color: #2C2C2C; font-size: 14px; font-family: 'Lato', sans-serif; outline: none; }
        input:focus, textarea:focus, select:focus { border-color: #C9A96E; background: rgba(255,255,255,0.95); }
        textarea { height: 90px; resize: vertical; }
        .current-img { width: 100%; height: 160px; object-fit: cover; border-radius: 10px; margin-top: 8px; border: 1px solid rgba(201,169,110,0.2); }
        .checkbox-row { display: flex; align-items: center; gap: 10px; margin-top: 18px; }
        .checkbox-row input { width: auto; }
        .checkbox-row label { margin: 0; font-size: 13px; color: #555; text-transform: none; letter-spacing: 0; }
        .submit-btn { width: 100%; margin-top: 24px; padding: 13px; background: linear-gradient(135deg, #C9A96E, #d4b896); color: #fff; border: none; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 16px rgba(201,169,110,0.3); }
        .success { color: #2e7d32; background: rgba(46,125,50,0.08); border: 1px solid rgba(46,125,50,0.2); border-radius: 10px; padding: 12px; margin-top: 16px; text-align: center; font-size: 14px; }
        .error   { color: #c62828; background: rgba(198,40,40,0.08); border: 1px solid rgba(198,40,40,0.2); border-radius: 10px; padding: 12px; margin-top: 16px; text-align: center; font-size: 14px; }
        .back-link { display: inline-block; margin-top: 16px; color: #C9A96E; font-size: 13px; text-decoration: none; border-bottom: 1px solid rgba(201,169,110,0.3); }
    </style>
</head>
<body>
<nav>
    <div class="nav-brand">
        <img src="images/TP_Jewellery.jpg" alt="TP Jewellery">
        <span>TP JEWELLERY · Admin</span>
    </div>
    <div class="nav-links">
        <a href="admin_dashboard.php"  class="btn-ghost">Dashboard</a>
        <a href="admin_products.php"   class="btn-ghost">Manage</a>
        <a href="admin_upload.php?logout=1" class="btn-ghost">Logout</a>
    </div>
</nav>
<div class="page-wrap">
    <h2 class="page-title">Edit Product</h2>
    <div class="form-glass">
        <form method="POST" enctype="multipart/form-data">
            <label>Product Name</label>
            <input type="text" name="name" required value="<?php echo $product['name']; ?>">

            <label>Price (₹)</label>
            <input type="number" name="price" required min="1" value="<?php echo $product['price']; ?>">

            <label>Category</label>
            <select name="category" required>
                <?php
                $cats = array('Necklace','Earrings','Bracelet','Ring','Bangles','Anklet');
                foreach ($cats as $cat) {
                    $sel = ($product['category'] == $cat) ? "selected" : "";
                    echo "<option value='$cat' $sel>$cat</option>";
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
                <label for="in_stock">In Stock</label>
            </div>

            <button type="submit" name="submit" class="submit-btn">Update Product</button>
        </form>
        <?php if ($success != "") echo "<p class='success'>$success</p>"; ?>
        <?php if ($error   != "") echo "<p class='error'>$error</p>"; ?>
        <a href="admin_products.php" class="back-link">← Back to All Products</a>
    </div>
</div>
</body>
</html>