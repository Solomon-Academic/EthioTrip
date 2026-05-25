window.EthioTripApi = {
    async get(url) {
        const response = await fetch(window.ETHIOTRIP.api(url));
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'Request failed');
        }
        return data;
    },

    async post(url, body) {
        const response = await fetch(window.ETHIOTRIP.api(url), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || 'Request failed');
        }
        return data;
    },

    listDestinations() {
        return this.get('/api/destinations');
    },

    getDestination(id) {
        return this.get('/api/destinations/detail?id=' + encodeURIComponent(id));
    },

    listPackages(destinationId) {
        return this.get('/api/packages?destination_id=' + encodeURIComponent(destinationId));
    },

    checkLogin() {
        return this.get('/api/check-login');
    },
};
