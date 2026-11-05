<?php

declare(strict_types=1);

namespace EvaPanel\SmmApi;

/**
 * A small client for the standard SMM panel API (v2), the format almost every
 * SMM panel and provider uses: services, add, status, balance, refill and
 * cancel. No dependencies beyond PHP's curl extension.
 *
 * Guide: https://evapanel.com/blog/smm-panel-api-explained
 *
 *     $api = new SmmApi('https://yourpanel.com/api/v2', getenv('SMM_API_KEY'));
 *     $order = $api->order(['service' => 1, 'link' => 'https://instagram.com/p/xyz', 'quantity' => 1000]);
 *     $status = $api->status($order['order']);
 */
final class SmmApi
{
    /**
     * @param  string  $url  the panel's API address, e.g. https://yourpanel.com/api/v2
     * @param  string  $key  your API key (keep it secret: it spends your balance)
     */
    public function __construct(
        private readonly string $url,
        #[\SensitiveParameter] private readonly string $key,
        private readonly int $timeout = 30,
    ) {
        // Plain http only for a panel on this machine (localhost, *.localhost, 127.0.0.1) while testing.
        if (! str_starts_with($url, 'https://') && ! preg_match('#^http://(localhost|127\.0\.0\.1|[a-z0-9.-]+\.localhost)(:\d+)?(/|$)#i', $url)) {
            throw new SmmApiException('Use the https:// address of the API: your key must not travel unencrypted.');
        }
    }

    /** Every service you can order, with its rate per 1,000 and limits. */
    public function services(): array
    {
        return $this->call(['action' => 'services']);
    }

    /**
     * Place an order. Always: service, link, quantity. Some service types take
     * more, e.g. comments (one per line), or runs and interval for drip-feed.
     *
     * @param  array<string, scalar>  $order
     * @return array{order: int}
     */
    public function order(array $order): array
    {
        return $this->call(['action' => 'add', ...$order]);
    }

    /** One order's status, charge, start count and remains. */
    public function status(int|string $order): array
    {
        return $this->call(['action' => 'status', 'order' => (string) $order]);
    }

    /**
     * Several orders at once (up to 100), keyed by order id.
     *
     * @param  list<int|string>  $orders
     */
    public function multiStatus(array $orders): array
    {
        return $this->call(['action' => 'status', 'orders' => implode(',', $orders)]);
    }

    /** Your balance and its currency. */
    public function balance(): array
    {
        return $this->call(['action' => 'balance']);
    }

    /** Ask for a refill of an order (services with a refill guarantee). */
    public function refill(int|string $order): array
    {
        return $this->call(['action' => 'refill', 'order' => (string) $order]);
    }

    /** @param  list<int|string>  $orders */
    public function multiRefill(array $orders): array
    {
        return $this->call(['action' => 'refill', 'orders' => implode(',', $orders)]);
    }

    /** A refill's status. */
    public function refillStatus(int|string $refill): array
    {
        return $this->call(['action' => 'refill_status', 'refill' => (string) $refill]);
    }

    /**
     * Ask to cancel orders (services that allow cancelling).
     *
     * @param  list<int|string>  $orders
     */
    public function cancel(array $orders): array
    {
        return $this->call(['action' => 'cancel', 'orders' => implode(',', $orders)]);
    }

    /**
     * @param  array<string, scalar>  $params
     *
     * @throws SmmApiException when the request fails or the panel answers with an error
     */
    private function call(array $params): array
    {
        $ch = curl_init($this->url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['key' => $this->key, ...$params]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_USERAGENT => 'smm-api-client-php/1.0',
        ]);
        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($raw === false) {
            throw new SmmApiException('Could not reach the API: '.$error);
        }
        $data = json_decode((string) $raw, true);
        if (! is_array($data)) {
            throw new SmmApiException("The API did not answer with JSON (HTTP $code).");
        }
        if (isset($data['error'])) {
            throw new SmmApiException(is_string($data['error']) ? $data['error'] : json_encode($data['error']));
        }

        return $data;
    }
}
