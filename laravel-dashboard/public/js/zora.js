(function (window) {
    const Zora = window.Zora = window.Zora || {};

    // =========================
    // CORE HELPERS
    // =========================

    Zora.extend = function (methods) {
        Object.keys(methods || {}).forEach((k) => {
            Zora[k] = methods[k];
        });
        return Zora;
    };

    // =========================
    // DEFAULT UTILITIES
    // =========================

    Zora.extend({
        toRupiah(value) {
            const number = Number(value) || 0;
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }
    });

})(window);
