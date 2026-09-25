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
   SUCCESS / ERROR MESSAGES
   ========================================================= */

$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';


/* =========================================================
   DELETE ITEM
   ========================================================= */

if (isset($_POST['delete_item'])) {

    $item_id = intval($_POST['item_id'] ?? 0);


    /* -----------------------------------------------------
       CHECK VALID ITEM ID
       ----------------------------------------------------- */

    if ($item_id <= 0) {

        header(
            "Location: items.php?error=" .
            urlencode("Invalid item.")
        );

        exit();
    }


    /* -----------------------------------------------------
       CHECK WHETHER ITEM EXISTS
       ----------------------------------------------------- */

    $item_check_sql = "
        SELECT
            item_id,
            item_name,
            availability,
            image
        FROM items
        WHERE item_id = ?
        LIMIT 1
    ";

    $item_check_stmt = mysqli_prepare(
        $conn,
        $item_check_sql
    );


    if (!$item_check_stmt) {

        header(
            "Location: items.php?error=" .
            urlencode("Database error.")
        );

        exit();
    }


    mysqli_stmt_bind_param(
        $item_check_stmt,
        "i",
        $item_id
    );

    mysqli_stmt_execute($item_check_stmt);

    $item_check_result =
        mysqli_stmt_get_result($item_check_stmt);

    $item_data =
        mysqli_fetch_assoc($item_check_result);

    mysqli_stmt_close($item_check_stmt);


    if (!$item_data) {

        header(
            "Location: items.php?error=" .
            urlencode("Item not found.")
        );

        exit();
    }


    /* -----------------------------------------------------
       DO NOT DELETE CURRENTLY BORROWED ITEM
       ----------------------------------------------------- */

    if ($item_data['availability'] === 'Borrowed') {

        header(
            "Location: items.php?error=" .
            urlencode(
                "Borrowed items cannot be deleted."
            )
        );

        exit();
    }


    /* -----------------------------------------------------
       DELETE OLD BORROWING RECORDS
       ----------------------------------------------------- */

    /*
     * Borrowing history does NOT prevent deletion.
     * Only an item currently marked as "Borrowed" is protected.
     *
     * Old borrowing records are deleted first so that the item
     * can be deleted without foreign-key errors.
     */

    mysqli_begin_transaction($conn);

    try {

        $history_delete_sql = "
            DELETE FROM borrow_requests
            WHERE item_id = ?
        ";

        $history_delete_stmt = mysqli_prepare(
            $conn,
            $history_delete_sql
        );

        if (!$history_delete_stmt) {
            throw new Exception("Unable to prepare history deletion.");
        }

        mysqli_stmt_bind_param(
            $history_delete_stmt,
            "i",
            $item_id
        );

        if (!mysqli_stmt_execute($history_delete_stmt)) {
            mysqli_stmt_close($history_delete_stmt);
            throw new Exception("Unable to delete borrowing history.");
        }

        mysqli_stmt_close($history_delete_stmt);

    /* -----------------------------------------------------
       DELETE ITEM
       ----------------------------------------------------- */

        $delete_sql = "
            DELETE FROM items
            WHERE item_id = ?
              AND availability <> 'Borrowed'
        ";

        $delete_stmt = mysqli_prepare(
            $conn,
            $delete_sql
        );

        if (!$delete_stmt) {
            throw new Exception("Unable to prepare item deletion.");
        }

        mysqli_stmt_bind_param(
            $delete_stmt,
            "i",
            $item_id
        );

        if (!mysqli_stmt_execute($delete_stmt)) {
            mysqli_stmt_close($delete_stmt);
            throw new Exception("Unable to delete this item.");
        }

        $deleted_rows = mysqli_stmt_affected_rows($delete_stmt);
        mysqli_stmt_close($delete_stmt);

        if ($deleted_rows <= 0) {
            throw new Exception("Unable to delete this item.");
        }

        mysqli_commit($conn);

        /*
         * Delete the physical image only after
         * successful database deletion.
         */

        if (!empty($item_data['image'])) {

            $image_file =
                "../uploads/items/" .
                basename($item_data['image']);

            if (file_exists($image_file)) {
                @unlink($image_file);
            }
        }

        header(
            "Location: items.php?success=" .
            urlencode("Item deleted successfully.")
        );

        exit();

    } catch (Exception $e) {

        mysqli_rollback($conn);

        header(
            "Location: items.php?error=" .
            urlencode(
                "Unable to delete this item. " . $e->getMessage()
            )
        );

        exit();
    }
}


/* =========================================================
   SEARCH
   ========================================================= */

$search = trim($_GET['search'] ?? '');


/* =========================================================
   FETCH ITEMS
   ========================================================= */

if ($search !== '') {

    $search_value = "%" . $search . "%";


    $sql = "
        SELECT
            i.item_id,
            i.item_name,
            i.description,
            i.item_condition,
            i.availability,
            i.location,
            i.image,
            i.created_at,

            u.full_name AS owner_name,
            u.email AS owner_email,

            c.category_name

        FROM items i

        LEFT JOIN users u
            ON i.user_id = u.user_id

        LEFT JOIN categories c
            ON i.category_id = c.category_id

        WHERE
            i.item_name LIKE ?
            OR i.description LIKE ?
            OR i.item_condition LIKE ?
            OR i.location LIKE ?
            OR u.full_name LIKE ?
            OR u.email LIKE ?
            OR c.category_name LIKE ?

        ORDER BY i.created_at DESC
    ";


    $stmt = mysqli_prepare(
        $conn,
        $sql
    );


    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "sssssss",
            $search_value,
            $search_value,
            $search_value,
            $search_value,
            $search_value,
            $search_value,
            $search_value
        );

        mysqli_stmt_execute($stmt);

        $result =
            mysqli_stmt_get_result($stmt);

    } else {

        $result = false;
        $error = "Unable to search items.";
    }

} else {

    $sql = "
        SELECT
            i.item_id,
            i.item_name,
            i.description,
            i.item_condition,
            i.availability,
            i.location,
            i.image,
            i.created_at,

            u.full_name AS owner_name,
            u.email AS owner_email,

            c.category_name

        FROM items i

        LEFT JOIN users u
            ON i.user_id = u.user_id

        LEFT JOIN categories c
            ON i.category_id = c.category_id

        ORDER BY i.created_at DESC
    ";


    $result = mysqli_query(
        $conn,
        $sql
    );
}


/* =========================================================
   TOTAL ITEMS
   ========================================================= */

$total_items = 0;

$count_sql = "
    SELECT COUNT(*) AS total
    FROM items
";

$count_result = mysqli_query(
    $conn,
    $count_sql
);

if ($count_result) {

    $count_data =
        mysqli_fetch_assoc($count_result);

    $total_items =
        intval($count_data['total']);
}


/* =========================================================
   TOTAL AVAILABLE
   ========================================================= */

$available_items = 0;

$available_sql = "
    SELECT COUNT(*) AS total
    FROM items
    WHERE availability = 'Available'
";

$available_result = mysqli_query(
    $conn,
    $available_sql
);

if ($available_result) {

    $available_data =
        mysqli_fetch_assoc($available_result);

    $available_items =
        intval($available_data['total']);
}


/* =========================================================
   TOTAL BORROWED
   ========================================================= */

$borrowed_items = 0;

$borrowed_sql = "
    SELECT COUNT(*) AS total
    FROM items
    WHERE availability = 'Borrowed'
";

$borrowed_result = mysqli_query(
    $conn,
    $borrowed_sql
);

if ($borrowed_result) {

    $borrowed_data =
        mysqli_fetch_assoc($borrowed_result);

    $borrowed_items =
        intval($borrowed_data['total']);
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

    <title>Manage Items | CampusShare Admin</title>


    <!-- =====================================================
         GOOGLE FONT
         ===================================================== -->

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- =====================================================
         FONT AWESOME
         ===================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- =====================================================
         ADMIN CSS
         ===================================================== -->

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


        <!-- LOGO -->

        <div class="sidebar-logo">

            <img
                src="../images/logo.png"
                alt="CampusShare Logo"
                onerror="this.style.display='none';"
            >

            <h2>CampusShare</h2>

            <p>Admin Panel</p>

        </div>


        <!-- MENU -->

        <nav class="sidebar-menu">


            <h4>Administration</h4>


            <!-- Dashboard -->

            <a href="dashboard.php">

                <i class="fa-solid fa-chart-line"></i>

                <span>Dashboard</span>

            </a>


            <!-- Users -->

            <a href="users.php">

                <i class="fa-solid fa-users"></i>

                <span>Manage Users</span>

            </a>


            <!-- Items -->

            <a
                href="items.php"
                class="active"
            >

                <i class="fa-solid fa-box"></i>

                <span>Manage Items</span>

            </a>


            <!-- Overdue -->

            <a href="overdue_items.php">

                <i class="fa-solid fa-triangle-exclamation"></i>

                <span>Overdue Items</span>

            </a>


            <!-- Contact -->

            <a href="user_messages.php">

                <i class="fa-solid fa-envelope"></i>

                <span>User Messages</span>

            </a>


            <!-- Feedback -->

            <a href="feedback.php">

                <i class="fa-solid fa-star"></i>

                <span>Feedback</span>

            </a>


            <!-- Logout -->

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


        <!-- =================================================
             HEADER
             ================================================= -->

        <header class="admin-header">


            <div>

                <h1>
                    Manage Items
                </h1>

                <p>
                    View and manage items shared by community members.
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

                <span>
                    <?php
                    echo htmlspecialchars($success);
                    ?>
                </span>

            </div>

        <?php endif; ?>



        <!-- =================================================
             ERROR MESSAGE
             ================================================= -->

        <?php if (!empty($error)): ?>

            <div class="alert alert-error">

                <i class="fa-solid fa-circle-exclamation"></i>

                <span>
                    <?php
                    echo htmlspecialchars($error);
                    ?>
                </span>

            </div>

        <?php endif; ?>



        <!-- =================================================
             STATISTICS
             ================================================= -->

        <div class="stats-grid">


            <!-- Total -->

            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-box"></i>

                </div>

                <div>

                    <h3>
                        <?php echo $total_items; ?>
                    </h3>

                    <p>
                        Total Items
                    </p>

                </div>

            </div>


            <!-- Available -->

            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-circle-check"></i>

                </div>

                <div>

                    <h3>
                        <?php echo $available_items; ?>
                    </h3>

                    <p>
                        Available Items
                    </p>

                </div>

            </div>


            <!-- Borrowed -->

            <div class="stat-card">

                <div class="stat-icon">

                    <i class="fa-solid fa-hand-holding"></i>

                </div>

                <div>

                    <h3>
                        <?php echo $borrowed_items; ?>
                    </h3>

                    <p>
                        Borrowed Items
                    </p>

                </div>

            </div>


        </div>



        <!-- =================================================
             SEARCH
             ================================================= -->

        <form
            method="GET"
            action="items.php"
            class="search-box"
        >


            <input
                type="text"
                name="search"
                placeholder="Search item, owner, category, location..."
                value="<?php echo htmlspecialchars($search); ?>"
            >


            <button type="submit">

                <i class="fa-solid fa-magnifying-glass"></i>

                Search

            </button>


            <?php if (!empty($search)): ?>

                <a
                    href="items.php"
                    class="btn btn-secondary"
                >

                    <i class="fa-solid fa-xmark"></i>

                    Clear

                </a>

            <?php endif; ?>


        </form>



        <!-- =================================================
             INFORMATION BOX
             ================================================= -->

        <div class="info-box">

            <i class="fa-solid fa-circle-info"></i>

            Currently borrowed items cannot be deleted.
            Available items can be deleted, including items that have
            old borrowing history.

        </div>



        <!-- =================================================
             ITEMS TABLE
             ================================================= -->

        <div class="table-container">


            <?php if ($result && mysqli_num_rows($result) > 0): ?>


                <table class="admin-table">


                    <thead>

                        <tr>

                            <th>
                                Image
                            </th>

                            <th>
                                Item
                            </th>

                            <th>
                                Category
                            </th>

                            <th>
                                Owner
                            </th>

                            <th>
                                Condition
                            </th>

                            <th>
                                Location
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Added
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while (
                        $row = mysqli_fetch_assoc($result)
                    ): ?>


                        <tr>


                            <!-- IMAGE -->

                            <td>

                                <?php if (!empty($row['image'])): ?>

                                    <img
                                        src="../uploads/items/<?php
                                            echo htmlspecialchars(
                                                basename($row['image'])
                                            );
                                        ?>"
                                        alt="Item Image"
                                        onerror="this.style.display='none';"
                                    >

                                <?php else: ?>

                                    <div
                                        style="
                                            width:50px;
                                            height:50px;
                                            border-radius:8px;
                                            background:#f1f5f9;
                                            display:flex;
                                            align-items:center;
                                            justify-content:center;
                                            color:#94a3b8;
                                        "
                                    >

                                        <i class="fa-solid fa-image"></i>

                                    </div>

                                <?php endif; ?>

                            </td>



                            <!-- ITEM -->

                            <td>

                                <strong
                                    style="color:#1e293b;"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $row['item_name']
                                    );
                                    ?>

                                </strong>


                                <?php if (
                                    !empty($row['description'])
                                ): ?>

                                    <div
                                        style="
                                            font-size:10px;
                                            color:#94a3b8;
                                            margin-top:4px;
                                            max-width:180px;
                                        "
                                    >

                                        <?php

                                        $description =
                                            $row['description'];

                                        if (
                                            strlen($description) > 70
                                        ) {

                                            $description =
                                                substr(
                                                    $description,
                                                    0,
                                                    70
                                                )
                                                . "...";
                                        }

                                        echo htmlspecialchars(
                                            $description
                                        );

                                        ?>

                                    </div>

                                <?php endif; ?>

                            </td>



                            <!-- CATEGORY -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row['category_name']
                                    ?? 'Not specified'
                                );
                                ?>

                            </td>



                            <!-- OWNER -->

                            <td>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $row['owner_name']
                                        ?? 'Unknown'
                                    );
                                    ?>

                                </strong>


                                <?php if (
                                    !empty($row['owner_email'])
                                ): ?>

                                    <div
                                        style="
                                            font-size:10px;
                                            color:#94a3b8;
                                            margin-top:3px;
                                        "
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $row['owner_email']
                                        );
                                        ?>

                                    </div>

                                <?php endif; ?>

                            </td>



                            <!-- CONDITION -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row['item_condition']
                                    ?? 'Not specified'
                                );
                                ?>

                            </td>



                            <!-- LOCATION -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $row['location']
                                    ?? 'Not specified'
                                );
                                ?>

                            </td>



                            <!-- STATUS -->

                            <td>


                                <?php if (
                                    $row['availability']
                                    === 'Available'
                                ): ?>


                                    <span class="badge badge-available">

                                        <i class="fa-solid fa-check"></i>

                                        Available

                                    </span>


                                <?php else: ?>


                                    <span class="badge badge-borrowed">

                                        <i class="fa-solid fa-hand-holding"></i>

                                        Borrowed

                                    </span>


                                <?php endif; ?>


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

                                } else {

                                    echo "N/A";

                                }

                                ?>

                            </td>



                            <!-- ACTION -->

                            <td>


                                <?php if (
                                    $row['availability']
                                    === 'Borrowed'
                                ): ?>


                                    <span
                                        class="badge badge-borrowed"
                                        title="Borrowed items cannot be deleted"
                                    >

                                        <i class="fa-solid fa-lock"></i>

                                        In Use

                                    </span>


                                <?php else: ?>


                                    <form
                                        method="POST"
                                        action="items.php"
                                        onsubmit="
                                            return confirm(
                                                'Are you sure you want to delete this item?'
                                            );
                                        "
                                    >


                                        <input
                                            type="hidden"
                                            name="item_id"
                                            value="<?php
                                                echo intval(
                                                    $row['item_id']
                                                );
                                            ?>"
                                        >


                                        <button
                                            type="submit"
                                            name="delete_item"
                                            class="btn btn-danger"
                                        >

                                            <i class="fa-solid fa-trash"></i>

                                            Delete

                                        </button>


                                    </form>


                                <?php endif; ?>


                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>


                </table>


            <?php else: ?>


                <!-- EMPTY STATE -->

                <div class="empty-state">


                    <i class="fa-solid fa-box-open"></i>


                    <h3>
                        No Items Found
                    </h3>


                    <p>

                        <?php if (!empty($search)): ?>

                            No items matched your search.

                        <?php else: ?>

                            There are no items in the system yet.

                        <?php endif; ?>

                    </p>


                </div>


            <?php endif; ?>


        </div>



        <!-- =================================================
             FOOTER
             ================================================= -->

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