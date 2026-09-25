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
   DELETE FEEDBACK
   ========================================================= */

$message = "";
$message_type = "";

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['delete_feedback'])
) {

    $feedback_id = intval($_POST['feedback_id'] ?? 0);

    if ($feedback_id > 0) {

        $delete_sql = "
            DELETE FROM feedback
            WHERE feedback_id = ?
        ";

        $delete_stmt = mysqli_prepare(
            $conn,
            $delete_sql
        );

        if ($delete_stmt) {

            mysqli_stmt_bind_param(
                $delete_stmt,
                "i",
                $feedback_id
            );

            if (mysqli_stmt_execute($delete_stmt)) {

                $message = "Feedback deleted successfully.";
                $message_type = "success";

            } else {

                $message = "Unable to delete the feedback.";
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
   FETCH FEEDBACK
   ========================================================= */

$sql = "
    SELECT
        f.*,
        u.full_name AS user_full_name,
        u.email AS user_email
    FROM feedback f
    LEFT JOIN users u
        ON f.user_id = u.user_id
    ORDER BY f.feedback_id DESC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    $message = "Unable to load feedback.";
    $message_type = "error";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Feedback | CampusShare</title>


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

        .feedback-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        .feedback-table th {
            background: #f1f5f9;
            color: #374151;
            padding: 13px 10px;
            text-align: left;
            font-size: 12px;
            white-space: nowrap;
        }

        .feedback-table td {
            padding: 14px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
            vertical-align: top;
        }

        .feedback-table tr:hover {
            background: #f8fafc;
        }

        .user-name {
            font-weight: 600;
            color: #111827;
        }

        .user-email {
            color: #6b7280;
            font-size: 11px;
            margin-top: 3px;
        }

        .rating {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            color: #f59e0b;
            font-size: 15px;
        }

        .rating-number {
            color: #374151;
            font-size: 11px;
            margin-left: 5px;
        }

        .feedback-content {
            max-width: 450px;
            color: #4b5563;
            line-height: 1.6;
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

        .no-feedback {
            text-align: center;
            padding: 50px 20px;
            color: #6b7280;
        }

        .no-feedback i {
            font-size: 50px;
            color: #f59e0b;
            margin-bottom: 15px;
        }

        .no-feedback h3 {
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


        <li>

            <a href="user_messages.php">

                <i class="fa-solid fa-message"></i>

                <span>User Messages</span>

            </a>

        </li>


        <li class="active">

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

            <h1>Feedback</h1>

            <p>
                View feedback submitted by CampusShare users.
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
             ALERT
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

        $total_feedback = $result
            ? mysqli_num_rows($result)
            : 0;

        ?>


        <div class="result-count">

            <?php echo $total_feedback; ?>

            feedback item(s) found.

        </div>



        <!-- =================================================
             FEEDBACK TABLE
             ================================================= -->

        <?php if ($total_feedback > 0): ?>


            <div class="table-wrapper">


                <table class="feedback-table">


                    <thead>

                        <tr>

                            <th>User</th>

                            <th>Rating</th>

                            <th>Feedback</th>

                            <th>Date</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $row = mysqli_fetch_assoc($result)
                    ): ?>


                        <tr>


                            <!-- USER -->

                            <td>

                                <div class="user-name">

                                    <?php

                                    echo htmlspecialchars(
                                        $row['user_full_name']
                                        ?? $row['full_name']
                                        ?? $row['name']
                                        ?? 'Unknown'
                                    );

                                    ?>

                                </div>


                                <?php if (
                                    !empty($row['user_email'])
                                    || !empty($row['email'])
                                ): ?>

                                    <div class="user-email">

                                        <?php

                                        echo htmlspecialchars(
                                            $row['user_email']
                                            ?? $row['email']
                                            ?? ''
                                        );

                                        ?>

                                    </div>

                                <?php endif; ?>

                            </td>



                            <!-- RATING -->

                            <td>

                                <?php

                                $rating = intval(
                                    $row['rating']
                                    ?? 0
                                );

                                if ($rating < 0) {
                                    $rating = 0;
                                }

                                if ($rating > 5) {
                                    $rating = 5;
                                }

                                ?>


                                <div class="rating">


                                    <?php for (
                                        $i = 1;
                                        $i <= 5;
                                        $i++
                                    ): ?>

                                        <?php if (
                                            $i <= $rating
                                        ): ?>

                                            <i
                                                class="fa-solid fa-star"
                                            ></i>

                                        <?php else: ?>

                                            <i
                                                class="fa-regular fa-star"
                                            ></i>

                                        <?php endif; ?>

                                    <?php endfor; ?>


                                    <span
                                        class="rating-number"
                                    >

                                        <?php
                                        echo $rating;
                                        ?>/5

                                    </span>


                                </div>

                            </td>



                            <!-- FEEDBACK -->

                            <td>

                                <div class="feedback-content">

                                    <?php

                                    $feedback_text = '';

                                    /*
                                     * Different versions of the feedback table
                                     * may use different names for the text column.
                                     * Check the common names first.
                                     */
                                    $feedback_columns = [
                                        'feedback',
                                        'feedback_text',
                                        'feedback_message',
                                        'message',
                                        'comment',
                                        'comments',
                                        'review',
                                        'review_text'
                                    ];

                                    foreach ($feedback_columns as $column) {
                                        if (
                                            array_key_exists($column, $row) &&
                                            trim((string)$row[$column]) !== ''
                                        ) {
                                            $feedback_text = (string)$row[$column];
                                            break;
                                        }
                                    }

                                    /*
                                     * If none of the common names exist, look for
                                     * another non-empty text field while ignoring
                                     * IDs, user information, rating and date fields.
                                     */
                                    if ($feedback_text === '') {
                                        $ignored_columns = [
                                            'feedback_id',
                                            'user_id',
                                            'rating',
                                            'created_at',
                                            'feedback_date',
                                            'user_full_name',
                                            'user_email',
                                            'full_name',
                                            'name',
                                            'email'
                                        ];

                                        foreach ($row as $column => $value) {
                                            if (
                                                !in_array($column, $ignored_columns, true) &&
                                                is_string($value) &&
                                                trim($value) !== ''
                                            ) {
                                                $feedback_text = $value;
                                                break;
                                            }
                                        }
                                    }

                                    if ($feedback_text !== '') {
                                        echo nl2br(
                                            htmlspecialchars(
                                                $feedback_text,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            )
                                        );
                                    } else {
                                        echo '<span style="color:#9ca3af;">No feedback text</span>';
                                    }

                                    ?>

                                </div>

                            </td>



                            <!-- DATE -->

                            <td>

                                <span class="date">

                                    <?php

                                    $date_value =
                                        $row['created_at']
                                        ?? $row['feedback_date']
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
                                    action="feedback.php"
                                    method="POST"
                                    onsubmit="
                                        return confirm(
                                            'Are you sure you want to delete this feedback?'
                                        );
                                    "
                                >


                                    <input
                                        type="hidden"
                                        name="feedback_id"
                                        value="<?php

                                        echo htmlspecialchars(
                                            $row['feedback_id']
                                            ?? ''
                                        );

                                        ?>"
                                    >


                                    <button
                                        type="submit"
                                        name="delete_feedback"
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


            <div class="no-feedback">

                <i class="fa-regular fa-star"></i>


                <h3>No Feedback Available</h3>


                <p>
                    There is currently no feedback submitted by users.
                </p>

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>