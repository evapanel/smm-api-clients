"""A small client for the standard SMM panel API (v2).

The format almost every SMM panel and provider uses: services, add, status,
balance, refill and cancel. No dependencies: only Python's standard library.

Guide: https://evapanel.com/blog/smm-panel-api-explained

    from smm_api import SmmApi

    api = SmmApi("https://yourpanel.com/api/v2", os.environ["SMM_API_KEY"])
    order = api.order(service=1, link="https://instagram.com/p/xyz", quantity=1000)
    print(api.status(order["order"]))
"""

from __future__ import annotations

import json
import re
import urllib.error
import urllib.parse
import urllib.request
from typing import Any, Iterable

__all__ = ["SmmApi", "SmmApiError"]
__version__ = "1.0.1"


class SmmApiError(Exception):
    """The API could not be reached, or it answered {"error": "..."}."""


class SmmApi:
    def __init__(self, url: str, key: str, timeout: float = 30.0) -> None:
        # Plain http only for a panel on this machine (localhost, *.localhost, 127.0.0.1) while testing.
        if not (url.startswith("https://") or re.match(r"^http://(localhost|127\.0\.0\.1|[a-z0-9.-]+\.localhost)(:\d+)?(/|$)", url, re.I)):
            raise SmmApiError("Use the https:// address of the API: your key must not travel unencrypted.")
        self._url = url
        self._key = key
        self._timeout = timeout

    def __repr__(self) -> str:  # never show the key
        return f"SmmApi(url={self._url!r})"

    def services(self) -> list[dict[str, Any]]:
        """Every service you can order, with its rate per 1,000 and limits."""
        return self._call(action="services")

    def order(self, **order: Any) -> dict[str, Any]:
        """Place an order: service, link, quantity (plus e.g. comments, or runs and interval for drip-feed)."""
        return self._call(action="add", **order)

    def status(self, order: int | str) -> dict[str, Any]:
        """One order's status, charge, start count and remains."""
        return self._call(action="status", order=order)

    def multi_status(self, orders: Iterable[int | str]) -> dict[str, Any]:
        """Several orders at once (up to 100), keyed by order id."""
        return self._call(action="status", orders=",".join(str(o) for o in orders))

    def balance(self) -> dict[str, Any]:
        """Your balance and its currency."""
        return self._call(action="balance")

    def refill(self, order: int | str) -> dict[str, Any]:
        """Ask for a refill of an order (services with a refill guarantee)."""
        return self._call(action="refill", order=order)

    def multi_refill(self, orders: Iterable[int | str]) -> Any:
        return self._call(action="refill", orders=",".join(str(o) for o in orders))

    def refill_status(self, refill: int | str) -> dict[str, Any]:
        """A refill's status."""
        return self._call(action="refill_status", refill=refill)

    def cancel(self, orders: Iterable[int | str]) -> Any:
        """Ask to cancel orders (services that allow cancelling)."""
        return self._call(action="cancel", orders=",".join(str(o) for o in orders))

    def _call(self, **params: Any) -> Any:
        body = urllib.parse.urlencode({"key": self._key, **params}).encode()
        request = urllib.request.Request(
            self._url,
            data=body,
            headers={"Accept": "application/json", "User-Agent": "smm-api-client-python/1.0"},
            method="POST",
        )
        try:
            with urllib.request.urlopen(request, timeout=self._timeout) as response:
                raw = response.read()
        except urllib.error.HTTPError as e:
            raw = e.read()
        except (urllib.error.URLError, TimeoutError) as e:
            raise SmmApiError(f"Could not reach the API: {e}") from None

        try:
            data = json.loads(raw)
        except ValueError:
            raise SmmApiError("The API did not answer with JSON.") from None
        if isinstance(data, dict) and "error" in data:
            raise SmmApiError(str(data["error"]))
        return data
