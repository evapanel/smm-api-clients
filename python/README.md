# smm-panel-api

A small, dependency-free Python client for the **standard SMM panel API (v2)**:
the format almost every SMM panel and provider uses. List services, place
orders, follow their status, check your balance, and ask for refills and
cancellations.

Needs Python 3.8+ and nothing else: only the standard library.

New to the SMM API? Read **[The SMM Panel API Explained](https://evapanel.com/blog/smm-panel-api-explained)**:
every action, with example requests and answers.

## Install

```bash
pip install smm-panel-api
```

## Quick start

```python
import os
from smm_api import SmmApi, SmmApiError

api = SmmApi("https://yourpanel.com/api/v2", os.environ["SMM_API_KEY"])

try:
    balance = api.balance()
    print(f"Balance: {balance['balance']} {balance['currency']}")

    order = api.order(service=1, link="https://instagram.com/p/xyz", quantity=1000)
    print(api.status(order["order"]))
except SmmApiError as e:
    print("API error:", e)
```

## Methods

| Action | Method |
| --- | --- |
| List services | `services()` |
| Place an order | `order(service=..., link=..., quantity=..., ...)` |
| Order status | `status(id)` |
| Many statuses (up to 100) | `multi_status([id, ...])` |
| Balance | `balance()` |
| Refill | `refill(id)` / `multi_refill([id, ...])` |
| Refill status | `refill_status(id)` |
| Cancel | `cancel([id, ...])` |

Every method returns the panel's JSON answer as Python data. Errors from the
panel (`{"error": "..."}`) and network problems raise `SmmApiError`. Pass
`timeout=60` to the constructor to change the 30-second timeout.

Some service types take more fields in `order()`, such as `comments` (one per
line) or `runs` and `interval` for drip-feed.

## Keep your key safe

Your API key spends your balance. Keep it in an environment variable or a
secrets store, never in code you commit, and only use the panel's `https://`
address (the client refuses plain `http://` except for localhost).

## Also for PHP and Node.js

The same client exists for [PHP](https://packagist.org/packages/evapanel/smm-api-client)
and [Node.js](https://www.npmjs.com/package/smm-panel-api). Source:
[github.com/evapanel/smm-api-clients](https://github.com/evapanel/smm-api-clients).

## Run your own SMM panel

These clients work with any panel that speaks the standard API. If you want a
panel of your own, with the reseller API built in, see
**[Eva Panel](https://evapanel.com)**: a ready-made SMM panel on your own
domain, with hosting included.

## License

MIT
