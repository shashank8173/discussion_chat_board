
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
include 'database/database.php';
// Initialize variables
$id = "";
$itemcode = "";
$product_description = "";
$quantity = "";
$mrp = "";
$margin = "";

$errorMessage = "";
$successMessage = "";

// Handle GET request: show data of the client
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET["id"])) {
        header("Location: index.php");
        exit;
    }

    $id = $_GET["id"];

    // Read the row of the selected client from database table
    $sql = "SELECT * FROM stack_available WHERE id=$id";
    $result = $conn->query($sql);

    if (!$result || $result->num_rows == 0) {
        header("Location: index.php");
        exit;
    }

    $row = $result->fetch_assoc();
    $itemcode = $row["itemcode"];
    $product_description = $row["product_description"];
    $quantity = $row["quantity"];
    $mrp = $row["mrp"];
    $margin = $row["margin"];
}

// Handle POST request: Update the data of the client
else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST["id"];
    $itemcode = $_POST["itemcode"];
    $product_description = $_POST["product_description"];
    $quantity = $_POST["quantity"];
    $mrp = $_POST["mrp"]; 
    $margin = $_POST["margin"];  

    do {
        // Validate input fields
        if (empty($id) || empty($itemcode) || empty($product_description) || empty($quantity) || empty($mrp) || empty($margin)) {
            $errorMessage = "All fields are required";
            break;
        }

        // Update query
        $sql = "UPDATE stack_available SET itemcode='$itemcode', product_description='$product_description', quantity='$quantity', mrp='$mrp', margin='$margin' WHERE id=$id";
        $result = $conn->query($sql);

        if (!$result) {
            $errorMessage = "Invalid query: " . $conn->error;
            break;
        }

        $successMessage = "Item updated successfully";
        header("Location: index.php");
        exit;

    } while (false);
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container my-5">
        <h2>Update Product</h2>
        <?php if (!empty($errorMessage)): ?>
            <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                <strong><?php echo $errorMessage; ?></strong>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Item Code</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="itemcode" value="<?php echo $itemcode; ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Product Description</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="product_description" value="<?php echo $product_description; ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Quantity</label>
                <div class="col-sm-6">
                    <input type="number" class="form-control" name="quantity" value="<?php echo $quantity; ?>" required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">MRP</label>
                <div class="col-sm-6">
                <input type="number" class="form-control" name="mrp" value="<?php echo $mrp; ?>" step="0.001">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Margin(%)</label>
                <div class="col-sm-6">
                    <input type="number" class="form-control" name="margin" value="<?php echo $margin; ?>">
                </div>
            </div>
            <?php if (!empty($successMessage)): ?>
                <div class='row mb-3'>
                    <div class='offset-sm-3 col-sm-6'>
                        <div class='alert alert-success alert-dismissible fade show' role='alert'>
                            <strong><?php echo $successMessage; ?></strong>
                            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row mb-3">
                <div class="offset-sm-3 col-sm-3 d-grid">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
                <div class="col-sm-3 d-grid">
                    <a class="btn btn-secondary" href="index.php">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
