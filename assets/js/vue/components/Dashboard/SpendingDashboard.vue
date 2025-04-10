<template>
    <div class="spending-wrapper">
        <div class="utility-actions">
            Utility Actions
        </div>
        
        <div class="key-performance-indicator-container">
            <div class="key-performance-indicator bordered">
                <p>
                    Total Spending
                    <span class="material-symbols-outlined icon">payments</span>
                </p>
                <h2 style="margin-bottom: 0;">{{ formatNumber(netSpending) }}</h2>
            </div>
            <div class="key-performance-indicator bordered">
                <p>
                    Net Spending
                    <span class="material-symbols-outlined icon">account_balance</span>
                </p>
                <h2 style="margin-bottom: 0;">{{ formatNumber(netSpending) }}</h2>
            </div>
            <div class="key-performance-indicator bordered">
                <p>
                    Customer Spent
                    <span class="material-symbols-outlined icon">account_circle</span>
                </p>
                <h2 style="margin-bottom: 0;">{{ formatNumber(customerSpent) }}</h2>
            </div>
            <div class="key-performance-indicator bordered">
                <p>
                    Base Delivery Cost
                    <span class="material-symbols-outlined icon">local_shipping</span>
                </p>
                <h2 style="margin-bottom: 0;">{{ formatNumber(baseDeliveryCost) }}</h2>
            </div>
            <div class="key-performance-indicator bordered">
                <p>
                    Shipping Subsidy
                    <span class="material-symbols-outlined icon">account_balance</span>
                </p>
                <h2 style="margin-bottom: 0;">{{ formatNumber(shippingSubsidy) }}</h2>
            </div>
            <div class="key-performance-indicator bordered">
                <p>
                    Priority Fee
                    <span class="material-symbols-outlined icon">attach_money</span>
                </p>
                <h2 style="margin-bottom: 0;">{{ formatNumber(priorityFee) }}</h2>
            </div>
        </div>

        <!-- First Section -->
        <section class="first-section ">
            <p style="width: 120%;">Hello </p>
            <ServiceCostBreakdown class="bordered"/>
        </section>

        <!-- Second Section -->
        <WalletBalanceTrend class="bordered WalletBalanceTrend"/>
        <ShippingSpendingTrend class="bordered ShippingSpendingTrend"/>

        <!-- Third Section -->
        <SubsidyVsCustomerPaid class="bordered SubsidyVsCustomerPaid"/>
        <AdditionalFeeFrequency class="bordered AdditionalFeeFrequency"/>

    </div>
</template>

<script setup>
import AdditionalFeeFrequency from './Charts/Spending/AdditionalFeeFrequency.vue';
import ServiceCostBreakdown from './Charts/Spending/ServiceCostBreakdown.vue';
import ShippingSpendingTrend from './Charts/Spending/ShippingSpendingTrend.vue';
import SubsidyVsCustomerPaid from './Charts/Spending/SubsidyVsCustomerPaid.vue';
import WalletBalanceTrend from './Charts/Spending/WalletBalanceTrend.vue';

const netSpending = 3123214;
const customerSpent = 1234567;
const baseDeliveryCost = 987654;
const shippingSubsidy = 54321;
const priorityFee = 12345;

function formatNumber(num) {
  if (num >= 1_000_000) {
    return (num / 1_000_000).toFixed(1) + 'M';
  } else if (num >= 1_000) {
    return (num / 1_000).toFixed(1) + 'K';
  }
  return num.toString(); 
}


</script>

<style lang="scss" scoped>
@use '@/css/scss/_variables.scss' as *;



* {
    box-sizing: border-box;
}

.spending-wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: auto auto auto auto;
    grid-template-areas: 
        "utility-actions utility-actions"
        "key-performance-indicator key-performance-indicator"
        "first-section first-section"
        "WalletBalanceTrend ShippingSpendingTrend"
        "SubsidyVsCustomerPaid AdditionalFeeFrequency";
    width: 100%;
    height: 100%;
    gap: 1rem;

    .key-performance-indicator-container {
        grid-area: key-performance-indicator;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        padding: 0;

        p {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0;
        }

        .icon {
            font-size: $font-size-md;
            margin-left: 3rem
        }
    }

    .utility-actions {
        grid-area: utility-actions;
    }

    .key-performance-indicator {
        grid-area: key-performance-indicator;
    }

    .first-section {
        grid-area: first-section;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }

    .WalletBalanceTrend {
        grid-area: WalletBalanceTrend;
    }

    .ShippingSpendingTrend {
        grid-area: ShippingSpendingTrend;
    }
    
    .SubsidyVsCustomerPaid {
        grid-area: SubsidyVsCustomerPaid;
    }

    .AdditionalFeeFrequency {
        grid-area: AdditionalFeeFrequency ;
    }

    .third-section {
        grid-area: third-section;
        display: flex;
        flex-direction: row;
    }
}

.bordered {
    background-color: #FCFCFC;
    padding: 1rem;
    border-radius: 0.5rem;
    border: 2px solid $border-color;
}
</style>