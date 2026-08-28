// ===========================================
// CampusShare Login JavaScript
// ===========================================

// Show / Hide Password

const password = document.getElementById("password");
const togglePassword = document.getElementById("togglePassword");

togglePassword.addEventListener("click", function(){

    if(password.type === "password"){

        password.type = "text";

        togglePassword.classList.remove("fa-eye");
        togglePassword.classList.add("fa-eye-slash");

    }else{

        password.type = "password";

        togglePassword.classList.remove("fa-eye-slash");
        togglePassword.classList.add("fa-eye");

    }

});

// =============================
// Form Validation
// =============================

const form = document.querySelector("form");

form.addEventListener("submit", function(e){

    const email = document.querySelector("input[name='email']").value.trim();

    const pass = document.querySelector("input[name='password']").value.trim();

    // Email Validation

    const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;

    if(!email.match(emailPattern)){

        alert("Please enter a valid email address.");

        e.preventDefault();

        return;

    }

    // Password Validation

    if(pass.length < 6){

        alert("Password must contain at least 6 characters.");

        e.preventDefault();

        return;

    }

});

// =============================
// Input Animation
// =============================

const inputs = document.querySelectorAll("input");

inputs.forEach(input=>{

    input.addEventListener("focus",function(){

        this.style.transform="scale(1.02)";
        this.style.transition=".3s";

    });

    input.addEventListener("blur",function(){

        this.style.transform="scale(1)";

    });

});

// =============================
// Press Enter Animation
// =============================

document.addEventListener("keydown",function(e){

    if(e.key==="Enter"){

        document.querySelector("button").style.transform="scale(.98)";

        setTimeout(function(){

            document.querySelector("button").style.transform="scale(1)";

        },150);

    }

});

// =============================
// Welcome Animation
// =============================

window.onload=function(){

    const loginBox=document.querySelector(".login-box");

    loginBox.style.opacity="0";
    loginBox.style.transform="translateY(40px)";

    setTimeout(function(){

        loginBox.style.transition=".8s";

        loginBox.style.opacity="1";

        loginBox.style.transform="translateY(0)";

    },200);

};

// =============================
// Console Message
// =============================

console.log("CampusShare Login Loaded Successfully");