<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include("includes/db.php");

$user_id = $_SESSION['user_id'];

/* -----------------------------
   Check Item ID
------------------------------ */

if (!isset($_GET['item_id']) || empty($_GET['item_id'])) {
    die("Invalid Item ID.");
}

$item_id = intval($_GET['item_id']);

/* -----------------------------
   Get Item Details
------------------------------ */

$sql = "
SELECT
    items.*,
    users.full_name,
    categories.category_name
FROM items
INNER JOIN users
    ON items.user_id = users.user_id
INNER JOIN categories
    ON items.category_id = categories.category_id
WHERE items.item_id = ?
LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $item_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Item not found.");
}

$item = mysqli_fetch_assoc($result);

/* -----------------------------
   Prevent Borrowing Own Item
------------------------------ */

if ($item['user_id'] == $user_id) {

    echo "<script>
    alert('You cannot borrow your own item.');
    window.location='browse_items.php';
    </script>";

    exit();
}

/* -----------------------------
   Check Availability
------------------------------ */

if ($item['availability'] != "Available") {

    echo "<script>
    alert('Sorry! This item is not available.');
    window.location='browse_items.php';
    </script>";

    exit();
}

/* -----------------------------
   Check Existing Pending Request
------------------------------ */

$check = mysqli_prepare(
    $conn,
    "SELECT request_id
     FROM borrow_requests
     WHERE item_id = ?
     AND borrower_id = ?
     AND status='Pending'"
);

mysqli_stmt_bind_param(
    $check,
    "ii",
    $item_id,
    $user_id
);

mysqli_stmt_execute($check);

$check_result = mysqli_stmt_get_result($check);

if (mysqli_num_rows($check_result) > 0) {

    echo "<script>
    alert('You have already sent a borrow request for this item.');
    window.location='browse_items.php';
    </script>";

    exit();
}

/* -----------------------------
   Today's Date
------------------------------ */

$today = date("Y-m-d");
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Borrow Item | CampusShare</title>

<link rel="stylesheet" href="css/borrow_requests.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="container">

<h1>

<i class="fa-solid fa-handshake"></i>

Borrow Item

</h1>

<div class="card">

<?php
if (!empty($item['image']) && file_exists("uploads/items/" . $item['image'])) {
?>
<img src="uploads/items/<?php echo htmlspecialchars($item['image']); ?>" alt="Item Image">
<?php
} else {
?>
<img src="images/default.png" alt="No Image">
<?php
}
?>

<h2>

<?php echo htmlspecialchars($item['item_name']); ?>

</h2>

<div class="item-details">

<p>

<strong>Owner :</strong>

<?php echo htmlspecialchars($item['full_name']); ?>

</p>

<p>

<strong>Category :</strong>

<?php echo htmlspecialchars($item['category_name']); ?>

</p>

<p>

<strong>Condition :</strong>

<?php echo htmlspecialchars($item['item_condition']); ?>

</p>

<p>

<strong>Location :</strong>

<?php echo htmlspecialchars($item['location']); ?>

</p>

<p>

<strong>Availability :</strong>

<span class="available">

<?php echo htmlspecialchars($item['availability']); ?>

</span>

</p>

<p>

<strong>Description :</strong>

</p>

<p class="description">

<?php echo nl2br(htmlspecialchars($item['description'])); ?>

</p>

</div>

<hr>

<form action="borrow_request_process.php" method="POST">

<input
type="hidden"
name="item_id"
value="<?php echo $item['item_id']; ?>">

<input
type="hidden"
name="owner_id"
value="<?php echo $item['user_id']; ?>">

<label>

Borrow Date

</label>

<input
type="date"
name="borrow_date"
min="<?php echo $today; ?>"
required>

<label>

Expected Return Date

</label>

<input
type="date"
name="expected_return_date"
min="<?php echo $today; ?>"
required>

<button type="submit" class="borrow-btn">

<i class="fa-solid fa-paper-plane"></i>

Send Borrow Request

</button>

</form>

<div class="button-group">

<a href="browse_items.php" class="back-btn">

<i class="fa-solid fa-arrow-left"></i>

Back to Browse Items

</a>

</div>

</div>

</div>
<script>

// Borrow Date
const borrowDate = document.querySelector('input[name="borrow_date"]');

// Return Date
const returnDate = document.querySelector('input[name="expected_return_date"]');

// Update minimum return date
borrowDate.addEventListener("change", function () {

    returnDate.min = this.value;

    // Clear invalid return date
    if (returnDate.value < this.value) {
        returnDate.value = "";
    }

});

// Form Validation
document.querySelector("form").addEventListener("submit", function (e) {

    let borrow = new Date(borrowDate.value);
    let ret = new Date(returnDate.value);

    if (returnDate.value == "") {

        alert("Please select the expected return date.");

        e.preventDefault();

        return;

    }

    if (ret <= borrow) {

        alert("Return date must be after the borrow date.");

        e.preventDefault();

        return;

    }

});

</script>

</body>
</html>