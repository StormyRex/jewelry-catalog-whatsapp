<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: admin_upload.php");
    exit();
}

require_once 'db_connect.php';

// Handle delete
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];

    // Get image filename first so we can delete the file too
    $result = mysqli_query($conn, "SELECT image_file FROM products WHERE id = $id");
    $row    = mysqli_fetch_assoc($result);

    if ($row) {
        // Delete image file from disk
        $image_path = 'images/' . $row['image_file'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        // Delete from database
        mysqli_query($conn, "DELETE FROM products WHERE id = $id");
        $success = "Product deleted.";
    }
}

// Fetch all products
$result = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #111; color: #eee; padding: 20px; }
        h2 { color: #d4af37; margin-bottom: 20px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .top-bar a { padding: 8px 16px; background: #d4af37; color: #111; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; background: #1e1e1e; border-radius: 10px; overflow: hidden; }
        th { background: #2a2a2a; padding: 12px; text-align: left; color: #d4af37; font-size: 14px; }
        td { padding: 10px 12px; border-bottom: 1px solid #2a2a2a; font-size: 14px; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        td img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
        .delete-btn { padding: 6px 12px; background: #c0392b; color: #fff; border-radius: 5px; text-decoration: none; font-size: 13px; }
        .success { color: #4caf50; margin-bottom: 15px; }
        .empty { text-align: center; padding: 40px; color: #666; }
        @media (max-width: 600px) {
            table, thead, tbody, th, td, tr { display: block; }
            thead { display: none; }
            td { border: none; padding: 6px 12px; }
            tr { background: #1e1e1e; margin-bottom: 10px; border-radius: 8px; padding: 10px; }
        }
    </style>
</head>
<body>

<div class="top-bar">
    <h2>Manage Products</h2>
    <div style="display:flex; gap:10px;">
        <a href="admin_upload.php">+ Add Product</a>
        <a href="index.php" style="background:#333; color:#aaa;">View Catalog</a>
        <a href="admin_upload.php?logout=1" style="background:#333; color:#aaa;">Logout</a>
    </div>
</div>

<?php if (isset($success)) { echo "<p class='success'>$success</p>"; } ?>

<table>
    <thead>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if (mysqli_num_rows($result) == 0) {
        echo "<tr><td colspan='5' class='empty'>No products yet.</td></tr>";
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "
            <tr>
                <td><img src='images/" . $row['image_file'] . "' alt='" . $row['name'] . "'></td>
                <td>" . $row['name'] . "</td>
                <td>" . $row['category'] . "</td>
                <td>&#8377;" . $row['price'] . "</td>
                <td><a href='admin_edit.php?id=" . $row['id'] . "' style='padding:6px 12px; background:#d4af37; color:#111; border-radius:5px; text-decoration:none; font-size:13px; margin-right:5px;'>Edit</a>
                    <a class='delete-btn' href='admin_products.php?delete_id=" . $row['id'] . "' onclick=\"return confirm('Delete this product?')\">Delete</a>
                </td>
            </tr>";
        }
    }
    ?>
    </tbody>
</table>

</body>
</html>