<?php
session_start();

if (!isset($_SESSION['admin'])) {
    if (isset($_POST['password'])) {
        if ($_POST['password'] === 'admin123') {
            $_SESSION['admin'] = true;
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $login_error = "Wrong password.";
        }
    }

    if (!isset($_SESSION['admin'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — TP Jewellery</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Lato', sans-serif;
            background: #FDF6F0;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            overflow: hidden;
        }
        body::before {
            content: '';
            position: fixed;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(232,180,184,0.35) 0%, transparent 70%);
            top: -150px; right: -150px;
            border-radius: 50%; pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(201,169,110,0.2) 0%, transparent 70%);
            bottom: -100px; left: -100px;
            border-radius: 50%; pointer-events: none;
        }
        .login-box {
            position: relative; z-index: 1;
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 24px;
            padding: 48px 40px;
            width: 100%; max-width: 400px;
            box-shadow: 0 8px 40px rgba(201,169,110,0.12);
            text-align: center;
        }
        .login-box img { height: 64px; margin-bottom: 16px; border-radius: 8px; }
        .login-box h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px; color: #C9A96E;
            letter-spacing: 3px; margin-bottom: 4px;
        }
        .login-box p { font-size: 11px; color: #E8B4B8; letter-spacing: 3px; text-transform: uppercase; margin-bottom: 28px; }
        label { display: block; text-align: left; font-size: 12px; color: #999; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 6px; }
        input[type="password"] {
            width: 100%; padding: 12px 16px;
            background: rgba(255,255,255,0.7);
            border: 1px solid rgba(201,169,110,0.3);
            border-radius: 10px; font-size: 15px;
            color: #2C2C2C; outline: none;
            font-family: 'Lato', sans-serif;
            margin-bottom: 18px;
        }
        input[type="password"]:focus { border-color: #C9A96E; }
        button {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #C9A96E, #d4b896);
            color: #fff; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700; cursor: pointer;
            letter-spacing: 1px;
            box-shadow: 0 4px 16px rgba(201,169,110,0.3);
        }
        .error { color: #e74c3c; font-size: 13px; margin-top: 14px; }
    </style>
</head>
<body>
<div class="login-box">
    <img src="images/TP_Jewellery.jpg" alt="TP Jewellery">
    <h1>TP JEWELLERY</h1>
    <p>Admin Panel</p>
    <form method="POST">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter admin password" autofocus>
        <button type="submit">Login</button>
    </form>
    <?php if (isset($login_error)) echo "<p class='error'>$login_error</p>"; ?>
</div>
</body>
</html>
<?php
        exit();
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_upload.php");
    exit();
}

require_once 'db_connect.php';
$success = $error = "";

if (isset($_POST['submit'])) {
    $name        = mysqli_real_escape_string($conn, trim($_POST['name']));
    $price       = (int)$_POST['price'];
    $category    = mysqli_real_escape_string($conn, trim($_POST['category']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description']));

    $allowed_types = array('image/jpeg', 'image/png', 'image/webp');
    $file_type     = $_FILES['image']['type'];
    $file_size     = $_FILES['image']['size'];
    $file_tmp      = $_FILES['image']['tmp_name'];
    $file_name     = $_FILES['image']['name'];

    if (!in_array($file_type, $allowed_types)) {
        $error = "Only JPG, PNG, WEBP allowed.";
    } elseif ($file_size > 2097152) {
        $error = "Image too large. Max 2MB.";
    } else {
        $ext         = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_name    = uniqid('product_') . '.' . $ext;
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
            $error = "File upload failed.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product — TP Jewellery Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Lato', sans-serif;
            background: #FDF6F0;
            min-height: 100vh;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: fixed; width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(232,180,184,0.3) 0%, transparent 70%);
            top: -150px; right: -150px;
            border-radius: 50%; pointer-events: none; z-index: 0;
        }
        body::after {
            content: '';
            position: fixed; width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(201,169,110,0.18) 0%, transparent 70%);
            bottom: -100px; left: -100px;
            border-radius: 50%; pointer-events: none; z-index: 0;
        }

        /* NAV */
        nav {
            position: sticky; top: 0; z-index: 100;
            background: rgba(255,255,255,0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(201,169,110,0.2);
            padding: 14px 30px;
            display: flex; align-items: center;
            justify-content: space-between; flex-wrap: wrap; gap: 10px;
            box-shadow: 0 4px 24px rgba(201,169,110,0.08);
        }
        .nav-brand {
            display: flex; align-items: center; gap: 12px;
        }
        .nav-brand img { height: 44px; border-radius: 6px; }
        .nav-brand span {
            font-family: 'Cormorant Garamond', serif;
            font-size: 18px; color: #C9A96E; letter-spacing: 2px;
        }
        .nav-links { display: flex; gap: 8px; flex-wrap: wrap; }
        .nav-links a {
            padding: 7px 16px; border-radius: 20px;
            text-decoration: none; font-size: 13px; font-weight: 700;
            transition: background 0.2s;
        }
        .btn-gold { background: rgba(201,169,110,0.15); color: #C9A96E; border: 1px solid rgba(201,169,110,0.35); }
        .btn-gold:hover { background: rgba(201,169,110,0.3); }
        .btn-ghost { background: rgba(0,0,0,0.04); color: #999; border: 1px solid rgba(0,0,0,0.08); }
        .btn-ghost:hover { background: rgba(0,0,0,0.08); }

        /* FORM */
        .page-wrap {
            position: relative; z-index: 1;
            max-width: 560px; margin: 40px auto;
            padding: 0 20px 60px;
        }
        .page-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px; color: #2C2C2C;
            margin-bottom: 24px;
        }
        .form-glass {
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,0.85);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 8px 40px rgba(201,169,110,0.08);
        }
        label {
            display: block; font-size: 11px; color: #999;
            text-transform: uppercase; letter-spacing: 1.5px;
            margin-bottom: 6px; margin-top: 18px;
        }
        label:first-child { margin-top: 0; }
        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea, select {
            width: 100%; padding: 11px 14px;
            background: rgba(255,255,255,0.75);
            border: 1px solid rgba(201,169,110,0.25);
            border-radius: 10px; color: #2C2C2C;
            font-size: 14px; font-family: 'Lato', sans-serif;
            outline: none;
        }
        input:focus, textarea:focus, select:focus {
            border-color: #C9A96E;
            background: rgba(255,255,255,0.95);
        }
        textarea { height: 90px; resize: vertical; }
        .submit-btn {
            width: 100%; margin-top: 24px; padding: 13px;
            background: linear-gradient(135deg, #C9A96E, #d4b896);
            color: #fff; border: none; border-radius: 10px;
            font-size: 15px; font-weight: 700; cursor: pointer;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 16px rgba(201,169,110,0.3);
        }
        .success { color: #2e7d32; background: rgba(46,125,50,0.08); border: 1px solid rgba(46,125,50,0.2); border-radius: 10px; padding: 12px; margin-top: 16px; text-align: center; font-size: 14px; }
        .error   { color: #c62828; background: rgba(198,40,40,0.08); border: 1px solid rgba(198,40,40,0.2); border-radius: 10px; padding: 12px; margin-top: 16px; text-align: center; font-size: 14px; }
    </style>
</head>
<body>
<nav>
    <div class="nav-brand">
        <img src="images/TP_Jewellery.jpg" alt="TP Jewellery">
        <span>TP JEWELLERY · Admin</span>
    </div>
    <div class="nav-links">
        <a href="admin_dashboard.php" class="btn-ghost">Dashboard</a>
        <a href="admin_products.php"  class="btn-ghost">Manage</a>
        <a href="index.php"           class="btn-ghost">View Site</a>
        <a href="?logout=1"           class="btn-ghost">Logout</a>
    </div>
</nav>

<div class="page-wrap">
    <h2 class="page-title">Add New Product</h2>
    <div class="form-glass">
        <form method="POST" enctype="multipart/form-data">
            <label>Product Name</label>
            <input type="text" name="name" required>

            <label>Price (₹)</label>
            <input type="number" name="price" required min="1">

            <label>Category</label>
            <select name="category" required>
                <option value="">-- Select Category --</option>
                <option value="Necklace">Necklace</option>
                <option value="Earrings">Earrings</option>
                <option value="Bracelet">Bracelet</option>
                <option value="Ring">Ring</option>
                <option value="Bangles">Bangles</option>
                <option value="Anklet">Anklet</option>
            </select>

            <label>Description</label>
            <textarea name="description" required></textarea>

            <label>Product Image (JPG / PNG / WEBP · max 2MB)</label>
            <input type="file" name="image" accept="image/*" required>

            <button type="submit" name="submit" class="submit-btn">Add Product</button>
        </form>
        <?php if ($success != "") echo "<p class='success'>$success</p>"; ?>
        <?php if ($error   != "") echo "<p class='error'>$error</p>"; ?>
    </div>
</div>
</body>
</html>