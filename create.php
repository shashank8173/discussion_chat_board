
<?php
include 'database/database.php';

$itemcode = "";
$product_description = "";
$quantity = "";
$mrp = "";
$margin = "";


$errorMessage = "";
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $itemcode = $_POST["itemcode"];
    $product_description = $_POST["product_description"];
    $quantity = $_POST["quantity"];
    $mrp = $_POST["mrp"];
    $margin = $_POST["margin"];


    do {
        if (empty($itemcode) || empty($product_description) || empty($quantity) || empty($mrp)|| empty($margin)) {
            $errorMessage = "All the fields are required";
            break;
        }
        $stmt = $conn->prepare("INSERT INTO stack_available(itemcode, product_description, quantity,mrp,margin) VALUES (?, ?, ?,?,?)");
        $stmt->bind_param("ssidi", $itemcode, $product_description, $quantity,$mrp,$margin); // "ssi" means string, string, integer

        if ($stmt->execute()) {
            // Reset form values
            $itemcode = "";
            $product_description = "";
            $quantity = "";
            $mrp = "";
$margin = "";

            $successMessage = "Item added successfully!";
            
            header("Location: index.php");
            exit;
        } else {
            $errorMessage = "Error: " . $stmt->error;
        }
        $stmt->close();
    } while (false);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container my-5">
        <h2>New Product</h2>

        <?php if (!empty($errorMessage)): ?>
            <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                <strong><?php echo $errorMessage; ?></strong>
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Item Code</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="itemcode" value="<?php echo $itemcode; ?>">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Product Description</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="product_description" value="<?php echo $product_description; ?>">
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Quantity</label>
                <div class="col-sm-6">
                    <input type="number" class="form-control" name="quantity" value="<?php echo $quantity; ?>">
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
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
                <div class="col-sm-3 d-grid">
                    <a class="btn btn-secondary" href="index.php">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
