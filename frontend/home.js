const stack = document.getElementById('cardStack');
let currentRotation = 0;
let targetRotation = 0;
let isUsingMouse = false;
let autoRotationSpeed = 0.15; 
let lastMoveTime = Date.now();

// --- Animation Logic ---
window.addEventListener('mousemove', (e) => {
    const screenWidth = window.innerWidth;
    targetRotation = (e.clientX / screenWidth - 0.5) * 360;
    isUsingMouse = true;
    lastMoveTime = Date.now();
});

window.addEventListener('mouseout', () => isUsingMouse = false);

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

// --- Auth Modal Logic ---
function toggleAuth() {
    const modal = document.getElementById('authModal');
    modal.style.display = (modal.style.display === 'flex') ? 'none' : 'flex';
}

function switchAuth(mode) {
    const loginF = document.getElementById('loginForm');
    const signupF = document.getElementById('signupForm');
    const loginT = document.getElementById('loginTab');
    const signupT = document.getElementById('signupTab');
    if(mode === 'login') {
        loginF.classList.remove('hidden'); signupF.classList.add('hidden');
        loginT.classList.add('active'); signupT.classList.remove('active');
    } else {
        loginF.classList.add('hidden'); signupF.classList.remove('hidden');
        signupT.classList.add('active'); loginT.classList.remove('active');
    }
}

// --- NEW: Simplified User Icon UI ---
const updateUIForLoggedInUser = () => {
    const signInBtn = document.querySelector('.btn-signin');
    if (signInBtn) {
        const li = signInBtn.closest('li');
        li.innerHTML = `
            <div class="profile-wrapper" style="position: relative; display: inline-block;">
                <div id="profileTrigger" style="width: 40px; height: 40px; background: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white; font-size: 1.2rem; transition: 0.3s;">
                    <i class="fas fa-user"></i>
                </div>
                <div id="logoutMenu" style="display: none; position: absolute; right: 0; top: 50px; background: white; padding: 10px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); z-index: 1000; min-width: 120px; border: 1px solid #eee;">
                    <button onclick="logoutUser()" style="width: 100%; background: none; border: none; color: #ff4757; cursor: pointer; font-weight: 600; text-align: left; font-size: 0.9rem;">
                        <i class="fas fa-sign-out-alt"></i> Sign Out
                    </button>
                </div>
            </div>
        `;

        // Toggle the logout menu when clicking the icon
        const trigger = document.getElementById('profileTrigger');
        const menu = document.getElementById('logoutMenu');
        
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        });

        // Close menu if user clicks anywhere else on the screen
        window.addEventListener('click', () => {
            if (menu) menu.style.display = 'none';
        });
    }
};

const handleAuth = (e) => {
    e.preventDefault();
    localStorage.setItem("isLoggedIn", "true");
    toggleAuth();
    updateUIForLoggedInUser();
};

function logoutUser() {
    localStorage.removeItem("isLoggedIn");
    location.reload();
}

// Check status on load
window.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem("isLoggedIn") === "true") {
        updateUIForLoggedInUser();
    }
});

document.getElementById('loginForm').onsubmit = handleAuth;
document.getElementById('signupForm').onsubmit = handleAuth;