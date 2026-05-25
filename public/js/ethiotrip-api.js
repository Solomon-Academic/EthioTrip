window.EthioTripApi = {
    async parseJsonResponse(response) {
        const text = await response.text();
        if (!text) {
            return {};
        }
        try {
            return JSON.parse(text);
        } catch {
            throw new Error('Server returned an invalid response. Check that Apache and PHP are running.');
        }
    },

    async get(url) {
        const response = await fetch(window.ETHIOTRIP.api(url));
        const data = await this.parseJsonResponse(response);
        if (!response.ok || data.success === false) {
            throw new Error(data.message || 'Request failed');
        }
        return data;
    },

    /** @param {string} url */
    async getOptional(url) {
        const response = await fetch(window.ETHIOTRIP.api(url));
        const data = await this.parseJsonResponse(response);
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
        const data = await this.parseJsonResponse(response);
        if (!response.ok || data.success === false) {
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
        return this.getOptional('/api/check-login');
    },
};
