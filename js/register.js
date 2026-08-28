// ==========================================
// CampusShare Register JavaScript
// ==========================================

// Show/Hide Password Feature

const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirmPassword");

const passwordContainer = password.parentElement;
const confirmContainer = confirmPassword.parentElement;

const eye1 = document.createElement("i");
eye1.className = "fa-solid fa-eye";
eye1.style.cursor = "pointer";
eye1.style.marginLeft = "10px";

const eye2 = document.createElement("i");
eye2.className = "fa-solid fa-eye";
eye2.style.cursor = "pointer";
eye2.style.marginLeft = "10px";

passwordContainer.appendChild(eye1);
confirmContainer.appendChild(eye2);

// Toggle Password

eye1.addEventListener("click", function(){

    if(password.type === "password"){

        password.type = "text";
        eye1.classList.replace("fa-eye","fa-eye-slash");

    }else{

        password.type = "password";
        eye1.classList.replace("fa-eye-slash","fa-eye");

    }

});

// Toggle Confirm Password

eye2.addEventListener("click", function(){

    if(confirmPassword.type === "password"){

        confirmPassword.type = "text";
        eye2.classList.replace("fa-eye","fa-eye-slash");

    }else{

        confirmPassword.type = "password";
        eye2.classList.replace("fa-eye-slash","fa-eye");

    }

});

// ==========================
// Form Validation
// ==========================

const form = document.querySelector("form");

form.addEventListener("submit",function(e){

    const name = document.querySelector("input[name='full_name']").value.trim();

    const email = document.querySelector("input[name='email']").value.trim();

    const phone = document.querySelector("input[name='phone']").value.trim();

    const pass = password.value;

    const confirm = confirmPassword.value;

    const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;

    const phonePattern = /^[0-9]{10}$/;

    if(name.length < 3){

        alert("Full name must contain at least 3 characters.");
        e.preventDefault();
        return;

    }

    if(!email.match(emailPattern)){

        alert("Enter a valid email address.");
        e.preventDefault();
        return;

    }

    if(!phone.match(phonePattern)){

        alert("Phone number must contain exactly 10 digits.");
        e.preventDefault();
        return;

    }

    if(pass.length < 6){

        alert("Password must be at least 6 characters.");
        e.preventDefault();
        return;

    }

    if(pass !== confirm){

        alert("Passwords do not match.");
        e.preventDefault();
        return;

    }

});

// ==========================
// Profile Image Preview
// ==========================

const imageInput = document.querySelector("input[name='profile_image']");

const preview = document.createElement("img");

preview.style.width = "120px";
preview.style.height = "120px";
preview.style.borderRadius = "50%";
preview.style.marginTop = "15px";
preview.style.display = "none";
preview.style.objectFit = "cover";

imageInput.parentNode.appendChild(preview);

imageInput.addEventListener("change",function(){

    const file = this.files[0];

    if(file){

        preview.src = URL.createObjectURL(file);

        preview.style.display = "block";

    }

});

// ==========================
// Input Animation
// ==========================

const inputs = document.querySelectorAll("input, select");

inputs.forEach(input=>{

    input.addEventListener("focus",function(){

        this.style.transform = "scale(1.02)";
        this.style.transition = ".3s";

    });

    input.addEventListener("blur",function(){

        this.style.transform = "scale(1)";

    });

});

// ==========================
// Welcome Animation
// ==========================

window.onload=function(){

    const formBox = document.querySelector(".form-box");

    formBox.style.opacity="0";
    formBox.style.transform="translateY(40px)";

    setTimeout(function(){

        formBox.style.transition=".8s";

        formBox.style.opacity="1";

        formBox.style.transform="translateY(0)";

    },200);

};

console.log("CampusShare Register Loaded Successfully");