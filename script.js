let currentSlide = 0; // Track the current slide index
const slides = document.querySelectorAll(".special-card"); // Select all slides
const totalSlides = slides.length; // Get the total number of slides

function showSlide(index) {
  slides.forEach((slide, i) => {
    slide.style.display = i === index ? "block" : "none"; // Show the current slide
  });
}

function nextSlide() {
  currentSlide = (currentSlide + 1) % totalSlides; // Increment slide index
  showSlide(currentSlide); // Show the next slide
}

function prevSlide() {
  currentSlide = (currentSlide - 1 + totalSlides) % totalSlides; // Decrement slide index
  showSlide(currentSlide); // Show the previous slide
}

// Show the first slide initially
showSlide(currentSlide);

// Change slide every 10 seconds
setInterval(nextSlide, 10000);

// Login and Signup functionality

function showLogin(type) {
  document.getElementById("userLogin").classList.add("hidden");
  document.getElementById("userSignup").classList.add("hidden");
  document.getElementById("employeeLogin").classList.add("hidden");
  document.getElementById("employeeSignup").classList.add("hidden");

  if (type === "user") {
    document.getElementById("userLogin").classList.remove("hidden");
  } else if (type === "employee") {
    document.getElementById("employeeLogin").classList.remove("hidden");
  }
}

function showSignup(type) {
  document.getElementById("userLogin").classList.add("hidden");
  document.getElementById("userSignup").classList.add("hidden");
  document.getElementById("employeeLogin").classList.add("hidden");
  document.getElementById("employeeSignup").classList.add("hidden");

  if (type === "user") {
    document.getElementById("userSignup").classList.remove("hidden");
  } else if (type === "employee") {
    document.getElementById("employeeSignup").classList.remove("hidden");
  }
}

//menu editor

function editMenuItem(item) {
  document.getElementById("edit_item_id").value = item.item_id;
  document.getElementById("edit_name").value = item.name;
  document.getElementById("edit_description").value = item.description;
  document.getElementById("edit_price").value = item.price;
  document.getElementById("edit_category").value = item.category;
  document.getElementById("edit_image_url").value = item.image_url;
  document.getElementById("editMenuItemForm").classList.remove("hidden");
}
