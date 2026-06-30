<?php
session_start();

$conn = new mysqli("localhost", "root", "", "scentaura");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch featured products and join with product details
$sql = "SELECT p.name, p.gender, p.price, p.image_path
        FROM featured_products fp
        JOIN products p ON fp.product_id = p.id";
$result = $conn->query($sql);

$featuredProducts = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $featuredProducts[] = $row;
    }
}

$newsletterStatus = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {

    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("INSERT IGNORE INTO newsletter_subscribers (email) VALUES (?)");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // Send confirmation (optional)
        $to = "scentaura25@gmail.com";
        $subject = "New Newsletter Subscriber";
        $message = "<html>
            <head>
            <title>New Subscriber</title>
            <style>
                body { font-family: Arial, sans-serif; color: #333; }
                .container { padding: 20px; background-color: #f9f9f9; border: 1px solid #ddd; }
                .header { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
                .content { font-size: 16px; }
            </style>
            </head>
            <body>
            <div class='container'>
                <div class='header'>📬 New Newsletter Subscriber</div>
                <div class='content'>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Subscribed At:</strong> " . date("Y-m-d H:i:s") . "</p>
                </div>
            </div>
            </body>
            </html>
            ";
            ;
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: ScentAura <noreply@Scentaura.com>" . "\r\n";

        if (mail($to, $subject, $message, $headers)) {
            mail($email, "Thanks for subscribing!", "You're now on our list!", $headers);
            $newsletterStatus = "success";
        } else {
            $newsletterStatus = "email_failed";
        }

        $stmt->close();
    } else {
        $newsletterStatus = "invalid_email";
    }

}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ScentAura</title>
    <link rel="icon" type="image/png" href="Components/Logo/logo.png">
    <link rel="stylesheet" href="Components/CSS/index.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap">
</head>
<body>
    <nav>
        <div class="logo">
            <a href="index.php"><img src="Components/Logo/logo.png" alt="ScentAura"></a>
        </div>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="blog.php">Blog</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li>
                <input type="text" placeholder="Search products...">
                <input type="button" value="Search">
            </li>
           <li class="profile-dropdown">
                <div class="dropdown">
                    <button class="user-icon">
                        <i class="fa-solid fa-circle-user"></i>
                    </button>
                    <div class="dropdown-menu">
                        <?php if (isset($_SESSION['username'])): ?>
                            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span><br>
                            <a href="profile.php">My Profile</a><br>
                            <a href="logout.php">Logout</a>
                        <?php else: ?>
                            <a href="login.php">LOGIN</a><br>
                            <a href="login.php#show-signup">SIGNUP</a>
                        <?php endif; ?>
                    </div>
                </div>
            </li>
            <li class="cart">
                <a href="cart.php">
                    <i class="fa-solid fa-cart-shopping"></i>
                    <span id="cart-count">0</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Hero Section -->
    <div class="image">
        <img src="Components/Images/1.jpg" alt="Hero Section Image">
    </div>
    
    <!-- Introduction Section -->
    <section class="intro">
        <div class="intro-content">
            <h1>Welcome to ScentAura</h1>
            <p>
                Discover the world of luxury fragrances. At ScentAura, we curate exquisite scents
                that define sophistication and elegance. Let your senses embark on a journey
                through our exclusive collection.
            </p>
            <a href="#featured-products"><button>Explore Now</button></a>
        </div>
    </section>
    
    <!-- Featured Products Section -->
    <section class="featured-products" id="featured-products">
        <h2>Featured Products</h2>
        <div class="card-wrapper">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="perfume-card">
                    <div class="image-container">
                        <img src="admin/<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <p><strong>Gender:</strong> <?= htmlspecialchars($product['gender']) ?></p>
                    <p><strong>Price:</strong> $<?= htmlspecialchars($product['price']) ?></p>
                    <a href="#"><button>Shop Now</button></a>
                </div>
            <?php endforeach; ?>

            <?php if (empty($featuredProducts)): ?>
                <p style="text-align:center;">No featured products found.</p>
            <?php endif; ?>
        </div>

    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <h2>What Our Customers Say</h2>
        <div class="slider-container">
          <div class="slider">
            <div class="slide">
              <p>"ScentWave Classic is my go-to fragrance. It's sophisticated yet subtle!"</p>
              <span>- Maria J.</span>
            </div>
            <div class="slide">
              <p>"I can't get enough of ScentWave Modern. The citrus notes are so refreshing."</p>
              <span>- Liam K.</span>
            </div>
            <div class="slide">
              <p>"ScentWave Noir is the perfect evening scent. It's bold and daring."</p>
              <span>- Sophie L.</span>
            </div>
            <div class="slide">
              <p>"ScentWave Fresh is my daily pick. Light, airy, and perfect for work."</p>
              <span>- Noah M.</span>
            </div>
            <div class="slide">
              <p>"ScentWave Blossom is a floral dream. I get compliments all the time!"</p>
              <span>- Olivia R.</span>
            </div>
          </div>
          <button class="prev" aria-label="Previous">❮</button>
          <button class="next" aria-label="Next">❯</button>
        </div>
    </section>     

    <!-- Newsletter Signup -->
    <section class="newsletter">
        <h3>Stay Updated</h3>
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Subscribe</button>
        </form>

        <?php if ($newsletterStatus == "success"): ?>
            <p style="color: green;">Thank you for subscribing!</p>
        <?php elseif ($newsletterStatus == "email_failed"): ?>
            <p style="color: red;">Failed to send email. Try again later.</p>
        <?php elseif ($newsletterStatus == "invalid_email"): ?>
            <p style="color: red;">Please enter a valid email.</p>
        <?php endif; ?>
    </section>

    <!-- Contact Form -->
    <section id="contact" class="contact-form">
        <h2>Contact Us</h2>
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
            <input type="text" placeholder="Your Name" required>
            <input type="email" placeholder="Your Email" required>
            <textarea placeholder="Your Message" required></textarea>
            <button type="submit">Send Message</button>
        </form>
    </section>

    <footer>
        <div class="footer-container">
            <div class="footer-section about">
                <h3>About ScentAura</h3>
                <p>
                    ScentAura brings you premium fragrances that blend luxury and sophistication.
                    Elevate your senses with our exclusive collection of perfumes.
                </p>
            </div>
            <div class="footer-section links">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="blog.php">Blog</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section social">
                <h3>Follow Us</h3>
                <div class="social-icons">
                    <a href="https://www.facebook.com/" target="_blank"><i class="fa-brands fa-facebook"></i></a>
                    <a href="https://www.instagram.com/" target="_blank"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://x.com/" target="_blank"><i class="fa-brands fa-twitter"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 <span>ScentAura</span>. All rights reserved.</p>
        </div>
    </footer>

     <!-- Back to Top Button -->
     <div id="backToTop" class="back-to-top" onclick="scrollToTop()">↑</div>

     <script src="Components/JS/index.js?v=<?php echo time(); ?>"></script>
</body>
</html>
