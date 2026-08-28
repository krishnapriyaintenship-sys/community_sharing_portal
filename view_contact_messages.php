<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

include("includes/db.php");

$query = mysqli_query($conn,"
SELECT
contact_messages.*,
users.full_name,
users.email
FROM contact_messages
INNER JOIN users
ON contact_messages.user_id = users.user_id
ORDER BY contact_messages.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Contact Messages</title>

<link rel="stylesheet"
href="css/view_contact_messages.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="sidebar">

<h2>CampusShare</h2>

<ul>

<li>
<a href="admin_dashboard.php">
<i class="fa fa-gauge"></i>
Dashboard
</a>
</li>

<li>
<a href="manage_users.php">
<i class="fa fa-users"></i>
Manage Users
</a>
</li>

<li>
<a href="manage_items.php">
<i class="fa fa-box"></i>
Manage Items
</a>
</li>

<li>
<a href="manage_requests.php">
<i class="fa fa-handshake"></i>
Borrow Requests
</a>
</li>

<li>
<a href="manage_categories.php">
<i class="fa fa-list"></i>
Categories
</a>
</li>

<li class="active">
<a href="view_contact_messages.php">
<i class="fa fa-envelope"></i>
Messages
</a>
</li>

<li>
<a href="admin_logout.php">
<i class="fa fa-right-from-bracket"></i>
Logout
</a>
</li>

</ul>

</div>

<div class="main">

<header>

<h1>Contact Messages</h1>

</header>

<table>

<tr>

<th>ID</th>

<th>User</th>

<th>Email</th>

<th>Subject</th>

<th>Message</th>

<th>Date</th>

</tr>
<tr>

<th>ID</th>

<th>User</th>

<th>Email</th>

<th>Subject</th>

<th>Message</th>

<th>Date</th>

</tr>
<?php

if(mysqli_num_rows($query) > 0)
{

while($row = mysqli_fetch_assoc($query))
{

?>

<tr>

<td><?php echo $row['message_id']; ?></td>

<td><?php echo htmlspecialchars($row['full_name']); ?></td>

<td><?php echo htmlspecialchars($row['email']); ?></td>

<td><?php echo htmlspecialchars($row['subject']); ?></td>

<td><?php echo nl2br(htmlspecialchars($row['message'])); ?></td>

<td><?php echo $row['created_at']; ?></td>

</tr>

<?php

}

}
else
{

?>

<tr>

<td colspan="6" style="text-align:center;">

No Contact Messages Found

</td>

</tr>

<?php

}

?>

</table>

</div>

</body>

</html>