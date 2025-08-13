# WooCommerce Lalamove Extension - Shipping Cost Management

## Overview

This plugin extends WooCommerce with Lalamove delivery services and provides comprehensive shipping cost management features that allow you to distinguish between admin-paid and customer-paid shipping costs.

## New Features

### 1. Shipping Cost Strategies

The plugin now supports three shipping cost strategies:

- **Customer Pays Full Cost**: Customer pays the full Lalamove shipping cost (with optional markup)
- **Admin Pays Full Cost**: Admin/seller absorbs the full shipping cost
- **Split Cost**: Cost is shared between customer and admin based on configurable percentages

### 2. Admin Settings

In WooCommerce → Settings → Shipping → Lalamove, you can configure:

- **Shipping Cost Strategy**: Choose who pays for shipping
- **Admin Cost Percentage**: When using split strategy, what percentage admin pays (default: 50%)
- **Markup Percentage**: Additional markup on shipping costs when customer pays (default: 0%)

### 3. Shipping Analytics Dashboard

Access detailed analytics at **WooCommerce → Lalamove Analytics**:

- **Summary Cards**: Total orders, customer shipping revenue, admin costs, Lalamove costs, net profit/loss
- **Payment Responsibility Breakdown**: Count of orders by payment type
- **Recent Orders Table**: Detailed view of shipping costs for each order

### 4. Order-Level Cost Tracking

Each order now tracks:
- `shipping_cost_customer`: Amount customer paid for shipping
- `shipping_cost_admin`: Amount admin paid for shipping
- `shipping_paid_by`: Who paid (customer/admin/split)
- `lalamove_actual_cost`: Actual cost charged by Lalamove
- `profit_loss`: Net profit/loss on shipping

## How It Works

### 1. Cost Calculation

1. **Lalamove API** calculates the actual shipping cost
2. **Plugin settings** determine the cost strategy
3. **Cost breakdown** is calculated and stored in session
4. **Shipping rate** is applied to cart/checkout
5. **Order meta** stores the complete cost breakdown

### 2. Database Schema

New fields added to `wp_wc_lalamove_orders` table:
```sql
shipping_cost_customer DOUBLE DEFAULT 0.00
shipping_cost_admin DOUBLE DEFAULT 0.00
shipping_paid_by ENUM('customer', 'admin', 'split') DEFAULT 'customer'
lalamove_actual_cost DOUBLE DEFAULT 0.00
profit_loss DOUBLE DEFAULT 0.00
```

### 3. Order Processing

- Cost breakdown is saved to order meta during checkout
- Admin can view shipping details in order page
- Analytics dashboard provides comprehensive reporting

## Usage Examples

### Example 1: Customer Pays Full Cost
- Lalamove cost: ₱150
- Markup: 10%
- Customer pays: ₱165
- Admin pays: ₱0
- Profit: ₱15

### Example 2: Admin Pays Full Cost
- Lalamove cost: ₱150
- Customer pays: ₱0
- Admin pays: ₱150
- Profit: -₱150 (loss)

### Example 3: Split Cost (50/50)
- Lalamove cost: ₱150
- Customer pays: ₱75
- Admin pays: ₱75
- Profit: ₱0

## Analytics Benefits

### For Sellers/Admins

1. **Clear Cost Visibility**: See exactly how much you're spending on shipping
2. **Profit Analysis**: Track shipping profit/loss per order and overall
3. **Strategy Optimization**: Analyze which cost strategy works best
4. **Financial Planning**: Better budget allocation for shipping costs

### For Business Intelligence

1. **Customer Behavior**: Understand shipping cost sensitivity
2. **Competitive Analysis**: Compare your shipping costs with market rates
3. **Operational Efficiency**: Identify cost-saving opportunities
4. **Revenue Optimization**: Balance customer experience with profitability

## Installation & Setup

1. **Activate Plugin**: The new features are automatically available
2. **Configure Shipping**: Go to WooCommerce → Settings → Shipping → Lalamove
3. **Set Strategy**: Choose your preferred shipping cost strategy
4. **View Analytics**: Access WooCommerce → Lalamove Analytics

## Migration

Existing orders will automatically get the new fields with default values:
- `shipping_paid_by`: 'customer'
- All cost fields: 0.00

## Support

For questions or issues with the shipping cost management features, please refer to the plugin documentation or contact support.

## Changelog

### Version 2.0
- Added shipping cost strategies (customer/admin/split)
- Implemented comprehensive cost tracking
- Created analytics dashboard
- Added order-level cost breakdown
- Enhanced admin interface for cost management