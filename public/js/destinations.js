(function () {
    const grid = document.getElementById('destinationsGrid');
    const loading = document.getElementById('destinationsLoading');
    const empty = document.getElementById('destinationsEmpty');
    const errorBox = document.getElementById('destinationsError');

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function renderCard(dest) {
        const card = document.createElement('article');
        card.className = 'dest-card';
        card.innerHTML = `
            <img src="${escapeHtml(dest.image_url)}" alt="${escapeHtml(dest.name)}" loading="lazy">
            <div class="dest-content">
                <h3>${escapeHtml(dest.name)}</h3>
                <div class="dest-location"><i class="fas fa-map-marker-alt"></i> ${escapeHtml(dest.location)}</div>
                <p class="dest-desc">${escapeHtml(dest.short_description)}</p>
                <div class="best-time"><i class="far fa-calendar-alt"></i> Best: ${escapeHtml(dest.best_time || 'All year')}</div>
                <a href="${ETHIOTRIP.page('/destination/details?id=' + dest.id)}" class="btn-select">View Details</a>
            </div>
        `;
        return card;
    }

    async function load() {
        try {
            const data = await EthioTripApi.listDestinations();
            loading.style.display = 'none';
            const list = data.destinations || [];
            if (!list.length) {
                empty.style.display = 'block';
                return;
            }
            list.forEach((dest) => grid.appendChild(renderCard(dest)));
        } catch (err) {
            loading.style.display = 'none';
            errorBox.textContent = err.message || 'Could not load destinations.';
            errorBox.style.display = 'block';
        }
    }

    load();
})();
