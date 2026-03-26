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
        if (file_exists('images/' . $row['image_file'])) {
            unlink('images/' . $row['image_file']);
        }
        mysqli_query($conn, "DELETE FROM products WHERE id = $id");
    }
    header("Location: admin_dashboard.php");
    exit();
}

// Stats
$total_products  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products"))['total'];
$total_instock   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE in_stock = 1"))['total'];
$total_outstock  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM products WHERE in_stock = 0"))['total'];
$total_cats      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT category) as total FROM products"))['total'];

// Last 5 added
$recent = mysqli_query($conn, "SELECT * FROM products ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #111; color: #eee; min-height: 100vh; padding: 20px; }

        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 10px; }
        .top-bar h2 { color: #d4af37; font-size: 22px; }
        .nav-links { display: flex; gap: 10px; flex-wrap: wrap; }
        .nav-links a { padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 14px; font-weight: bold; }
        .btn-gold  { background: #d4af37; color: #111; }
        .btn-dark  { background: #2a2a2a; color: #aaa; }

        /* Stat cards */
        .stats { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .stat-card { background: #1e1e1e; border-radius: 10px; padding: 20px; text-align: center; border: 1px solid #2a2a2a; }
        .stat-card .number { font-size: 36px; font-weight: bold; color: #d4af37; }
        .stat-card .label  { font-size: 13px; color: #888; margin-top: 6px; }

        /* Recent table */
        h3 { color: #d4af37; margin-bottom: 15px; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; background: #1e1e1e; border-radius: 10px; overflow: hidden; }
        th { background: #2a2a2a; padding: 12px; text-align: left; color: #d4af37; font-size: 14px; }
        td { padding: 10px 12px; border-bottom: 1px solid #2a2a2a; font-size: 14px; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        td img { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; }
        .badge-in  { background: #1a3a1a; color: #4caf50; padding: 3px 8px; border-radius: 4px; font-size: 12px; }
        .badge-out { background: #3a1a1a; color: #e74c3c; padding: 3px 8px; border-radius: 4px; font-size: 12px; }
    </style>
</head>
<body>

<div class="top-bar">
    <h2>Admin Dashboard</h2>
    <div class="nav-links">
        <a href="admin_upload.php"   class="btn-gold">+ Add Product</a>
        <a href="admin_products.php" class="btn-dark">Manage Products</a>
        <a href="index.php"          class="btn-dark">View Catalog</a>
        <a href="admin_upload.php?logout=1" class="btn-dark">Logout</a>
    </div>
</div>

<!-- Stats -->
<div class="stats">
    <div class="stat-card">
        <div class="number"><?php echo $total_products; ?></div>
        <div class="label">Total Products</div>
    </div>
    <div class="stat-card">
        <div class="number" style="color:#4caf50;"><?php echo $total_instock; ?></div>
        <div class="label">In Stock</div>
    </div>
    <div class="stat-card">
        <div class="number" style="color:#e74c3c;"><?php echo $total_outstock; ?></div>
        <div class="label">Out of Stock</div>
    </div>
    <div class="stat-card">
        <div class="number"><?php echo $total_cats; ?></div>
        <div class="label">Categories</div>
    </div>
</div>

<!-- Recent Products -->
<h3>Recently Added</h3>
<table>
    <thead>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = mysqli_fetch_assoc($recent)): ?>
        <tr>
            <td><img src="images/<?php echo $row['image_file']; ?>" alt="<?php echo $row['name']; ?>"></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['category']; ?></td>
            <td>&#8377;<?php echo $row['price']; ?></td>
            <td><?php echo $row['in_stock'] ? "<span class='badge-in'>In Stock</span>" : "<span class='badge-out'>Out of Stock</span>"; ?></td>
            <td>
                <a href="admin_edit.php?id=<?php echo $row['id']; ?>" style="color:#d4af37; font-size:13px; margin-right:10px;">Edit</a>
                <a href="admin_dashboard.php?delete_id=<?php echo $row['id']; ?>" style="color:#e74c3c; font-size:13px;" onclick="return confirm('Delete this product?')">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>