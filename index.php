<?php
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

<!-- Most Popular Product -->
<h2 class="section-title">Most Popular Product</h2>

<div class="popular-product-wrapper">
    <div class="popular-product-box">
        <img src="Images/Products/product1.jpg" alt="Sleek white headset and earpods set" class="popular-product-img">
    </div>

    <div class="popular-product-info">
        <h3>Sleek white headset + earpods set</h3>
        <p class="product-subtitle">WH-1000XM5 Industry Leading Noise-Cancelling Headphones</p>
    </div>
</div>

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