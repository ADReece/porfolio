# Stripe Customer Portal Setup

## ⚠️ Current Issue

You're seeing this error when clicking "Manage Billing":
```
No configuration provided and your test mode default configuration has not been created.
```

This happens because the Stripe Customer Portal hasn't been activated yet.

## ✅ Quick Fix (2 minutes)

### Step 1: Activate the Customer Portal

1. Go to https://dashboard.stripe.com/test/settings/billing/portal
2. Make sure you're in **Test mode** (toggle in top right)
3. Click **"Activate test link"** or **"Create configuration"**
4. **Branding:**
   - Add your business name
   - Add a logo (optional)
   - Choose accent color
5. **Functionality - Enable these options:**
   - ✅ Allow customers to update their payment methods
   - ✅ Allow customers to update their billing information
   - ✅ Allow customers to view their invoice history
   - ✅ Allow customers to cancel subscriptions (or set to "At end of billing period")
6. Click **Save** at the bottom

### Step 2: Test It

1. Visit your dashboard at http://localhost/dashboard
2. Click **"Manage Billing"**
3. You should now be redirected to Stripe's hosted billing portal
4. You'll be able to:
   - Update payment methods
   - View invoices
   - Cancel/resume subscriptions
   - Update billing address

## 🎯 What This Does

The Stripe Customer Portal is a **hosted page** where your users can:
- Update their credit card
- Download invoices
- Cancel their subscription
- Update billing information
- View payment history

It's maintained by Stripe, so you don't have to build these pages yourself!

## 🔒 For Production

When you're ready to go live:
1. Switch to **Live mode** in Stripe Dashboard
2. Go to https://dashboard.stripe.com/settings/billing/portal
3. Configure the same settings for live mode
4. Update your .env to use live Stripe keys

## ⚡ Error Handling

I've added error handling to the code, so if the portal isn't configured:
- Users see a friendly message: "Billing portal is not yet configured. Please contact support."
- They can still cancel/resume from the dashboard
- Download invoices directly

## 🎨 Customization Options

In the portal settings, you can customize:
- **Branding:** Logo, colors, business name
- **Features:** What customers can do
- **Invoice template:** Custom invoice design
- **Business information:** Tax ID, address, phone
- **Email notifications:** Customize what emails Stripe sends

## ✨ Alternative Actions Available

Even without the portal, users can:
- **Cancel subscription:** Button on dashboard (already working)
- **Resume subscription:** Button shows during grace period
- **Download invoices:** Direct download links on dashboard
- **View next billing date:** Shown in subscription card

---

**Ready?** Just click that "Activate test link" button in Stripe and you're good to go! 🚀

