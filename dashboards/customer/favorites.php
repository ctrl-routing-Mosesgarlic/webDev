<?php
/**
 * Customer Favorites Page
 * This page allows customers to view and manage their favorite products
 */

// Include session management
require_once '../../session.php';
require_once '../../connection.php';

// Check if user is logged in and has customer role
if (!isLoggedIn() || getSessionVar('role') !== 'customer') {
    // Redirect to login page if not logged in or not a customer
    header("Location: ../../templates/auth/sign-in.html");
    exit();
}

// Get user information from session
$user_id = getSessionVar('user_id');
$fullname = getSessionVar('fullname');
$email = getSessionVar('email');

// Handle remove from favorites
if (isset($_POST['remove_favorite']) && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    // In a real application, you would remove the product from favorites in the database
    // For demo purposes, we'll just show a success message
    $success_message = "Product removed from favorites!";
}

// Handle add to cart
if (isset($_POST['add_to_cart']) && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    // In a real application, you would add the product to the cart in the database
    // For demo purposes, we'll just show a success message
    $success_message = "Product added to cart!";
}

// Sample favorites data (in a real app, this would come from database)
$favorites = [
    [
        'id' => 1,
        'name' => 'Ergonomic Office Chair',
        'price' => 199.99,
        'image' => 'https://via.placeholder.com/150',
        'category' => 'Furniture',
        'rating' => 4.5,
        'in_stock' => true
    ],
    [
        'id' => 2,
        'name' => 'Wireless Keyboard and Mouse Combo',
        'price' => 79.99,
        'image' => 'https://via.placeholder.com/150',
        'category' => 'Electronics',
        'rating' => 4.2,
        'in_stock' => true
    ],
    [
        'id' => 3,
        'name' => 'LED Desk Lamp',
        'price' => 45.50,
        'image' => 'https://via.placeholder.com/150',
        'category' => 'Lighting',
        'rating' => 4.8,
        'in_stock' => true
    ],
    [
        'id' => 4,
        'name' => 'Notebook Set (5-pack)',
        'price' => 24.99,
        'image' => 'https://via.placeholder.com/150',
        'category' => 'Stationery',
        'rating' => 4.0,
        'in_stock' => false
    ],
    [
        'id' => 5,
        'name' => 'Wireless Charging Pad',
        'price' => 35.00,
        'image' => 'https://via.placeholder.com/150',
        'category' => 'Electronics',
        'rating' => 4.3,
        'in_stock' => true
    ]
];

// Filter favorites if needed
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';
if ($category_filter !== 'all') {
    $filtered_favorites = array_filter($favorites, function($product) use ($category_filter) {
        return $product['category'] === $category_filter;
    });
} else {
    $filtered_favorites = $favorites;
}

// Get unique categories for filter
$categories = array_unique(array_column($favorites, 'category'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - Customer Dashboard</title>
    <link rel="stylesheet" href="../../static/css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .filter-tabs {
            display: flex;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-tab {
            padding: 8px 15px;
            margin-right: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
            cursor: pointer;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            text-decoration: none;
            color: #344767;
        }
        .filter-tab.active {
            background-color: #4a6fdc;
            color: white;
            border-color: #4a6fdc;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .product-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .product-info {
            padding: 15px;
        }
        .product-name {
            margin: 0 0 10px;
            font-size: 1rem;
            font-weight: 600;
        }
        .product-price {
            font-weight: 600;
            color: #344767;
            margin-bottom: 10px;
        }
        .product-category {
            font-size: 0.8rem;
            color: #8898aa;
            margin-bottom: 10px;
        }
        .product-rating {
            margin-bottom: 10px;
            color: #f5b74f;
        }
        .product-stock {
            font-size: 0.8rem;
            margin-bottom: 15px;
        }
        .in-stock {
            color: #2dce89;
        }
        .out-of-stock {
            color: #f5365c;
        }
        .product-actions {
            display: flex;
            gap: 10px;
        }
        .action-btn {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .add-to-cart {
            background-color: #4a6fdc;
            color: white;
        }
        .add-to-cart:disabled {
            background-color: #c8c8c8;
            cursor: not-allowed;
        }
        .remove-favorite {
            background-color: #f8f9fa;
            color: #344767;
            border: 1px solid #e9ecef;
        }
        .action-btn i {
            margin-right: 5px;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .empty-state {
            text-align: center;
            padding: 40px 0;
        }
        .empty-state i {
            font-size: 3rem;
            color: #8898aa;
            margin-bottom: 20px;
        }
        .empty-state h3 {
            margin-bottom: 10px;
            color: #344767;
        }
        .empty-state p {
            color: #8898aa;
            margin-bottom: 20px;
        }
        .browse-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4a6fdc;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>W&SM System</h2>
                <p>Customer Portal</p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="../customer_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> My Orders</a></li>
                    <li class="active"><a href="favorites.php"><i class="fas fa-heart"></i> Favorites</a></li>
                    <li><a href="support.php"><i class="fas fa-comment-alt"></i> Support</a></li>
                    <li><a href="account.php"><i class="fas fa-user-circle"></i> My Account</a></li>
                    <li><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="content">
            <header class="content-header">
                <h1>My Favorites</h1>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($fullname); ?></span>
                    <a href="../../logout.php" class="logout-btn">Logout</a>
                </div>
            </header>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            
            <?php if (count($filtered_favorites) > 0): ?>
                <div class="filter-tabs">
                    <a href="favorites.php?category=all" class="filter-tab <?php echo $category_filter === 'all' ? 'active' : ''; ?>">All Categories</a>
                    <?php foreach ($categories as $category): ?>
                        <a href="favorites.php?category=<?php echo urlencode($category); ?>" class="filter-tab <?php echo $category_filter === $category ? 'active' : ''; ?>"><?php echo htmlspecialchars($category); ?></a>
                    <?php endforeach; ?>
                </div>
                
                <div class="products-grid">
                    <?php foreach ($filtered_favorites as $product): ?>
                        <div class="product-card">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                            <div class="product-info">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                                <div class="product-category"><?php echo htmlspecialchars($product['category']); ?></div>
                                <div class="product-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= floor($product['rating'])): ?>
                                            <i class="fas fa-star"></i>
                                        <?php elseif ($i - 0.5 <= $product['rating']): ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    <span>(<?php echo $product['rating']; ?>)</span>
                                </div>
                                <div class="product-stock <?php echo $product['in_stock'] ? 'in-stock' : 'out-of-stock'; ?>">
                                    <?php echo $product['in_stock'] ? 'In Stock' : 'Out of Stock'; ?>
                                </div>
                                <div class="product-actions">
                                    <form method="post" style="flex: 1;">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" name="add_to_cart" class="action-btn add-to-cart" <?php echo !$product['in_stock'] ? 'disabled' : ''; ?>>
                                            <i class="fas fa-shopping-cart"></i> Add to Cart
                                        </button>
                                    </form>
                                    <form method="post" style="flex: 1;">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" name="remove_favorite" class="action-btn remove-favorite">
                                            <i class="fas fa-trash"></i> Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="far fa-heart"></i>
                    <h3>No favorites yet</h3>
                    <p>You haven't added any products to your favorites list.</p>
                    <a href="#" class="browse-btn">Browse Products</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
<?php
// Close database connection
mysqli_close($conn);
?>
