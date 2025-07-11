<?php
header('Content-Type: application/json');

// Database connection configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "warehouse_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

// Get search parameters
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 999999;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Build base query
$sql = "SELECT * FROM products WHERE ";
$params = [];
$types = '';

// Add search term conditions
if ($searchTerm) {
    $sql .= "(name LIKE ? OR description LIKE ? OR category LIKE ?) ";
    $likeTerm = "%" . $searchTerm . "%";
    array_push($params, $likeTerm, $likeTerm, $likeTerm);
    $types .= 'sss';
}

// Add category filter
if ($category) {
    if ($searchTerm) $sql .= "AND ";
    $sql .= "category = ? ";
    array_push($params, $category);
    $types .= 's';
}

// Add price range filter
if ($searchTerm || $category) $sql .= "AND ";
$sql .= "price BETWEEN ? AND ? ";
array_push($params, $minPrice, $maxPrice);
$types .= 'dd';

// Add sorting
$validSortColumns = ['name', 'price', 'stock_quantity'];
$validOrders = ['ASC', 'DESC'];

$sort = in_array($sort, $validSortColumns) ? $sort : 'name';
$order = in_array($order, $validOrders) ? $order : 'ASC';
$sql .= "ORDER BY $sort $order";

// Prepare and execute query
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(['error' => "Error preparing statement: " . $conn->error]));
}

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Build HTML response
$html = '';
if ($result->num_rows > 0) {
    $html .= "<h2>Search Results" . ($searchTerm ? " for: " . htmlspecialchars($searchTerm) : "") . "</h2>";
    $html .= "<div class='search-results'>";
    
    while($row = $result->fetch_assoc()) {
        $html .= "<div class='product-card'>";
        $html .= "<div class='product-img-container'>";
        $html .= "<img src='" . htmlspecialchars($row['image_path']) . "' alt='" . htmlspecialchars($row['name']) . "' class='product-img'>";
        $html .= "</div>";
        $html .= "<div class='product-details'>";
        $html .= "<h3 class='product-title'>" . htmlspecialchars($row['name']) . "</h3>";
        $html .= "<p class='product-price'>$" . htmlspecialchars($row['price']) . "</p>";
        $html .= "<p class='product-stock'>In Stock (" . htmlspecialchars($row['stock_quantity']) . ")</p>";
        $html .= "<div class='product-actions'>";
        $html .= "<button class='btn-add-cart'>Add to Cart</button>";
        $html .= "<button class='btn-wishlist'><i class='far fa-heart'></i></button>";
        $html .= "</div></div></div>";
    }
    
    $html .= "</div>";
} else {
    $html = "<div class='no-results'><p>No products found matching your criteria.</p></div>";
}

$stmt->close();
$conn->close();

echo json_encode(['html' => $html]);
?>
