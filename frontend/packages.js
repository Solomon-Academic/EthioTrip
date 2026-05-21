function selectPkg(element, pkgName) {
    // Check if destination is selected
    const selectedDest = localStorage.getItem('selectedDestinationName');
    if (!selectedDest) {
        if (confirm('🗺️ Please select a destination first!\n\nClick OK to go to Destinations page.')) {
            window.location.href = 'destination.html';
        }
        return;
    }

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
        window.location.href = "payment.html";
    }, 800);
}