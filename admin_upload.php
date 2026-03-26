<?php
session_start();

// Admin login check
if (!isset($_SESSION['admin'])) {
    if (isset($_POST['password'])) {
        if ($_POST['password'] === 'admin123') {
            $_SESSION['admin'] = true;
        } else {
            $login_error = "Wrong password.";
        }
    }

    if (!isset($_SESSION['admin'])) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        body { font-family: Arial; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; background:#111; }
        .box { background:#222; padding:30px; border-radius:10px; text-align:center; }
        input { padding:10px; margin:10px 0; width:200px; border-radius:5px; border:none; }
        button { padding:10px 20px; background:#d4af37; border:none; border-radius:5px; cursor:pointer; font-weight:bold; }
        p { color:red; }
    </style>
</head>
<body>
    <div class="box">
        <h2 style="color:#d4af37">Admin Login</h2>
        <form method="POST">
            <input type="password" name="password" placeholder="Enter password"><br>
            <button type="submit">Login</button>
        </form>
        <?php if (isset($login_error)) { echo "<p>$login_error</p>"; } ?>
    </div>
</body>
</html>
<?php
        exit();
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_upload.php");
    exit();
}

require_once 'db_connect.php';

$success = "";
$error   = "";

if (isset($_POST['submit'])) {

    // Get form values
    $name        = trim($_POST['name']);
    $price       = (int)$_POST['price'];
    $category    = trim($_POST['category']);
    $description = trim($_POST['description']);

    // Escape strings to prevent SQL injection
    $name        = mysqli_real_escape_string($conn, $name);
    $category    = mysqli_real_escape_string($conn, $category);
    $description = mysqli_real_escape_string($conn, $description);

    // File upload handling
    $allowed_types = array('image/jpeg', 'image/png', 'image/webp');
    $file_type     = $_FILES['image']['type'];
    $file_size     = $_FILES['image']['size'];
    $file_tmp      = $_FILES['image']['tmp_name'];
    $file_name     = $_FILES['image']['name'];

    if (!in_array($file_type, $allowed_types)) {
        $error = "Only JPG, PNG, WEBP images allowed.";
    } elseif ($file_size > 2097152) { // 2MB in bytes
        $error = "Image too large. Maximum size is 2MB.";
    } else {
        // Get file extension
        $ext      = pathinfo($file_name, PATHINFO_EXTENSION);
        // Create unique filename
        $new_name = uniqid('product_') . '.' . $ext;
        $destination = 'images/' . $new_name;

        if (move_uploaded_file($file_tmp, $destination)) {
            $sql = "INSERT INTO products (name, price, category, image_file, description) 
                    VALUES ('$name', $price, '$category', '$new_name', '$description')";

            if (mysqli_query($conn, $sql)) {
                $success = "Product added successfully!";
            } else {
                $error = "Database error: " . mysqli_error($conn);
            }
        } else {
            $error = "File upload failed. Check images/ folder exists.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Add Product</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #111; color: #eee; min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .container { background: #1e1e1e; padding: 30px; border-radius: 12px; width: 100%; max-width: 500px; }
        h2 { color: #d4af37; margin-bottom: 20px; text-align: center; }
        label { display: block; margin: 12px 0 4px; font-size: 14px; color: #aaa; }
        input, textarea, select { width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #444; border-radius: 6px; color: #fff; font-size: 14px; }
        textarea { height: 80px; resize: vertical; }
        button { width: 100%; margin-top: 20px; padding: 12px; background: #d4af37; color: #111; font-size: 16px; font-weight: bold; border: none; border-radius: 6px; cursor: pointer; }
        .success { color: #4caf50; margin-top: 15px; text-align: center; }
        .error   { color: #f44336; margin-top: 15px; text-align: center; }
        .logout  { text-align: center; margin-top: 15px; }
        .logout a { color: #888; font-size: 13px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Add New Product</h2>

    <form method="POST" enctype="multipart/form-data">
        <label>Product Name</label>
        <input type="text" name="name" required>

        <label>Price (₹)</label>
        <input type="number" name="price" required min="1">

        <label>Category</label>
        <select name="category" required>
            <option value="">-- Select --</option>
            <option value="Necklace">Necklace</option>
            <option value="Earrings">Earrings</option>
            <option value="Bracelet">Bracelet</option>
            <option value="Ring">Ring</option>
            <option value="Bangles">Bangles</option>
            <option value="Anklet">Anklet</option>
        </select>

        <label>Description</label>
        <textarea name="description" required></textarea>

        <label>Product Image (JPG/PNG/WEBP, max 2MB)</label>
        <input type="file" name="image" accept="image/*" required>

        <button type="submit" name="submit">Add Product</button>
    </form>

    <?php if ($success != "") { echo "<p class='success'>$success</p>"; } ?>
    <?php if ($error != "")   { echo "<p class='error'>$error</p>"; } ?>

    <div class="logout">
    <a href="admin_products.php" style="color:#d4af37; margin-right:15px;">Manage Products</a>
    <a href="?logout=1" style="color:#888;">Logout</a>
    </div>
</div>
</body>
</html>