<?php

// Curated snippet: FormRequest -> DTO -> Action mapping, matching references/patterns/full-api.md
// Generic example — adapt namespace, fields, and DTO/Action classes to the project.

namespace App\Http\Requests\Api\Admin\V1\Order;

use App\Modules\Order\DTO\OrderData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:1'],
        ];
    }

    // Build from validated() only. Never ::from($this) / ::from($request) —
    // that reads the whole unvalidated payload.
    public function data(): OrderData
    {
        return OrderData::from($this->validated());
    }
}

// Controller: pass the DTO (not the Request) into the Action.
//
// public function update(UpdateOrderRequest $request, Order $order): OrderDetailResource
// {
//     $order = DB::transaction(
//         fn () => (new UpdateOrderAction($order))->execute($request->data())
//     );
//
//     return OrderDetailResource::make($order);
// }
