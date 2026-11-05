/**
 * A small client for the standard SMM panel API (v2): services, add, status,
 * balance, refill and cancel. No dependencies: Node 18+ (built-in fetch).
 *
 * Guide: https://evapanel.com/blog/smm-panel-api-explained
 *
 *   const { SmmApi } = require('smm-panel-api');
 *   const api = new SmmApi('https://yourpanel.com/api/v2', process.env.SMM_API_KEY);
 *   const { order } = await api.order({ service: 1, link: 'https://instagram.com/p/xyz', quantity: 1000 });
 *   console.log(await api.status(order));
 */
'use strict';

class SmmApiError extends Error {
    constructor(message) {
        super(message);
        this.name = 'SmmApiError';
    }
}

class SmmApi {
    #url;
    #key;
    #timeout;

    /**
     * @param {string} url the panel's API address, e.g. https://yourpanel.com/api/v2
     * @param {string} key your API key (keep it secret: it spends your balance)
     * @param {{ timeout?: number }} [options] timeout in milliseconds (default 30000)
     */
    constructor(url, key, options = {}) {
        // Plain http only for a panel on this machine (localhost, *.localhost, 127.0.0.1) while testing.
        if (!/^(https:\/\/|http:\/\/(localhost|127\.0\.0\.1|[a-z0-9.-]+\.localhost)(:\d+)?(\/|$))/i.test(url)) {
            throw new SmmApiError('Use the https:// address of the API: your key must not travel unencrypted.');
        }
        this.#url = url;
        this.#key = key;
        this.#timeout = options.timeout ?? 30000;
    }

    /** Every service you can order, with its rate per 1,000 and limits. */
    services() {
        return this.#call({ action: 'services' });
    }

    /** Place an order: service, link, quantity (plus e.g. comments, or runs and interval for drip-feed). */
    order(order) {
        return this.#call({ action: 'add', ...order });
    }

    /** One order's status, charge, start count and remains. */
    status(order) {
        return this.#call({ action: 'status', order });
    }

    /** Several orders at once (up to 100), keyed by order id. */
    multiStatus(orders) {
        return this.#call({ action: 'status', orders: orders.join(',') });
    }

    /** Your balance and its currency. */
    balance() {
        return this.#call({ action: 'balance' });
    }

    /** Ask for a refill of an order (services with a refill guarantee). */
    refill(order) {
        return this.#call({ action: 'refill', order });
    }

    multiRefill(orders) {
        return this.#call({ action: 'refill', orders: orders.join(',') });
    }

    /** A refill's status. */
    refillStatus(refill) {
        return this.#call({ action: 'refill_status', refill });
    }

    /** Ask to cancel orders (services that allow cancelling). */
    cancel(orders) {
        return this.#call({ action: 'cancel', orders: orders.join(',') });
    }

    toJSON() {
        return { url: this.#url }; // never the key
    }

    async #call(params) {
        const body = new URLSearchParams({ key: this.#key });
        for (const [name, value] of Object.entries(params)) {
            if (value !== undefined && value !== null) body.append(name, String(value));
        }

        let response;
        try {
            response = await fetch(this.#url, {
                method: 'POST',
                body,
                headers: { Accept: 'application/json', 'User-Agent': 'smm-api-client-node/1.0' },
                signal: AbortSignal.timeout(this.#timeout),
            });
        } catch (e) {
            throw new SmmApiError(`Could not reach the API: ${e.message}`);
        }

        let data;
        try {
            data = await response.json();
        } catch {
            throw new SmmApiError(`The API did not answer with JSON (HTTP ${response.status}).`);
        }
        if (data && typeof data === 'object' && !Array.isArray(data) && 'error' in data) {
            throw new SmmApiError(String(data.error));
        }

        return data;
    }
}

module.exports = { SmmApi, SmmApiError };
