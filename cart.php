<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$phone = "919327171633"; // CHANGE to real number

// Add item
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $id    = (int)$_POST['id'];
    $name  = htmlspecialchars($_POST['name']);
    $price = (int)$_POST['price'];

    $_SESSION['cart'][$id] = array('name' => $name, 'price' => $price);

    // Redirect back to wherever user was
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'index.php';
    header("Location: $redirect");
    exit();
}

// Remove item
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php");
    exit();
}

// Clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = array();
    header("Location: cart.php");
    exit();
}

// Build WhatsApp message from cart
$total        = 0;
$message      = "Hi! I am interested in ordering the following items:%0A%0A";
$i            = 1;
$has_instock  = false;

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #111; color: #eee; min-height: 100vh; padding: 20px; }

        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 10px; }
        .top-bar h2 { color: #d4af37; font-size: 22px; }
        .top-bar a { color: #aaa; text-decoration: none; font-size: 14px; }

        .cart-table { width: 100%; max-width: 700px; margin: 0 auto; border-collapse: collapse; background: #1e1e1e; border-radius: 10px; overflow: hidden; }
        th { background: #2a2a2a; padding: 12px; text-align: left; color: #d4af37; font-size: 14px; }
        td { padding: 12px; border-bottom: 1px solid #2a2a2a; font-size: 14px; }
        tr:last-child td { border-bottom: none; }

        .remove-btn { color: #e74c3c; text-decoration: none; font-size: 13px; }

        .summary { max-width: 700px; margin: 20px auto 0; background: #1e1e1e; border-radius: 10px; padding: 20px; }
        .summary .total { font-size: 20px; color: #d4af37; font-weight: bold; margin-bottom: 15px; }

        .wa-btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: #25d366;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .clear-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #2a2a2a;
            color: #aaa;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }
        .empty { text-align: center; padding: 60px 20px; color: #666; }
        .empty a { color: #d4af37; text-decoration: none; }
    </style>
</head>
<body>

<div class="top-bar" style="max-width:700px; margin:0 auto 25px;">
    <h2>🛒 Your Cart</h2>
    <a href="index.php">← Back to Catalog</a>
</div>

<?php if (count($_SESSION['cart']) == 0): ?>
    <div class="empty">
        <h3>Your cart is empty.</h3>
        <p style="margin-top:10px;"><a href="index.php">Browse products →</a></p>
    </div>

<?php else: ?>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($_SESSION['cart'] as $id => $item): 
                $check  = mysqli_query($conn, "SELECT in_stock FROM products WHERE id = $id");
                $status = mysqli_fetch_assoc($check);
                $out    = ($status && $status['in_stock'] == 0);
        ?>
                    <tr>
                        <td><?php echo $item['name']; ?> <?php if($out) echo "<span style='color:#e74c3c; font-size:12px;'>(Out of Stock)</span>"; ?></td>
                        <td>&#8377;<?php echo $item['price']; ?></td>
                        <td><a href="cart.php?remove=<?php echo $id; ?>" class="remove-btn">Remove</a></td>
                    </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="summary">
        <div class="total">Total: &#8377;<?php echo $total; ?></div>
        <?php if ($has_instock): ?>
            <a href="<?php echo $wa_link; ?>" class="wa-btn" target="_blank">Order All on WhatsApp</a>
        <?php else: ?>
            <p style="color:#e74c3c; text-align:center; margin-bottom:10px;">All items in your cart are out of stock.</p>
        <?php endif; ?>
        <a href="cart.php?clear=1" class="clear-btn">Clear Cart</a>
    </div>
<?php endif; ?>

</body>
</html>