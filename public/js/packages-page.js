(function () {
    const params = new URLSearchParams(window.location.search);
    const destinationId = params.get('destination_id') || localStorage.getItem('selectedDestinationId');
    const list = document.getElementById('packageList');
    const loading = document.getElementById('packagesLoading');
    const empty = document.getElementById('packagesEmpty');
    const errorBox = document.getElementById('packagesError');
    const subtitle = document.getElementById('packagesSubtitle');
    const toast = document.getElementById('toast');

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function showToastMessage(message, duration = 2000) {
        if (!toast) return;
        toast.textContent = message;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, duration);
    }

    function parseDurationDays(durationStr) {
        const d = String(durationStr || '').toLowerCase();
        if (d.includes('per day') || d.includes('/ day') || d.includes('daily')) {
            return 1;
        }
        const match = d.match(/(\d+)/);
        return match ? Math.max(1, parseInt(match[1], 10)) : 3;
    }

    function getPerDayRate(pkg) {
        const price = Number(pkg.price) || 0;
        const duration = String(pkg.duration || '').toLowerCase();
        if (duration.includes('per day') || duration.includes('/ day') || duration.includes('daily')) {
            return price;
        }
        const days = parseDurationDays(pkg.duration);
        return price / Math.max(days, 1);
    }

    function persistDestination(dest) {
        if (!dest) return;
        localStorage.setItem('selectedDestinationId', String(dest.id));
        localStorage.setItem('selectedDestinationName', dest.name || '');
        if (dest.location) {
            localStorage.setItem('selectedDestinationLocation', dest.location);
        }
    }

    function persistPackage(pkg) {
        const perDay = getPerDayRate(pkg);
        const durationDays = parseDurationDays(pkg.duration);
        const isPerDay = String(pkg.duration || '').toLowerCase().includes('per day')
            || String(pkg.duration || '').toLowerCase().includes('/ day');

        localStorage.setItem('selectedPackageId', String(pkg.id));
        localStorage.setItem('selectedPackage', pkg.name);
        localStorage.setItem('selectedPrice', String(Number(pkg.price) || 0));
        localStorage.setItem('pricePerDay', String(perDay));
        localStorage.setItem('packageDurationDays', String(durationDays));
        localStorage.setItem('packageDurationLabel', pkg.duration || '');
        localStorage.setItem('packageIsPerDay', isPerDay ? '1' : '0');
    }

    function selectPackage(pkg) {
        persistPackage(pkg);
        showToastMessage('✓ ' + pkg.name + ' selected. Opening payment...');
        window.location.href = ETHIOTRIP.page('/payment');
    }

    function renderCard(pkg) {
        const price = Number(pkg.price) || 0;
        const perDay = getPerDayRate(pkg);
        const isPerDay = String(pkg.duration || '').toLowerCase().includes('per day')
            || String(pkg.duration || '').toLowerCase().includes('/ day');
        const priceLabel = isPerDay
            ? `$${price.toFixed(2)}<span> / day</span>`
            : `$${price.toFixed(2)}<span> package</span>`;

        const features = (pkg.features || []).slice(0, 4);
        const featureHtml = features.map((f) =>
            `<li><i class="fas fa-check"></i> ${escapeHtml(f)}</li>`
        ).join('');

        const card = document.createElement('div');
        card.className = 'pkg-card';
        card.innerHTML = `
            <span class="pkg-tag">${escapeHtml(pkg.category || 'package')}</span>
            <h3>${escapeHtml(pkg.name)}</h3>
            <div class="pkg-price">${priceLabel}</div>
            <div class="pkg-per-day"><i class="fas fa-calendar-day"></i> ~$${perDay.toFixed(2)} per day (est.)</div>
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
        if (!destinationId) {
            loading.style.display = 'none';
            errorBox.innerHTML = 'Please <a href="' + ETHIOTRIP.page('/destination') + '">select a destination</a> first.';
            errorBox.style.display = 'block';
            return;
        }

        try {
            const data = await EthioTripApi.listPackages(destinationId);

            if (data.success === false) {
                throw new Error(data.message || 'Could not load packages.');
            }

            loading.style.display = 'none';

            if (data.destination) {
                persistDestination(data.destination);
                subtitle.textContent = 'All packages available for ' + data.destination.name
                    + ' — choose one to continue to payment.';
            }

            const packages = Array.isArray(data.packages) ? data.packages : [];
            if (!packages.length) {
                empty.style.display = 'block';
                return;
            }

            packages.forEach((pkg) => list.appendChild(renderCard(pkg)));
        } catch (err) {
            loading.style.display = 'none';
            errorBox.textContent = err.message || 'Could not load packages.';
            errorBox.style.display = 'block';
            console.error('Packages load error:', err);
        }
    }

    load();
})();
