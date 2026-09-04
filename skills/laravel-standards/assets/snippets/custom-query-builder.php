<?php

// Curated snippet: typed model query builder for composed domain filters.
// Generic example — adapt namespace and enum to project.

namespace App\Modules\Order\QueryBuilder;

use App\Modules\Order\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;

class OrderQueryBuilder extends Builder
{
    public function whereIsPaid(): static
    {
        return $this->where('status', OrderStatus::Paid);
    }

    public function whereIsNotCanceled(): static
    {
        return $this->where('status', '!=', OrderStatus::Canceled);
    }
}

// In App\Modules\Order\Models\Order:
// public function newEloquentBuilder($query): OrderQueryBuilder
// {
//     return new OrderQueryBuilder($query);
// }
//
// Order::query()->whereIsPaid()->whereIsNotCanceled()->get();
