# Prodigi Integration - Process Flow Diagram

## Order Fulfillment Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                    CUSTOMER ORDER FLOW                              │
└─────────────────────────────────────────────────────────────────────┘

1. CUSTOMER PLACES ORDER
   ↓
   Customer selects products (print + digital)
   Customer checks out
   
2. PAYMENT PROCESSING  
   ↓
   Stripe webhook received → payment_intent.succeeded
   ↓
   StripeWebhookController::handlePaymentSucceeded()
   
3. ORDER MARKED AS PAID
   ↓
   $order->update([
       'status' => 'completed',
       'paid_at' => now(),
   ])
   
4. DETERMINE FULFILLMENT TYPE
   ├─ Has digital items? → Deliver immediately
   ├─ Has print items? → Queue for Prodigi
   └─ Both? → Both workflows
   
5. PRODIGI SUBMISSION (For print items)
   ↓
   dispatch(SubmitOrderToProdigi::class, $order)
   ↓
   Job queued to Redis/Database/Sync
   
6. QUEUE WORKER PROCESSES JOB
   ↓
   worker starts executing SubmitOrderToProdigi
   
   a) Build Prodigi order payload
      - Get print items from order
      - Map products to Prodigi SKUs
      - Collect recipient address
      - Collect customer phone
      - Get image URLs
   
   b) Submit to Prodigi API
      ProdigiService::createOrder($payload)
      ↓
      HTTP POST → https://api.prodigi.com/orders
   
   c) Store response
      $order->update([
          'prodigi_order_id' => $response['id'],
          'prodigi_status' => $response['status'],
          'prodigi_charge' => $response['charge'],
          'prodigi_submitted_at' => now(),
      ])
   
7. TRACK FULFILLMENT STATUS
   ↓
   Periodic: php artisan prodigi:sync-orders
   OR: Manual: dispatch(SyncProdigiOrderStatus::class, $order)
   
   SyncProdigiOrderStatus job:
   ↓
   ProdigiService::getOrderStatus($prodigi_order_id)
   ↓
   HTTP GET → https://api.prodigi.com/orders/{id}
   ↓
   Update $order->prodigi_status
   
8. ORDER SHIPPED
   ↓
   Prodigi status changes to 'shipped'
   ↓
   $order->prodigi_tracking_number = tracking_number
   $order->prodigi_shipped_at = timestamp
   
9. CUSTOMER RECEIVES NOTIFICATION
   ├─ Email with tracking number
   ├─ Portal shows status
   └─ Can create related orders

```

## Component Interaction Diagram

```
┌──────────────────────┐
│  StripeWebhook       │
│  Handler             │──────┐
└──────────────────────┘      │
                              │ dispatch()
                              ↓
                    ┌──────────────────────┐
                    │ SubmitOrderToProdigi │
                    │ (Job)                │
                    └──────────────────────┘
                              │
                              ↓
            ┌─────────────────────────────────┐
            │ ProdigiOrderService             │
            │ .submitOrderToProdigi()         │
            │                                 │
            │ Builds payload from order:     │
            │  - mapProductToPraigiSku()     │
            │  - getRecipientAddress()       │
            │  - getCustomerPhone()          │
            │  - getProductImageUrl()        │
            └──────────────┬──────────────────┘
                           │
                           ↓
            ┌──────────────────────────────┐
            │ ProdigiService               │
            │ .createOrder($payload)       │
            └──────────────┬───────────────┘
                           │
                           ↓ HTTP POST
            ┌──────────────────────────────┐
            │ Prodigi API                  │
            │ POST /orders                 │
            └──────────────┬───────────────┘
                           │
                           ↓ Response
            ┌──────────────────────────────┐
            │ Order Model                  │
            │ Update with:                 │
            │  - prodigi_order_id          │
            │  - prodigi_status            │
            │  - prodigi_charge            │
            │  - prodigi_submitted_at      │
            └──────────────────────────────┘
```

## Data Flow - Order Submission

```
CLASS: Order
├─ id (UUID)
├─ user_id (photographer)
├─ customer_email
├─ customer_name
├─ total (decimal)
├─ status (completed)
├─ paid_at (timestamp)
└─ items (HasMany OrderItem)

  CLASS: OrderItem
  ├─ id
  ├─ order_id
  ├─ product_id
  ├─ quantity
  └─ price (decimal)

    CLASS: Product
    ├─ id (UUID)
    ├─ user_id
    ├─ photo_id
    ├─ type (print_poster, print_mug, etc)
    ├─ name
    ├─ price
    └─ photo (BelongsTo)

      CLASS: Photo
      ├─ id (UUID)
      ├─ user_id
      ├─ path (storage/photos/...)
      └─ url → storage/{path}

COLLECTED DATA FOR PRODIGI:
{
  "idempotencyKey": "order_uuid_timestamp",
  "shipments": [
    {
      "recipient": {
        "name": "customer_name",
        "email": "customer_email",
        "phoneNumber": "+1-555-xxxx",
        "address": {
          "line1": "...",
          "city": "...",
          "county": "...",
          "postcode": "...",
          "countryCode": "US"
        }
      },
      "lineItems": [
        {
          "sku": "PSTR_A2_GLOSS",          ← from mapProductToPraigiSku()
          "quantity": 1,
          "assets": [
            {
              "printArea": "default",
              "assetType": "image",
              "imageUrl": "https://app.com/storage/photos/..."
            }
          ]
        }
      ]
    }
  ]
}

Prodigi API Response:
{
  "id": "PROD123456",
  "status": "received",
  "charge": {
    "amount": 12.50,
    "currency": "USD"
  }
}

Stored in Order Model:
├─ prodigi_order_id = "PROD123456"
├─ prodigi_status = "received"
├─ prodigi_charge = 12.50
└─ prodigi_submitted_at = now()
```

## Status Flow - Prodigi Order Lifecycle

```
Order Submitted
     ↓
  pending
  ├─ Order received by Prodigi
  ↓
  received
  ├─ Order validation in progress
  ↓
  validated
  ├─ Order ready for production
  ↓
  processing
  ├─ Order being produced
  ↓
  shipped ✓
  ├─ Order dispatched
  ├─ tracking_number: "..."
  └─ prodigi_shipped_at: timestamp
  
OR → failed ✗
  ├─ prodigi_error_message: "..."
  └─ Manual intervention needed

OR → cancelled ✗
  ├─ Manual cancellation
  └─ Refund processed
```

## Database Relationships

```
User
├─ hasMany Orders
├─ hasMany Products
└─ hasMany Photos

Order
├─ belongsTo User
├─ hasMany OrderItems
├─ prodigi_order_id (nullable, index)
├─ prodigi_status (enum)
├─ prodigi_charge (decimal)
├─ prodigi_error_message (text)
└─ prodigi_submitted_at (timestamp)

OrderItem
├─ belongsTo Order
├─ belongsTo Product
├─ quantity
└─ price

Product
├─ belongsTo User
├─ belongsTo Photo
├─ hasMany OrderItems
├─ type (enum: print_*)
└─ price

Photo
├─ belongsTo User
├─ hasMany Products
└─ path (storage location)
```

## Queue Processing

```
LaravelApplication
    │
    ├─ Event: Payment Confirmed
    │
    └─ Dispatch Job: SubmitOrderToProdigi
         │
         ├─ Stored in Queue
         │  └─ Redis / Database / SQS
         │
         └─ Queue Worker Processes
            │
            ├─ Pulls job from queue
            │
            ├─ Executes handle()
            │  └─ ProdigiOrderService::submitOrderToProdigi()
            │
            ├─ On Success:
            │  └─ Remove from queue
            │
            └─ On Failure:
               ├─ Retry (up to 3 times)
               └─ Move to failed queue if max retries
```

## Command Execution Flow

```
php artisan prodigi:sync-orders
   │
   ├─ Find all orders with prodigi_order_id
   ├─ Filter by status not in ['shipped', 'cancelled']
   │
   └─ For each order:
      │
      ├─ Dispatch SyncProdigiOrderStatus job
      │
      └─ Each job:
         │
         ├─ ProdigiService::getOrderStatus()
         │
         └─ Update $order->prodigi_status
```

## Error Handling Flow

```
Job Execution
    │
    ├─ Try: ProdigiService::createOrder()
    │
    ├─ Catch Exception (1st attempt)
    │  └─ Log error
    │  └─ Retry (2 more times)
    │
    ├─ Catch Exception (2nd attempt)
    │  └─ Log error
    │  └─ Retry (1 more time)
    │
    ├─ Catch Exception (3rd attempt)
    │  └─ Call failed() method:
    │     ├─ Log critical error
    │     ├─ Update $order->prodigi_status = 'failed'
    │     └─ Store error in prodigi_error_message
    │
    └─ On Success:
       └─ Update order with prodigi_order_id
```

## Integration Points Matrix

```
┌─────────────────┬──────────────────┬──────────────────────┐
│ Integration     │ File             │ Must Implement       │
├─────────────────┼──────────────────┼──────────────────────┤
│ Payment         │ Your webhook     │ dispatch() job       │
│ Confirmed       │ handler          │ after payment        │
├─────────────────┼──────────────────┼──────────────────────┤
│ Event           │ EventService     │ Register listener    │
│ Listener        │ Provider.php     │ in provider          │
├─────────────────┼──────────────────┼──────────────────────┤
│ Address         │ ProdigiOrder     │ Custom method to     │
│ Collection      │ Service.php      │ fetch address        │
├─────────────────┼──────────────────┼──────────────────────┤
│ Product         │ ProdigiOrder     │ Custom SKU mapping   │
│ Mapping         │ Service.php      │ function             │
├─────────────────┼──────────────────┼──────────────────────┤
│ Phone           │ ProdigiOrder     │ Custom method to     │
│ Collection      │ Service.php      │ fetch phone          │
├─────────────────┼──────────────────┼──────────────────────┤
│ Image URLs      │ ProdigiOrder     │ Verify photo URLs    │
│ Verification    │ Service.php      │ are accessible       │
├─────────────────┼──────────────────┼──────────────────────┤
│ Status Sync     │ Command /        │ Schedule task or     │
│ Schedule        │ Kernel.php       │ call manually        │
└─────────────────┴──────────────────┴──────────────────────┘
```

## Testing Flow

```
Test Setup
    │
    ├─ Create User
    ├─ Create Photo 
    ├─ Create Product (type: print_*)
    ├─ Create Order
    └─ Create OrderItem

Test Execution
    │
    ├─ Mock Prodigi API (optional)
    ├─ Dispatch SubmitOrderToProdigi
    ├─ Wait for job processing
    │
    └─ Assertions:
       ├─ Order has prodigi_order_id
       ├─ prodigi_status is valid
       ├─ prodigi_submitted_at is set
       └─ No error message (if success)

Integration Test
    │
    ├─ Create real test account with Prodigi sandbox
    ├─ Set PRODIGI_ENVIRONMENT=sandbox
    ├─ Submit real order to sandbox
    ├─ Verify in Prodigi sandbox dashboard
    └─ Confirm status sync works
```

---

This visual representation helps understand how all components interact with each other in the Prodigi integration.
