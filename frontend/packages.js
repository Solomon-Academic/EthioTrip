function selectPkg(element, pkgName) {
    // 1. CHECK LOGIN STATUS
    const isLoggedIn = localStorage.getItem("isLoggedIn");

    if (isLoggedIn !== "true") {
        // 2. TRIGGER POPUP IF NOT LOGGED IN
        alert("Access Denied: Please sign in or register on the Home page first to book a package!");
        return; // Stop the function here
    }

    // 3. PROCEED IF LOGGED IN
    const priceText = element.querySelector(".pkg-price").innerText;
    const priceValue = priceText.replace(/[^0-9]/g, ""); // Get only the number

    // 4. SAVE SELECTION
    localStorage.setItem("selectedPackage", pkgName);
    localStorage.setItem("selectedPrice", priceValue);

    // 5. SHOW FEEDBACK
    const toast = document.getElementById("toast");
    if (toast) {
        toast.innerText = pkgName + " Selected!";
        toast.style.display = "block";
    }

    // 6. REDIRECT
    setTimeout(() => {
        window.location.href = "Payment.html"; 
    }, 800);
}