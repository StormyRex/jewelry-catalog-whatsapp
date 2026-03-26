<?php
session_start();
require_once 'db_connect.php';

$id = (int)$_GET['id'];

$result  = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: index.php");
    exit();
}

$phone      = "919327171633";
$wa_message = urlencode("Hi! I am interested in " . $product['name'] . " priced at Rs." . $product['price'] . ". Is it available?");
$wa_link    = "https://wa.me/$phone?text=$wa_message";

// Handle add to cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}
$in_cart = isset($_SESSION['cart'][$id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> — Jewelry Catalog</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #111; color: #eee; min-height: 100vh; }

        header {
            background: #1a1a1a;
            border-bottom: 2px solid #d4af37;
            padding: 15px 20px;
            text-align: center;
        }
        header h1 { color: #d4af37; font-size: 22px; }

        .back {
            display: inline-block;
            margin: 20px;
            color: #888;
            text-decoration: none;
            font-size: 14px;
        }

        .product-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px 40px;
        }

        .product-img {
            flex: 1;
            min-width: 280px;
        }
        .product-img img {
            width: 100%;
            border-radius: 12px;
            object-fit: cover;
            max-height: 400px;
        }

        .product-info {
            flex: 1;
            min-width: 280px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .category {
            font-size: 13px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .product-info h2 {
            font-size: 26px;
            color: #fff;
        }
        .price {
            font-size: 28px;
            color: #d4af37;
            font-weight: bold;
        }
        .description {
            font-size: 14px;
            color: #999;
            line-height: 1.7;
        }

        .whatsapp-btn {
            display: block;
            padding: 14px;
            background: #25d366;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
        }
        .cart-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: #2a2a2a;
            color: #d4af37;
            border: 1px solid #d4af37;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: bold;
            text-align: center;
        }
        .out-of-stock {
            padding: 14px;
            background: #2a2a2a;
            color: #e74c3c;
            text-align: center;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
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
</header>

<a href="index.php" class="back">← Back to Catalog</a>

<div class="product-wrap">
    <div class="product-img">
        <div style="position:relative;">
            <img src="images/<?php echo $product['image_file']; ?>" alt="<?php echo $product['name']; ?>">
            <?php if ($product['in_stock'] == 0): ?>
                <div style="position:absolute; top:10px; left:10px; background:#e74c3c; color:#fff; padding:6px 14px; border-radius:4px; font-weight:bold;">Out of Stock</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="product-info">
        <div class="category"><?php echo $product['category']; ?></div>
        <h2><?php echo $product['name']; ?></h2>
        <div class="price">&#8377;<?php echo $product['price']; ?></div>
        <div class="description"><?php echo $product['description']; ?></div>

        <?php if ($product['in_stock'] == 1): ?>
            <a href="<?php echo $wa_link; ?>" class="whatsapp-btn" target="_blank">Order on WhatsApp</a>
            <form method="POST" action="cart.php">
                <input type="hidden" name="id"       value="<?php echo $product['id']; ?>">
                <input type="hidden" name="name"     value="<?php echo $product['name']; ?>">
                <input type="hidden" name="price"    value="<?php echo $product['price']; ?>">
                <input type="hidden" name="action"   value="add">
                <input type="hidden" name="redirect" value="product.php?id=<?php echo $product['id']; ?>">
                <button type="submit" class="cart-btn"><?php echo $in_cart ? "✓ Added to Cart" : "+ Add to Cart"; ?></button>
            </form>
        <?php else: ?>
            <div class="out-of-stock">Out of Stock</div>
        <?php endif; ?>
    </div>
</div>

<?php
$cart_count = count($_SESSION['cart']);
if ($cart_count > 0) {
    echo "<a href='cart.php' class='cart-float'>🛒 Cart ($cart_count)</a>";
}
?>

</body>
</html>