export class SmmApiError extends Error {}

export interface Service {
    service: number;
    name: string;
    type: string;
    category: string;
    rate: string;
    min: string;
    max: string;
    refill?: boolean;
    cancel?: boolean;
    dripfeed?: boolean;
}

export interface OrderStatus {
    charge: string;
    start_count: string;
    status: string;
    remains: string;
    currency: string;
}

export class SmmApi {
    constructor(url: string, key: string, options?: { timeout?: number });
    services(): Promise<Service[]>;
    order(order: { service: number | string; link: string; quantity?: number | string; [field: string]: unknown }): Promise<{ order: number }>;
    status(order: number | string): Promise<OrderStatus>;
    multiStatus(orders: Array<number | string>): Promise<Record<string, OrderStatus | { error: string }>>;
    balance(): Promise<{ balance: string; currency: string }>;
    refill(order: number | string): Promise<{ refill: number | string }>;
    multiRefill(orders: Array<number | string>): Promise<unknown>;
    refillStatus(refill: number | string): Promise<{ status: string }>;
    cancel(orders: Array<number | string>): Promise<unknown>;
}
