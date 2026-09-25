<?php

session_start();

/* =========================================================
   ADMIN ACCESS CHECK
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
   SUCCESS / ERROR MESSAGE
   ========================================================= */

$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';


/* =========================================================
   DELETE FEEDBACK
   ========================================================= */

if (isset($_POST['delete_feedback'])) {

    $feedback_id = intval($_POST['feedback_id'] ?? 0);

    if ($feedback_id <= 0) {

        header(
            "Location: feedback.php?error=" .
            urlencode("Invalid feedback.")
        );

        exit();
    }


    $delete_sql = "
        DELETE FROM feedback
        WHERE feedback_id = ?
    ";

    $delete_stmt = mysqli_prepare(
        $conn,
        $delete_sql
    );


    if (!$delete_stmt) {

        header(
            "Location: feedback.php?error=" .
            urlencode("Database error.")
        );

        exit();
    }


    mysqli_stmt_bind_param(
        $delete_stmt,
        "i",
        $feedback_id
    );


    if (mysqli_stmt_execute($delete_stmt)) {

        mysqli_stmt_close($delete_stmt);

        header(
            "Location: feedback.php?success=" .
            urlencode("Feedback deleted successfully.")
        );

        exit();

    } else {

        mysqli_stmt_close($delete_stmt);

        header(
            "Location: feedback.php?error=" .
            urlencode("Unable to delete feedback.")
        );

        exit();
    }
}


/* =========================================================
   FETCH FEEDBACK
   ========================================================= */

$sql = "
    SELECT
        f.feedback_id,
        f.user_id,
        f.rating,
        f.comments,
        f.created_at,

        u.full_name,
        u.email

    FROM feedback f

    LEFT JOIN users u
        ON f.user_id = u.user_id

    ORDER BY f.created_at DESC
";


$result = mysqli_query(
    $conn,
    $sql
);


/* =========================================================
   TOTAL FEEDBACK
   ========================================================= */

$total_feedback = 0;

$count_sql = "
    SELECT COUNT(*) AS total
    FROM feedback
";

$count_result = mysqli_query(
    $conn,
    $count_sql
);

if ($count_result) {

    $count_data =
        mysqli_fetch_assoc($count_result);

    $total_feedback =
        intval($count_data['total']);
}


/* =========================================================
   AVERAGE RATING
   ========================================================= */

$average_rating = 0;

$rating_sql = "
    SELECT AVG(rating) AS average_rating
    FROM feedback
";

$rating_result = mysqli_query(
    $conn,
    $rating_sql
);

if ($rating_result) {

    $rating_data =
        mysqli_fetch_assoc($rating_result);

    if ($rating_data['average_rating'] !== null) {

        $average_rating =
            round(
                floatval(
                    $rating_data['average_rating']
                ),
                1
            );
    }
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

    <title>Feedback | CampusShare Admin</title>


    <!-- Google Font -->

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- Admin CSS -->

    <link
        rel="stylesheet"
        href="admin.css"
    >

</head>


<body>


<div class="admin-container">


    <!-- =====================================================
         SIDEBAR
         ===================================================== -->

    <aside class="sidebar">


        <div class="sidebar-logo">

            <img
                src="../images/logo.png"
                alt="CampusShare Logo"
                onerror="this.style.display='none';"
            >

            <h2>CampusShare</h2>

            <p>Admin Panel</p>

        </div>


        <nav class="sidebar-menu">


            <h4>Administration</h4>


            <a href="dashboard.php">

                <i class="fa-solid fa-chart-line"></i>

                <span>Dashboard</span>

            </a>


            <a href="users.php">

                <i class="fa-solid fa-users"></i>

                <span>Manage Users</span>

            </a>


            <a href="items.php">

                <i class="fa-solid fa-box"></i>

                <span>Manage Items</span>

            </a>


            <a href="overdue_items.php">

                <i class="fa-solid fa-triangle-exclamation"></i>

                <span>Overdue Items</span>

            </a>


            <a href="contact_messages.php">

                <i class="fa-solid fa-envelope"></i>

                <span>Contact Messages</span>

            </a>


            <a
                href="feedback.php"
                class="active"
            >

                <i class="fa-solid fa-star"></i>

                <span>Feedback</span>

            </a>


            <a
                href="logout.php"
                class="logout"
            >

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Logout</span>

            </a>


        </nav>

    </aside>



    <!-- =====================================================
         MAIN CONTENT
         ===================================================== -->

    <main class="main-content">


        <!-- HEADER -->

        <header class="admin-header">

            <div>

                <h1>
                    User Feedback
                </h1>

                <p>
                    View feedback and ratings submitted by users.
                </p>

            </div>


            <div class="admin-profile">

                <div class="admin-icon">

                    <i class="fa-solid fa-user-shield"></i>

                </div>


                <div>

                    <strong>
                        Administrator
                    </strong>

                    <span>

                        <?php
                        echo htmlspecialchars(
                            $_SESSION['admin_email'] ?? ''
                        );
                        ?>

                    </span>

                </div>

            </div>

        </header>



        <!-- =================================================
             SUCCESS MESSAGE
             ================================================= -->

        <?php if (!empty($success)): ?>

            <div class="alert alert-success">

                <i class="fa-solid fa-circle-check"></i>

                <?php
                echo htmlspecialchars($success);
                ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             ERROR MESSAGE
             ================================================= -->

        <?php if (!empty($error)): ?>

            <div class="alert alert-error">

                <i class="fa-solid fa-circle-exclamation"></i>

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php endif; ?>



        <!-- =================================================
             STATISTICS
             ================================================= -->

        <div class="stats-grid">


            <!-- Total Feedback -->

            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-comments"></i>

                </div>

                <div>

                    <h3>
                        <?php echo $total_feedback; ?>
                    </h3>

                    <p>
                        Total Feedback
                    </p>

                </div>

            </div>


            <!-- Average Rating -->

            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-star"></i>

                </div>

                <div>

                    <h3>
                        <?php
                        echo number_format(
                            $average_rating,
                            1
                        );
                        ?>
                        / 5
                    </h3>

                    <p>
                        Average Rating
                    </p>

                </div>

            </div>


        </div>



        <!-- =================================================
             INFORMATION BOX
             ================================================= -->

        <div class="info-box">

            <i class="fa-solid fa-circle-info"></i>

            This page displays feedback submitted by registered
            users through the Give Feedback option.

        </div>



        <!-- =================================================
             FEEDBACK TABLE
             ================================================= -->

        <div class="table-container">


            <?php if (
                $result &&
                mysqli_num_rows($result) > 0
            ): ?>


                <table class="admin-table">


                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                User
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Rating
                            </th>

                            <th>
                                Comments
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $row =
                        mysqli_fetch_assoc($result)
                    ): ?>


                        <tr>


                            <!-- ID -->

                            <td>

                                <?php
                                echo intval(
                                    $row['feedback_id']
                                );
                                ?>

                            </td>



                            <!-- USER -->

                            <td>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['full_name']
                                        ?? 'Unknown User'
                                    );
                                    ?>

                                </strong>


                                <div
                                    style="
                                        font-size:10px;
                                        color:#94a3b8;
                                        margin-top:3px;
                                    "
                                >

                                    User ID:

                                    <?php
                                    echo intval(
                                        $row['user_id']
                                    );
                                    ?>

                                </div>

                            </td>



                            <!-- EMAIL -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row['email']
                                    ?? 'N/A'
                                );
                                ?>

                            </td>



                            <!-- RATING -->

                            <td>

                                <div
                                    style="
                                        white-space:nowrap;
                                    "
                                >

                                    <?php

                                    $rating =
                                        intval(
                                            $row['rating']
                                        );

                                    for (
                                        $i = 1;
                                        $i <= 5;
                                        $i++
                                    ) {

                                        if (
                                            $i <= $rating
                                        ) {

                                            echo
                                                '<span style="color:#f59e0b;font-size:17px;">★</span>';

                                        } else {

                                            echo
                                                '<span style="color:#cbd5e1;font-size:17px;">★</span>';
                                        }
                                    }

                                    ?>

                                </div>


                                <div
                                    style="
                                        font-size:10px;
                                        color:#64748b;
                                        margin-top:3px;
                                    "
                                >

                                    <?php
                                    echo $rating;
                                    ?>/5

                                </div>

                            </td>



                            <!-- COMMENTS -->

                            <td>

                                <div
                                    style="
                                        max-width:350px;
                                        line-height:1.6;
                                        color:#475569;
                                    "
                                >

                                    <?php
                                    echo nl2br(
                                        htmlspecialchars(
                                            $row['comments']
                                        )
                                    );
                                    ?>

                                </div>

                            </td>



                            <!-- DATE -->

                            <td>

                                <?php

                                if (
                                    !empty(
                                        $row['created_at']
                                    )
                                ) {

                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $row['created_at']
                                        )
                                    );

                                    echo "<br>";

                                    echo
                                        '<span style="font-size:10px;color:#94a3b8;">';

                                    echo date(
                                        "h:i A",
                                        strtotime(
                                            $row['created_at']
                                        )
                                    );

                                    echo "</span>";

                                } else {

                                    echo "N/A";
                                }

                                ?>

                            </td>



                            <!-- DELETE -->

                            <td>

                                <form
                                    method="POST"
                                    action="feedback.php"
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
                                            echo intval(
                                                $row['feedback_id']
                                            );
                                        ?>"
                                    >


                                    <button
                                        type="submit"
                                        name="delete_feedback"
                                        class="btn btn-danger"
                                    >

                                        <i class="fa-solid fa-trash"></i>

                                        Delete

                                    </button>


                                </form>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>


                </table>


            <?php else: ?>


                <!-- EMPTY STATE -->

                <div class="empty-state">

                    <i class="fa-solid fa-comments"></i>

                    <h3>
                        No Feedback Yet
                    </h3>

                    <p>
                        Users have not submitted any feedback yet.
                    </p>

                </div>


            <?php endif; ?>


        </div>



        <!-- FOOTER -->

        <footer class="admin-footer">

            <p>

                &copy;
                <?php echo date("Y"); ?>
                CampusShare Admin Panel

            </p>

        </footer>


    </main>


</div>


</body>

</html>