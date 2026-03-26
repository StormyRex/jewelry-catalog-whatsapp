<?php
session_start();
require_once 'db_connect.php';

// Get filter values
$search   = "";
$category = "";

if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = mysqli_real_escape_string($conn, trim($_GET['search']));
}
if (isset($_GET['category']) && $_GET['category'] != "") {
    $category = mysqli_real_escape_string($conn, $_GET['category']);
}

// Build query with both filters combined
$where = array();
if ($search != "")   { $where[] = "name LIKE '%$search%'"; }
if ($category != "") { $where[] = "category = '$category'"; }

$query = "SELECT * FROM products";
if (count($where) > 0) {
    $query .= " WHERE " . implode(" AND ", $where);
}
$query .= " ORDER BY created_at DESC";

$result   = mysqli_query($conn, $query);
$count    = mysqli_num_rows($result);

// Get distinct categories from DB (dynamic — not hardcoded)
$cat_result = mysqli_query($conn, "SELECT DISTINCT category FROM products ORDER BY category");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jewelry Catalog</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #111; color: #eee; min-height: 100vh; }

        header {
            background: #1a1a1a;
            border-bottom: 2px solid #d4af37;
            padding: 15px 20px;
            text-align: center;
        }
        header h1 { color: #d4af37; font-size: 24px; }
        header p  { color: #aaa; font-size: 13px; margin-top: 4px; }

        .filters {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .filters form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .filters input, .filters select {
            padding: 9px 12px;
            background: #1e1e1e;
            border: 1px solid #444;
            border-radius: 6px;
            color: #fff;
            font-size: 14px;
            flex: 1;
            min-width: 160px;
        }
        .filters button {
            padding: 9px 20px;
            background: #d4af37;
            color: #111;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }
        .filters a.clear {
            padding: 9px 16px;
            background: #2a2a2a;
            color: #aaa;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }
        .result-count {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 10px;
            color: #666;
            font-size: 13px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
            padding: 10px 20px 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .card {
            background: #1e1e1e;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #2a2a2a;
        }
        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }
        .card-body { padding: 12px; }
        .card-body .category { font-size: 12px; color: #888; margin-bottom: 4px; }
        .card-body h3 { font-size: 15px; color: #fff; margin-bottom: 6px; }
        .card-body .price { font-size: 18px; color: #d4af37; font-weight: bold; margin-bottom: 8px; }
        .card-body .desc { font-size: 12px; color: #999; margin-bottom: 12px; line-height: 1.4; }
        .whatsapp-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #25d366;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }
        .empty {
            text-align: center;
            color: #666;
            padding: 60px 20px;
            grid-column: 1 / -1;
        }
        .cart-btn {
            width: 100%;
            margin-top: 6px;
            padding: 9px;
            background: #2a2a2a;
            color: #d4af37;
            border: 1px solid #d4af37;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;      
        }

        .cart-float {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #25d366;
            color: #fff;
            padding: 12px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            font-size: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.4);
        }
    </style>
</head>
<body>

<header>
    <h1>✨ Jewelry Collection</h1>
    <p>Order directly via WhatsApp</p>
</header>

<div class="filters">
    <form method="GET">
        <input type="text" name="search" placeholder="Search by name..."
               value="<?php echo $search; ?>">

        <select name="category">
            <option value="">All Categories</option>
            <?php
            while ($cat = mysqli_fetch_assoc($cat_result)) {
                $selected = ($category == $cat['category']) ? "selected" : "";
                echo "<option value='" . $cat['category'] . "' $selected>" . $cat['category'] . "</option>";
            }
            ?>
        </select>

        <button type="submit">Search</button>
        <a href="index.php" class="clear">Clear</a>
    </form>
</div>

<div class="result-count">
    <?php
    if ($search != "" || $category != "") {
        echo "Showing $count result(s)";
        if ($search != "")   { echo " for \"$search\""; }
        if ($category != "") { echo " in $category"; }
    }
    ?>
</div>

<div class="grid">
<?php
$phone = "919327171633"; // CHANGE to real number

// Get cart from session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

if ($count == 0) {
    echo "<div class='empty'><h3>No products found.</h3><p>Try a different search or category.</p></div>";
} else {
    while ($row = mysqli_fetch_assoc($result)) {
        $in_cart = in_array($row['id'], array_keys($_SESSION['cart']));
        $wa_message = urlencode("Hi! I am interested in " . $row['name'] . " priced at Rs." . $row['price'] . ". Is it available?");
        $wa_link    = "https://wa.me/$phone?text=$wa_message";
        echo "
        <div class='card'>
            <img src='images/" . $row['image_file'] . "' alt='" . $row['name'] . "'>
            <div class='card-body'>
                <div class='category'>" . $row['category'] . "</div>
                <h3>" . $row['name'] . "</h3>
                <div class='price'>&#8377;" . $row['price'] . "</div>
                <div class='desc'>" . $row['description'] . "</div>
                <a href='" . $wa_link . "' class='whatsapp-btn' target='_blank'>Order Now</a>
                <form method='POST' action='cart.php'>
                    <input type='hidden' name='id'    value='" . $row['id'] . "'>
                    <input type='hidden' name='name'  value='" . $row['name'] . "'>
                    <input type='hidden' name='price' value='" . $row['price'] . "'>
                    <input type='hidden' name='action' value='add'>
                    <input type='hidden' name='redirect' value='" . $_SERVER['REQUEST_URI'] . "'>
                    <button type='submit' class='cart-btn'>" . ($in_cart ? "✓ Added" : "+ Add to Cart") . "</button>
                </form>
            </div>
        </div>";
    }
}
?>

</div>

<?php
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
if ($cart_count > 0) {
    echo "<a href='cart.php' class='cart-float'>🛒 Cart ($cart_count)</a>";
}
?>

</body>
</html>