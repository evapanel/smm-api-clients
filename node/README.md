# smm-panel-api

A small, dependency-free Node.js client for the **standard SMM panel API (v2)**:
the format almost every SMM panel and provider uses. List services, place
orders, follow their status, check your balance, and ask for refills and
cancellations.

Needs Node 18+ (built-in `fetch`). TypeScript types included.

New to the SMM API? Read **[The SMM Panel API Explained](https://evapanel.com/blog/smm-panel-api-explained)**:
every action, with example requests and answers.

## Install

```bash
npm install smm-panel-api
```

## Quick start

```js
const { SmmApi, SmmApiError } = require('smm-panel-api');

const api = new SmmApi('https://yourpanel.com/api/v2', process.env.SMM_API_KEY);

try {
    const { balance, currency } = await api.balance();
    console.log(`Balance: ${balance} ${currency}`);

    const { order } = await api.order({ service: 1, link: 'https://instagram.com/p/xyz', quantity: 1000 });
    console.log(await api.status(order));
} catch (e) {
    if (e instanceof SmmApiError) console.error('API error:', e.message);
    else throw e;
}
```

## Methods

| Action | Method |
| --- | --- |
| List services | `services()` |
| Place an order | `order({ service, link, quantity, ... })` |
| Order status | `status(id)` |
| Many statuses (up to 100) | `multiStatus([id, ...])` |
| Balance | `balance()` |
| Refill | `refill(id)` / `multiRefill([id, ...])` |
| Refill status | `refillStatus(id)` |
| Cancel | `cancel([id, ...])` |

Every method returns a promise with the panel's JSON answer. Errors from the
panel (`{"error": "..."}`) and network problems throw `SmmApiError`. Pass
`{ timeout: 60000 }` as the third constructor argument to change the 30-second
timeout.

Some service types take more fields in `order()`, such as `comments` (one per
line) or `runs` and `interval` for drip-feed.

## Keep your key safe

Your API key spends your balance. Keep it in an environment variable or a
secrets store, never in code you commit, and only use the panel's `https://`
address (the client refuses plain `http://` except for localhost).

## Also for PHP and Python

The same client exists for [PHP](https://packagist.org/packages/evapanel/smm-api-client)
and [Python](https://pypi.org/project/smm-panel-api/). Source:
[github.com/evapanel/smm-api-clients](https://github.com/evapanel/smm-api-clients).

## Run your own SMM panel

These clients work with any panel that speaks the standard API. If you want a
panel of your own, with the reseller API built in, see
**[Eva Panel](https://evapanel.com)**: a ready-made SMM panel on your own
domain, with hosting included.

## License

MIT
