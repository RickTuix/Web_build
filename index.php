<?php
// Include database connection & define $base_url
require_once 'db.php';

// Page Configuration
$pageTitle = "Taan Tech - Home";

// Optional dynamic content array for Services
$services = [
    [
        "title" => "PC Building Service",
        "description" => "Have one of our staff build your PC with custom parts.",
        "image" => "Images/services/PCbuild.jpg"
    ],
    [
        "title" => "Device Repair Service",
        "description" => "Replacement and Repair",
        "image" => "Images/services/Repair.png"
    ],
    [
        "title" => "BIOS & Device Maintenance",
        "description" => "Updating BIOS, Drivers and checking for Malware.",
        "image" => "Images/services/bios.jpg"
    ]
];

// Optional dynamic content array for Testimonials
$testimonials = [
    [
        "stars" => "★★★★",
        "text" => "Great service every time! The staff is friendly, and always explain what they are doing. Trustworthy and reliable.",
        "author" => "John Smith",
        "location" => "Washington, D.C."
    ],
    [
        "stars" => "★★★★★",
        "text" => "Saved me a lot of trouble. They diagnosed an hardware issue that another shop couldn't fix. Excellent technical knowledge.",
        "author" => "Emily Johansson",
        "location" => "San Francisco, California"
    ],
    [
        "stars" => "★★★★★",
        "text" => "Taan Tech is my go-to place for maintenance. They always use quality parts and my laptop runs like a dream.",
        "author" => "Bonnie Red",
        "location" => "Los Angeles, California"
    ],
    [
        "stars" => "★★★★★",
        "text" => "Ordering parts through their website was seamless, and the delivery was quick. The parts were well-packaged and exactly what I needed.",
        "author" => "John Red",
        "location" => "Washington, D.C."
    ]
];

// Fetch the top 4 best-selling products
$popularStmt = $pdo->query("
    SELECT p.id, p.name, p.description, p.price,
           SUM(oi.quantity) AS total_sold,
           COALESCE((SELECT url FROM images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), '') AS image_url
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'completed' AND p.is_active = 1
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 4
");
$popularProducts = $popularStmt->fetchAll();

include 'header.php';
?>

  <!-- Hero Section -->
  <section class="hero">
    <div class="container">
      <div class="hero-content">
        <h1 class="hero-title">Trusted and reliable source of gadgets and tech accessories</h1>
        <div class="hero-actions">
          <a href="#" class="btn btn-primary">Shop Now</a>
        </div>
      </div>
    </div>
  </section>

  <div class="container">
    
    <!-- Featured Services Section -->
    <h2 class="section-title">Featured Services</h2>
    <div class="grid-3">
      <?php foreach ($services as $service): ?>
  <div class="card">
    <div>
      <img src="<?php echo htmlspecialchars($service['image']); ?>" alt="<?php echo htmlspecialchars($service['title']); ?>" class="card-icon">
      <h3 class="card-title"><?php echo htmlspecialchars($service['title']); ?></h3>
      <p class="card-text"><?php echo htmlspecialchars($service['description']); ?></p>
    </div>
    <a href="#" class="btn btn-dark">Book Service</a>
  </div>
<?php endforeach; ?>
    </div>

    <!-- Featured Promo Banner -->
    <div class="promo-banner">
      <div class="promo-content">
        <h2>Featured Promo</h2>
        <h3>BUNDLE & SAVE: GET 15% OFF WHEN YOU BUY PARTS + SERVICE.</h3>
        <ul>
          <li>✔ Includes genuine parts</li>
          <li>✔ Expert installation and services.</li>
          <li>✔ 15% total savings</li>
          <li>✔ Applicable to all makes & models</li>
        </ul>
      </div>
      <div>
        <a href="#" class="btn btn-primary">Get Started</a>
        <a href="#" class="btn btn-secondary promo-btn-secondary">VIEW ALL PROMOS</a>
      </div>
    </div>

<!-- Most Popular Products -->
<?php if (count($popularProducts) > 0): ?>
    <h2 class="section-title">Most Popular Products</h2>

    <div class="popular-section-wrapper">
        <!-- Product grid -->
        <div class="popular-products-grid">
            <?php foreach ($popularProducts as $product): ?>
                <div class="popular-product-card">
                    <a href="<?php echo $base_url; ?>/product.php?id=<?php echo $product['id']; ?>" class="popular-card-image-link">
                        <?php if ($product['image_url']): ?>
                            <img src="<?php echo $base_url . '/' . htmlspecialchars($product['image_url']); ?>"
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 class="popular-card-img">
                        <?php else: ?>
                            <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMDAiIGhlaWdodD0iMzAwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGFsaWdubWVudC1iYXNlbGluZT0ibWlkZGxlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTYiIGZpbGw9IiM2NjYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg=="
                                 alt="No image" class="popular-card-img">
                        <?php endif; ?>
                    </a>
                    <div class="popular-card-body">
                        <h3 class="popular-card-title">
                            <a href="<?php echo $base_url; ?>/product.php?id=<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h3>
                        <p class="popular-card-price">$<?php echo number_format($product['price'], 2); ?></p>
                        <p class="popular-card-sold"><?php echo (int)$product['total_sold']; ?> sold</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Side CTA -->
        <div class="popular-side-cta">
            <h3>Want more?</h3>
            <p>Browse our full catalog of tech gadgets and parts.</p>
            <a href="<?php echo $base_url; ?>/products.php" class="btn btn-primary">View More</a>
        </div>
    </div>
<?php endif; ?>

    <!-- CTA Section -->
    <div class="cta-banner">
      <h2>READY TO UPGRADE YOUR RIG?</h2>
      <p>Schedule your service appointment or order premium genuine parts online in just a few clicks.</p>
      <a href="#" class="btn btn-primary">Book Appointment</a>
      <a href="#" class="btn btn-secondary cta-btn-secondary">Explore Parts</a>
    </div>

    <!-- Why Choose Us / Testimonials Section -->
    <div class="testimonials">
      <h2 class="section-title">HERE'S THE REASON WHY YOU SHOULD CHOOSE US</h2>
      <div class="testimonials-grid">
        
        <?php foreach ($testimonials as $item): ?>
          <div class="testimonial-card">
            <div class="stars"><?php echo $item['stars']; ?></div>
            <p class="testimonial-text">"<?php echo htmlspecialchars($item['text']); ?>"</p>
            <div class="author-name"><?php echo htmlspecialchars($item['author']); ?></div>
            <div class="author-location"><?php echo htmlspecialchars($item['location']); ?></div>
          </div>
        <?php endforeach; ?>

      </div>
    </div>

  </div>

<?php include 'footer.php'; ?>