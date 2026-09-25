// ==========================================
// CampusShare Register JavaScript
// ==========================================


// ==========================================
// Get Elements
// ==========================================

const form = document.getElementById("registerForm");

const password = document.getElementById("password");

const confirmPassword =
    document.getElementById("confirmPassword");

const imageInput =
    document.getElementById("profile_image");


// ==========================================
// Password Show / Hide
// ==========================================

function createPasswordToggle(input) {

    const container = input.parentElement;

    const icon = document.createElement("i");

    icon.className = "fa-solid fa-eye";

    icon.style.cursor = "pointer";

    icon.style.marginLeft = "10px";

    icon.title = "Show password";

    container.appendChild(icon);


    icon.addEventListener("click", function () {

        if (input.type === "password") {

            input.type = "text";

            icon.classList.remove("fa-eye");

            icon.classList.add("fa-eye-slash");

            icon.title = "Hide password";

        } else {

            input.type = "password";

            icon.classList.remove("fa-eye-slash");

            icon.classList.add("fa-eye");

            icon.title = "Show password";

        }

    });

}


createPasswordToggle(password);

createPasswordToggle(confirmPassword);


// ==========================================
// Profile Image Preview
// ==========================================

const preview = document.createElement("img");

preview.className = "profile-preview";

preview.alt = "Profile Preview";

imageInput.parentNode.appendChild(preview);


imageInput.addEventListener("change", function () {

    const file = this.files[0];

    if (!file) {

        preview.style.display = "none";

        preview.src = "";

        return;

    }


    // Check file size

    const maxSize = 2 * 1024 * 1024;

    if (file.size > maxSize) {

        alert("Profile image must be less than 2 MB.");

        this.value = "";

        preview.style.display = "none";

        return;

    }


    // Check image type

    const allowedTypes = [
        "image/jpeg",
        "image/png"
    ];

    if (!allowedTypes.includes(file.type)) {

        alert("Only JPG, JPEG and PNG images are allowed.");

        this.value = "";

        preview.style.display = "none";

        return;

    }


    // Display preview

    const imageURL = URL.createObjectURL(file);

    preview.src = imageURL;

    preview.style.display = "block";

});


// ==========================================
// Form Validation
// ==========================================

form.addEventListener("submit", function (event) {

    const name =
        document.getElementById("full_name").value.trim();

    const email =
        document.getElementById("email").value.trim();

    const phone =
        document.getElementById("phone").value.trim();

    const department =
        document.getElementById("department").value;

    const year =
        document.getElementById("year").value;

    const pass =
        password.value;

    const confirm =
        confirmPassword.value;


    // Name validation

    if (name.length < 3) {

        alert(
            "Full name must contain at least 3 characters."
        );

        event.preventDefault();

        return;

    }


    // Email validation

    const emailPattern =
        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email)) {

        alert(
            "Please enter a valid email address."
        );

        event.preventDefault();

        return;

    }


    // Phone validation

    const phonePattern =
        /^[0-9]{10}$/;

    if (!phonePattern.test(phone)) {

        alert(
            "Phone number must contain exactly 10 digits."
        );

        event.preventDefault();

        return;

    }


    // Department

    if (department === "") {

        alert("Please select your department.");

        event.preventDefault();

        return;

    }


    // Year

    if (year === "") {

        alert("Please select your year of study.");

        event.preventDefault();

        return;

    }


    // Password

    if (pass.length < 6) {

        alert(
            "Password must be at least 6 characters."
        );

        event.preventDefault();

        return;

    }


    // Confirm password

    if (pass !== confirm) {

        alert("Passwords do not match.");

        event.preventDefault();

        return;

    }


    // Show submitting message

    const button =
        document.getElementById("registerButton");

    button.disabled = true;

    button.innerHTML =
        '<i class="fa-solid fa-spinner fa-spin"></i> Registering...';

});


// ==========================================
// Password Match Indicator
// ==========================================

confirmPassword.addEventListener("input", function () {

    if (this.value === "") {

        this.style.borderColor = "";

        return;

    }


    if (password.value === this.value) {

        this.style.borderColor = "green";

    } else {

        this.style.borderColor = "red";

    }

});


// ==========================================
// Input Animation
// ==========================================

const inputs =
    document.querySelectorAll(
        "input:not([type='checkbox']):not([type='file']), select"
    );


inputs.forEach(function (input) {

    input.addEventListener("focus", function () {

        this.style.transform = "scale(1.01)";

        this.style.transition = "0.2s";

    });


    input.addEventListener("blur", function () {

        this.style.transform = "scale(1)";

    });

});


// ==========================================
// Welcome Animation
// ==========================================

window.addEventListener("load", function () {

    const formBox =
        document.querySelector(".form-box");

    formBox.style.opacity = "0";

    formBox.style.transform =
        "translateY(30px)";


    setTimeout(function () {

        formBox.style.transition =
            "0.8s ease";

        formBox.style.opacity = "1";

        formBox.style.transform =
            "translateY(0)";

    }, 150);

});


console.log(
    "CampusShare Register Loaded Successfully"
);