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
   DELETE USER
   ========================================================= */

$message = "";
$message_type = "";

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST['delete_user'])
) {

    $user_id = intval($_POST['user_id'] ?? 0);

    if ($user_id > 0) {

        /*
         * Check whether the user has items
         */
        $check_items_sql = "
            SELECT COUNT(*) AS total
            FROM items
            WHERE user_id = ?
        ";

        $check_items_stmt = mysqli_prepare(
            $conn,
            $check_items_sql
        );

        mysqli_stmt_bind_param(
            $check_items_stmt,
            "i",
            $user_id
        );

        mysqli_stmt_execute($check_items_stmt);

        $check_result = mysqli_stmt_get_result(
            $check_items_stmt
        );

        $check_data = mysqli_fetch_assoc($check_result);

        mysqli_stmt_close($check_items_stmt);


        /*
         * If user has items, don't delete automatically.
         */
        if ($check_data['total'] > 0) {

            $message =
                "This user has shared items. Please manage or remove the items before deleting the user.";

            $message_type = "error";

        } else {

            /*
             * Delete user
             */
            $delete_sql = "
                DELETE FROM users
                WHERE user_id = ?
            ";

            $delete_stmt = mysqli_prepare(
                $conn,
                $delete_sql
            );

            mysqli_stmt_bind_param(
                $delete_stmt,
                "i",
                $user_id
            );

            if (mysqli_stmt_execute($delete_stmt)) {

                $message = "User deleted successfully.";
                $message_type = "success";

            } else {

                $message =
                    "Unable to delete this user. The user may have related records.";

                $message_type = "error";
            }

            mysqli_stmt_close($delete_stmt);
        }
    }
}


/* =========================================================
   SEARCH USERS
   ========================================================= */

$search = trim($_GET['search'] ?? '');


if (!empty($search)) {

    $search_value = "%" . $search . "%";

    $sql = "
        SELECT
            user_id,
            full_name,
            email,
            phone,
            department,
            year_of_study,
            profile_image,
            created_at
        FROM users
        WHERE
            full_name LIKE ?
            OR email LIKE ?
            OR phone LIKE ?
            OR department LIKE ?
        ORDER BY created_at DESC
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $search_value,
        $search_value,
        $search_value,
        $search_value
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

} else {

    $sql = "
        SELECT
            user_id,
            full_name,
            email,
            phone,
            department,
            year_of_study,
            profile_image,
            created_at
        FROM users
        ORDER BY created_at DESC
    ";

    $result = mysqli_query($conn, $sql);
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

    <title>Manage Users | CampusShare</title>


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

        .search-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-form {
            display: flex;
            gap: 8px;
            flex: 1;
            max-width: 500px;
        }

        .search-form input {
            flex: 1;
            padding: 11px 13px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-family: inherit;
            outline: none;
        }

        .search-form input:focus {
            border-color: #2563eb;
        }

        .search-btn {
            border: none;
            background: #2563eb;
            color: white;
            padding: 0 18px;
            border-radius: 7px;
            cursor: pointer;
            font-family: inherit;
        }

        .clear-btn {
            display: flex;
            align-items: center;
            padding: 0 15px;
            border-radius: 7px;
            text-decoration: none;
            background: #e5e7eb;
            color: #333;
            font-size: 13px;
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

        .users-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 850px;
        }

        .users-table th {
            background: #f1f5f9;
            color: #374151;
            padding: 13px 10px;
            text-align: left;
            font-size: 12px;
            white-space: nowrap;
        }

        .users-table td {
            padding: 13px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
            vertical-align: middle;
        }

        .users-table tr:hover {
            background: #f8fafc;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-image {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e5e7eb;
        }

        .default-user-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #dbeafe;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .user-name {
            font-weight: 600;
            color: #111827;
        }

        .user-email {
            color: #6b7280;
            font-size: 11px;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .view-btn,
        .delete-btn {
            border: none;
            padding: 7px 10px;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .view-btn {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .delete-btn {
            background: #fee2e2;
            color: #dc2626;
        }

        .view-btn:hover {
            background: #bfdbfe;
        }

        .delete-btn:hover {
            background: #fecaca;
        }

        .no-users {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        .no-users i {
            font-size: 40px;
            margin-bottom: 12px;
            color: #9ca3af;
        }

        .result-count {
            color: #6b7280;
            font-size: 12px;
            margin-bottom: 15px;
        }

        @media (max-width: 700px) {

            .page-card {
                padding: 15px;
            }

            .search-form {
                width: 100%;
                max-width: none;
            }

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


        <li class="active">

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

            <h1>Manage Users</h1>

            <p>
                View and manage registered CampusShare users.
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
             MESSAGE
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



        <!-- =================================================
             SEARCH
             ================================================= -->

        <div class="search-row">


            <form
                action="users.php"
                method="GET"
                class="search-form"
            >

                <input
                    type="text"
                    name="search"
                    placeholder="Search by name, email, phone or department..."
                    value="<?php echo htmlspecialchars($search); ?>"
                >


                <button
                    type="submit"
                    class="search-btn"
                >

                    <i class="fa-solid fa-magnifying-glass"></i>

                    Search

                </button>


                <?php if (!empty($search)): ?>

                    <a
                        href="users.php"
                        class="clear-btn"
                    >
                        Clear
                    </a>

                <?php endif; ?>


            </form>


        </div>



        <!-- =================================================
             RESULT COUNT
             ================================================= -->

        <?php

        $user_count = mysqli_num_rows($result);

        ?>

        <div class="result-count">

            <?php echo $user_count; ?> user(s) found.

        </div>



        <!-- =================================================
             USERS TABLE
             ================================================= -->

        <div class="table-wrapper">


            <?php if ($user_count > 0): ?>


                <table class="users-table">


                    <thead>

                        <tr>

                            <th>User</th>

                            <th>Phone</th>

                            <th>Department</th>

                            <th>Year</th>

                            <th>Joined</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while ($user = mysqli_fetch_assoc($result)): ?>


                        <tr>


                            <!-- USER -->

                            <td>

                                <div class="user-profile">


                                    <?php

                                    $profile_image =
                                        $user['profile_image'] ?? '';

                                    $image_path =
                                        "../uploads/" .
                                        basename($profile_image);

                                    ?>


                                    <?php if (
                                        !empty($profile_image) &&
                                        file_exists(
                                            __DIR__ .
                                            "/../uploads/" .
                                            basename($profile_image)
                                        )
                                    ): ?>

                                        <img
                                            src="<?php
                                            echo htmlspecialchars(
                                                $image_path
                                            );
                                            ?>"
                                            alt="Profile"
                                            class="user-image"
                                        >

                                    <?php else: ?>

                                        <div class="default-user-icon">

                                            <i class="fa-solid fa-user"></i>

                                        </div>

                                    <?php endif; ?>


                                    <div>

                                        <div class="user-name">

                                            <?php
                                            echo htmlspecialchars(
                                                $user['full_name']
                                            );
                                            ?>

                                        </div>


                                        <div class="user-email">

                                            <?php
                                            echo htmlspecialchars(
                                                $user['email']
                                            );
                                            ?>

                                        </div>

                                    </div>


                                </div>

                            </td>



                            <!-- PHONE -->

                            <td>

                                <?php

                                echo !empty($user['phone'])
                                    ? htmlspecialchars($user['phone'])
                                    : '—';

                                ?>

                            </td>



                            <!-- DEPARTMENT -->

                            <td>

                                <?php

                                echo !empty($user['department'])
                                    ? htmlspecialchars(
                                        $user['department']
                                    )
                                    : '—';

                                ?>

                            </td>



                            <!-- YEAR -->

                            <td>

                                <?php

                                echo !empty($user['year_of_study'])
                                    ? htmlspecialchars(
                                        $user['year_of_study']
                                    )
                                    : '—';

                                ?>

                            </td>



                            <!-- JOINED -->

                            <td>

                                <?php

                                echo !empty($user['created_at'])
                                    ? date(
                                        'd M Y',
                                        strtotime(
                                            $user['created_at']
                                        )
                                    )
                                    : '—';

                                ?>

                            </td>



                            <!-- ACTIONS -->

                            <td>

                                <div class="action-buttons">


                                    <a
                                        href="view_user.php?id=<?php
                                        echo $user['user_id'];
                                        ?>"
                                        class="view-btn"
                                    >

                                        <i class="fa-solid fa-eye"></i>

                                        View

                                    </a>



                                    <form
                                        action="users.php"
                                        method="POST"
                                        onsubmit="
                                            return confirm(
                                                'Are you sure you want to delete this user?'
                                            );
                                        "
                                    >

                                        <input
                                            type="hidden"
                                            name="user_id"
                                            value="<?php
                                            echo $user['user_id'];
                                            ?>"
                                        >


                                        <button
                                            type="submit"
                                            name="delete_user"
                                            class="delete-btn"
                                        >

                                            <i class="fa-solid fa-trash"></i>

                                            Delete

                                        </button>

                                    </form>


                                </div>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>


                </table>


            <?php else: ?>


                <div class="no-users">

                    <i class="fa-solid fa-users-slash"></i>

                    <h3>No Users Found</h3>

                    <p>
                        No users match your search.
                    </p>

                </div>


            <?php endif; ?>


        </div>


    </div>


</div>


</body>

</html>