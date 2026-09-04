<?php

// Curated snippet: model-to-DTO normalization in one named factory.
// Generic example — adapt namespace and fields to project.

namespace App\Modules\Order\DataTransferObjects\Factories;

use App\Modules\Order\DataTransferObjects\OrderData;
use App\Modules\Order\Models\Order;

class OrderDataFactory
{
    public static function fromModel(Order $order): OrderData
    {
        return new OrderData([
            'name'       => $order->name,
            'status'     => $order->status,
            'starts_at'  => $order->starts_at?->toDateString(),
            'line_items' => $order->lineItems->toArray(),
        ]);
    }
}

// Keep request-to-DTO mapping in FormRequest::data(), built from validated().
// Do not confuse this with Laravel database factories in database/factories.
