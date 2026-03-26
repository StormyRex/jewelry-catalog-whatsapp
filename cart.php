<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = array(); }

$phone = "919327171633";

// Add item
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $id    = (int)$_POST['id'];
    $name  = htmlspecialchars($_POST['name']);
    $price = (int)$_POST['price'];
    $_SESSION['cart'][$id] = array('name' => $name, 'price' => $price);
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'index.php';
    header("Location: $redirect");
    exit();
}

// Remove item
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][(int)$_GET['remove']]);
    header("Location: cart.php");
    exit();
}

// Clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = array();
    header("Location: cart.php");
    exit();
}

// Build WhatsApp message — check stock
$total       = 0;
$message     = "Hi! I am interested in ordering the following items:%0A%0A";
$i           = 1;
$has_instock = false;

foreach ($_SESSION['cart'] as $id => $item) {
    $check  = mysqli_query($conn, "SELECT in_stock FROM products WHERE id = $id");
    $status = mysqli_fetch_assoc($check);
    if ($status && $status['in_stock'] == 1) {
        $message .= $i . ". " . urlencode($item['name']) . " - Rs." . $item['price'] . "%0A";
        $total += $item['price'];
        $i++;
        $has_instock = true;
    }
}
$message .= "%0ATotal: Rs.$total%0A%0APlease confirm availability.";
$wa_link  = "https://wa.me/$phone?text=$message";

$cart_count = count($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart — TP Jewellery</title>
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
            border-radius: 50%; pointer-events: none; z-index: 0;
        }
        body::after {
            content: '';
            position: fixed;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(201,169,110,0.2) 0%, transparent 70%);
            bottom: -100px; left: -100px;
            border-radius: 50%; pointer-events: none; z-index: 0;
        }

        /* HEADER */
        header {
            position: sticky; top: 0; z-index: 100;
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(201,169,110,0.2);
            padding: 16px 30px;
            display: flex; align-items: center;
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

        /* PAGE */
        .page-wrap {
            position: relative; z-index: 1;
            max-width: 760px;
            margin: 0 auto;
            padding: 30px 24px 60px;
        }

        .page-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 32px; font-weight: 600;
            color: #2C2C2C;
            margin-bottom: 6px;
        }
        .back-link {
            display: inline-block;
            color: #C9A96E; font-size: 13px;
            text-decoration: none;
            border-bottom: 1px solid rgba(201,169,110,0.3);
            padding-bottom: 2px;
            margin-bottom: 24px;
        }
        .back-link:hover { border-color: #C9A96E; }

        /* CART ITEMS */
        .cart-glass {
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(201,169,110,0.08);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .cart-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
            border-bottom: 1px solid rgba(201,169,110,0.1);
            gap: 12px;
            flex-wrap: wrap;
        }
        .cart-item:last-child { border-bottom: none; }
        .item-name {
            font-family: 'Cormorant Garamond', serif;
            font-size: 18px; font-weight: 600;
            color: #2C2C2C; flex: 1;
        }
        .item-oos {
            font-size: 11px; color: #e74c3c;
            background: rgba(231,76,60,0.08);
            border: 1px solid rgba(231,76,60,0.2);
            padding: 2px 8px; border-radius: 20px;
            margin-left: 8px;
        }
        .item-price {
            font-size: 17px; font-weight: 700;
            color: #C9A96E; min-width: 80px;
            text-align: right;
        }
        .remove-btn {
            color: #e74c3c; text-decoration: none;
            font-size: 12px; font-weight: 700;
            letter-spacing: 0.5px;
            padding: 4px 10px;
            border: 1px solid rgba(231,76,60,0.25);
            border-radius: 20px;
            background: rgba(231,76,60,0.06);
            transition: background 0.2s;
        }
        .remove-btn:hover { background: rgba(231,76,60,0.15); }

        /* SUMMARY */
        .summary-glass {
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 20px;
            box-shadow: 0 8px 40px rgba(201,169,110,0.08);
            padding: 28px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(201,169,110,0.15);
        }
        .total-label {
            font-family: 'Cormorant Garamond', serif;
            font-size: 20px; color: #888;
        }
        .total-amount {
            font-size: 28px; font-weight: 700;
            color: #C9A96E;
        }
        .wa-btn {
            display: block; width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #25d366, #20b858);
            color: #fff; text-align: center;
            border-radius: 12px; text-decoration: none;
            font-weight: 700; font-size: 16px;
            box-shadow: 0 4px 20px rgba(37,211,102,0.25);
            margin-bottom: 12px;
            letter-spacing: 0.3px;
        }
        .clear-btn {
            display: block; width: 100%;
            padding: 12px;
            background: transparent;
            color: #bbb;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 12px;
            text-decoration: none;
            font-size: 13px; text-align: center;
            transition: background 0.2s;
        }
        .clear-btn:hover { background: rgba(0,0,0,0.04); }
        .oos-warning {
            text-align: center; padding: 15px;
            background: rgba(231,76,60,0.06);
            border: 1px solid rgba(231,76,60,0.15);
            border-radius: 12px; color: #e74c3c;
            font-size: 14px; font-weight: 700;
            margin-bottom: 12px;
        }

        /* EMPTY */
        .empty-wrap {
            text-align: center; padding: 80px 20px;
        }
        .empty-glass {
            display: inline-block;
            background: rgba(255,255,255,0.45);
            backdrop-filter: blur(14px);
            border: 1px solid rgba(255,255,255,0.75);
            border-radius: 20px;
            padding: 50px 60px;
        }
        .empty-glass h3 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 28px; color: #bbb; margin-bottom: 12px;
        }
        .empty-glass a {
            color: #C9A96E; text-decoration: none;
            font-size: 14px; font-weight: 700;
            border-bottom: 1px solid rgba(201,169,110,0.3);
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

        @media (max-width: 600px) {
            header { padding: 12px 16px; }
            .page-wrap { padding: 20px 16px 50px; }
            .cart-item { padding: 14px 16px; }
            .summary-glass { padding: 20px; }
            .empty-glass { padding: 40px 24px; }
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

<header>
    <div class="logo-wrap">
        <img src="images/TP_Jewellery.jpg" alt="TP Jewellery">
        <div class="logo-text">
            <h1>TP JEWELLERY</h1>
            <p>Elegance Redefined</p>
        </div>
    </div>
    <a href="index.php" style="color:#C9A96E; font-size:13px; text-decoration:none; border-bottom: 1px solid rgba(201,169,110,0.3); padding-bottom:2px;">← Back to Collection</a>
</header>

<div class="page-wrap">

    <?php if ($cart_count == 0): ?>
        <div class="empty-wrap">
            <div class="empty-glass">
                <h3>Your cart is empty.</h3>
                <a href="index.php">Browse Collection →</a>
            </div>
        </div>

    <?php else: ?>

        <h2 class="page-title">Your Cart</h2>
        <a href="index.php" class="back-link">← Continue Shopping</a>

        <div class="cart-glass">
            <?php foreach ($_SESSION['cart'] as $id => $item):
                $check  = mysqli_query($conn, "SELECT in_stock FROM products WHERE id = $id");
                $status = mysqli_fetch_assoc($check);
                $out    = ($status && $status['in_stock'] == 0);
            ?>
            <div class="cart-item">
                <div class="item-name">
                    <?php echo $item['name']; ?>
                    <?php if ($out) echo "<span class='item-oos'>Out of Stock</span>"; ?>
                </div>
                <div class="item-price">&#8377;<?php echo $item['price']; ?></div>
                <a href="cart.php?remove=<?php echo $id; ?>" class="remove-btn">Remove</a>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="summary-glass">
            <div class="total-row">
                <span class="total-label">Total</span>
                <span class="total-amount">&#8377;<?php echo $total; ?></span>
            </div>

            <?php if ($has_instock): ?>
                <a href="<?php echo $wa_link; ?>" class="wa-btn" target="_blank">&#128226; Order All on WhatsApp</a>
            <?php else: ?>
                <div class="oos-warning">All items in your cart are out of stock.</div>
            <?php endif; ?>

            <a href="cart.php?clear=1" class="clear-btn">Clear Cart</a>
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