<?php
session_start();

/* =========================================================
   ADMIN LOGIN CHECK
   ========================================================= */

if (
    !isset($_SESSION['admin_id']) ||
    !isset($_SESSION['admin_role']) ||
    $_SESSION['admin_role'] !== 'admin'
) {
    header("Location: ../login.php");
    exit();
}


/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

require_once "../includes/db.php";


/* =========================================================
   DELETE MESSAGE
   ========================================================= */

$message = "";
$message_type = "";

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['delete_message'])
) {

    $message_id = intval($_POST['message_id'] ?? 0);

    if ($message_id > 0) {

        $delete_sql = "
            DELETE FROM contact_messages
            WHERE message_id = ?
        ";

        $delete_stmt = mysqli_prepare(
            $conn,
            $delete_sql
        );

        if ($delete_stmt) {

            mysqli_stmt_bind_param(
                $delete_stmt,
                "i",
                $message_id
            );

            if (mysqli_stmt_execute($delete_stmt)) {

                $message = "Message deleted successfully.";
                $message_type = "success";

            } else {

                $message = "Unable to delete the message.";
                $message_type = "error";
            }

            mysqli_stmt_close($delete_stmt);

        } else {

            $message = "Database error.";
            $message_type = "error";
        }
    }
}


/* =========================================================
   FETCH CONTACT MESSAGES
   ========================================================= */

$sql = "
    SELECT *
    FROM contact_messages
    ORDER BY message_id DESC
";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Contact Messages | CampusShare</title>


    <!-- Admin CSS -->

    <link
        rel="stylesheet"
        href="admin.css"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- Google Font -->

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <style>

        .page-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .alert {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .messages-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 950px;
        }

        .messages-table th {
            background: #f1f5f9;
            color: #374151;
            padding: 13px 10px;
            text-align: left;
            font-size: 12px;
            white-space: nowrap;
        }

        .messages-table td {
            padding: 14px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
            vertical-align: top;
        }

        .messages-table tr:hover {
            background: #f8fafc;
        }

        .sender-name {
            font-weight: 600;
            color: #111827;
        }

        .sender-email {
            color: #6b7280;
            font-size: 11px;
            margin-top: 3px;
        }

        .subject {
            font-weight: 600;
            color: #1f2937;
        }

        .message-content {
            max-width: 350px;
            line-height: 1.6;
            color: #4b5563;
            white-space: normal;
            word-break: break-word;
        }

        .date {
            color: #6b7280;
            white-space: nowrap;
        }

        .delete-btn {
            border: none;
            background: #fee2e2;
            color: #dc2626;
            padding: 8px 11px;
            border-radius: 6px;
            cursor: pointer;
            font-family: inherit;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .delete-btn:hover {
            background: #fecaca;
        }

        .result-count {
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 15px;
        }

        .no-messages {
            text-align: center;
            padding: 50px 20px;
            color: #6b7280;
        }

        .no-messages i {
            font-size: 50px;
            color: #9ca3af;
            margin-bottom: 15px;
        }

        .no-messages h3 {
            color: #374151;
            margin-bottom: 7px;
        }

    </style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
     ========================================================= -->

<div class="sidebar">


    <div class="sidebar-logo">

        <i class="fa-solid fa-users"></i>

        <h2>CampusShare</h2>

    </div>


    <div class="admin-label">

        <i class="fa-solid fa-user-shield"></i>

        <span>Admin Panel</span>

    </div>


    <ul class="sidebar-menu">


        <li>

            <a href="dashboard.php">

                <i class="fa-solid fa-house"></i>

                <span>Dashboard</span>

            </a>

        </li>


        <li>

            <a href="users.php">

                <i class="fa-solid fa-users"></i>

                <span>Manage Users</span>

            </a>

        </li>


        <li>

            <a href="items.php">

                <i class="fa-solid fa-box"></i>

                <span>Manage Items</span>

            </a>

        </li>


        <li>

            <a href="overdue_items.php">

                <i class="fa-solid fa-triangle-exclamation"></i>

                <span>Overdue Items</span>

            </a>

        </li>


        <li class="active">

            <a href="user_messages.php">

                <i class="fa-solid fa-message"></i>

                <span>User messages</span>

            </a>

        </li>


        <li>

            <a href="feedback.php">

                <i class="fa-solid fa-star"></i>

                <span>Feedback</span>

            </a>

        </li>


        <li class="logout-menu">

            <a href="logout.php">

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Logout</span>

            </a>

        </li>


    </ul>

</div>



<!-- =========================================================
     MAIN CONTENT
     ========================================================= -->

<div class="main-content">


    <!-- HEADER -->

    <div class="top-header">

        <div>

            <h1>User Messages</h1>

            <p>
                View messages submitted by CampusShare users.
            </p>

        </div>


        <div class="admin-profile">

            <i class="fa-solid fa-circle-user"></i>

            <div>

                <strong>Administrator</strong>

                <small>

                    <?php

                    echo htmlspecialchars(
                        $_SESSION['admin_email'] ?? 'Admin'
                    );

                    ?>

                </small>

            </div>

        </div>

    </div>



    <!-- PAGE CARD -->

    <div class="page-card">


        <!-- =================================================
             ALERT MESSAGE
             ================================================= -->

        <?php if (!empty($message)): ?>

            <div
                class="alert
                <?php
                echo $message_type === 'success'
                    ? 'alert-success'
                    : 'alert-error';
                ?>"
            >

                <i
                    class="fa-solid
                    <?php
                    echo $message_type === 'success'
                        ? 'fa-circle-check'
                        : 'fa-circle-exclamation';
                    ?>"
                ></i>

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>



        <?php

        $total_messages = $result
            ? mysqli_num_rows($result)
            : 0;

        ?>


        <div class="result-count">

            <?php echo $total_messages; ?>

            message(s) found.

        </div>



        <!-- =================================================
             MESSAGES TABLE
             ================================================= -->

        <?php if ($total_messages > 0): ?>


            <div class="table-wrapper">


                <table class="messages-table">


                    <thead>

                        <tr>

                            <th>Sender</th>

                            <th>Subject</th>

                            <th>Message</th>

                            <th>Date</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $row = mysqli_fetch_assoc($result)
                    ): ?>


                        <tr>


                            <!-- SENDER -->

                            <td>

                                <div class="sender-name">

                                    <?php

                                    echo htmlspecialchars(
                                        $row['name']
                                        ?? 'Unknown'
                                    );

                                    ?>

                                </div>


                                <div class="sender-email">

                                    <?php

                                    echo htmlspecialchars(
                                        $row['email']
                                        ?? ''
                                    );

                                    ?>

                                </div>

                            </td>



                            <!-- SUBJECT -->

                            <td>

                                <div class="subject">

                                    <?php

                                    echo htmlspecialchars(
                                        $row['subject']
                                        ?? 'No Subject'
                                    );

                                    ?>

                                </div>

                            </td>



                            <!-- MESSAGE -->

                            <td>

                                <div class="message-content">

                                    <?php

                                    echo nl2br(
                                        htmlspecialchars(
                                            $row['message']
                                            ?? ''
                                        )
                                    );

                                    ?>

                                </div>

                            </td>



                            <!-- DATE -->

                            <td>

                                <span class="date">

                                    <?php

                                    $date_value =
                                        $row['created_at']
                                        ?? null;

                                    if ($date_value) {

                                        echo date(
                                            'd M Y, h:i A',
                                            strtotime(
                                                $date_value
                                            )
                                        );

                                    } else {

                                        echo '—';

                                    }

                                    ?>

                                </span>

                            </td>



                            <!-- DELETE -->

                            <td>

                                <form
                                    action="user_messages.php"
                                    method="POST"
                                    onsubmit="
                                        return confirm(
                                            'Are you sure you want to delete this message?'
                                        );
                                    "
                                >


                                    <input
                                        type="hidden"
                                        name="message_id"
                                        value="<?php

                                        echo htmlspecialchars(
                                            $row['message_id']
                                            ?? ''
                                        );

                                        ?>"
                                    >


                                    <button
                                        type="submit"
                                        name="delete_message"
                                        class="delete-btn"
                                    >

                                        <i
                                            class="fa-solid fa-trash"
                                        ></i>

                                        Delete

                                    </button>


                                </form>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>


                </table>


            </div>


        <?php else: ?>


            <div class="no-messages">

                <i class="fa-solid fa-envelope-open"></i>


                <h3>No Contact Messages</h3>


                <p>
                    There are currently no messages from users.
                </p>

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>