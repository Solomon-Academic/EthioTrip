(function () {
    const params = new URLSearchParams(window.location.search);
    const destinationId = params.get('destination_id') || localStorage.getItem('selectedDestinationId');
    const list = document.getElementById('packageList');
    const loading = document.getElementById('packagesLoading');
    const empty = document.getElementById('packagesEmpty');
    const errorBox = document.getElementById('packagesError');
    const subtitle = document.getElementById('packagesSubtitle');
    const authModal = document.getElementById('authModal');
    const toast = document.getElementById('toast');
    let selectedPkgData = null;

    if (!destinationId) {
        loading.style.display = 'none';
        errorBox.innerHTML = 'Please <a href="' + ETHIOTRIP.page('/destination') + '">select a destination</a> first.';
        errorBox.style.display = 'block';
        return;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function showToastMessage(message, duration = 2000) {
        toast.textContent = message;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, duration);
    }

    function persistPackage(pkg) {
        const perDay = Math.round((pkg.price / 3) * 100) / 100;
        localStorage.setItem('selectedPackageId', String(pkg.id));
        localStorage.setItem('selectedPackage', pkg.name);
        localStorage.setItem('selectedPrice', String(pkg.price));
        localStorage.setItem('pricePerDay', String(perDay));
        localStorage.setItem('packageDurationDays', '3');
    }

    window.openAuthModal = function (pkgData) {
        selectedPkgData = pkgData;
        authModal.style.display = 'flex';
    };
    window.closeAuthModal = function () {
        authModal.style.display = 'none';
        selectedPkgData = null;
    };
    window.redirectToLogin = function () {
        if (selectedPkgData) persistPackage(selectedPkgData);
        localStorage.setItem('returnAfterLogin', '/payment');
        window.location.href = ETHIOTRIP.page('/login');
    };
    window.redirectToRegister = function () {
        if (selectedPkgData) persistPackage(selectedPkgData);
        localStorage.setItem('returnAfterLogin', '/payment');
        window.location.href = ETHIOTRIP.page('/register');
    };
    window.continueAsGuest = function () {
        if (!selectedPkgData) return;
        persistPackage(selectedPkgData);
        window.location.href = ETHIOTRIP.page('/payment');
    };

    async function selectPackage(pkg) {
        const pkgData = { ...pkg, perDay: Math.round((pkg.price / 3) * 100) / 100 };
        try {
            const data = await EthioTripApi.checkLogin();
            persistPackage(pkg);
            if (data.logged_in) {
                showToastMessage('✓ ' + pkg.name + ' selected! Redirecting...');
                setTimeout(() => { window.location.href = ETHIOTRIP.page('/payment'); }, 600);
            } else {
                openAuthModal(pkgData);
            }
        } catch {
            openAuthModal(pkgData);
        }
    }

    function renderCard(pkg) {
        const features = (pkg.features || []).slice(0, 4);
        const featureHtml = features.map((f) =>
            `<li><i class="fas fa-check"></i> ${escapeHtml(f)}</li>`
        ).join('');
        const perDay = Math.round((pkg.price / 3) * 100) / 100;
        const card = document.createElement('div');
        card.className = 'pkg-card';
        card.innerHTML = `
            <span class="pkg-tag">${escapeHtml(pkg.category || 'package')}</span>
            <h3>${escapeHtml(pkg.name)}</h3>
            <div class="pkg-price">$${pkg.price.toFixed(2)}<span> base rate</span></div>
            <div class="pkg-per-day"><i class="fas fa-calendar-day"></i> ~$${perDay} per day</div>
            <span class="pkg-duration"><i class="far fa-clock"></i> ${escapeHtml(pkg.duration || 'Flexible')}</span>
            <p style="color:#636e72;font-size:0.85rem;margin-bottom:12px;">${escapeHtml(pkg.description || '')}</p>
            <ul class="pkg-features">${featureHtml}</ul>
            <button type="button" class="btn-pkg">Select Package</button>
        `;
        card.querySelector('.btn-pkg').addEventListener('click', (e) => {
            e.stopPropagation();
            selectPackage(pkg);
        });
        card.addEventListener('click', () => selectPackage(pkg));
        return card;
    }

    async function load() {
        try {
            const data = await EthioTripApi.listPackages(destinationId);
            loading.style.display = 'none';
            if (data.destination) {
                subtitle.textContent = 'Packages for ' + data.destination.name;
                localStorage.setItem('selectedDestinationId', String(data.destination.id));
                localStorage.setItem('selectedDestinationName', data.destination.name);
            }
            const packages = data.packages || [];
            if (!packages.length) {
                empty.style.display = 'block';
                return;
            }
            packages.forEach((pkg) => list.appendChild(renderCard(pkg)));
        } catch (err) {
            loading.style.display = 'none';
            errorBox.textContent = err.message || 'Could not load packages.';
            errorBox.style.display = 'block';
        }
    }

    window.onclick = function (event) {
        if (event.target === authModal) closeAuthModal();
    };

    load();
})();
