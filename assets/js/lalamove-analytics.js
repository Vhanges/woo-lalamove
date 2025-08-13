/**
 * Lalamove Analytics Integration for WooCommerce
 * Adds shipping payment columns to WooCommerce Analytics Orders report
 */

import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

/**
 * Add custom shipping columns to WooCommerce Analytics Orders report
 */
const addLalamoveShippingColumns = (reportTableData) => {
    // Only modify the orders report
    if (!reportTableData || reportTableData.endpoint !== 'orders') {
        return reportTableData;
    }

    // Check if we have the necessary data
    if (!reportTableData.headers || !reportTableData.rows || !reportTableData.items?.data) {
        return reportTableData;
    }

    // Add new headers for shipping data
    const newHeaders = [
        ...reportTableData.headers,
        {
            label: __('Shipping Type', 'woocommerce-lalamove-extension'),
            key: 'lalamove_shipping_type',
            required: false,
            isLeftAligned: true,
        },
        {
            label: __('Actual Cost', 'woocommerce-lalamove-extension'),
            key: 'lalamove_actual_cost',
            required: false,
            isCurrency: true,
            isNumeric: true,
        },
        {
            label: __('Profit/Loss', 'woocommerce-lalamove-extension'),
            key: 'lalamove_profit_loss',
            required: false,
            isCurrency: true,
            isNumeric: true,
        },
    ];

    // Add new rows with shipping data
    const newRows = reportTableData.rows.map((row, index) => {
        const item = reportTableData.items.data[index];
        
        if (!item) {
            return row;
        }

        // Format shipping type display
        let shippingTypeDisplay = '-';
        let shippingTypeClass = '';
        
        switch (item.lalamove_shipping_type) {
            case 'free':
                shippingTypeDisplay = __('Free Shipping', 'woocommerce-lalamove-extension');
                shippingTypeClass = 'lalamove-shipping-free';
                break;
            case 'admin_paid':
                shippingTypeDisplay = __('Admin Paid', 'woocommerce-lalamove-extension');
                shippingTypeClass = 'lalamove-shipping-admin';
                break;
            case 'customer_paid':
                shippingTypeDisplay = __('Customer Paid', 'woocommerce-lalamove-extension');
                shippingTypeClass = 'lalamove-shipping-customer';
                break;
            default:
                shippingTypeDisplay = __('No Lalamove', 'woocommerce-lalamove-extension');
                shippingTypeClass = 'lalamove-shipping-none';
        }

        // Format currency values
        const formatCurrency = (value) => {
            if (!value || value === 0) return '-';
            const symbol = lalamoveAnalytics?.currency_symbol || '$';
            return `${symbol}${parseFloat(value).toFixed(2)}`;
        };

        // Format profit/loss with color coding
        const profitLoss = parseFloat(item.lalamove_profit_loss || 0);
        let profitLossDisplay = formatCurrency(profitLoss);
        let profitLossClass = '';
        
        if (profitLoss > 0) {
            profitLossClass = 'lalamove-profit';
        } else if (profitLoss < 0) {
            profitLossClass = 'lalamove-loss';
        }

        const newRow = [
            ...row,
            {
                display: (
                    <span className={shippingTypeClass}>
                        {shippingTypeDisplay}
                    </span>
                ),
                value: item.lalamove_shipping_type || '',
            },
            {
                display: formatCurrency(item.lalamove_actual_cost),
                value: parseFloat(item.lalamove_actual_cost || 0),
            },
            {
                display: (
                    <span className={profitLossClass}>
                        {profitLossDisplay}
                    </span>
                ),
                value: profitLoss,
            },
        ];

        return newRow;
    });

    // Update the report data
    reportTableData.headers = newHeaders;
    reportTableData.rows = newRows;

    return reportTableData;
};

/**
 * Add CSS styles for the shipping columns
 */
const addLalamoveAnalyticsStyles = () => {
    const styles = `
        <style id="lalamove-analytics-styles">
            .lalamove-shipping-free {
                color: #00a32a;
                font-weight: bold;
                background: #e7f5e7;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 11px;
            }
            
            .lalamove-shipping-admin {
                color: #d63638;
                font-weight: bold;
                background: #fce8e8;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 11px;
            }
            
            .lalamove-shipping-customer {
                color: #0073aa;
                font-weight: bold;
                background: #e8f4f8;
                padding: 2px 6px;
                border-radius: 3px;
                font-size: 11px;
            }
            
            .lalamove-shipping-none {
                color: #666;
                font-style: italic;
                font-size: 11px;
            }
            
            .lalamove-profit {
                color: #00a32a;
                font-weight: bold;
            }
            
            .lalamove-loss {
                color: #d63638;
                font-weight: bold;
            }
            
            /* Table column widths */
            .woocommerce-table__table th[data-key="lalamove_shipping_type"],
            .woocommerce-table__table td[data-key="lalamove_shipping_type"] {
                width: 120px;
                white-space: nowrap;
            }
            
            .woocommerce-table__table th[data-key="lalamove_actual_cost"],
            .woocommerce-table__table td[data-key="lalamove_actual_cost"],
            .woocommerce-table__table th[data-key="lalamove_profit_loss"],
            .woocommerce-table__table td[data-key="lalamove_profit_loss"] {
                width: 100px;
                text-align: right;
            }
        </style>
    `;
    
    // Add styles to the document head
    if (!document.getElementById('lalamove-analytics-styles')) {
        document.head.insertAdjacentHTML('beforeend', styles);
    }
};

/**
 * Add summary stats to the analytics summary cards
 */
const addLalamoveAnalyticsSummary = (summaryData) => {
    // Only modify orders summary
    if (!summaryData || summaryData.report !== 'orders') {
        return summaryData;
    }

    // Add shipping-specific summary cards
    if (summaryData.data && summaryData.data.totals) {
        const totals = summaryData.data.totals;
        
        // Add Lalamove-specific totals if available
        if (totals.lalamove_total_admin_cost !== undefined) {
            summaryData.data.totals.lalamove_admin_shipping_cost = totals.lalamove_total_admin_cost;
            summaryData.data.totals.lalamove_shipping_profit_loss = totals.lalamove_total_profit_loss;
            summaryData.data.totals.lalamove_admin_paid_count = totals.lalamove_admin_paid_orders;
        }
    }

    return summaryData;
};

// Register the filters
addFilter(
    'woocommerce_admin_report_table',
    'lalamove/analytics-table',
    addLalamoveShippingColumns
);

addFilter(
    'woocommerce_admin_analytics_summary',
    'lalamove/analytics-summary',
    addLalamoveAnalyticsSummary
);

// Initialize styles when DOM is ready
document.addEventListener('DOMContentLoaded', addLalamoveAnalyticsStyles);

// Also add styles when the report loads (for SPA navigation)
window.addEventListener('load', addLalamoveAnalyticsStyles);