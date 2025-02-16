<?php
session_start();
require 'db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Check user role
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Handle Create (only accessible to admin)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create']) && $isAdmin) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];

    $sql = "INSERT INTO products (name, category, price, stock_quantity) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $category, $price, $stock_quantity]);

    $_SESSION['message'] = "Product added successfully!";
    $_SESSION['message_type'] = "success";
    header("Location: index.php");
    exit;
}

// Handle Update (only accessible to admin)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update']) && $isAdmin) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];

    $sql = "UPDATE products SET name = ?, category = ?, price = ?, stock_quantity = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $category, $price, $stock_quantity, $id]);

    $_SESSION['message'] = "Product updated successfully!";
    $_SESSION['message_type'] = "info";
    header("Location: index.php");
    exit;
}

// Handle Delete (only accessible to admin)
if (isset($_GET['delete']) && $isAdmin) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    $_SESSION['message'] = "Product deleted successfully!";
    $_SESSION['message_type'] = "danger";
    header("Location: index.php");
    exit;
}

// Fetch Products
$sql = "SELECT * FROM products";
$products = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grocery CRUD</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Grocery CRUD</a>
            <div class="d-flex">
                <span class="navbar-text me-3">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>

    <h1 class="text-center mb-4">Manage Products</h1>

    <!-- Display Alert Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
    <?php endif; ?>

    <div class="row">
        <?php if ($isAdmin): ?>
            <div class="col-md-6">
                <!-- Create Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Add New Product</h5>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <input type="text" id="category" name="category" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" id="price" name="price" step="0.01" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="stock_quantity" class="form-label">Stock Quantity</label>
                                <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" required>
                            </div>
                            <button type="submit" name="create" class="btn btn-primary">Add Product</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="<?= $isAdmin ? 'col-md-6' : 'col-md-12' ?>">
            <!-- Product Table -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Product List</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <?php if ($isAdmin): ?>
                                    <th>Actions</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><?= htmlspecialchars($product['category']) ?></td>
                                        <td><?= htmlspecialchars($product['price']) ?></td>
                                        <td><?= htmlspecialchars($product['stock_quantity']) ?></td>
                                        <?php if ($isAdmin): ?>
                                            <td>
                                                <button class="btn btn-warning btn-sm" onclick="editProduct(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>', '<?= htmlspecialchars($product['category']) ?>', <?= $product['price'] ?>, <?= $product['stock_quantity'] ?>)">Edit</button>
                                                <a href="?delete=<?= $product['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= $isAdmin ? '5' : '4' ?>" class="text-center">No products available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<?php if ($isAdmin): ?>
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Product</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id">
                        <div class="mb-3">
                            <label for="edit-name" class="form-label">Name</label>
                            <input type="text" id="edit-name" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-category" class="form-label">Category</label>
                            <input type="text" id="edit-category" name="category" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="edit-price" class="form-label">Price</label>
                            <input type="number" id="edit-price" name="price" step="0.01" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-stock_quantity" class="form-label">Stock Quantity</label>
                            <input type="number" id="edit-stock_quantity" name="stock_quantity" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function editProduct(id, name, category, price, stock) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-category').value = category;
    document.getElementById('edit-price').value = price;
    document.getElementById('edit-stock_quantity').value = stock;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}
</script>
</body>
</html>
