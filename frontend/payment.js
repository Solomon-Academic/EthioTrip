// ==========================================
// 1. AUTO-FILL LOGIC (FROM PACKAGES PAGE)
// ==========================================
window.addEventListener('DOMContentLoaded', () => {
    const savedPrice = localStorage.getItem("selectedPrice");
    const inputField = document.getElementById('inputAmount');
    
    if (savedPrice && inputField) {
        inputField.value = savedPrice;
        localStorage.removeItem("selectedPrice");
    }
});

// ==========================================
// 2. ETHIOTRIP DATABASE & GLOBAL ELEMENTS
// ==========================================
const ethioTripUsers = [
    { name: "Aster Alemu", trips: 15, discount: 0.08 },
    { name: "Zerihun Berhanu", trips: 9, discount: 0.08 },
    { name: "Suheil Ali", trips: 6, discount: 0.08 },
    { name: "Solomon Kahsay", trips: 4, discount: 0.05 },
    { name: "Yordanos Abebe", trips: 3, discount: 0.05 },
    { name: "Zemedkun Workalem", trips: 1, discount: 0.00 }
];

const pAmount = document.getElementById('phase-amount');
const pCheckout = document.getElementById('phase-checkout');
const pReceipt = document.getElementById('phase-receipt');

const sSub = document.getElementById('sum-sub');
const sTax = document.getElementById('sum-tax');
const sTotal = document.getElementById('sum-total');
const sDisc = document.getElementById('sum-discount');
const discRow = document.getElementById('discount-row');

// ==========================================
// 3. PHASE 1: INITIALIZATION & LOYALTY (UPDATED)
// ==========================================
document.getElementById('btnProceed').onclick = () => {
    const amount = parseFloat(document.getElementById('inputAmount').value);
    // FIXED: Reads from input field instead of prompt
    const userName = document.getElementById('inputUserName').value.trim();
    
    if (!amount || amount <= 0) return alert("Enter a valid amount.");
    if (!userName) return alert("Full Name is required to check for discounts.");

    // Check loyalty database
    const user = ethioTripUsers.find(u => u.name.toLowerCase() === userName.toLowerCase());
    const rate = user ? user.discount : 0;

    // Calculations
    const discAmt = amount * rate;
    const taxableAmt = amount - discAmt;
    const tax = taxableAmt * 0.10; 
    const finalTotal = taxableAmt + tax;

    // Update Invoice Summary UI
    sSub.innerText = `$${amount.toFixed(2)}`;
    if (rate > 0) {
        discRow.style.display = 'flex';
        sDisc.innerText = `-$${discAmt.toFixed(2)}`;
    } else {
        discRow.style.display = 'none';
    }
    sTax.innerText = `$${tax.toFixed(2)}`;
    sTotal.innerText = `$${finalTotal.toFixed(2)}`;

    // Sync name to phase 2 forms
    document.getElementById('cardName').value = userName;

    // Switch Phase
    pAmount.style.display = 'none';
    pCheckout.style.display = 'block';
    document.getElementById('step2').classList.add('active');

    if (rate > 0) {
        alert(`Member Recognized: ${userName}. A ${rate*100}% Loyalty Discount has been applied!`);
    }
};

// ==========================================
// 4. PHASE 2: LOGICAL TAB SWITCHING
// ==========================================
const tabs = document.querySelectorAll('.tab-btn');
const views = { 
    card: document.getElementById('form-card'), 
    telebirr: document.getElementById('view-telebirr'), 
    paypal: document.getElementById('view-paypal'), 
    mobile: document.getElementById('view-mobile'), 
    cash: document.getElementById('view-cash') 
};

tabs.forEach(t => {
    t.onclick = () => {
        tabs.forEach(btn => btn.classList.remove('active'));
        t.classList.add('active');
        Object.values(views).forEach(v => { if(v) v.style.display = 'none'; });
        const method = t.dataset.method;
        if(views[method]) views[method].style.display = 'block';
    };
});

// ==========================================
// 5. PHASE 3: LOGICAL RECEIPT GENERATION
// ==========================================
function finalize(method, userReference = null) {
    const name = document.getElementById('cardName').value;
    const selectedPkg = localStorage.getItem("selectedPackage") || "EthioTrip Booking Service";
    const date = new Date().toLocaleString('en-GB', { 
        day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' 
    });
    
    const finalRef = userReference || "ET-" + Math.floor(Math.random()*900000 + 100000);

    pCheckout.style.display = 'none';
    pReceipt.style.display = 'block';
    document.getElementById('step3').classList.add('active');

    pReceipt.innerHTML = `
        <div class="receipt">
            <div style="background:#27ae60; color:white; padding:15px; border-radius:10px 10px 0 0; margin:-50px -50px 30px -50px;">
                <h2 style="margin:0;">Payment Confirmed</h2>
            </div>
            <p>Your journey with EthioTrip is officially booked, <strong>${name}</strong>.</p>
            <div style="text-align:left; background:#f9f9f9; padding:25px; border-radius:12px; margin:20px 0; line-height: 1.8; border: 1px solid #eee;">
                <p><strong>Traveler:</strong> ${name}</p>
                <p><strong>Package:</strong> ${selectedPkg}</p>
                <p><strong>Method:</strong> ${method}</p>
                <p><strong>Total Paid:</strong> <span style="color:#1a237e; font-weight:bold;">${sTotal.innerText}</span></p>
                <p><strong>Transaction Ref:</strong> ${finalRef}</p>
                <p><strong>Date:</strong> ${date}</p>
            </div>
            <button class="btn-primary" style="width:auto; padding:12px 50px;" onclick="window.print()">Print Receipt</button>
            <button class="btn-primary" style="width:auto; padding:12px 50px; background:#2d3436; margin-left:10px;" onclick="location.reload()">Finish</button>
        </div>
    `;
    localStorage.removeItem("selectedPackage");
}

// ==========================================
// 6. EVENT BINDINGS
// ==========================================
document.getElementById('form-card').onsubmit = (e) => {
    e.preventDefault();
    finalize('Credit Card');
};

document.getElementById('btnTelebirr').onclick = () => {
    const phone = document.getElementById('telePhone').value;
    if(phone.length < 10) return alert("Please enter a valid 10-digit Telebirr number.");
    finalize('Telebirr Mobile');
};

document.getElementById('btnPaypal').onclick = () => {
    const ref = document.getElementById('paypalRef').value || "P-ID-" + Date.now();
    finalize('PayPal', ref);
};

document.getElementById('btnMobile').onclick = () => {
    const ref = document.getElementById('bankRef').value;
    if(!ref) return alert("Please enter the Bank Transaction Reference number.");
    finalize('Bank Transfer', ref);
};

document.getElementById('btnCash').onclick = () => finalize('Cash at Office');

document.getElementById('cardNumber').oninput = (e) => {
    let v = e.target.value.replace(/\D/g, '').match(/.{1,4}/g);
    e.target.value = v ? v.join(' ') : '';
};