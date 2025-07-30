<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Database configuration
$servername = "localhost";
$username = "root"; // Change to your database username
$password = ""; // Change to your database password
$dbname = "matcha_moami";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Hardcoded login for demo (in production, use database with hashed passwords)
    if ($username === 'Moami' && $password === '123456') {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Invalid credentials"]);
    }
    exit;
}

// Verify session (in a real app, you'd use proper session management)
if (!isset($_SERVER['HTTP_X_AUTH']) || $_SERVER['HTTP_X_AUTH'] !== 'Moami:123456') {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

// CRUD Operations
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_products':
        $result = $conn->query("SELECT * FROM products");
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode($products);
        break;
        
    case 'add_product':
        $name = $_POST['name'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $image = $_POST['image'];
        
        $stmt = $conn->prepare("INSERT INTO products (name, price, stock, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdds", $name, $price, $stock, $image);
        $stmt->execute();
        echo json_encode(["success" => true, "id" => $stmt->insert_id]);
        break;
        
    case 'update_product':
        $id = $_POST['id'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $image = $_POST['image'];
        
        $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=?, image=? WHERE id=?");
        $stmt->bind_param("sddsi", $name, $price, $stock, $image, $id);
        $stmt->execute();
        echo json_encode(["success" => $stmt->affected_rows > 0]);
        break;
        
    case 'delete_product':
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["success" => $stmt->affected_rows > 0]);
        break;
        
    case 'get_transactions':
        $result = $conn->query("SELECT * FROM transactions ORDER BY date DESC");
        $transactions = [];
        while ($row = $result->fetch_assoc()) {
            $transactions[] = $row;
        }
        echo json_encode($transactions);
        break;
        
    case 'add_transaction':
        $number = $_POST['number'];
        $date = $_POST['date'];
        $items = json_decode($_POST['items'], true);
        $total = $_POST['total'];
        
        // Insert transaction
        $stmt = $conn->prepare("INSERT INTO transactions (number, date, items, total) VALUES (?, ?, ?, ?)");
        $items_json = json_encode($items);
        $stmt->bind_param("sssd", $number, $date, $items_json, $total);
        $stmt->execute();
        
        // Update product stocks
        foreach ($items as $item) {
            $conn->query("UPDATE products SET stock = stock - {$item['quantity']} WHERE id = {$item['id']}");
        }
        
        echo json_encode(["success" => true, "id" => $stmt->insert_id]);
        break;
        
    case 'delete_transaction':
        $id = $_POST['id'];
        
        // First get the transaction to restore stock
        $result = $conn->query("SELECT items FROM transactions WHERE id = $id");
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $items = json_decode($row['items'], true);
            
            // Restore stock for each item
            foreach ($items as $item) {
                $conn->query("UPDATE products SET stock = stock + {$item['quantity']} WHERE id = {$item['id']}");
            }
        }
        
        // Then delete the transaction
        $conn->query("DELETE FROM transactions WHERE id = $id");
        echo json_encode(["success" => true]);
        break;
        
    default:
        echo json_encode(["error" => "Invalid action"]);
}

$conn->close();
?>