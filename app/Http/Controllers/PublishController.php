<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class PublishController extends Controller
{
    public function index(): RedirectResponse|Response
    {
        $event = auth()->user()->event()->with('questions')->first();

        if (! $event) {
            return redirect()->back();
        }

        if ($event->questions->where('is_tiebreaker', false)->count() === 0) {
            return redirect()->back();
        }

        return Inertia::render('admin/PublishPage', [
            'event' => $event,
            'isPublished' => $event->is_published,
            'appUrl' => config('app.url'),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $event = auth()->user()->event;

        if (! $event) {
            return response()->json(['message' => 'Event not found.'], 404);
        }

        if ($event->is_published) {
            return response()->json(['message' => 'Event is already published.'], 400);
        }

        $validated = $request->validate([
            'max_entries' => 'required|integer|in:10,60,0',
        ]);

        $maxEntries = (int) $validated['max_entries'];

        // Free tier - publish immediately
        if ($maxEntries === 10) {
            $event->update([
                'is_published' => true,
                'published_at' => now(),
                'max_entries' => $maxEntries,
            ]);

            return redirect()->route('publish.index')->with('success', 'Event published successfully!');
        }

        // Paid tiers - redirect to Stripe checkout
        Stripe::setApiKey(config('services.stripe.secret'));

        $amount = match ($maxEntries) {
            60 => 1500, // $15.00 in cents
            0 => 10000, // $100.00 in cents
            default => throw new \Exception('Invalid tier amount'),
        };

        try {
            $checkoutSession = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Event Publishing Fee',
                            'description' => "Publish event: {$event->title}",
                        ],
                        'unit_amount' => $amount,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('publish.index').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('publish.index'),
                'metadata' => [
                    'event_id' => $event->id,
                    'user_id' => auth()->id(),
                    'max_entries' => $maxEntries,
                ],
            ]);

            return response()->json([
                'checkout_url' => $checkoutSession->url,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create payment session.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $endpoint_secret = config('services.stripe.webhook_secret');

        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the checkout.session.completed event
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $eventModel = \App\Models\Event::find($session->metadata->event_id);

            if ($eventModel) {
                $eventModel->update([
                    'is_published' => true,
                    'published_at' => now(),
                    'payment_intent_id' => $session->payment_intent,
                    'amount_paid' => $session->amount_total,
                    'max_entries' => $session->metadata->max_entries ?? 60,
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
