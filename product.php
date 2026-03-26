<?php
session_start();
require_once 'db_connect.php';

$id = (int)$_GET['id'];
$result  = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
$product = mysqli_fetch_assoc($result);

if (!$product) {
    header("Location: 404.php");
    exit();
}

$phone      = "919327171633";
$wa_message = urlencode("Hi! I am interested in " . $product['name'] . " priced at Rs." . $product['price'] . ". Is it available?");
$wa_link    = "https://wa.me/$phone?text=$wa_message";

if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = array(); }
$in_cart    = isset($_SESSION['cart'][$id]);
$cart_count = count($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> — TP Jewellery</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Lato', sans-serif;
            color: #2C2C2C;
            min-height: 100vh;
            background: #FDF6F0;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(232,180,184,0.35) 0%, transparent 70%);
            top: -150px; right: -150px;
            border-radius: 50%;
            pointer-events: none; z-index: 0;
        }
        body::after {
            content: '';
            position: fixed;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(201,169,110,0.2) 0%, transparent 70%);
            bottom: -100px; left: -100px;
            border-radius: 50%;
            pointer-events: none; z-index: 0;
        }

        header {
            position: sticky; top: 0; z-index: 100;
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(201,169,110,0.2);
            padding: 16px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap; gap: 12px;
            box-shadow: 0 4px 24px rgba(201,169,110,0.08);
        }
        .logo-wrap { display: flex; align-items: center; gap: 14px; }
        .logo-wrap img { height: 52px; border-radius: 6px; }
        .logo-text h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 22px; font-weight: 600;
            color: #C9A96E; letter-spacing: 3px;
        }
        .logo-text p { font-size: 10px; color: #E8B4B8; letter-spacing: 4px; text-transform: uppercase; }

        .cart-link {
            background: rgba(201,169,110,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(201,169,110,0.4);
            color: #C9A96E;
            padding: 10px 20px; border-radius: 30px;
            text-decoration: none; font-size: 13px; font-weight: 700;
        }

        .page-wrap {
            position: relative; z-index: 1;
            max-width: 960px;
            margin: 0 auto;
            padding: 30px 24px 60px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 24px;
            color: #C9A96E;
            text-decoration: none;
            font-size: 13px;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(201,169,110,0.3);
            padding-bottom: 2px;
        }
        .back-link:hover { border-color: #C9A96E; }

        /* Glass product panel */
        .product-glass {
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 24px;
            box-shadow: 0 8px 40px rgba(201,169,110,0.1);
            display: flex;
            flex-wrap: wrap;
            overflow: hidden;
        }

        /* Image side */
        .product-img {
            flex: 1;
            min-width: 280px;
            position: relative;
        }
        .product-img img {
            width: 100%;
            height: 100%;
            min-height: 380px;
            object-fit: cover;
            display: block;
        }
        .oos-badge {
            position: absolute; top: 16px; left: 16px;
            background: rgba(231,76,60,0.85);
            backdrop-filter: blur(6px);
            color: #fff; padding: 6px 14px;
            border-radius: 20px; font-size: 12px; font-weight: 700;
        }

        /* Info side */
        .product-info {
            flex: 1;
            min-width: 280px;
            padding: 36px 32px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            background: rgba(255,255,255,0.3);
        }
        .cat-label {
            font-size: 11px;
            color: #E8B4B8;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        .product-info h2 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 34px; font-weight: 600;
            color: #2C2C2C; line-height: 1.2;
        }
        .divider {
            width: 40px; height: 2px;
            background: linear-gradient(to right, #C9A96E, #E8B4B8);
            border-radius: 2px;
        }
        .price {
            font-size: 30px; font-weight: 700;
            color: #C9A96E;
        }
        .description {
            font-size: 14px; color: #999;
            line-height: 1.8;
            flex: 1;
        }

        .whatsapp-btn {
            display: block;
            padding: 14px;
            background: linear-gradient(135deg, #25d366, #20b858);
            color: #fff; text-align: center;
            border-radius: 12px; text-decoration: none;
            font-weight: 700; font-size: 15px;
            box-shadow: 0 4px 16px rgba(37,211,102,0.25);
            margin-bottom: 10px;
        }
        .cart-btn {
            display: block; width: 100%;
            padding: 13px;
            background: rgba(201,169,110,0.08);
            color: #C9A96E;
            border: 1px solid rgba(201,169,110,0.4);
            border-radius: 12px;
            cursor: pointer; font-size: 14px; font-weight: 700;
            font-family: 'Lato', sans-serif;
            transition: all 0.2s; text-align: center;
        }
        .cart-btn:hover { background: #C9A96E; color: #fff; }
        .oos-label {
            padding: 14px;
            background: rgba(231,76,60,0.08);
            color: #e74c3c; text-align: center;
            border-radius: 12px; font-weight: 700;
            border: 1px solid rgba(231,76,60,0.2);
        }

        footer {
            position: relative; z-index: 1;
            text-align: center; padding: 24px;
            font-size: 12px; color: #ccc;
            letter-spacing: 1px;
            border-top: 1px solid rgba(201,169,110,0.15);
            background: rgba(255,255,255,0.3);
            backdrop-filter: blur(10px);
        }

        @media (max-width: 640px) {
            .product-img img { min-height: 260px; }
            .product-info { padding: 24px 20px; }
            .product-info h2 { font-size: 26px; }
            header { padding: 12px 16px; }
            .page-wrap { padding: 20px 16px 50px; }
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
        /* RELATED PRODUCTS */
            .related-wrap {
                margin-top: 40px;
        }
        .related-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .related-header h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px; color: #2C2C2C;
        }
        .rel-line {
            width: 40px; height: 2px;
            background: linear-gradient(to right, #C9A96E, #E8B4B8);
            margin: 10px auto 0; border-radius: 2px;
        }
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .rel-card {
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 14px; overflow: hidden;
            box-shadow: 0 4px 20px rgba(201,169,110,0.07);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .rel-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(201,169,110,0.15);
        }   
        .rel-card img {
            width: 100%; height: 160px;
            object-fit: cover; display: block;
            transition: transform 0.3s;
        }
        .rel-card:hover img { transform: scale(1.05); }
        .rel-body { padding: 12px 14px 14px; }
        .rel-cat { font-size: 10px; color: #E8B4B8; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 4px; }
        .rel-body h4 { font-family: 'Cormorant Garamond', serif; font-size: 16px; margin-bottom: 4px; }
        .rel-body h4 a { color: #2C2C2C; text-decoration: none; }
        .rel-body h4 a:hover { color: #C9A96E; }    
        .rel-price { font-size: 16px; font-weight: 700; color: #C9A96E; margin-bottom: 10px; }
        .rel-wa {
            display: block; width: 100%; padding: 8px;
            background: linear-gradient(135deg, #25d366, #20b858);
            color: #fff; text-align: center; border-radius: 8px;
            text-decoration: none; font-weight: 700; font-size: 12px;
        }
    </style>
</head>
<body>

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

<div class="page-wrap">
    <a href="index.php" class="back-link">← Back to Collection</a>

    <div class="product-glass">
        <div class="product-img">
            <img src="images/<?php echo $product['image_file']; ?>" alt="<?php echo $product['name']; ?>">
            <?php if ($product['in_stock'] == 0): ?>
                <div class="oos-badge">Out of Stock</div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <div class="cat-label"><?php echo $product['category']; ?></div>
            <h2><?php echo $product['name']; ?></h2>
            <div class="divider"></div>
            <div class="price">&#8377;<?php echo $product['price']; ?></div>
            <div class="description"><?php echo $product['description']; ?></div>

            <?php if ($product['in_stock'] == 1): ?>
                <a href="<?php echo $wa_link; ?>" class="whatsapp-btn" target="_blank">&#128226; Order on WhatsApp</a>
                <form method="POST" action="cart.php">
                    <input type="hidden" name="id"       value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="name"     value="<?php echo $product['name']; ?>">
                    <input type="hidden" name="price"    value="<?php echo $product['price']; ?>">
                    <input type="hidden" name="action"   value="add">
                    <input type="hidden" name="redirect" value="product.php?id=<?php echo $product['id']; ?>">
                    <button type="submit" class="cart-btn">
                        <?php echo $in_cart ? "✓ Added to Cart" : "+ Add to Cart"; ?>
                    </button>
                </form>
            <?php else: ?>
                <div class="oos-label">Out of Stock</div>
            <?php endif; ?>
        </div>
    </div>
            <?php
                // Fetch related products — same category, exclude current
            $rel_result = mysqli_query($conn,
                "SELECT * FROM products 
                WHERE category = '" . mysqli_real_escape_string($conn, $product['category']) . "' 
                AND id != $id 
                AND in_stock = 1 
                ORDER BY created_at DESC 
                LIMIT 4"
            );
if (mysqli_num_rows($rel_result) > 0):
?>
<div class="related-wrap">
    <div class="related-header">
        <h3>You May Also Like</h3>
        <div class="rel-line"></div>
    </div>
    <div class="related-grid">
    <?php while ($rel = mysqli_fetch_assoc($rel_result)):
        $rel_wa = urlencode("Hi! I am interested in " . $rel['name'] . " priced at Rs." . $rel['price'] . ". Is it available?");
    ?>
        <div class="rel-card">
            <a href="product.php?id=<?php echo $rel['id']; ?>">
                <img src="images/<?php echo $rel['image_file']; ?>" alt="<?php echo $rel['name']; ?>">
            </a>
            <div class="rel-body">
                <div class="rel-cat"><?php echo $rel['category']; ?></div>
                <h4><a href="product.php?id=<?php echo $rel['id']; ?>"><?php echo $rel['name']; ?></a></h4>
                <div class="rel-price">&#8377;<?php echo $rel['price']; ?></div>
                <a href="https://wa.me/919327171633?text=<?php echo $rel_wa; ?>" 
                   class="rel-wa" target="_blank">Order on WhatsApp</a>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>
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