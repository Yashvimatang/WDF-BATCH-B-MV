let signupForm = document.getElementById("signupForm");
signupForm.addEventListener("submit", function (event) {
  event.preventDefault();
  let fullname = document.getElementById("fullname").value.trim();
  let phone = document.getElementById("phone").value.trim();
  let email = document.getElementById("email").value.trim();
  let password = document.getElementById("password").value;
  let confirmPassword = document.getElementById("confirmPassword").value;
  let terms = document.getElementById("terms").checked;
  if (fullname === "") {
    alert("Please enter your full name.");
    return;
  }
  if (phone.length !== 10) {
    alert("Please enter a valid 10-digit mobile number.");
    return;
  }
  if (!email.includes("@")) {
    alert("Please enter a valid email address.");
    return;
  }
  if (password.length < 8) {
    alert("Password must contain at least 8 characters.");
    return;
  }
  if (!/[A-Z]/.test(password)) {
    alert("Password must contain at least one uppercase letter.");
    return;
  }
  if (!/[a-z]/.test(password)) {
    alert("Password must contain at least one lowercase letter.");
    return;
  }
  if (!/[0-9]/.test(password)) {
    alert("Password must contain at least one number.");
    return;
  }
  if (!/[!@#$%^&*]/.test(password)) {
    alert("Password must contain at least one special character.");
    return;
  }
  if (password !== confirmPassword) {
    alert("Passwords do not match.");
    return;
  }
  if (!terms) {
    alert("Please accept the Terms & Conditions.");

    return;
  }
  alert("Congratulations! Your account has been created successfully.");
  window.location.href = "index.html";
});

let showPassword = document.getElementById("showPassword");
showPassword.addEventListener("change", function () {
  let password = document.getElementById("password");
  let confirmPassword = document.getElementById("confirmPassword");
  if (showPassword.checked) {
    password.type = "text";
    confirmPassword.type = "text";
  } else {
    password.type = "password";
    confirmPassword.type = "password";
  }
});
