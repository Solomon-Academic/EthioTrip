function selectPkg(element, pkgName) {
    // Get price from the card
    const priceText = element.querySelector(".pkg-price").innerText;
    const priceValue = priceText.replace(/[^0-9]/g, "");

    // Save selection to localStorage for payment page
    localStorage.setItem("selectedPackage", pkgName);
    localStorage.setItem("selectedPrice", priceValue);

    // Show toast notification
    const toast = document.getElementById("toast");
    if (toast) {
        toast.innerText = pkgName + " Selected!";
        toast.style.display = "block";
        setTimeout(() => {
            toast.style.display = "none";
        }, 2000);
    }

    // Redirect to payment page
    setTimeout(() => {
        window.location.href = "Payment.html"; 
    }, 800);
}