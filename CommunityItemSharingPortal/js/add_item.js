// ==========================================
// CampusShare Add Item JavaScript
// ==========================================

// Image Preview

const imageInput = document.querySelector("input[name='item_image']");

const previewContainer = document.createElement("div");
previewContainer.className = "preview-container";

const previewImage = document.createElement("img");

previewContainer.appendChild(previewImage);

imageInput.parentNode.appendChild(previewContainer);

imageInput.addEventListener("change", function(){

    const file = this.files[0];

    if(file){

        // Check file type

        const allowed = ["image/jpeg","image/jpg","image/png"];

        if(!allowed.includes(file.type)){

            alert("Please upload JPG, JPEG or PNG image.");

            this.value="";

            return;

        }

        // Check file size (2MB)

        if(file.size > 2 * 1024 * 1024){

            alert("Image size must be below 2MB.");

            this.value="";

            return;

        }

        previewImage.src = URL.createObjectURL(file);

        previewImage.style.display="block";

    }

});

// ==========================================
// Date Validation
// ==========================================

const fromDate = document.querySelector("input[name='available_from']");
const untilDate = document.querySelector("input[name='available_until']");

untilDate.addEventListener("change", function(){

    if(fromDate.value=="" || untilDate.value=="")
        return;

    if(untilDate.value < fromDate.value){

        alert("Available Until date must be after Available From date.");

        untilDate.value="";

    }

});

// ==========================================
// Form Validation
// ==========================================

const form = document.querySelector("form");

form.addEventListener("submit", function(e){

    const itemName = document.querySelector("input[name='item_name']").value.trim();

    const description = document.querySelector("textarea").value.trim();

    if(itemName.length < 3){

        alert("Item name should contain at least 3 characters.");

        e.preventDefault();

        return;

    }

    if(description.length < 15){

        alert("Description should contain at least 15 characters.");

        e.preventDefault();

        return;

    }

});

// ==========================================
// Input Animation
// ==========================================

const inputs = document.querySelectorAll("input, textarea, select");

inputs.forEach(input=>{

    input.addEventListener("focus",function(){

        this.style.transform="scale(1.02)";

        this.style.transition=".3s";

    });

    input.addEventListener("blur",function(){

        this.style.transform="scale(1)";

    });

});

// ==========================================
// Page Animation
// ==========================================

window.onload=function(){

    const formContainer=document.querySelector(".form-container");

    formContainer.style.opacity="0";

    formContainer.style.transform="translateY(40px)";

    setTimeout(function(){

        formContainer.style.transition=".7s";

        formContainer.style.opacity="1";

        formContainer.style.transform="translateY(0)";

    },200);

};

console.log("Add Item Page Loaded Successfully");