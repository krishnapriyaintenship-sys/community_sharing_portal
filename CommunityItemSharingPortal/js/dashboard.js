// ==========================================
// CampusShare Dashboard JavaScript
// ==========================================

// Welcome Message

console.log("CampusShare Dashboard Loaded Successfully");

// ==========================================
// Card Hover Animation
// ==========================================

const cards = document.querySelectorAll(".card");

cards.forEach(card => {

    card.addEventListener("mouseenter", function(){

        this.style.transform = "translateY(-8px) scale(1.03)";

    });

    card.addEventListener("mouseleave", function(){

        this.style.transform = "translateY(0) scale(1)";

    });

});

// ==========================================
// Live Search (Recently Added Items)
// ==========================================

const searchInput = document.querySelector(".search-box input");

if(searchInput){

searchInput.addEventListener("keyup", function(){

    let value = this.value.toLowerCase();

    let items = document.querySelectorAll(".item");

    items.forEach(item=>{

        let text = item.innerText.toLowerCase();

        if(text.indexOf(value)>-1){

            item.style.display="block";

        }else{

            item.style.display="none";

        }

    });

});

}

// ==========================================
// Notification
// ==========================================

const bell = document.querySelector(".notification");

if(bell){

bell.addEventListener("click",function(){

    alert("No new notifications.");

});

}

// ==========================================
// Animated Statistics
// ==========================================

const numbers=document.querySelectorAll(".card h2");

numbers.forEach(number=>{

    let target=parseInt(number.innerText);

    let count=0;

    let speed=Math.ceil(target/50);

    let interval=setInterval(function(){

        count+=speed;

        if(count>=target){

            count=target;

            clearInterval(interval);

        }

        number.innerText=count;

    },30);

});

// ==========================================
// Current Date
// ==========================================

const today=new Date();

console.log(today.toDateString());