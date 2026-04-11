const stack = document.getElementById('cardStack');
let currentRotation = 0;
let targetRotation = 0;
let isUsingMouse = false;
let autoRotationSpeed = 0.15; 
let lastMoveTime = Date.now();

// --- 3D Carousel Mouse Interaction ---
window.addEventListener('mousemove', (e) => {
    const screenWidth = window.innerWidth;
    targetRotation = (e.clientX / screenWidth - 0.5) * 360;
    isUsingMouse = true;
    lastMoveTime = Date.now();
});

window.addEventListener('mouseout', () => isUsingMouse = false);

// --- Animation Loop ---
function animate() {
    if (Date.now() - lastMoveTime > 2000) isUsingMouse = false;
    if (isUsingMouse) {
        currentRotation += (targetRotation - currentRotation) * 0.05;
    } else {
        currentRotation += autoRotationSpeed;
    }
    stack.style.transform = `rotateY(${currentRotation}deg)`;
    requestAnimationFrame(animate);
}
animate();