<?php
session_start();
require_once 'db_connect.php';

$search   = "";
$category = "";

if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = mysqli_real_escape_string($conn, trim($_GET['search']));
}
if (isset($_GET['category']) && $_GET['category'] != "") {
    $category = mysqli_real_escape_string($conn, $_GET['category']);
}

$where = array();
if ($search != "")   { $where[] = "name LIKE '%$search%'"; }
if ($category != "") { $where[] = "category = '$category'"; }

$query = "SELECT * FROM products";
if (count($where) > 0) {
    $query .= " WHERE " . implode(" AND ", $where);
}
$sort = "created_at DESC"; // default
if (isset($_GET['sort'])) {
    if ($_GET['sort'] == 'price_asc')  { $sort = "price ASC"; }
    if ($_GET['sort'] == 'price_desc') { $sort = "price DESC"; }
    if ($_GET['sort'] == 'newest')     { $sort = "created_at DESC"; }
}
$query .= " ORDER BY $sort";

$result     = mysqli_query($conn, $query);
$count      = mysqli_num_rows($result);
$cat_result = mysqli_query($conn, "SELECT DISTINCT category FROM products ORDER BY category");

if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = array(); }
$cart_count = count($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TP Jewellery</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── BACKGROUND with blobs ── */
        body {
            font-family: 'Lato', sans-serif;
            color: #2C2C2C;
            min-height: 100vh;
            background: #FDF6F0;
            position: relative;
            overflow-x: hidden;
        }

        /* Decorative background blobs */
        body::before {
            content: '';
            position: fixed;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(232,180,184,0.35) 0%, transparent 70%);
            top: -150px;
            right: -150px;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        body::after {
            content: '';
            position: fixed;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(201,169,110,0.2) 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        .blob-mid {
            position: fixed;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(232,180,184,0.18) 0%, transparent 70%);
            top: 40%;
            left: 30%;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        /* All content above blobs */
        header, .hero, .filters-wrap,
        .result-info, .grid, footer { position: relative; z-index: 1; }

        /* ── GLASSMORPHISM base ── */
        .glass {
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.75);
        }

        /* ── HEADER ── */
        header {
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(201,169,110,0.2);
            padding: 16px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 24px rgba(201,169,110,0.08);
        }
        .logo-wrap {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .logo-wrap img {
            height: 52px;
            width: auto;
            border-radius: 6px;
        }
        .logo-text h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px;
            font-weight: 600;
            color: #C9A96E;
            letter-spacing: 3px;
        }
        .logo-text p {
            font-size: 10px;
            color: #E8B4B8;
            letter-spacing: 4px;
            text-transform: uppercase;
        }
        .cart-link {
            background: rgba(201,169,110,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(201,169,110,0.4);
            color: #C9A96E;
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: background 0.2s;
        }
        .cart-link:hover {
            background: rgba(201,169,110,0.3);
        }

        /* ── HERO ── */
        .hero {
            text-align: center;
            padding: 64px 20px 52px;
        }
        .hero-glass {
            display: inline-block;
            background: rgba(255,255,255,0.45);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 24px;
            padding: 40px 60px;
            box-shadow: 0 8px 40px rgba(201,169,110,0.1);
        }
        .hero h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 42px;
            font-weight: 600;
            color: #2C2C2C;
            margin-bottom: 10px;
        }
        .hero h2 em {
            color: #C9A96E;
            font-style: italic;
        }
        .hero p {
            font-size: 13px;
            color: #aaa;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        .hero-line {
            width: 50px;
            height: 2px;
            background: linear-gradient(to right, #C9A96E, #E8B4B8);
            margin: 14px auto;
            border-radius: 2px;
        }

        /* ── FILTERS ── */
        .filters-wrap {
            padding: 0 30px 20px;
        }
        .filter-glass {
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.75);
            border-radius: 60px;
            padding: 12px 20px;
            box-shadow: 0 4px 20px rgba(201,169,110,0.08);
            max-width: 1200px;
            margin: 0 auto;
        }
        .filter-glass form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .filter-glass input,
        .filter-glass select {
            padding: 10px 16px;
            border: 1px solid rgba(201,169,110,0.25);
            border-radius: 30px;
            font-size: 14px;
            color: #2C2C2C;
            background: rgba(255,255,255,0.6);
            outline: none;
            flex: 1;
            min-width: 160px;
            font-family: 'Lato', sans-serif;
        }
        .filter-glass input:focus,
        .filter-glass select:focus {
            border-color: #C9A96E;
            background: rgba(255,255,255,0.85);
        }
        .filter-glass button {
            padding: 10px 26px;
            background: linear-gradient(135deg, #C9A96E, #d4b896);
            color: #fff;
            border: none;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(201,169,110,0.3);
        }
        .filter-glass a.clear {
            padding: 10px 18px;
            color: #bbb;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 30px;
            text-decoration: none;
            font-size: 13px;
            background: rgba(255,255,255,0.4);
        }
        .result-info {
            max-width: 1200px;
            margin: 12px auto 0;
            padding: 0 30px;
            font-size: 13px;
            color: #bbb;
        }

        /* ── GRID ── */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 24px;
            padding: 24px 30px 50px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ── CARD ── */
        .card {
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(201,169,110,0.07);
            transition: box-shadow 0.25s, transform 0.25s;
        }
        .card:hover {
            box-shadow: 0 12px 40px rgba(201,169,110,0.18);
            transform: translateY(-5px);
        }
        .card-img-wrap {
            position: relative;
            overflow: hidden;
        }
        .card-img-wrap a img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
            transition: transform 0.35s;
        }
        .card:hover .card-img-wrap img {
            transform: scale(1.06);
        }
        .oos-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: rgba(231,76,60,0.85);
            backdrop-filter: blur(6px);
            color: #fff;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .card-body {
            padding: 16px 18px 18px;
        }
        .card-body .cat {
            font-size: 10px;
            color: #E8B4B8;
            text-transform: uppercase;
            letter-spacing: 2.5px;
            margin-bottom: 5px;
        }
        .card-body h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 19px;
            font-weight: 600;
            color: #2C2C2C;
            margin-bottom: 4px;
        }
        .card-body h3 a { color: #2C2C2C; text-decoration: none; }
        .card-body h3 a:hover { color: #C9A96E; }
        .card-body .price {
            font-size: 20px;
            font-weight: 700;
            color: #C9A96E;
            margin-bottom: 8px;
        }
        .card-body .desc {
            font-size: 12px;
            color: #aaa;
            line-height: 1.6;
            margin-bottom: 14px;
        }
        .whatsapp-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #25d366, #20b858);
            color: #fff;
            text-align: center;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 8px;
            box-shadow: 0 4px 12px rgba(37,211,102,0.25);
        }
        .cart-btn {
            width: 100%;
            padding: 9px;
            background: rgba(201,169,110,0.08);
            color: #C9A96E;
            border: 1px solid rgba(201,169,110,0.4);
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 700;
            font-family: 'Lato', sans-serif;
            transition: all 0.2s;
        }
        .cart-btn:hover {
            background: #C9A96E;
            color: #fff;
        }
        .oos-label {
            width: 100%;
            padding: 10px;
            background: rgba(231,76,60,0.08);
            color: #e74c3c;
            text-align: center;
            border-radius: 10px;
            font-weight: 700;
            font-size: 13px;
            border: 1px solid rgba(231,76,60,0.2);
        }

        /* ── EMPTY ── */
        .empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 80px 20px;
        }
        .empty-glass {
            display: inline-block;
            background: rgba(255,255,255,0.45);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.75);
            border-radius: 20px;
            padding: 50px 60px;
        }
        .empty h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            color: #bbb;
            margin-bottom: 8px;
        }
        .empty p { font-size: 13px; color: #ccc; }

        /* ── FOOTER ── */
        footer {
            text-align: center;
            padding: 28px;
            font-size: 12px;
            color: #ccc;
            border-top: 1px solid rgba(201,169,110,0.15);
            background: rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
            letter-spacing: 1px;
        }

        .cart-float:hover { background: rgba(201,169,110,0.35); }

        /* ── MOBILE ── */
        @media (max-width: 600px) {
            header { padding: 12px 16px; }
            .hero { padding: 40px 16px; }
            .hero-glass { padding: 28px 24px; }
            .hero h2 { font-size: 30px; }
            .filters-wrap { padding: 0 16px 16px; }
            .filter-glass { border-radius: 20px; padding: 12px 16px; }
            .grid { padding: 16px; gap: 16px; }
        }
        .wa-contact {
            position: fixed;
            bottom: 24px;
            left: 24px;
            background: linear-gradient(135deg, #25d366, #20b858);
            color: #fff;
            padding: 13px 20px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            box-shadow: 0 4px 20px rgba(37,211,102,0.35);
            z-index: 200;
            letter-spacing: 0.3px;
        }
    </style>
</head>
<body>

<div class="blob-mid"></div>

<!-- HEADER -->
<header>
    <div class="logo-wrap">
        <img src="images/TP_Jewellery.jpg" alt="TP Jewellery">
        <div class="logo-text">
            <h1>TP JEWELLERY</h1>
            <p>Elegance Redefined</p>
        </div>
    </div>
    <?php if ($cart_count > 0): ?>
        <a href="cart.php" class="cart-link">🛒 Cart (<?php echo $cart_count; ?>)</a>
    <?php endif; ?>
</header>

<!-- HERO -->
<div class="hero">
    <div class="hero-glass">
        <h2>Our <em>Collection</em></h2>
        <div class="hero-line"></div>
        <p>Handpicked imitation jewellery for every occasion</p>
    </div>
</div>

<!-- FILTERS -->
<div class="filters-wrap">
    <div class="filter-glass">
        <form method="GET">
            <input type="text" name="search" placeholder="Search jewellery..."
                   value="<?php echo $search; ?>">
            <select name="category">
                <option value="">All Categories</option>
                <?php while ($cat = mysqli_fetch_assoc($cat_result)):
                    $selected = ($category == $cat['category']) ? "selected" : "";
                    $cnt = mysqli_fetch_assoc(mysqli_query($conn,
                        "SELECT COUNT(*) as t FROM products WHERE category='" . $cat['category'] . "' AND in_stock=1"));
                    echo "<option value='" . $cat['category'] . "' $selected>" . $cat['category'] . " (" . $cnt['t'] . ")</option>";
                endwhile; ?>
            </select>
            <select name="sort">
                <option value="newest"     <?php if(isset($_GET['sort']) && $_GET['sort']=='newest')     echo 'selected'; ?>>Newest First</option>
                <option value="price_asc"  <?php if(isset($_GET['sort']) && $_GET['sort']=='price_asc')  echo 'selected'; ?>>Price: Low to High</option>
                <option value="price_desc" <?php if(isset($_GET['sort']) && $_GET['sort']=='price_desc') echo 'selected'; ?>>Price: High to Low</option>
            </select>
            <button type="submit">Search</button>
            <a href="index.php" class="clear">Clear</a>
        </form>
    </div>
</div>

<?php if ($search != "" || $category != ""): ?>
<div class="result-info">
    Showing <?php echo $count; ?> result(s)
    <?php if ($search != "") echo " for \"$search\""; ?>
    <?php if ($category != "") echo " in $category"; ?>
</div>
<?php endif; ?>

<!-- GRID -->
<div class="grid">
<?php
$phone = "919327171633";

if ($count == 0) {
    echo "<div class='empty'><div class='empty-glass'><h3>No products found.</h3><p>Try a different search or category.</p></div></div>";
} else {
    while ($row = mysqli_fetch_assoc($result)) {
        $in_cart    = array_key_exists($row['id'], $_SESSION['cart']);
        $wa_message = urlencode("Hi! I am interested in " . $row['name'] . " priced at Rs." . $row['price'] . ". Is it available?");
        $wa_link    = "https://wa.me/$phone?text=$wa_message";

        echo "
        <div class='card'>
            <div class='card-img-wrap'>
                <a href='product.php?id=" . $row['id'] . "'>
                    <img src='images/" . $row['image_file'] . "' alt='" . $row['name'] . "'>
                </a>
                " . ($row['in_stock'] == 0 ? "<div class='oos-badge'>Out of Stock</div>" : "") . "
            </div>
            <div class='card-body'>
                <div class='cat'>" . $row['category'] . "</div>
                <h3><a href='product.php?id=" . $row['id'] . "'>" . $row['name'] . "</a></h3>
                <div class='price'>&#8377;" . $row['price'] . "</div>
                <div class='desc'>" . $row['description'] . "</div>";

        if ($row['in_stock'] == 1) {
            echo "
                <a href='" . $wa_link . "' class='whatsapp-btn' target='_blank'>&#128226; Order on WhatsApp</a>
                <form method='POST' action='cart.php'>
                    <input type='hidden' name='id'       value='" . $row['id'] . "'>
                    <input type='hidden' name='name'     value='" . $row['name'] . "'>
                    <input type='hidden' name='price'    value='" . $row['price'] . "'>
                    <input type='hidden' name='action'   value='add'>
                    <input type='hidden' name='redirect' value='" . $_SERVER['REQUEST_URI'] . "'>
                    <button type='submit' class='cart-btn'>" . ($in_cart ? "✓ Added to Cart" : "+ Add to Cart") . "</button>
                </form>";
        } else {
            echo "<div class='oos-label'>Out of Stock</div>";
        }

        echo "
            </div>
        </div>";
    }
}
?>
</div>

<footer>
    &copy; <?php echo date('Y'); ?> &nbsp;TP Jewellery &nbsp;|&nbsp; All Rights Reserved
</footer>

<a href="https://wa.me/919327171633?text=Hi! I would like to know more about your jewellery collection." 
   class="wa-contact" target="_blank">
   💬 Chat with us
</a>

</body>
</html>