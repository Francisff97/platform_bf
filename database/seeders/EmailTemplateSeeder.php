<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // === già presente ===
        EmailTemplate::updateOrCreate(
            ['key' => 'order_completed'],
            [
                'name'      => 'Order Completed',
                'slug'      => 'order-completed',
                'subject'   => 'Thanks for your order #{{ $order->number }}',
                'body_html' => <<<'BLADE'
<p>Hi {{ $customer_name }},</p>
<p>We’re happy to confirm your order <strong>#{{ $order->number }}</strong>.</p>

<p>
  <strong>Total:</strong> {{ $order->total_formatted }}<br>
  <strong>Placed at:</strong> {{ $order->created_at->format('Y-m-d H:i') }} ({{config('app.timezone') ?? 'UTC'}})
</p>

@if(!empty($order->items))
  <p><strong>Items:</strong></p>
  <ul>
    @foreach($order->items as $it)
      <li>{{ $it->name }} &times; {{ $it->qty }} — {{ $it->price_formatted }}</li>
    @endforeach
  </ul>
@endif

<p>Need help? Reply to this email and we’ll get back to you.</p>
<p>— The Team</p>
BLADE,
                'enabled'   => true,
            ]
        );

        // === NEW: order_deleted ===
        EmailTemplate::updateOrCreate(
            ['key' => 'order_deleted'],
            [
                'name'      => 'Order Deleted',
                'slug'      => 'order-deleted',
                'subject'   => 'Order #{{ $order->number }} has been deleted',
                'body_html' => <<<'BLADE'
<p>Hello {{ $customer_name }},</p>
<p>Your order <strong>#{{ $order->number }}</strong> has been <strong>deleted</strong>.</p>

@if(!empty($reason))
  <p><strong>Reason:</strong> {{ $reason }}</p>
@endif

<p>If this was unexpected, please contact support.</p>
<p>— The Team</p>
BLADE,
                'enabled'   => true,
            ]
        );

        // === NEW: order_cancelled (annullato, in inglese) ===
        EmailTemplate::updateOrCreate(
            ['key' => 'order_cancelled'],
            [
                'name'      => 'Order Cancelled',
                'slug'      => 'order-cancelled',
                'subject'   => 'Order #{{ $order->number }} was cancelled',
                'body_html' => <<<'BLADE'
<p>Hello {{ $customer_name }},</p>
<p>Your order <strong>#{{ $order->number }}</strong> was <strong>cancelled</strong>.</p>

@if(!empty($refund))
  <p><strong>Refund:</strong> {{ $refund }}</p>
@endif

<p>We’re here if you need help.</p>
<p>— The Team</p>
BLADE,
                'enabled'   => true,
            ]
        );

        // === NEW: welcome_user ===
        EmailTemplate::updateOrCreate(
            ['key' => 'welcome_user'],
            [
                'name'      => 'Welcome User',
                'slug'      => 'welcome-user',
                'subject'   => 'Welcome to {{ $app_name ?? config("app.name") }}, {{ $user->name ?? $customer_name ?? "there" }}!',
                'body_html' => <<<'BLADE'
<p>Hi {{ $user->name ?? $customer_name ?? 'there' }},</p>
<p>Welcome to {{ $app_name ?? config('app.name') }} — we’re happy to have you 🎉</p>

@if(!empty($getting_started_url))
  <p>Get started here:
    <a href="{{ $getting_started_url }}">{{ $getting_started_url }}</a>
  </p>
@endif

<p>— The Team</p>
BLADE,
                'enabled'   => true,
            ]
        );
    }
}