<?php
session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location:login.php");
    exit();
}

include("includes/db.php");

$user_id=$_SESSION['user_id'];


/* =====================================================
   LOGGED IN USER
===================================================== */

$user_query=mysqli_query($conn,
"SELECT * FROM users WHERE user_id='$user_id'");

$user=mysqli_fetch_assoc($user_query);


/* =====================================================
   RETURN ITEM REQUEST
===================================================== */

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_item']))
{
    $request_id = (int)($_POST['request_id'] ?? 0);

    /*
       Only the borrower who owns the request can
       send a return request.

       The request must currently be Approved.
    */

    $return_sql = "
        UPDATE borrow_requests
        SET status='Return Requested'
        WHERE request_id=?
        AND borrower_id=?
        AND status='Approved'
    ";

    $return_stmt = mysqli_prepare($conn, $return_sql);

    if($return_stmt)
    {
        mysqli_stmt_bind_param(
            $return_stmt,
            "ii",
            $request_id,
            $user_id
        );

        mysqli_stmt_execute($return_stmt);

        mysqli_stmt_close($return_stmt);
    }

    /*
       Refresh the page after submitting the
       return request.
    */

    header("Location: my_borrow_requests.php");
    exit();
}


/* =====================================================
   BORROW REQUESTS
===================================================== */

$request_query=mysqli_query($conn,"
SELECT
borrow_requests.*,
items.item_name,
items.image,
users.full_name

FROM borrow_requests

INNER JOIN items
ON borrow_requests.item_id=items.item_id

INNER JOIN users
ON borrow_requests.owner_id=users.user_id

WHERE borrow_requests.borrower_id='$user_id'

ORDER BY borrow_requests.request_date DESC
");
?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>My Borrow Requests</title>

<link rel="stylesheet"
href="css/my_borrow_requests.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">


<!-- =====================================================
     RETURN REQUEST STYLES
===================================================== -->

<style>

.return-requested {
    color: #b45309;
    font-weight: bold;
}

.return-btn {
    background: #2563eb;
    color: white;
    border: none;
    padding: 10px 18px;
    border-radius: 7px;
    cursor: pointer;
    font-size: 14px;
    font-weight: bold;
    margin-top: 8px;
}

.return-btn:hover {
    background: #1d4ed8;
}

.return-pending {
    margin-top: 10px;
    padding: 10px 14px;
    background: #fef3c7;
    border-radius: 7px;
    color: #92400e;
    font-weight: bold;
}

.returned-message {
    margin-top: 10px;
    padding: 10px 14px;
    background: #dbeafe;
    border-radius: 7px;
    color: #1d4ed8;
    font-weight: bold;
}

</style>

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<div class="sidebar">

<div class="logo">

<img src="images/logo.png">

<h2>CampusShare</h2>

</div>


<ul>

<li>

<a href="dashboard.php">

<i class="fa fa-house"></i>

Dashboard

</a>

</li>


<li>

<a href="browse_items.php">

<i class="fa fa-box"></i>

Browse Items

</a>

</li>


<li class="active">

<a href="my_borrow_requests.php">

<i class="fa fa-handshake"></i>

My Borrow Requests

</a>

</li>


<li>

<a href="logout.php">

<i class="fa fa-right-from-bracket"></i>

Logout

</a>

</li>

</ul>

</div>


<!-- =====================================================
     MAIN
===================================================== -->

<div class="main">


<header>

<h1>My Borrow Requests</h1>


<div class="profile">

<img src="images/default.png">

<span>

<?php echo htmlspecialchars($user['full_name']); ?>

</span>

</div>

</header>


<!-- =====================================================
     REQUEST CONTAINER
===================================================== -->

<div class="request-container">


<?php

if(mysqli_num_rows($request_query) > 0)
{

while($request = mysqli_fetch_assoc($request_query))
{

?>


<!-- =====================================================
     REQUEST CARD
===================================================== -->

<div class="request-card">


<!-- =====================================================
     ITEM IMAGE
===================================================== -->

<div class="request-image">


<?php

if(!empty($request['image']) &&
   file_exists("uploads/items/".$request['image']))
{

?>

<img
src="uploads/items/<?php echo htmlspecialchars($request['image']); ?>"
alt="Item Image">

<?php

}

else

{

?>

<img
src="images/default.png"
alt="No Image">

<?php

}

?>

</div>


<!-- =====================================================
     REQUEST DETAILS
===================================================== -->

<div class="request-details">


<h2>

<?php

echo htmlspecialchars(
    $request['item_name']
);

?>

</h2>


<!-- OWNER -->

<p>

<strong>Owner :</strong>

<?php

echo htmlspecialchars(
    $request['full_name']
);

?>

</p>


<!-- BORROW DATE -->

<p>

<strong>Borrow Date :</strong>

<?php

echo htmlspecialchars(
    $request['borrow_date'] ?? ''
);

?>

</p>


<!-- EXPECTED RETURN DATE -->

<p>

<strong>Expected Return :</strong>

<?php

echo htmlspecialchars(
    $request['expected_return_date'] ?? ''
);

?>

</p>


<!-- =====================================================
     STATUS
===================================================== -->

<p>

<strong>Status :</strong>


<?php

$status = $request['status'];


if($status=="Pending")
{

?>

<span class="pending">
Pending
</span>

<?php

}


elseif($status=="Approved")
{

?>

<span class="approved">
Approved
</span>

<?php

}


elseif($status=="Rejected")
{

?>

<span class="rejected">
Rejected
</span>

<?php

}


elseif($status=="Return Requested")
{

?>

<span class="return-requested">
Return Requested
</span>

<?php

}


elseif($status=="Returned")
{

?>

<span class="returned">
Returned
</span>

<?php

}

?>

</p>


<!-- =====================================================
     RETURN ITEM OPTION
===================================================== -->

<?php

/*
|--------------------------------------------------------------------------
| APPROVED
|--------------------------------------------------------------------------
|
| Borrower can request a return only when the
| borrowing request has been approved.
|
*/

if($status=="Approved")
{

?>

<form method="POST"
      onsubmit="return confirm('Are you sure you want to send a return request for this item?');">

<input
type="hidden"
name="request_id"
value="<?php echo (int)$request['request_id']; ?>"
>


<button
type="submit"
name="return_item"
class="return-btn"
>

<i class="fa fa-rotate-left"></i>

Return Item

</button>

</form>


<?php

}


/*
|--------------------------------------------------------------------------
| RETURN REQUESTED
|--------------------------------------------------------------------------
|
| Borrower has already requested the return.
| Wait for the owner to accept or reject it.
|
*/

elseif($status=="Return Requested")
{

?>

<div class="return-pending">

<i class="fa fa-clock"></i>

Return request sent. Waiting for owner approval.

</div>


<?php

}


/*
|--------------------------------------------------------------------------
| RETURNED
|--------------------------------------------------------------------------
|
| Owner accepted the return request.
|
*/

elseif($status=="Returned")
{

?>

<div class="returned-message">

<i class="fa fa-check-circle"></i>

Item returned successfully.

</div>


<?php

}

?>


</div>


</div>


<?php

}

}

else

{

?>


<!-- =====================================================
     NO REQUESTS
===================================================== -->

<div class="no-request">

<h2>
No Borrow Requests
</h2>

<p>
You have not requested any items yet.
</p>

</div>


<?php

}

?>


</div>


</div>


</body>

</html>