<?php
// Include database connection
require_once 'connection.php';

// Create database instance
$db = new database();
$conn = $db->getConnection();

// Get search term from GET request
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Start output buffering
ob_start();
?>

<!-- Main content that will be inserted into base.html -->
<main class="container">
    <div class="filter-panel">
        <form class="filter-form">
            <div class="filter-group">
                <h3>Filters</h3>
                <label for="category">Category</label>
                <select name="category" id="category">
                    <option value="">All Categories</option>
                    <option value="electronics">Electronics</option>
                    <option value="furniture">Furniture</option>
                    <option value="clothing">Clothing</option>
                    <option value="kitchen">Kitchen</option>
                    <option value="tools">Tools</option>
                    <option value="health">Health</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="min_price">Price Range</label>
                <div class="price-range">
                    <input type="number" name="min_price" id="min_price" placeholder="Min" min="0">
                    <input type="number" name="max_price" id="max_price" placeholder="Max" min="0">
                </div>
            </div>
            
            <div class="filter-group">
                <label for="sort">Sort By</label>
                <div class="sort-options">
                    <select name="sort" id="sort">
                        <option value="name">Name</option>
                        <option value="price">Price</option>
                        <option value="stock_quantity">Stock</option>
                    </select>
                    <select name="order" id="order">
                        <option value="ASC">Ascending</option>
                        <option value="DESC">Descending</option>
                    </select>
                </div>
            </div>
            
            <button type="button" class="apply-filters">Apply Filters</button>
        </form>
    </div>
    
    <div class="search-results-container">
<?php
// Prepare SQL query with LIKE for partial matching
$sql = "SELECT * FROM products WHERE 
        name LIKE ? OR 
        description LIKE ? OR 
        category LIKE ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

// Add wildcards to search term for LIKE matching
$likeTerm = "%" . $searchTerm . "%";
$stmt->bind_param("sss", $likeTerm, $likeTerm, $likeTerm);

// Execute query
$stmt->execute();
$result = $stmt->get_result();

// Display search results
if ($result->num_rows > 0) {
    echo "<h2>Search Results for: " . htmlspecialchars($searchTerm) . "</h2>";
    echo "<div class='search-results'>";
    
    while($row = $result->fetch_assoc()) {
        echo "<div class='product-card'>";
        echo "<div class='product-img-container'>";
        echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='" . htmlspecialchars($row['name']) . "' class='product-img'>";
        echo "</div>";
        echo "<div class='product-details'>";
        echo "<h3 class='product-title'>" . htmlspecialchars($row['name']) . "</h3>";
        echo "<p class='product-price'>$" . htmlspecialchars($row['price']) . "</p>";
        echo "<p class='product-stock'>In Stock (" . htmlspecialchars($row['stock_quantity']) . ")</p>";
        echo "<div class='product-actions'>";
        echo "<button class='btn-add-cart'>Add to Cart</button>";
        echo "<button class='btn-wishlist'><i class='far fa-heart'></i></button>";
        echo "</div></div></div>";
    }
    
    echo "</div>";
} else {
    echo "<div class='no-results'>";
    echo "<p>No products found matching your search.</p>";
    echo "</div>";
}

$stmt->close();
$conn->close();
?>
    </div>
</main>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the base template
include 'templates/base.html';
?>
