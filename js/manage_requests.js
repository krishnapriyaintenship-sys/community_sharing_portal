document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       SEARCH
    ===================================================== */

    const searchInput =
        document.getElementById("requestSearch");

    const clearSearch =
        document.getElementById("clearSearch");

    const statusFilter =
        document.getElementById("statusFilter");

    const cards =
        document.querySelectorAll(".request-card");

    const noResults =
        document.getElementById("noResults");


    function filterRequests() {

        if (!searchInput) return;

        const search =
            searchInput.value
                .toLowerCase()
                .trim();

        const status =
            statusFilter.value.toLowerCase();

        let visibleCount = 0;


        cards.forEach(function (card) {

            const cardSearch =
                card.dataset.search || "";

            const cardStatus =
                card.dataset.status || "";


            const matchesSearch =
                cardSearch.includes(search);

            const matchesStatus =
                status === "all" ||
                cardStatus === status;


            if (matchesSearch && matchesStatus) {

                card.style.display = "";

                visibleCount++;

            } else {

                card.style.display = "none";

            }

        });


        if (search.length > 0) {
            clearSearch.style.display = "block";
        } else {
            clearSearch.style.display = "none";
        }


        if (visibleCount === 0) {

            noResults.style.display = "block";

        } else {

            noResults.style.display = "none";

        }

    }


    if (searchInput) {

        searchInput.addEventListener(
            "input",
            filterRequests
        );

    }


    if (statusFilter) {

        statusFilter.addEventListener(
            "change",
            filterRequests
        );

    }


    if (clearSearch) {

        clearSearch.addEventListener(
            "click",
            function () {

                searchInput.value = "";

                filterRequests();

                searchInput.focus();

            }
        );

    }


    /* =====================================================
       EXPAND / COLLAPSE DETAILS
    ===================================================== */

    const detailButtons =
        document.querySelectorAll(".details-toggle");


    detailButtons.forEach(function (button) {

        button.addEventListener(
            "click",
            function () {

                const card =
                    button.closest(".request-card");


                if (!card) return;


                card.classList.toggle("expanded");


                if (card.classList.contains("expanded")) {

                    button.querySelector("span").textContent =
                        "Hide Details";

                } else {

                    button.querySelector("span").textContent =
                        "Details";

                }

            }
        );

    });


    /* =====================================================
       MOBILE SIDEBAR
    ===================================================== */

    const menuButton =
        document.getElementById("mobileMenuBtn");

    const sidebar =
        document.querySelector(".sidebar");


    if (menuButton && sidebar) {

        menuButton.addEventListener(
            "click",
            function () {

                sidebar.classList.toggle("open");

            }
        );

    }


    /* =====================================================
       CLOSE SIDEBAR WHEN CLICKING OUTSIDE
    ===================================================== */

    document.addEventListener(
        "click",
        function (event) {

            if (
                window.innerWidth <= 800 &&
                sidebar &&
                sidebar.classList.contains("open") &&
                !sidebar.contains(event.target) &&
                !menuButton.contains(event.target)
            ) {

                sidebar.classList.remove("open");

            }

        }
    );


    /* =====================================================
       ESCAPE KEY
    ===================================================== */

    document.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Escape") {

                closeConfirmModal();

                closeRejectModal();

                if (
                    sidebar &&
                    sidebar.classList.contains("open")
                ) {

                    sidebar.classList.remove("open");

                }

            }

        }
    );


    /* =====================================================
       ALERT AUTO CLOSE
    ===================================================== */

    const alert =
        document.getElementById("alertMessage");


    if (alert) {

        setTimeout(
            function () {

                alert.style.opacity = "0";

                alert.style.transform =
                    "translateY(-8px)";

                alert.style.transition =
                    "all .3s ease";


                setTimeout(
                    function () {

                        alert.remove();

                    },
                    300
                );

            },
            5000
        );

    }

});


/* =====================================================
   ALERT CLOSE
===================================================== */

function closeAlert() {

    const alert =
        document.getElementById("alertMessage");

    if (alert) {

        alert.remove();

    }

}


/* =====================================================
   CONFIRM MODAL
===================================================== */

let pendingForm = null;


function showConfirm(
    button,
    title,
    text
) {

    pendingForm =
        button.closest("form");


    document.getElementById(
        "confirmTitle"
    ).textContent = title;


    document.getElementById(
        "confirmText"
    ).textContent = text;


    const modal =
        document.getElementById(
            "confirmModal"
        );


    modal.classList.add("show");


    document.body.style.overflow =
        "hidden";


    return false;

}


/* =====================================================
   CONFIRM ACTION
===================================================== */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const confirmButton =
            document.getElementById(
                "confirmButton"
            );


        if (confirmButton) {

            confirmButton.addEventListener(
                "click",
                function () {

                    if (pendingForm) {

                        pendingForm.submit();

                        pendingForm = null;

                    }

                    closeConfirmModal();

                }
            );

        }

    }
);


/* =====================================================
   CLOSE CONFIRM
===================================================== */

function closeConfirmModal() {

    const modal =
        document.getElementById(
            "confirmModal"
        );


    if (modal) {

        modal.classList.remove("show");

    }

    document.body.style.overflow =
        "";

    pendingForm = null;

}


/* =====================================================
   REJECT MODAL
===================================================== */

function openRejectModal(requestId) {

    const modal =
        document.getElementById(
            "rejectModal"
        );


    const requestInput =
        document.getElementById(
            "rejectRequestId"
        );


    const reason =
        document.getElementById(
            "rejectReason"
        );


    requestInput.value =
        requestId;


    reason.value = "";


    modal.classList.add("show");

    document.body.style.overflow =
        "hidden";


    setTimeout(
        function () {

            reason.focus();

        },
        200
    );

}


/* =====================================================
   CLOSE REJECT MODAL
===================================================== */

function closeRejectModal() {

    const modal =
        document.getElementById(
            "rejectModal"
        );


    if (modal) {

        modal.classList.remove("show");

    }

    document.body.style.overflow =
        "";

}


/* =====================================================
   CLICK OUTSIDE MODAL
===================================================== */

document.addEventListener(
    "click",
    function (event) {

        const confirmModal =
            document.getElementById(
                "confirmModal"
            );

        const rejectModal =
            document.getElementById(
                "rejectModal"
            );


        if (
            event.target === confirmModal
        ) {

            closeConfirmModal();

        }


        if (
            event.target === rejectModal
        ) {

            closeRejectModal();

        }

    }
);