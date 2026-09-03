---
owner: human
title: Jobs, Queues, Events & Listeners
---

# Jobs, Queues, Events & Listeners

## Listeners

- Implement `ShouldQueue` on every listener by default. The only exception is a listener whose result the triggering request genuinely needs synchronously before it can respond — that should be rare.
- Implement `ShouldHandleEventsAfterCommit` (or dispatch with `->afterCommit()`) when the listener reacts to a model change made inside a transaction, so it doesn't run against uncommitted state.

## Job / listener body

Wrap the work in a transaction, log structured context on failure, then either suppress or allow re-dispatch:

```php
final class GrantBirthdayCoupon implements ShouldQueue, ShouldHandleEventsAfterCommit
{
    public function handle(RequiredInformationCompleted $event): void
    {
        try {
            DB::transaction(function () use ($event) {
                $membership = Membership::with('detail')->findOrFail($event->membershipId);
                // ... logic
                SendClaimedCouponNotification::dispatch($membership->id);
            });
        } catch (\Throwable $e) {
            logger()->error('grant birthday coupon failed', [
                'membership_id' => $event->membershipId,
                'exception' => $e->getMessage(),
            ]);

            return; // suppress re-dispatch; rethrow instead to retry
        }
    }
}
```

Use a `failed()` hook for cleanup that must run after the final attempt:

```php
public function failed(\Throwable $exception): void
{
    logger()->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
    $this->pushNotification->update(['status' => NotificationStatus::Unsent]);
}
```

Manual `beginTransaction` / `commit` / `rollBack` is justified only when you need the exception object for recovery (e.g. writing a failure status). Otherwise use the `DB::transaction()` closure.

## Dispatch patterns

- Async job: `SomeJob::dispatch($arg)` or `dispatch(new SomeJob($arg))`.
- After the surrounding transaction commits: `SomeJob::dispatch($arg)->afterCommit()`.
- Fire an event: `event(new OrderPaid($order->id))` — pass an id, not the model, so the payload survives serialization.
- Skip model events for a bulk/system write: `$model->saveQuietly()`.

## Pick one orchestration strategy project-wide

Do not mix these within one project:

1. **Listener chain (default):** Event → Listener → child Job. Most discoverable — "what happens when X occurs" is answered by grepping the event's listeners in `EventServiceProvider`.
2. **Action-then-event:** an Action finishes its work, then fires an Event for side effects.
3. **Model-event-driven:** a model `saved`/`updated` hook dispatches the Event.

Start with the listener chain. Reach for another only when a specific flow genuinely doesn't fit an explicit Event.

<!-- Add new team jobs/events rules below this line. Machine ingestion never touches this file. -->
