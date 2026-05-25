window.ETHIOTRIP = {
    basePath: '/ethiotrip1/ethiotrip/public',
    api(path) {
        const p = path.startsWith('/') ? path : '/' + path;
        return this.basePath + p;
    },
    page(path) {
        const p = path.startsWith('/') ? path : '/' + path;
        return this.basePath + p;
    },
};
