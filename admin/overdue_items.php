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
   SUCCESS / ERROR MESSAGES
   ========================================================= */

$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Overdue Items | CampusShare</title>


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


        /* =====================================================
           ALERT MESSAGES
           ===================================================== */

        .alert {
            padding: 13px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #22c55e;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        .alert i {
            margin-right: 7px;
        }


        /* =====================================================
           INFORMATION BOX
           ===================================================== */

        .info-box {
            background: #fff7ed;
            border-left: 4px solid #f97316;
            padding: 15px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #7c2d12;
            font-size: 13px;
        }

        .info-box i {
            margin-right: 7px;
        }


        /* =====================================================
           TABLE
           ===================================================== */

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .overdue-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1250px;
        }

        .overdue-table th {
            background: #f1f5f9;
            color: #374151;
            padding: 13px 10px;
            text-align: left;
            font-size: 12px;
            white-space: nowrap;
        }

        .overdue-table td {
            padding: 14px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
            vertical-align: middle;
        }

        .overdue-table tr:hover {
            background: #fff7ed;
        }


        /* =====================================================
           ITEM
           ===================================================== */

        .item-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .item-image {
            width: 55px;
            height: 55px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
        }

        .default-item-icon {
            width: 55px;
            height: 55px;
            border-radius: 8px;
            background: #ffedd5;
            color: #ea580c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .item-name {
            font-weight: 600;
            color: #111827;
        }


        /* =====================================================
           PERSON
           ===================================================== */

        .person-name {
            font-weight: 600;
            color: #111827;
        }

        .person-email {
            color: #6b7280;
            font-size: 11px;
            margin-top: 3px;
        }


        /* =====================================================
           DATE
           ===================================================== */

        .date {
            font-weight: 600;
            color: #374151;
        }


        /* =====================================================
           OVERDUE DAYS
           ===================================================== */

        .overdue-days {
            display: inline-block;
            background: #fee2e2;
            color: #b91c1c;
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 11px;
        }


        /* =====================================================
           STATUS
           ===================================================== */

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-approved {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-received {
            background: #dcfce7;
            color: #166534;
        }

        .status-return {
            background: #fef3c7;
            color: #92400e;
        }


        /* =====================================================
           ACTION BUTTONS
           ===================================================== */

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 135px;
        }


        .reminder-btn,
        .contact-btn {
            border: none;
            color: white;
            padding: 8px 12px;
            border-radius: 7px;
            cursor: pointer;
            font-family: inherit;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            white-space: nowrap;
        }


        /* Reminder */

        .reminder-btn {
            background: #ea580c;
        }

        .reminder-btn:hover {
            background: #c2410c;
        }


        /* Contact User */

        .contact-btn {
            background: #2563eb;
        }

        .contact-btn:hover {
            background: #1d4ed8;
        }


        /* =====================================================
           NO OVERDUE
           ===================================================== */

        .no-overdue {
            text-align: center;
            padding: 50px 20px;
            color: #6b7280;
        }

        .no-overdue i {
            font-size: 50px;
            color: #22c55e;
            margin-bottom: 15px;
        }

        .no-overdue h3 {
            margin-bottom: 7px;
            color: #374151;
        }


        /* =====================================================
           RESULT COUNT
           ===================================================== */

        .result-count {
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 15px;
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


        <!-- Dashboard -->

        <li>

            <a href="dashboard.php">

                <i class="fa-solid fa-house"></i>

                <span>Dashboard</span>

            </a>

        </li>


        <!-- Users -->

        <li>

            <a href="users.php">

                <i class="fa-solid fa-users"></i>

                <span>Manage Users</span>

            </a>

        </li>


        <!-- Items -->

        <li>

            <a href="items.php">

                <i class="fa-solid fa-box"></i>

                <span>Manage Items</span>

            </a>

        </li>


        <!-- Overdue -->

        <li class="active">

            <a href="overdue_items.php">

                <i class="fa-solid fa-triangle-exclamation"></i>

                <span>Overdue Items</span>

            </a>

        </li>


        <!-- Contact Messages -->

        <li>

            <a href="user_messages.php">

                <i class="fa-solid fa-message"></i>

                <span>User Messages</span>

            </a>

        </li>


        <!-- Feedback -->

        <li>

            <a href="feedback.php">

                <i class="fa-solid fa-star"></i>

                <span>Feedback</span>

            </a>

        </li>


        <!-- Logout -->

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


    <!-- =====================================================
         HEADER
         ===================================================== -->

    <div class="top-header">

        <div>

            <h1>Overdue Items</h1>

            <p>
                Monitor items that have not been returned after the due date.
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



    <!-- =====================================================
         PAGE CARD
         ===================================================== -->

    <div class="page-card">


        <!-- =================================================
             SUCCESS MESSAGE
             ================================================= -->

        <?php if (!empty($success)): ?>

            <div class="alert alert-success">

                <i class="fa-solid fa-circle-check"></i>

                <?= htmlspecialchars($success); ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             ERROR MESSAGE
             ================================================= -->

        <?php if (!empty($error)): ?>

            <div class="alert alert-error">

                <i class="fa-solid fa-circle-exclamation"></i>

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             INFORMATION
             ================================================= -->

        <div class="info-box">

            <i class="fa-solid fa-circle-info"></i>

            Only active borrowings whose expected return date has
            already passed are shown here.

            The Admin can send an overdue reminder or directly
            contact the borrower by email.

        </div>



        <?php

        /* =====================================================
           FETCH OVERDUE ITEMS
           ===================================================== */

        $sql = "

            SELECT

                br.request_id,
                br.item_id,
                br.borrower_id,
                br.owner_id,
                br.expected_return_date,
                br.status,
                br.request_date,

                i.item_name,
                i.image,
                i.location,

                u.full_name AS borrower_name,
                u.email AS borrower_email,

                owner.full_name AS owner_name,
                owner.email AS owner_email,

                DATEDIFF(
                    CURDATE(),
                    br.expected_return_date
                ) AS days_overdue

            FROM borrow_requests br


            INNER JOIN items i

                ON br.item_id = i.item_id


            LEFT JOIN users u

                ON br.borrower_id = u.user_id


            LEFT JOIN users owner

                ON br.owner_id = owner.user_id


            WHERE

                br.expected_return_date < CURDATE()

                AND br.actual_return_date IS NULL

                AND br.status IN (
                    'Approved',
                    'Item Received',
                    'Return Requested'
                )


            ORDER BY

                days_overdue DESC,

                br.expected_return_date ASC

        ";


        $result = mysqli_query($conn, $sql);

        ?>


        <?php if ($result): ?>


            <?php

            $total_overdue = mysqli_num_rows($result);

            ?>


            <div class="result-count">

                <?php echo $total_overdue; ?>

                overdue item(s) found.

            </div>



            <?php if ($total_overdue > 0): ?>


                <!-- =================================================
                     OVERDUE TABLE
                     ================================================= -->

                <div class="table-wrapper">


                    <table class="overdue-table">


                        <thead>

                            <tr>

                                <th>Item</th>

                                <th>Borrower</th>

                                <th>Owner</th>

                                <th>Expected Return</th>

                                <th>Days Overdue</th>

                                <th>Status</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while (
                            $row = mysqli_fetch_assoc($result)
                        ): ?>


                            <tr>


                                <!-- =================================
                                     ITEM
                                     ================================= -->

                                <td>

                                    <div class="item-info">


                                        <?php

                                        $image_file =
                                            basename(
                                                $row['image'] ?? ''
                                            );


                                        $image_server_path =
                                            __DIR__ .
                                            "/../uploads/items/" .
                                            $image_file;


                                        $image_browser_path =
                                            "../uploads/items/" .
                                            $image_file;

                                        ?>


                                        <?php if (
                                            !empty($image_file) &&
                                            file_exists(
                                                $image_server_path
                                            )
                                        ): ?>


                                            <img
                                                src="<?php
                                                echo htmlspecialchars(
                                                    $image_browser_path
                                                );
                                                ?>"
                                                alt="Item"
                                                class="item-image"
                                            >


                                        <?php else: ?>


                                            <div class="default-item-icon">

                                                <i
                                                    class="fa-solid fa-box"
                                                ></i>

                                            </div>


                                        <?php endif; ?>


                                        <div>

                                            <div class="item-name">

                                                <?php

                                                echo htmlspecialchars(
                                                    $row['item_name']
                                                );

                                                ?>

                                            </div>


                                            <?php if (
                                                !empty(
                                                    $row['location']
                                                )
                                            ): ?>

                                                <small>

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $row['location']
                                                    );

                                                    ?>

                                                </small>

                                            <?php endif; ?>

                                        </div>


                                    </div>

                                </td>



                                <!-- =================================
                                     BORROWER
                                     ================================= -->

                                <td>

                                    <div class="person-name">

                                        <?php

                                        echo htmlspecialchars(
                                            $row['borrower_name']
                                            ?? 'Unknown'
                                        );

                                        ?>

                                    </div>


                                    <div class="person-email">

                                        <?php

                                        echo htmlspecialchars(
                                            $row['borrower_email']
                                            ?? ''
                                        );

                                        ?>

                                    </div>

                                </td>



                                <!-- =================================
                                     OWNER
                                     ================================= -->

                                <td>

                                    <div class="person-name">

                                        <?php

                                        echo htmlspecialchars(
                                            $row['owner_name']
                                            ?? 'Unknown'
                                        );

                                        ?>

                                    </div>


                                    <div class="person-email">

                                        <?php

                                        echo htmlspecialchars(
                                            $row['owner_email']
                                            ?? ''
                                        );

                                        ?>

                                    </div>

                                </td>



                                <!-- =================================
                                     EXPECTED RETURN
                                     ================================= -->

                                <td>

                                    <span class="date">

                                        <?php

                                        echo date(
                                            'd M Y',
                                            strtotime(
                                                $row[
                                                    'expected_return_date'
                                                ]
                                            )
                                        );

                                        ?>

                                    </span>

                                </td>



                                <!-- =================================
                                     DAYS OVERDUE
                                     ================================= -->

                                <td>

                                    <span class="overdue-days">

                                        <i
                                            class="fa-solid fa-clock"
                                        ></i>

                                        <?php

                                        echo (int)
                                            $row['days_overdue'];

                                        ?>

                                        day(s)

                                    </span>

                                </td>



                                <!-- =================================
                                     STATUS
                                     ================================= -->

                                <td>


                                    <?php

                                    $status =
                                        $row['status'];

                                    ?>


                                    <?php if (
                                        $status === 'Approved'
                                    ): ?>


                                        <span
                                            class="status status-approved"
                                        >

                                            Approved

                                        </span>


                                    <?php elseif (
                                        $status === 'Item Received'
                                    ): ?>


                                        <span
                                            class="status status-received"
                                        >

                                            Item Received

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="status status-return"
                                        >

                                            Return Requested

                                        </span>


                                    <?php endif; ?>


                                </td>



                                <!-- =================================
                                     ACTIONS
                                     ================================= -->

                                <td>


                                    <?php if (
                                        !empty(
                                            $row['borrower_email']
                                        )
                                    ): ?>


                                        <div class="action-buttons">


                                            <!-- =====================
                                                 SEND REMINDER
                                                 ===================== -->

                                            <form
                                                action="send_reminder.php"
                                                method="POST"
                                                onsubmit="
                                                    return confirm(
                                                        'Send an overdue reminder to this borrower?'
                                                    );
                                                "
                                            >


                                                <input
                                                    type="hidden"
                                                    name="request_id"
                                                    value="<?php
                                                    echo (int)
                                                        $row['request_id'];
                                                    ?>"
                                                >


                                                <button
                                                    type="submit"
                                                    class="reminder-btn"
                                                >

                                                    <i
                                                        class="fa-solid fa-bell"
                                                    ></i>

                                                    Send Reminder

                                                </button>


                                            </form>



                                            <!-- =====================
                                                 CONTACT USER
                                                 ===================== -->

                                            <a
                                                href="contact_user.php?id=<?php
                                                echo (int)
                                                    $row['request_id'];
                                                ?>"
                                                class="contact-btn"
                                            >

                                                <i
                                                    class="fa-solid fa-envelope"
                                                ></i>

                                                Contact User

                                            </a>


                                        </div>


                                    <?php else: ?>


                                        <span>
                                            No email
                                        </span>


                                    <?php endif; ?>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <!-- =================================================
                     NO OVERDUE ITEMS
                     ================================================= -->

                <div class="no-overdue">

                    <i
                        class="fa-solid fa-circle-check"
                    ></i>


                    <h3>No Overdue Items</h3>


                    <p>

                        All borrowed items are currently within
                        their expected return dates.

                    </p>

                </div>


            <?php endif; ?>


        <?php else: ?>


            <!-- =================================================
                 DATABASE ERROR
                 ================================================= -->

            <div class="no-overdue">

                <i
                    class="fa-solid fa-triangle-exclamation"
                ></i>


                <h3>
                    Unable to load overdue items
                </h3>


                <p>
                    Please check the database connection.
                </p>

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>