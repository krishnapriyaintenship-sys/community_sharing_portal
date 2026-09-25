<?php

require_once __DIR__ . "/mail.php";


/* =========================================================
   1. NEW BORROW REQUEST
   Borrower -> Owner
========================================================= */

function sendNewBorrowRequestEmail(
    $ownerEmail,
    $ownerName,
    $borrowerName,
    $itemName,
    $borrowDate,
    $expectedReturnDate
) {

    $subject = "New Borrow Request - " . $itemName;

    $body = "
    <h2>New Borrow Request</h2>

    <p>Hello <strong>" . htmlspecialchars($ownerName) . "</strong>,</p>

    <p>
        You have received a new borrow request.
    </p>

    <p>
        <strong>Item:</strong> " . htmlspecialchars($itemName) . "<br>
        <strong>Borrower:</strong> " . htmlspecialchars($borrowerName) . "<br>
        <strong>Borrow Date:</strong> " . htmlspecialchars($borrowDate) . "<br>
        <strong>Expected Return Date:</strong> " . htmlspecialchars($expectedReturnDate) . "
    </p>

    <p>
        Please login to your account to accept or reject the request.
    </p>
    ";

    return sendEmail(
        $ownerEmail,
        $ownerName,
        $subject,
        $body
    );
}


/* =========================================================
   2. BORROW APPROVED
   Owner -> Borrower
========================================================= */

function sendBorrowApprovedEmail(
    $borrowerEmail,
    $borrowerName,
    $ownerName,
    $itemName,
    $expectedReturnDate
) {

    $subject = "Borrow Request Approved - " . $itemName;

    $body = "
    <h2>Borrow Request Approved</h2>

    <p>
        Hello <strong>" . htmlspecialchars($borrowerName) . "</strong>,
    </p>

    <p>
        Your borrow request has been approved by the owner.
    </p>

    <p>
        <strong>Item:</strong> " . htmlspecialchars($itemName) . "<br>
        <strong>Owner:</strong> " . htmlspecialchars($ownerName) . "<br>
        <strong>Expected Return Date:</strong> "
        . htmlspecialchars($expectedReturnDate) . "
    </p>

    <p>
        After receiving the item, please click
        <strong>Item Received</strong>.
    </p>
    ";

    return sendEmail(
        $borrowerEmail,
        $borrowerName,
        $subject,
        $body
    );
}


/* =========================================================
   3. BORROW REJECTED
   Owner -> Borrower
========================================================= */

function sendBorrowRejectedEmail(
    $borrowerEmail,
    $borrowerName,
    $ownerName,
    $itemName,
    $ownerMessage
) {

    $subject = "Borrow Request Rejected - " . $itemName;

    $body = "
    <h2>Borrow Request Rejected</h2>

    <p>
        Hello <strong>" . htmlspecialchars($borrowerName) . "</strong>,
    </p>

    <p>
        Your borrow request has been rejected by the owner.
    </p>

    <p>
        <strong>Item:</strong> " . htmlspecialchars($itemName) . "<br>
        <strong>Owner:</strong> " . htmlspecialchars($ownerName) . "
    </p>
    ";

    if (!empty($ownerMessage)) {

        $body .= "
        <p>
            <strong>Owner Message:</strong><br>
            " . nl2br(htmlspecialchars($ownerMessage)) . "
        </p>
        ";
    }

    return sendEmail(
        $borrowerEmail,
        $borrowerName,
        $subject,
        $body
    );
}


/* =========================================================
   4. ITEM RECEIVED
   Borrower -> Owner
========================================================= */

function sendItemReceivedEmail(
    $ownerEmail,
    $ownerName,
    $borrowerName,
    $itemName
) {

    $subject = "Item Received - " . $itemName;

    $body = "
    <h2>Item Received</h2>

    <p>
        Hello <strong>" . htmlspecialchars($ownerName) . "</strong>,
    </p>

    <p>
        The borrower has confirmed that they received the item.
    </p>

    <p>
        <strong>Item:</strong> " . htmlspecialchars($itemName) . "<br>
        <strong>Borrower:</strong> " . htmlspecialchars($borrowerName) . "
    </p>

    <p>
        The item is currently being borrowed.
    </p>
    ";

    return sendEmail(
        $ownerEmail,
        $ownerName,
        $subject,
        $body
    );
}


/* =========================================================
   5. RETURN REQUEST
   Borrower -> Owner
========================================================= */

function sendReturnRequestEmail(
    $ownerEmail,
    $ownerName,
    $borrowerName,
    $itemName
) {

    $subject = "Return Request - " . $itemName;

    $body = "
    <h2>Item Return Request</h2>

    <p>
        Hello <strong>" . htmlspecialchars($ownerName) . "</strong>,
    </p>

    <p>
        The borrower has requested to return your item.
    </p>

    <p>
        <strong>Item:</strong> " . htmlspecialchars($itemName) . "<br>
        <strong>Borrower:</strong> " . htmlspecialchars($borrowerName) . "
    </p>

    <p>
        Please login to your account and confirm whether you have received
        the returned item.
    </p>
    ";

    return sendEmail(
        $ownerEmail,
        $ownerName,
        $subject,
        $body
    );
}


/* =========================================================
   6. RETURN ACCEPTED
   Owner -> Borrower
========================================================= */

function sendReturnAcceptedEmail(
    $borrowerEmail,
    $borrowerName,
    $ownerName,
    $itemName
) {

    $subject = "Return Confirmed - " . $itemName;

    $body = "
    <h2>Return Confirmed Successfully</h2>

    <p>
        Hello <strong>" . htmlspecialchars($borrowerName) . "</strong>,
    </p>

    <p>
        The owner has confirmed that the returned item has been received.
    </p>

    <p>
        <strong>Item:</strong> " . htmlspecialchars($itemName) . "<br>
        <strong>Owner:</strong> " . htmlspecialchars($ownerName) . "
    </p>

    <p>
        The borrowing process is now completed.
    </p>

    <p>
        <strong>Status: Returned</strong>
    </p>
    ";

    return sendEmail(
        $borrowerEmail,
        $borrowerName,
        $subject,
        $body
    );
}


/* =========================================================
   7. RETURN REJECTED
   Owner -> Borrower
========================================================= */

function sendReturnRejectedEmail(
    $borrowerEmail,
    $borrowerName,
    $ownerName,
    $itemName,
    $ownerMessage
) {

    $subject = "Return Request Rejected - " . $itemName;

    $body = "
    <h2>Return Request Rejected</h2>

    <p>
        Hello <strong>" . htmlspecialchars($borrowerName) . "</strong>,
    </p>

    <p>
        The owner has not confirmed the return of the item.
    </p>

    <p>
        <strong>Item:</strong> " . htmlspecialchars($itemName) . "<br>
        <strong>Owner:</strong> " . htmlspecialchars($ownerName) . "
    </p>
    ";

    if (!empty($ownerMessage)) {

        $body .= "
        <p>
            <strong>Owner Message:</strong><br>
            " . nl2br(htmlspecialchars($ownerMessage)) . "
        </p>
        ";
    }

    $body .= "
    <p>
        You may contact the owner and return the item again if required.
    </p>
    ";

    return sendEmail(
        $borrowerEmail,
        $borrowerName,
        $subject,
        $body
    );
}
?>