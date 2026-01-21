# Stripe Integration Setup

This document explains how to set up and use the Stripe integrations in the EventSchedule application.

## Overview

EventSchedule uses two separate Stripe integrations:

1. **Stripe Connect** - Enables event creators to sell tickets and receive payments directly to their own Stripe accounts
2. **Laravel Cashier** - Enables SaaS subscription billing for the Pro plan (hosted mode only)

These integrations use separate Stripe accounts/API keys and can be configured independently.

## Prerequisites

1. A Stripe account (https://stripe.com)
2. For Stripe Connect: Event creators need their own Stripe accounts
3. For Laravel Cashier: A Stripe account owned by the platform operator

## Stripe Connect Setup (Ticket Sales)

Stripe Connect allows event creators to accept payments for ticket sales. Payments go directly to the event creator's connected Stripe account.

### 1. Stripe Dashboard Setup

1. Go to the [Stripe Dashboard](https://dashboard.stripe.com/)
2. Navigate to **Settings** > **Connect** > **Settings**
3. Enable Connect for your platform
4. Configure your branding and platform profile
5. Note your platform's API keys from **Developers** > **API keys**

### 2. Environment Configuration

Add the following environment variables to your `.env` file:

```env
# Stripe Connect (for ticket sales)
STRIPE_KEY=sk_live_your_stripe_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

- `STRIPE_KEY`: Your platform's Stripe secret key (starts with `sk_live_` or `sk_test_`)
- `STRIPE_WEBHOOK_SECRET`: The webhook signing secret for verifying webhook events

### 3. Webhook Configuration

1. Go to **Developers** > **Webhooks** in the Stripe Dashboard
2. Click **Add endpoint**
3. Set the endpoint URL:
   - Production: `https://yourdomain.com/stripe/webhook`
   - Development: Use a tool like [Stripe CLI](https://stripe.com/docs/stripe-cli) or ngrok
4. Select events to listen to:
   - `payment_intent.succeeded`
5. Save the endpoint and copy the signing secret to `STRIPE_WEBHOOK_SECRET`

### 4. User Onboarding Flow

When event creators want to accept payments:

1. User navigates to their profile settings > Payment Methods
2. User clicks "Connect Stripe Account"
3. User is redirected to Stripe's onboarding flow
4. After completing onboarding, user returns to EventSchedule
5. Their `stripe_account_id` and `stripe_completed_at` are saved

### 5. Checkout Process Flow

When a customer purchases tickets:

1. Customer selects tickets and fills out checkout form
2. EventSchedule creates a Stripe Checkout Session on the connected account
3. Customer is redirected to Stripe's hosted checkout page
4. After payment, customer returns to the success URL
5. Webhook confirms payment and updates the sale status

### API Endpoints

- `GET /stripe/link` - Start Stripe Connect onboarding
- `GET /stripe/complete` - Complete onboarding callback
- `GET /stripe/unlink` - Disconnect Stripe account
- `POST /stripe/webhook` - Webhook handler for payment events

---

## Laravel Cashier Setup (SaaS Subscriptions)

Laravel Cashier manages subscription billing for the Pro plan. This is separate from Stripe Connect and uses the platform's own Stripe account.

### 1. Stripe Dashboard Setup

1. Go to the [Stripe Dashboard](https://dashboard.stripe.com/)
2. Navigate to **Products** > **Add product**
3. Create a product for your Pro plan:
   - Name: "Pro Plan" (or your preferred name)
   - Add pricing:
     - Monthly price (e.g., $9.99/month, recurring)
     - Yearly price (e.g., $99.99/year, recurring)
4. Note the **Price IDs** for both pricing options (starts with `price_`)
5. Get your API keys from **Developers** > **API keys**

### 2. Environment Configuration

Add the following environment variables to your `.env` file:

```env
# Stripe Platform (for subscription billing)
STRIPE_PLATFORM_KEY=pk_live_your_publishable_key
STRIPE_PLATFORM_SECRET=sk_live_your_secret_key
STRIPE_PLATFORM_WEBHOOK_SECRET=whsec_your_subscription_webhook_secret
STRIPE_PRICE_MONTHLY=price_monthly_price_id
STRIPE_PRICE_YEARLY=price_yearly_price_id
```

- `STRIPE_PLATFORM_KEY`: Your Stripe publishable key (starts with `pk_live_` or `pk_test_`)
- `STRIPE_PLATFORM_SECRET`: Your Stripe secret key (starts with `sk_live_` or `sk_test_`)
- `STRIPE_PLATFORM_WEBHOOK_SECRET`: The webhook signing secret for subscription events
- `STRIPE_PRICE_MONTHLY`: The Price ID for monthly subscription
- `STRIPE_PRICE_YEARLY`: The Price ID for yearly subscription

### 3. Webhook Configuration

1. Go to **Developers** > **Webhooks** in the Stripe Dashboard
2. Click **Add endpoint**
3. Set the endpoint URL:
   - Production: `https://yourdomain.com/stripe/subscription-webhook`
4. Select events to listen to:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `customer.subscription.trial_will_end`
   - `invoice.payment_succeeded`
   - `invoice.payment_failed`
5. Save the endpoint and copy the signing secret to `STRIPE_PLATFORM_WEBHOOK_SECRET`

### 4. Customer Portal Setup

1. Go to **Settings** > **Billing** > **Customer portal** in Stripe Dashboard
2. Configure the portal settings:
   - Enable subscription cancellation
   - Enable plan switching
   - Enable payment method updates
   - Customize branding to match your site
3. Save your settings

### 5. Subscription Flow

1. User navigates to their schedule's admin page > Plan tab
2. User clicks "Upgrade to Pro"
3. User enters payment details using Stripe Elements
4. Subscription is created with optional trial period
5. User can manage subscription via Stripe Customer Portal

### API Endpoints

- `GET /{subdomain}/subscribe` - Show subscription page
- `POST /{subdomain}/subscribe` - Create new subscription
- `GET /{subdomain}/subscription/portal` - Redirect to Stripe Customer Portal
- `POST /{subdomain}/subscription/cancel` - Cancel subscription
- `POST /{subdomain}/subscription/resume` - Resume cancelled subscription
- `POST /{subdomain}/subscription/swap` - Switch between monthly/yearly plans
- `POST /stripe/subscription-webhook` - Webhook handler for subscription events

---

## Testing

### Test Mode Configuration

For development and testing, use Stripe's test mode:

1. Toggle to "Test mode" in the Stripe Dashboard
2. Use test API keys (starting with `pk_test_` and `sk_test_`)
3. Create test webhook endpoints pointing to your development environment

### Test Card Numbers

Use these test card numbers in test mode:

| Card Number | Description |
|-------------|-------------|
| `4242 4242 4242 4242` | Successful payment |
| `4000 0000 0000 3220` | 3D Secure authentication required |
| `4000 0000 0000 9995` | Declined (insufficient funds) |
| `4000 0000 0000 0002` | Declined (generic) |

Use any future expiration date and any 3-digit CVC.

### Testing Webhooks Locally

Use the [Stripe CLI](https://stripe.com/docs/stripe-cli) to forward webhooks to your local environment:

```bash
# Install Stripe CLI
brew install stripe/stripe-cli/stripe

# Login to your Stripe account
stripe login

# Forward Stripe Connect webhooks
stripe listen --forward-to localhost:8000/stripe/webhook

# Forward subscription webhooks (in another terminal)
stripe listen --forward-to localhost:8000/stripe/subscription-webhook
```

The CLI will display a webhook signing secret to use in your `.env` file.

---

## Troubleshooting

### Common Issues

1. **"Stripe account not connected" error**
   - User needs to complete Stripe Connect onboarding
   - Check if `stripe_account_id` and `stripe_completed_at` are set on the user

2. **"Invalid signature" webhook error**
   - Verify the webhook secret matches the one in Stripe Dashboard
   - Ensure you're using the correct secret for each endpoint:
     - `STRIPE_WEBHOOK_SECRET` for `/stripe/webhook`
     - `STRIPE_PLATFORM_WEBHOOK_SECRET` for `/stripe/subscription-webhook`

3. **Payments not being recorded**
   - Check that webhooks are being received (Stripe Dashboard > Webhooks > View logs)
   - Verify the webhook endpoint is accessible
   - Check application logs for errors

4. **Subscription not updating after payment**
   - Verify webhook events are being received
   - Check that the correct events are selected in Stripe Dashboard
   - Review `storage/logs/laravel.log` for errors

5. **"No such price" error**
   - Verify `STRIPE_PRICE_MONTHLY` and `STRIPE_PRICE_YEARLY` contain valid Price IDs
   - Ensure the prices exist in the same Stripe account (test vs live)

### Logs

Check the following logs for debugging:

- Application logs: `storage/logs/laravel.log`
- Stripe Dashboard: **Developers** > **Logs** (API requests)
- Stripe Dashboard: **Developers** > **Webhooks** > Select endpoint > View logs

### Verifying Configuration

To verify your Stripe configuration is correct:

1. **Test Stripe Connect**: Go to profile settings and try connecting a Stripe account
2. **Test Subscriptions**: Try subscribing to the Pro plan with a test card
3. **Test Webhooks**: Use Stripe CLI to trigger test events:

```bash
# Trigger a test payment_intent.succeeded event
stripe trigger payment_intent.succeeded

# Trigger a test subscription event
stripe trigger customer.subscription.created
```

---

## Security Considerations

1. **API Key Security**: Never expose secret keys in client-side code or version control
2. **Webhook Verification**: Always verify webhook signatures to prevent spoofed events
3. **HTTPS Required**: Stripe requires HTTPS for webhook endpoints in production
4. **PCI Compliance**: Using Stripe Checkout and Elements keeps you out of PCI scope

---

## Architecture Notes

### Billable Model

Laravel Cashier is configured to use the `Role` model (representing a schedule/calendar) as the billable entity, not the `User` model. This means:

- Each schedule has its own subscription status
- Users can have multiple schedules with different plans
- Subscription-related fields (`stripe_id`, `pm_type`, `pm_last_four`, `trial_ends_at`) are on the `roles` table

### Separate Stripe Accounts

The two integrations use completely separate Stripe configurations:

| Feature | Stripe Connect | Laravel Cashier |
|---------|---------------|-----------------|
| Purpose | Ticket sales | Subscription billing |
| Config key | `services.stripe.key` | `cashier.secret` |
| Webhook endpoint | `/stripe/webhook` | `/stripe/subscription-webhook` |
| Env prefix | `STRIPE_` | `STRIPE_PLATFORM_` |
| Money flows to | Event creator | Platform operator |
