<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ErgoPro Office Chair | Product Details</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<!-- Header would be included here -->
<header class="site-header">
    <!-- Your header content -->
</header>

<main class="product-page">
    <div class="container">
        <div class="product-details">
            <div class="product-gallery">
                <div class="main-image">
                    <img src="images/ergopro-chair.jpg" alt="ErgoPro Office Chair">
                </div>
            </div>

            <div class="product-info">
                <h1>ErgoPro Office Chair</h1>
                <div class="price">$299.99</div>

                <div class="rating">
                    ★★★★★
                    <span class="reviews">(24 reviews)</span>
                </div>

                <p class="description">High-back ergonomic chair with lumbar support. Perfect for long work sessions with adjustable height and tilt features.</p>

                <form method="POST" class="add-to-cart-form">
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" value="1" min="1" required>
                    <button type="submit" name="add_to_cart" class="btn">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                </form>

                <div class="product-meta">
                    <div><i class="fas fa-truck"></i> Free shipping on orders over $100</div>
                    <div><i class="fas fa-undo"></i> 30-day return policy</div>
                </div>
            </div>
        </div>

        <section class="product-specs">
            <h2>Specifications</h2>
            <table>
                <tr><th>Material</th><td>Mesh & Aluminum</td></tr>
                <tr><th>Dimensions</th><td>27"W x 25"D x 45"H</td></tr>
                <tr><th>Weight Capacity</th><td>300 lbs</td></tr>
                <tr><th>Colors</th><td>Black, Gray, Blue</td></tr>
                <tr><th>Category</th><td>Office Chairs</td></tr>
                <tr><th>Stock</th><td>10 available</td></tr>
            </table>
        </section>

        <section class="related-products">
            <h2>You May Also Like</h2>
            <div class="products-grid">
                <div class="product-card">
                    <a href="product.html?id=2">
                        <img src="images/modern-dining-chair.jpg" alt="Modern Dining Chair">
                        <h3>Modern Dining Chair</h3>
                        <p class="price">$149.99</p>
                        <span class="btn">View Details</span>
                    </a>
                </div>
                <div class="product-card">
                    <a href="product.html?id=3">
                        <img src="images/cloud-lounge-chair.jpg" alt="Cloud Lounge Chair">
                        <h3>Cloud Lounge Chair</h3>
                        <p class="price">$399.99</p>
                        <span class="btn">View Details</span>
                    </a>
                </div>
                <div class="product-card">
                    <a href="product.html?id=5">
                        <img src="images/executive-leather-chair.jpg" alt="Executive Leather Chair">
                        <h3>Executive Leather Chair</h3>
                        <p class="price">$499.99</p>
                        <span class="btn">View Details</span>
                    </a>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Footer would be included here -->
<footer class="site-footer">
    <!-- Your footer content -->
</footer>
</body>
</html>