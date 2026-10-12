<?php
require_once 'config.php';

// Handle contact form
$contact_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($name && $email && $message) {
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        $contact_success = "Thank you! Your message has been sent.";
    }
}

// Fetch all dynamic data
$services     = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC")->fetchAll();
$projects     = $pdo->query("SELECT * FROM projects ORDER BY sort_order ASC")->fetchAll();
$testimonials = $pdo->query("SELECT * FROM testimonials ORDER BY sort_order ASC")->fetchAll();
$stats        = $pdo->query("SELECT * FROM stats ORDER BY sort_order ASC")->fetchAll();
$pricing      = $pdo->query("SELECT * FROM pricing ORDER BY sort_order ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaal Solutions (Pty) Ltd</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<!-- NAVBAR -->
<nav>
    <div class="logo">
        <img src="images/logo.jpg" alt="Vaal Solutions logo">
    </div>
    <ul class="nav-links">
        <li><a href="#home">Home</a></li>
        <li><a href="#services">Services</a></li>
        <li><a href="#about">About</a></li>
        <li><a href="#contact">Contact</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="admin.php">Admin</a></li>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
        <?php endif; ?>
    </ul>
    <div class="menu-btn"><i class="fa fa-bars"></i></div>
</nav>

<!-- HERO -->
<section class="hero" id="home">
    <video autoplay muted loop playsinline class="hero-video">
        <source src="videos/hero-video.mp4" type="video/mp4">
    </video>
    <div class="overlay"></div>
    <div class="hero-content">
        <h1>We Build <span class="typing"></span></h1>
        <p>Innovative IT solutions for businesses, homes and organizations.</p>
        <div class="hero-buttons">
            <a class="primary-btn" href="login.php">Get Started</a>
            <a class="secondary-btn" href="#services">Explore Services</a>
        </div>
    </div>
</section>

<!-- SERVICES -->
<section class="services" id="services">
    <h2>Our Services</h2>
    <div class="services-container">
        <?php foreach ($services as $s): ?>
        <div class="service-card">
            <i class="<?= htmlspecialchars($s['icon']) ?>"></i>
            <h3><?= htmlspecialchars($s['title']) ?></h3>
            <p><?= htmlspecialchars($s['description']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- PORTFOLIO -->
<section class="portfolio" id="portfolio">
    <h2>Our Projects</h2>
    <div class="portfolio-container">
        <?php foreach ($projects as $p): ?>
        <div class="portfolio-card">
            <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['title']) ?>">
            <div class="portfolio-overlay">
                <h3><?= htmlspecialchars($p['title']) ?></h3>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- PRICING -->
<section class="pricing">
    <h2>Pricing Plans</h2>
    <div class="pricing-container">
        <?php foreach ($pricing as $plan): ?>
        <div class="price-card <?= $plan['featured'] ? 'featured' : '' ?>">
            <h3><?= htmlspecialchars($plan['name']) ?></h3>
            <h1><?= htmlspecialchars($plan['price']) ?></h1>
            <?php foreach (explode("\n", $plan['features']) as $f): ?>
                <?php if (trim($f) !== ''): ?>
                <p><?= htmlspecialchars(trim($f)) ?></p>
                <?php endif; ?>
            <?php endforeach; ?>
            <button>Choose Plan</button>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ABOUT -->
<section class="about" id="about">
    <div class="about-text">
        <h2>About Us</h2>
        <p>VaalTech Solutions (Pty) Ltd delivers innovative IT solutions designed for businesses, homes and organizations.</p>
        <p>We specialize in software engineering, networking infrastructure, smart technology, cloud systems and digital transformation.</p>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="testimonials">
    <h2>What Clients Say</h2>
    <div class="testimonial-slider">
        <?php foreach ($testimonials as $i => $t): ?>
        <div class="testimonial <?= $i === 0 ? 'active' : '' ?>">
            <p>"<?= htmlspecialchars($t['quote']) ?>"</p>
            <h4>- <?= htmlspecialchars($t['author']) ?></h4>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- STATS -->
<section class="stats">
    <?php foreach ($stats as $s): ?>
    <div class="stat-box">
        <h2 class="counter" data-target="<?= (int)$s['target'] ?>">0</h2>
        <p><?= htmlspecialchars($s['label']) ?></p>
    </div>
    <?php endforeach; ?>
</section>

<!-- CONTACT -->
<section class="contact" id="contact">
    <h2>Contact Us</h2>
    <div class="contact-container">
        <div class="contact-box"><i class="fa-solid fa-phone"></i><p>+27 12 345 6789</p></div>
        <div class="contact-box"><i class="fa-solid fa-envelope"></i><p>info@vaaltech.co.za</p></div>
        <div class="contact-box"><i class="fa-solid fa-location-dot"></i><p>Gauteng, Vanderbijlpark, South Africa</p></div>
    </div>
</section>

<section class="contact-form">
    <h2>Send us a message</h2>
    <?php if ($contact_success): ?><p class="success"><?= htmlspecialchars($contact_success) ?></p><?php endif; ?>
    <form method="post">
        <input type="text" name="name" placeholder="Your Name" required>
        <input type="email" name="email" placeholder="Your Email" required>
        <textarea name="message" placeholder="Your Message" required></textarea>
        <button type="submit" name="contact_submit">Send</button>
    </form>
    <div class="social-links" aria-label="Social media and email">
        <a href="https://wa.me/27123456789" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
        <a href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fa-brands fa-facebook"></i></a>
        <a href="https://x.com/" target="_blank" rel="noopener noreferrer" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
        <a href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
        <a href="mailto:info@vaaltech.co.za" aria-label="Email"><i class="fa-solid fa-envelope"></i></a>
    </div>
</section>

<footer><p>© Vaal Solutions (Pty) Ltd. All Rights Reserved.</p></footer>
<script src="script.js"></script>
</body>
</html>