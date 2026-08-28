// Contact Form

const form=document.getElementById("contactForm");

if(form){

form.addEventListener("submit",function(e){

e.preventDefault();

document.getElementById("successMessage").style.display="block";

form.reset();

setTimeout(function(){

document.getElementById("successMessage").style.display="none";

},3000);

});

}

// Back To Top

const topBtn=document.getElementById("topBtn");

window.addEventListener("scroll",function(){

if(window.scrollY>300){

topBtn.style.display="block";

}
else{

topBtn.style.display="none";

}

});

topBtn.onclick=function(){

window.scrollTo({

top:0,

behavior:"smooth"

});

};