// Function to handle the counting animation for statistics
const counters = document.querySelectorAll('.stat-number');

counters.forEach(counter => {
    const updateCount = () => {
        const target = +counter.getAttribute('data-target');
        const current = +counter.innerText;
        // Adjust the divisor (100) to make the animation faster or slower
        const increment = Math.ceil(target / 100);

        if (current < target) {
            counter.innerText = current + increment;
            setTimeout(updateCount, 30);
        } else {
            counter.innerText = target;
        }
    };
    updateCount();
});

// Function to handle contact form submission
document.querySelector('.contact-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // In a real application, you would use fetch() or XMLHttpRequest here
    alert("Thank you for contacting EthioTrip! We will reach out soon.");
    
    // Reset form fields after submission
    this.reset();
});