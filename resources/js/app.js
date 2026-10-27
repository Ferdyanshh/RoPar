import './api.js';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
const currencyMeta = document.querySelector('meta[name="user-currency"]');
window.currentCurrency = currencyMeta?.content || 'IDR';
window.idrToUsdRate = null;
Alpine.store('currency', { rate: null });
Alpine.start();

window.exchangeRatePromise = currencyMeta
    ? window.api.get('/backend/exchange-rate')
        .then(response => {
            window.idrToUsdRate = Number(response.data.idr_to_usd);
            Alpine.store('currency').rate = window.idrToUsdRate;
            window.dispatchEvent(new CustomEvent('currency-rate-updated'));
            return response.data;
        })
        .catch(error => {
            if (window.currentCurrency === 'USD') {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { message: error.message || 'Kurs mata uang sedang tidak tersedia.', type: 'error' }
                }));
            }
            return null;
        })
    : Promise.resolve(null);
