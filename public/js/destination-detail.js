(function () {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    const loading = document.getElementById('detailLoading');
    const errorBox = document.getElementById('detailError');
    const content = document.getElementById('detailContent');

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    function renderHighlights(highlights) {
        if (!highlights || !highlights.length) return '';
        const items = highlights.map((h) => `
            <div class="highlight-card">
                <h4>${escapeHtml(h.title)}</h4>
                ${h.description ? `<p>${escapeHtml(h.description)}</p>` : ''}
            </div>
        `).join('');
        return `<section class="detail-section"><h2>Highlights</h2><div class="highlights-grid">${items}</div></section>`;
    }

    function renderAttractions(attractions) {
        if (!attractions || !attractions.length) return '';
        const items = attractions.map((a) => `
            <div class="attraction-card">
                <h4>${escapeHtml(a.name)}</h4>
                ${a.description ? `<p>${escapeHtml(a.description)}</p>` : ''}
            </div>
        `).join('');
        return `<section class="detail-section"><h2>Related Attractions</h2><div class="attractions-grid">${items}</div></section>`;
    }

    function persistSelection(dest) {
        localStorage.setItem('selectedDestinationId', String(dest.id));
        localStorage.setItem('selectedDestinationName', dest.name);
        localStorage.setItem('selectedDestination', JSON.stringify({
            id: dest.id,
            name: dest.name,
            location: dest.location,
            bestTime: dest.best_time,
        }));
    }

    async function load() {
        if (!id) {
            loading.style.display = 'none';
            errorBox.textContent = 'No destination selected.';
            errorBox.style.display = 'block';
            return;
        }

        try {
            const data = await EthioTripApi.getDestination(id);
            const dest = data.destination;
            loading.style.display = 'none';
            content.style.display = 'block';

            document.title = dest.name + ' | EthioTrip';
            document.getElementById('destName').textContent = dest.name;
            document.getElementById('destLocation').textContent = dest.location;
            document.getElementById('destBestTime').textContent = dest.best_time || 'All year';
            document.getElementById('destImage').src = dest.image_url;
            document.getElementById('destImage').alt = dest.name;
            document.getElementById('destDescription').textContent = dest.description || '';
            document.getElementById('destTravelGuide').textContent = dest.travel_guide || 'Travel guide information will be updated soon.';
            if (dest.activities) {
                document.getElementById('destActivities').textContent = dest.activities;
                document.getElementById('activitiesBlock').style.display = 'block';
            }

            document.getElementById('dynamicSections').innerHTML =
                renderHighlights(dest.highlights) + renderAttractions(dest.attractions);

            const proceedBtn = document.getElementById('btnProceedPackages');
            proceedBtn.href = ETHIOTRIP.page('/packages?destination_id=' + dest.id);
            proceedBtn.addEventListener('click', () => persistSelection(dest));
            persistSelection(dest);
        } catch (err) {
            loading.style.display = 'none';
            errorBox.textContent = err.message || 'Destination not found.';
            errorBox.style.display = 'block';
        }
    }

    load();
})();
