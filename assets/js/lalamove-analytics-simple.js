/**
 * Lalamove Analytics Integration for WooCommerce (Vanilla JS Version)
 * Adds shipping payment columns to WooCommerce Analytics Orders report
 */

(function() {
    'use strict';

    // Wait for WordPress hooks to be available
    if (typeof wp === 'undefined' || !wp.hooks) {
        console.log('WordPress hooks not available');
        return;
    }

    /**
     * Add custom shipping columns to WooCommerce Analytics Orders report
     */
    function addLalamoveShippingColumns(reportTableData) {
        // Only modify the orders report
        if (!reportTableData || reportTableData.endpoint !== 'orders') {
            return reportTableData;
        }

        // Check if we have the necessary data
        if (!reportTableData.headers || !reportTableData.rows || !reportTableData.items || !reportTableData.items.data) {
            return reportTableData;
        }

        console.log('Adding Lalamove columns to analytics report');

        // Add new headers for shipping data
        const newHeaders = [
            ...reportTableData.headers,
            {
                label: 'Shipping Type',
                key: 'lalamove_shipping_type',
                required: false,
                isLeftAligned: true,
            },
            {
                label: 'Actual Cost',
                key: 'lalamove_actual_cost',
                required: false,
                isCurrency: true,
                isNumeric: true,
            },
            {
                label: 'Profit/Loss',
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
            
            switch (item.lalamove_shipping_type) {
                case 'free':
                    shippingTypeDisplay = 'Free Shipping';
                    break;
                case 'admin_paid':
                    shippingTypeDisplay = 'Admin Paid';
                    break;
                case 'customer_paid':
                    shippingTypeDisplay = 'Customer Paid';
                    break;
                default:
                    shippingTypeDisplay = 'No Lalamove';
            }

            // Format currency values
            const formatCurrency = (value) => {
                if (!value || value === 0) return '-';
                const symbol = window.lalamoveAnalytics?.currency_symbol || '$';
                return symbol + parseFloat(value).toFixed(2);
            };

            // Format profit/loss
            const profitLoss = parseFloat(item.lalamove_profit_loss || 0);
            const profitLossDisplay = formatCurrency(profitLoss);

            const newRow = [
                ...row,
                {
                    display: shippingTypeDisplay,
                    value: item.lalamove_shipping_type || '',
                },
                {
                    display: formatCurrency(item.lalamove_actual_cost),
                    value: parseFloat(item.lalamove_actual_cost || 0),
                },
                {
                    display: profitLossDisplay,
                    value: profitLoss,
                },
            ];

            return newRow;
        });

        // Update the report data
        reportTableData.headers = newHeaders;
        reportTableData.rows = newRows;

        console.log('Lalamove columns added successfully');
        return reportTableData;
    }

    /**
     * Add CSS styles for the shipping columns
     */
    function addLalamoveAnalyticsStyles() {
        if (document.getElementById('lalamove-analytics-styles')) {
            return; // Already added
        }

        const styles = document.createElement('style');
        styles.id = 'lalamove-analytics-styles';
        styles.textContent = `
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
        `;
        
        document.head.appendChild(styles);
        console.log('Lalamove analytics styles added');
    }

    // Register the filter when hooks are available
    function initializeLalamoveAnalytics() {
        if (wp.hooks && wp.hooks.addFilter) {
            wp.hooks.addFilter(
                'woocommerce_admin_report_table',
                'lalamove/analytics-table',
                addLalamoveShippingColumns
            );
            
            console.log('Lalamove analytics filter registered');
            addLalamoveAnalyticsStyles();
        } else {
            // Retry in 100ms if hooks aren't ready
            setTimeout(initializeLalamoveAnalytics, 100);
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeLalamoveAnalytics);
    } else {
        initializeLalamoveAnalytics();
    }

    // Also initialize on window load (for SPA navigation)
    window.addEventListener('load', () => {
        addLalamoveAnalyticsStyles();
        initializeLalamoveAnalytics();
    });

})();