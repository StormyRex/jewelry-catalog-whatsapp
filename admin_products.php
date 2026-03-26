<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin_upload.php");
    exit();
}
require_once 'db_connect.php';

if (isset($_GET['delete_id'])) {
    $id     = (int)$_GET['delete_id'];
    $result = mysqli_query($conn, "SELECT image_file FROM products WHERE id = $id");
    $row    = mysqli_fetch_assoc($result);
    if ($row) {
        if (file_exists('images/' . $row['image_file'])) unlink('images/' . $row['image_file']);
        mysqli_query($conn, "DELETE FROM products WHERE id = $id");
    }
    header("Location: admin_products.php");
    exit();
}

// Bulk out of stock by category
if (isset($_POST['bulk_category']) && $_POST['bulk_category'] != "") {
    $bulk_cat = mysqli_real_escape_string($conn, $_POST['bulk_category']);
    mysqli_query($conn, "UPDATE products SET in_stock = 0 WHERE category = '$bulk_cat'");
    $bulk_success = "All $bulk_cat products marked as out of stock.";
}

$result = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products — TP Jewellery Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Lato', sans-serif; background: #FDF6F0; color: #2C2C2C; min-height: 100vh; overflow-x: hidden; }
        body::before { content: ''; position: fixed; width: 600px; height: 600px; background: radial-gradient(circle, rgba(232,180,184,0.3) 0%, transparent 70%); top: -150px; right: -150px; border-radius: 50%; pointer-events: none; z-index: 0; }
        body::after  { content: ''; position: fixed; width: 500px; height: 500px; background: radial-gradient(circle, rgba(201,169,110,0.18) 0%, transparent 70%); bottom: -100px; left: -100px; border-radius: 50%; pointer-events: none; z-index: 0; }
        nav { position: sticky; top: 0; z-index: 100; background: rgba(255,255,255,0.65); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border-bottom: 1px solid rgba(201,169,110,0.2); padding: 14px 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; box-shadow: 0 4px 24px rgba(201,169,110,0.08); }
        .nav-brand { display: flex; align-items: center; gap: 12px; }
        .nav-brand img { height: 44px; border-radius: 6px; }
        .nav-brand span { font-family: 'Cormorant Garamond', serif; font-size: 18px; color: #C9A96E; letter-spacing: 2px; }
        .nav-links { display: flex; gap: 8px; flex-wrap: wrap; }
        .nav-links a { padding: 7px 16px; border-radius: 20px; text-decoration: none; font-size: 13px; font-weight: 700; }
        .btn-gold  { background: rgba(201,169,110,0.15); color: #C9A96E; border: 1px solid rgba(201,169,110,0.35); }
        .btn-ghost { background: rgba(0,0,0,0.04); color: #999; border: 1px solid rgba(0,0,0,0.08); }
        .page-wrap { position: relative; z-index: 1; max-width: 1100px; margin: 0 auto; padding: 36px 24px 60px; }
        .page-title { font-family: 'Cormorant Garamond', serif; font-size: 30px; color: #2C2C2C; margin-bottom: 24px; }
        .table-glass { background: rgba(255,255,255,0.55); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.85); border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(201,169,110,0.07); }
        table { width: 100%; border-collapse: collapse; }
        th { padding: 14px 18px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 1.5px; color: #C9A96E; background: rgba(201,169,110,0.08); font-weight: 700; }
        td { padding: 12px 18px; border-bottom: 1px solid rgba(201,169,110,0.08); font-size: 14px; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        td img { width: 52px; height: 52px; object-fit: cover; border-radius: 8px; }
        .badge-in  { background: rgba(46,125,50,0.1);  color: #2e7d32; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .badge-out { background: rgba(198,40,40,0.1);  color: #c62828; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .action-edit { color: #C9A96E; font-size: 13px; text-decoration: none; font-weight: 700; margin-right: 12px; }
        .action-del  { color: #e74c3c; font-size: 13px; text-decoration: none; font-weight: 700; }
        .empty { text-align: center; padding: 60px; color: #bbb; font-family: 'Cormorant Garamond', serif; font-size: 20px; }
        @media (max-width: 600px) { nav { padding: 12px 16px; } .page-wrap { padding: 20px 16px 50px; } td, th { padding: 10px 12px; } }
    </style>
</head>
<body>
<nav>
    <div class="nav-brand">
        <img src="images/TP_Jewellery.jpg" alt="TP Jewellery">
        <span>TP JEWELLERY · Admin</span>
    </div>
    <div class="nav-links">
        <a href="admin_upload.php"   class="btn-gold">+ Add Product</a>
        <a href="admin_dashboard.php" class="btn-ghost">Dashboard</a>
        <a href="index.php"           class="btn-ghost">View Site</a>
        <a href="admin_upload.php?logout=1" class="btn-ghost">Logout</a>
    </div>
</nav>
<div class="page-wrap">
    <h2 class="page-title">All Products</h2>
    <!-- Bulk Action Panel -->
    <div style="
        background: rgba(255,255,255,0.55);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255,255,255,0.85);
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(201,169,110,0.07);
        display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    ">
        <span style="font-size:13px; color:#999; font-weight:700; text-transform:uppercase; letter-spacing:1px;">Bulk Action:</span>
        <form method="POST" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <select name="bulk_category" style="padding:8px 14px; border:1px solid rgba(201,169,110,0.25); border-radius:8px; background:rgba(255,255,255,0.75); color:#2C2C2C; font-size:14px; outline:none;">
                <option value="">-- Select Category --</option>
                <?php
                $cats = array('Necklace','Earrings','Bracelet','Ring','Bangles','Anklet');
                foreach ($cats as $cat) {
                    echo "<option value='$cat'>$cat</option>";
                }
                ?>
            </select>
            <button type="submit" style="padding:8px 18px; background:rgba(198,40,40,0.1); color:#c62828; border:1px solid rgba(198,40,40,0.25); border-radius:8px; font-size:13px; font-weight:700; cursor:pointer;">
                Mark All Out of Stock
            </button>
        </form>
        <?php if (isset($bulk_success)) echo "<span style='color:#2e7d32; font-size:13px; font-weight:700;'>✓ $bulk_success</span>"; ?>
    </div>
    <div class="table-glass">
        <table>
            <thead>
                <tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php if (mysqli_num_rows($result) == 0): ?>
                <tr><td colspan="6" class="empty">No products yet.</td></tr>
            <?php else: ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><img src="images/<?php echo $row['image_file']; ?>" alt="<?php echo $row['name']; ?>"></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['category']; ?></td>
                    <td>&#8377;<?php echo $row['price']; ?></td>
                    <td><?php echo $row['in_stock'] ? "<span class='badge-in'>In Stock</span>" : "<span class='badge-out'>Out of Stock</span>"; ?></td>
                    <td>
                        <a href="admin_edit.php?id=<?php echo $row['id']; ?>" class="action-edit">Edit</a>
                        <a href="admin_products.php?delete_id=<?php echo $row['id']; ?>" class="action-del" onclick="return confirm('Delete this product?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>