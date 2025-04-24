<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Comfort Chairs</title>
  <link rel="stylesheet" href="css/style.css" />
  <!-- Add Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
</head>
<body>
  <header>
    <div class="container">
      <div class="logo">
        <h1>Comfort Chairs</h1>
      </div>
      <nav>
        <ul>
          <li><a href="index.php" class="active">Home</a></li>
          <li><a href="products.php">Our Chairs</a></li>
          <li><a href="about.php">About Us</a></li>
          <li><a href="contact.php">Contact</a></li>
          <li class="auth-link">
            <a href="login.php"><i class="fas fa-user"></i> Login</a>
          </li>
          <li><a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart <span class="cart-count">2</span></a></li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- Rest of your existing content remains exactly the same -->
  <main>
    <section class="hero">
      <div class="container">
        <h2>Discover Perfect Seating for Every Space</h2>
        <p>From ergonomic office chairs to luxurious lounge chairs, we have what you need.</p>
        <a href="products.php" class="btn">Shop Now</a>
      </div>
    </section>

    <section class="featured-categories">
      <div class="container">
        <h2>Our Chair Categories</h2>
        <div class="categories-grid">
          <div class="category-card">
            <img src="image/product-4 (2).png" alt="Office Chairs" />
            <h3>Office Chairs</h3>
            <a href="products.php?category=1" class="btn">View Collection</a>
          </div>
          <div class="category-card">
            <img src="image/product-2.png" alt="Lounge Chairs" />
            <h3>Lounge Chairs</h3>
            <a href="products.php?category=2" class="btn">View Collection</a>
          </div>
          <div class="category-card">
            <img src="image/modern-dining-chair.jpg" alt="Dining Chairs" />
            <h3>Dining Chairs</h3>
            <a href="products.php?category=3" class="btn">View Collection</a>
          </div>
          <div class="category-card">
            <img src="image/ergopro-chair-1.jpg" alt="Gaming Chairs" />
            <h3>Gaming Chairs</h3>
            <a href="products.php?category=4" class="btn">View Collection</a>
          </div>
        </div>
      </div>
    </section>

    <section class="featured-products">
      <div class="container">
        <h2>Featured Chairs</h2>
        <div class="products-grid">
          <div class="product-card">
            <img src="image/ergopro-chair-1.jpg" alt="Chair 1" />
            <h3>Ergo Comfort Pro</h3>
            <p class="price">$199.99</p>
            <a href="product.php?id=1" class="btn">View Details</a>
          </div>
          <div class="product-card">
            <img src="image/luxury-chair.png" alt="Chair 2" />
            <h3>Luxury Lounge</h3>
            <p class="price">$299.99</p>
            <a href="product.php?id=2" class="btn">View Details</a>
          </div>
          <div class="product-card">
            <img src="image/classic-diner.png" alt="Chair 3" />
            <h3>Classic Diner</h3>
            <p class="price">$89.99</p>
            <a href="product.php?id=3" class="btn">View Details</a>
          </div>
          <div class="product-card">
            <img src="image/gamer.png" alt="Chair 4" />
            <h3>Gamer Xtreme</h3>
            <p class="price">$149.99</p>
            <a href="product.php?id=4" class="btn">View Details</a>
          </div>
        </div>
        <div class="center">
          <a href="products.php" class="btn">View All Products</a>
        </div>
      </div>
    </section>

    <section class="testimonials">
      <div class="container">
        <h2>What Our Customers Say</h2>
        <div class="testimonial-grid">
          <div class="testimonial">
            <p>"The most comfortable office chair I've ever used. Worth every penny!"</p>
            <p class="author">- Sarah J.</p>
          </div>
          <div class="testimonial">
            <p>"Great selection and excellent customer service. Will buy again!"</p>
            <p class="author">- Michael T.</p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer>
    <div class="container">
      <div class="footer-section">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="products.php">Products</a></li>
          <li><a href="about.php">About Us</a></li>
          <li><a href="contact.php">Contact</a></li>
        </ul>
      </div>
      <div class="footer-section">
        <h3>Customer Service</h3>
        <ul>
          <li><a href="shipping.html">Shipping Policy</a></li>
          <li><a href="returns.html">Returns & Refunds</a></li>
          <li><a href="faq.html">FAQ</a></li>
        </ul>
      </div>
      <div class="footer-section">
        <h3>Contact Us</h3>
        <p>Email: info@comfortchairs.com</p>
        <p>Phone: (123) 456-7890</p>
      </div>
      <div class="footer-section">
        <h3>Follow Us</h3>
        <div class="social-icons">
          <a href="#"><i class="fab fa-facebook"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-pinterest"></i></a>
        </div>
      </div>
    </div>
    <div class="copyright">
      <p>&copy; 2025 Comfort Chairs. All Rights Reserved.</p>
    </div>
  </footer>

  <script src="js/script.js"></script>
</body>
</html>