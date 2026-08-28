<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include("includes/db.php");

$borrower_id = $_SESSION['user_id'];

/* -----------------------------
   Check Form Submission
----------------------------- */

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: browse_items.php");
    exit();
}

/* -----------------------------
   Get Form Data
----------------------------- */

$item_id = intval($_POST['item_id']);
$owner_id = intval($_POST['owner_id']);
$borrow_date = $_POST['borrow_date'];
$expected_return_date = $_POST['expected_return_date'];

/* -----------------------------
   Validation
----------------------------- */

if (
    empty($item_id) ||
    empty($owner_id) ||
    empty($borrow_date) ||
    empty($expected_return_date)
) {

    echo "<script>
    alert('Please fill all fields.');
    window.history.back();
    </script>";

    exit();
}

if ($borrow_date >= $expected_return_date) {

    echo "<script>
    alert('Expected return date must be after borrow date.');
    window.history.back();
    </script>";

    exit();
}

/* -----------------------------
   Check Item
----------------------------- */

$stmt = mysqli_prepare($conn,
"SELECT * FROM items
WHERE item_id=?");

mysqli_stmt_bind_param($stmt,"i",$item_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){

echo "<script>

alert('Item not found.');

window.location='browse_items.php';

</script>";

exit();

}

$item=mysqli_fetch_assoc($result);

/* -----------------------------
   Cannot Borrow Own Item
----------------------------- */

if($item['user_id']==$borrower_id){

echo "<script>

alert('You cannot borrow your own item.');

window.location='browse_items.php';

</script>";

exit();

}

/* -----------------------------
   Item Availability
----------------------------- */

if($item['availability']!="Available"){

echo "<script>

alert('Item is currently unavailable.');

window.location='browse_items.php';

</script>";

exit();

}

/* -----------------------------
   Duplicate Request Check
----------------------------- */

$check=mysqli_prepare($conn,

"SELECT request_id
FROM borrow_requests
WHERE item_id=?
AND borrower_id=?
AND status='Pending'");

mysqli_stmt_bind_param(

$check,

"ii",

$item_id,

$borrower_id

);

mysqli_stmt_execute($check);

$checkResult=mysqli_stmt_get_result($check);

if(mysqli_num_rows($checkResult)>0){

echo "<script>

alert('Borrow request already sent.');

window.location='browse_items.php';

</script>";

exit();

}

/* -----------------------------
   Insert Borrow Request
----------------------------- */

$insert=mysqli_prepare(

$conn,

"INSERT INTO borrow_requests
(
item_id,
borrower_id,
owner_id,
borrow_date,
expected_return_date,
status,
request_date
)

VALUES

(?,?,?,?,?,'Pending',NOW())"

);

mysqli_stmt_bind_param(

$insert,

"iiiss",

$item_id,

$borrower_id,

$owner_id,

$borrow_date,

$expected_return_date

);

if(mysqli_stmt_execute($insert)){

echo "<script>

alert('Borrow request sent successfully.');

window.location='my_borrow_requests.php';

</script>";

}
else{

echo "<script>

alert('Something went wrong.');

window.location='browse_items.php';

</script>";

}
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();