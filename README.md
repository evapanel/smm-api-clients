# SMM Panel API clients (PHP, Python, Node)

Small, dependency-free clients for the **standard SMM panel API (v2)**: the
format almost every SMM panel and provider uses. Order services, follow orders,
check your balance, and ask for refills and cancellations from your own code,
bots or panels.

| Language | Install | Folder | Needs |
| --- | --- | --- | --- |
| PHP | `composer require evapanel/smm-api-client` | [`php/`](php) | PHP 8.1+, curl |
| Python | `pip install smm-panel-api` | [`python/`](python) | Python 3.8+ (standard library only) |
| Node.js | `npm install smm-panel-api` | [`node/`](node) | Node 18+ (built-in fetch) |

New to the SMM API? Read **[The SMM Panel API Explained](https://evapanel.com/blog/smm-panel-api-explained)**:
every action, with example requests and answers.

## Actions

| Action | PHP | Python | Node |
| --- | --- | --- | --- |
| List services | `services()` | `services()` | `services()` |
| Place an order | `order([...])` | `order(...)` | `order({...})` |
| Order status | `status($id)` | `status(id)` | `status(id)` |
| Many statuses (up to 100) | `multiStatus([...])` | `multi_status([...])` | `multiStatus([...])` |
| Balance | `balance()` | `balance()` | `balance()` |
| Refill | `refill($id)` / `multiRefill([...])` | `refill(id)` / `multi_refill([...])` | `refill(id)` / `multiRefill([...])` |
| Refill status | `refillStatus($id)` | `refill_status(id)` | `refillStatus(id)` |
| Cancel | `cancel([...])` | `cancel([...])` | `cancel([...])` |

Errors from the panel (`{"error": "..."}`) and network problems raise
`SmmApiException` / `SmmApiError`.

## Quick start

**PHP**

```php
use EvaPanel\SmmApi\SmmApi;

$api = new SmmApi('https://yourpanel.com/api/v2', getenv('SMM_API_KEY'));
$order = $api->order(['service' => 1, 'link' => 'https://instagram.com/p/xyz', 'quantity' => 1000]);
print_r($api->status($order['order']));
```

**Python**

```python
import os
from smm_api import SmmApi

api = SmmApi("https://yourpanel.com/api/v2", os.environ["SMM_API_KEY"])
order = api.order(service=1, link="https://instagram.com/p/xyz", quantity=1000)
print(api.status(order["order"]))
```

**Node.js**

```js
const { SmmApi } = require('smm-panel-api');

const api = new SmmApi('https://yourpanel.com/api/v2', process.env.SMM_API_KEY);
const { order } = await api.order({ service: 1, link: 'https://instagram.com/p/xyz', quantity: 1000 });
console.log(await api.status(order));
```

## Keep your key safe

Your API key spends your balance. Keep it in an environment variable or a
secrets store, never in code you commit, and only use the panel's `https://`
address (the clients refuse plain `http://` except for localhost).

## Run your own SMM panel

These clients work with any panel that speaks the standard API. If you want a
panel of your own, with the reseller API built in, see
**[Eva Panel](https://evapanel.com)**: a ready-made SMM panel on your own
domain, with hosting included. Try the free
**[SMM panel profit calculator](https://evapanel.com/tools/smm-panel-profit-calculator)**
too.

## License

MIT
