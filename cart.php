<?php
session_start();
include 'db.php'; // database connection

$user_id = $_SESSION['user_id']; // if user login system exists

$sql = "SELECT c.quantity, p.name, p.price, p.image 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
?>

<section class="cart-items">
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="cart-item">
            <div class="item-image">
                <img src="<?= $row['image']; ?>" alt="<?= $row['name']; ?>">
            </div>
            <div class="item-details">
                <h3><?= $row['name']; ?></h3>
                <div class="item-price">$<?= $row['price']; ?></div>
            </div>
            <div class="item-quantity">
                <form action="update_quantity.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= $row['product_id']; ?>">
                    <button type="submit" name="decrease">−</button>
                    <input type="number" name="quantity" value="<?= $row['quantity']; ?>" min="1">
                    <button type="submit" name="increase">+</button>
                </form>
            </div>
            <div class="item-actions">
                <form action="remove_item.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= $row['product_id']; ?>">
                    <button type="submit"><i class="fa-solid fa-trash"></i></button>
                </form>
            </div>
        </div>
        <?php $total += $row['price'] * $row['quantity']; ?>
    <?php endwhile; ?>
</section>

<section class="cart-summary">
    <h2>Order Summary</h2>
    <div class="summary-row">
        <span>Subtotal</span>
        <span>$<?= number_format($total, 2); ?></span>
    </div>
    <button class="checkout-btn">Proceed to Checkout</button>
</section>
