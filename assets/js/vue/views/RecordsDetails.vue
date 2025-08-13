<template>
  <div class="details-wrapper">
    <!-- Status Header -->
    <header v-if="!loading && !noData && status == 'ASSIGNING_DRIVER'" class="status-header">
      <div class="status-card assigning">
        <div class="status-indicator">
          <span class="status-dot"></span>
          <div class="status-text">
            <h4>Assigning Driver</h4>
            <p class="order-id">{{ lalaOrderId }}</p>
          </div>
        </div>
        <p class="schedule-date">{{ scheduleData }}</p>
      </div>
      
      <div class="header-content">
        <div class="status-message">
          <h3>🔍 Finding Nearby Driver</h3>
          <p>Please wait while we assign a driver to your order</p>
        </div>
        
        <div class="action-buttons">
          <button @click="openShareLink" class="btn-primary">
            <span class="material-symbols-outlined">location_searching</span>
            Track Order
          </button>
          <button v-if="isCancelAvailable" @click="cancelOrder(lalaOrderId)" class="btn-secondary">
            <span class="material-symbols-outlined">close</span>
            Cancel Order
          </button>
        </div>
      </div>
    </header>

    <header v-if="!loading && !noData && status == 'ON_GOING'" class="status-header">
      <div class="status-card ongoing">
        <div class="status-indicator">
          <span class="status-dot"></span>
          <div class="status-text">
            <h4>Driver Assigned</h4>
            <p class="order-id">{{ lalaOrderId }}</p>
          </div>
        </div>
        <p class="schedule-date">{{ scheduleData }}</p>
      </div>
      
      <div class="header-content">
        <div class="driver-info">
          <h3>👨‍🚗 {{ driverName }}</h3>
          <div class="driver-details">
            <p><strong>Plate:</strong> {{ driverPlateNumber }}</p>
            <p><strong>Contact:</strong> {{ driverContactNo }}</p>
          </div>
        </div>
        
        <div class="status-message pickup">
          <p>🚗 The driver is on their way to pick up your order</p>
        </div>
        
        <div class="action-buttons">
          <button @click="openShareLink" class="btn-primary">
            <span class="material-symbols-outlined">location_searching</span>
            Track Order
          </button>
          <button v-if="isCancelAvailable" @click="cancelOrder(lalaOrderId)" class="btn-secondary">
            <span class="material-symbols-outlined">close</span>
            Cancel Order
          </button>
        </div>
      </div>
    </header>

    <header v-if="!loading && !noData && status == 'PICKED_UP'" class="status-header">
      <div class="status-card picked-up">
        <div class="status-indicator">
          <span class="status-dot"></span>
          <div class="status-text">
            <h4>Picked Up</h4>
            <p class="order-id">{{ lalaOrderId }}</p>
          </div>
        </div>
        <p class="schedule-date">{{ scheduleData }}</p>
      </div>
      
      <div class="header-content">
        <div class="driver-info">
          <h3>👨‍🚗 {{ driverName }}</h3>
          <div class="driver-details">
            <p><strong>Plate:</strong> {{ driverPlateNumber }}</p>
            <p><strong>Contact:</strong> {{ driverContactNo }}</p>
          </div>
        </div>
        
        <div class="status-message delivery">
          <p>📦 Your order has been picked up and is on the way!</p>
        </div>
        
        <div class="action-buttons">
          <button @click="openShareLink" class="btn-primary">
            <span class="material-symbols-outlined">location_searching</span>
            Track Order
          </button>
        </div>
      </div>
    </header>

    <header v-if="!loading && !noData && status == 'COMPLETED'" class="status-header">
      <div class="status-card completed">
        <div class="status-indicator">
          <span class="status-dot"></span>
          <div class="status-text">
            <h4>Completed</h4>
            <p class="order-id">{{ lalaOrderId }}</p>
          </div>
        </div>
        <p class="schedule-date">{{ scheduleData }}</p>
      </div>
      
      <div class="header-content">
        <div class="driver-info">
          <h3>👨‍🚗 {{ driverName }}</h3>
          <div class="driver-details">
            <p><strong>Plate:</strong> {{ driverPlateNumber }}</p>
            <p><strong>Contact:</strong> {{ driverContactNo }}</p>
          </div>
        </div>
        
        <div class="status-message success">
          <p>✅ Order delivered successfully!</p>
        </div>
        
        <div class="action-buttons">
          <button @click="openShareLink" class="btn-primary">
            <span class="material-symbols-outlined">receipt_long</span>
            View Details
          </button>
        </div>
      </div>
    </header>

    <header v-if="!loading && !noData && status == 'REJECTED'" class="status-header">
      <div class="status-card rejected">
        <div class="status-indicator">
          <span class="status-dot"></span>
          <div class="status-text">
            <h4>Order Rejected</h4>
            <p class="order-id">{{ lalaOrderId }}</p>
          </div>
        </div>
        <p class="schedule-date">{{ scheduleData }}</p>
      </div>
      
      <div class="header-content">
        <div class="status-message error">
          <h3>❌ Order Rejected</h3>
          <p>Your order was rejected twice by drivers. Please try placing a new order.</p>
        </div>
      </div>
    </header>

    <header v-if="!loading && !noData && status == 'CANCELED'" class="status-header">
      <div class="status-card canceled">
        <div class="status-indicator">
          <span class="status-dot"></span>
          <div class="status-text">
            <h4>Order Canceled</h4>
            <p class="order-id">{{ lalaOrderId }}</p>
          </div>
        </div>
        <p class="schedule-date">{{ scheduleData }}</p>
      </div>
      
      <div class="header-content">
        <div class="status-message error">
          <h3>🚫 Order Canceled</h3>
          <p>Your order has been canceled or rejected by drivers twice.</p>
        </div>
      </div>
    </header>

    <header v-if="!loading && !noData && status == 'EXPIRED'" class="status-header">
      <div class="status-card expired">
        <div class="status-indicator">
          <span class="status-dot"></span>
          <div class="status-text">
            <h4>Order Expired</h4>
            <p class="order-id">{{ lalaOrderId }}</p>
          </div>
        </div>
        <p class="schedule-date">{{ scheduleData }}</p>
      </div>
      
      <div class="header-content">
        <div class="status-message warning">
          <h3>⏰ Order Expired</h3>
          <p>The order expired after no driver accepted it within two hours.</p>
        </div>
      </div>
    </header>

    <!-- Skeleton Loader -->
    <div v-if="loading && !noData" class="skeleton-wrapper">
      <div class="skeleton-header">
        <div class="skeleton-status-card">
          <div class="skeleton-indicator">
            <div class="skeleton-dot"></div>
            <div class="skeleton-text">
              <div class="skeleton-line skeleton-line-lg"></div>
              <div class="skeleton-line skeleton-line-sm"></div>
            </div>
          </div>
          <div class="skeleton-line skeleton-line-md"></div>
        </div>
        
        <div class="skeleton-content">
          <div class="skeleton-line skeleton-line-lg"></div>
          <div class="skeleton-line skeleton-line-md"></div>
          <div class="skeleton-buttons">
            <div class="skeleton-button"></div>
            <div class="skeleton-button"></div>
          </div>
        </div>
      </div>
      
      <div class="skeleton-main">
        <div class="skeleton-section">
          <div class="skeleton-line skeleton-line-lg"></div>
          <div class="skeleton-timeline">
            <div class="skeleton-location">
              <div class="skeleton-marker"></div>
              <div class="skeleton-details">
                <div class="skeleton-line skeleton-line-md"></div>
                <div class="skeleton-line skeleton-line-lg"></div>
                <div class="skeleton-line skeleton-line-sm"></div>
              </div>
            </div>
            <div class="skeleton-location">
              <div class="skeleton-marker"></div>
              <div class="skeleton-details">
                <div class="skeleton-line skeleton-line-md"></div>
                <div class="skeleton-line skeleton-line-lg"></div>
                <div class="skeleton-line skeleton-line-sm"></div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="skeleton-info-grid">
          <div class="skeleton-card">
            <div class="skeleton-line skeleton-line-sm"></div>
            <div class="skeleton-line skeleton-line-md"></div>
          </div>
          <div class="skeleton-card">
            <div class="skeleton-line skeleton-line-sm"></div>
            <div class="skeleton-line skeleton-line-md"></div>
          </div>
        </div>
        
        <div class="skeleton-section">
          <div class="skeleton-line skeleton-line-lg"></div>
          <div class="skeleton-price-list">
            <div class="skeleton-price-item" v-for="n in 4" :key="n">
              <div class="skeleton-line skeleton-line-md"></div>
              <div class="skeleton-line skeleton-line-sm"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- No Data State -->
    <div v-else-if="noData" class="no-record">
      <div class="no-data-content">
        <span class="material-symbols-outlined no-data-icon">receipt_long</span>
        <h2>No Order Found</h2>
        <p>We couldn't find any records for this order.</p>
      </div>
    </div>

    <!-- Main Content -->
    <main v-else-if="!loading" class="main-content">
      <!-- Route Section -->
      <section class="route-section">
        <h3 class="section-title">
          <span class="material-symbols-outlined">route</span>
          ROUTE
        </h3>
        
        <div class="location-timeline">
          <div class="location-item pickup">
            <div class="location-marker">
              <span class="material-symbols-outlined">radio_button_checked</span>
            </div>
            <div class="location-details">
              <h4>Pickup Location</h4>
              <p class="address">{{ senderAddress }}</p>
              <p class="contact">{{ senderName }} • {{ senderPhoneNo }}</p>
            </div>
          </div>

          <div class="route-line"></div>

          <div class="location-item dropoff">
            <div class="location-marker">
              <span class="material-symbols-outlined">location_on</span>
            </div>
            <div class="location-details">
              <h4>Drop Off Location</h4>
              <p class="address">{{ customerAddress }}</p>
              <p class="contact">{{ customerName }} • {{ customerPhoneNo }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Order Info Section -->
      <section class="info-section">
        <div class="info-grid">
          <div class="info-card">
            <h4>Placed By</h4>
            <p>{{ senderName }}</p>
          </div>
          <div class="info-card">
            <h4>Service Type</h4>
            <p>{{ serviceType }}</p>
          </div>
        </div>
      </section>

      <!-- Price Breakdown Section -->
      <section class="price-section">
        <h3 class="section-title">
          <span class="material-symbols-outlined">payments</span>
          PRICE BREAKDOWN
        </h3>
        
        <div class="price-list">
          <div v-if="basePrice" class="price-item">
            <span>Base Price</span>
            <span class="price">{{ basePrice }}</span>
          </div>
          <div v-if="extraMileage" class="price-item">
            <span>Additional Distance Fee</span>
            <span class="price">{{ extraMileage }}</span>
          </div>
          <div v-if="specialRequest" class="price-item">
            <span>Special Request Fee</span>
            <span class="price">{{ specialRequest }}</span>
          </div>
          <div v-if="priorityFee" class="price-item">
            <span>Priority Fee</span>
            <span class="price">{{ priorityFee }}</span>
          </div>
          <div v-if="surcharge" class="price-item">
            <span>Surcharge</span>
            <span class="price">{{ surcharge }}</span>
          </div>
          <div v-if="totalExcludedPriorityFee" class="price-item subtotal">
            <span>Subtotal</span>
            <span class="price">{{ totalExcludedPriorityFee }}</span>
          </div>
          <div class="price-item total">
            <span>Total</span>
            <span class="price">{{ total }}</span>
          </div>
        </div>
      </section>

      <!-- Proof of Delivery -->
      <section v-if="podImage" class="pod-section">
        <h3 class="section-title">
          <span class="material-symbols-outlined">photo_camera</span>
          PROOF OF DELIVERY
        </h3>
        <div class="pod-image">
          <img :src="podImage" alt="Proof of Delivery" />
        </div>
      </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
      <button @click="$router.push({ name: 'records' })" class="btn-back">
        <span class="material-symbols-outlined">arrow_back</span>
        Back to Orders
      </button>
    </footer>
  </div>
</template>



<script setup>
import {ref, onMounted} from 'vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const status = ref();
const isCancelAvailable = ref(true);
const isOrderAgainAvailable = ref(true);

const noData = ref(false);
const loading = ref(true);

const lalaOrderId = ref();
const lalaQuotationId = ref();
const lalaOrderBody = ref();
const scheduleData = ref();
const shareLink = ref();

// Customer & Sender Info

const senderAddress = ref();
const senderName = ref();
const senderPhoneNo = ref();

const customerAddress = ref();
const customerName = ref();
const customerPhoneNo = ref();

// Service & Pricing Info
const serviceType = ref();
const addService = ref();

const basePrice = ref();
const extraMileage = ref();
const surcharge = ref();
const specialRequest = ref();
const priorityFee = ref();
const totalExcludedPriorityFee = ref();
const total = ref();

const podImage = ref();
const deliveredAt = ref();

// Driver-related data
const driverId = ref();
const driverName = ref();
const driverPlateNumber = ref();
const driverContactNo = ref();



const props = defineProps({
    lala_id: String,
});

const fetchLalaOrderData = async (lala_id) => {
    
    try {
        loading.value = true;

        const orderResponse = await axios.get(
            wooLalamoveAdmin.root + 'woo-lalamove/v1/get-lala-order-details/?lala_id=' + lala_id,
            {
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wooLalamoveAdmin.api_nonce,
            },
            }
        );

        
        const order = orderResponse.data;
        console.log("ORDER RESPONSE: ", order);
        

        if (!order.data || order.data.errors) {
            throw new Error('LALAMOVE: No data for this order');
        }

        status.value = order.data.status;

        console.log("STATUS: ", status);
        lalaOrderId.value = order.data.orderId;
        console.log("ORDER ID", lalaOrderId.value);
        lalaQuotationId.value = order.data.quotationId;
        driverId.value = order.data.driverId;
        shareLink.value = order.data.shareLink;

        console.log("SHARELINK", shareLink.value);


        const stops = order.data.stops;
        if (Array.isArray(stops) && stops.length > 0) {
            const firstStop = stops[0] || {};
            const lastStop = stops[stops.length - 1] || {};

            senderAddress.value = firstStop.address || '';
            senderName.value = firstStop.name || '';
            senderPhoneNo.value = firstStop.phone || '';

            customerAddress.value = lastStop.address || '';
            customerName.value = lastStop.name || '';
            customerPhoneNo.value = lastStop.phone || '';
            podImage.value = lastStop.image || '';
            deliveredAt.value = lastStop.deliveredAt || '';
        }


        const priceBreakdown = order.data.priceBreakdown;

        basePrice.value = priceBreakdown.base;
        extraMileage.value = priceBreakdown.extraMileage;
        priorityFee.value = priceBreakdown.priorityFee;
        surcharge.value = priceBreakdown.surcharge;
        specialRequest.value = priceBreakdown.specialRequest;
        totalExcludedPriorityFee.value = priceBreakdown.totalExcludedPriorityFee;
        total.value = priceBreakdown.total;


        const driverResponse = await axios.get(
            wooLalamoveAdmin.root + 'woo-lalamove/v1/get-lala-driver-details/?lala_id=' + lala_id + '&driver_id=' + driverId.value,
            {
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wooLalamoveAdmin.api_nonce,
            },
            }
            
        );
        

        const lalaOrderBody = await axios.get(
            wooLalamoveAdmin.root + 'woo-lalamove/v1/lalamove-order-body/?lala_id=' + lala_id ,
            {
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wooLalamoveAdmin.api_nonce,
            },
            }
        );

        console.log("ORDER BODY", lalaOrderBody.data);

        serviceType.value = lalaOrderBody.data[0].service_type;
        scheduleData.value = new Date(
        lalaOrderBody.data[0].scheduled_on.replace(' ', 'T')
        ).toLocaleString('en-PH', {
            year:   'numeric',
            month:  'long',
            day:    'numeric',
            hour:   '2-digit',
            minute: '2-digit'
        });

        lalaOrderBody.value = lalaOrderBody.data[0].order_json_body;

        const driver = driverResponse.data.data;
        
        if(!driver || driver.errors){
          loading.value = false;
            return;
        } else {
            console.log("DRIVER RESPONSE: ", driver);

            driverName.value = driver.name;
            driverContactNo.value = driver.phone;
            driverPlateNumber.value = driver.plateNumber;
        }

        loading.value = false;
    } catch (error) {
      console.error('Error fetching Woo Lalamove Orders:', error);
        noData.value = true;
    } 

};

const openShareLink = () => {
  if (shareLink.value) {
    window.open(shareLink.value, '_blank');
  } else {
    toast.error('An error occured. Please try again later', { autoClose: 2000 });
  }
};

const cancelOrder = async (id) => { 

  try {
    if (!confirm('Are you sure you want to cancel this order?')) return;

    const response = await axios.delete(
      `${wooLalamoveAdmin.root}woo-lalamove/v1/cancel-order/`,
      {
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': wooLalamoveAdmin.api_nonce,
        },
        params: {
          lala_id: props.lala_id, 
          body: lalaOrderBody,
        }   
      }
    );

    toast.success('Order canceled successfully!');
    await fetchLalaOrderData(props.lala_id, props.wc_id); 
    
  } catch (error) {
    console.error('Cancel error:', error);
    toast.error(error.response?.data?.message || 'Failed to cancel order');
  }
};

const addPriorityFee = () => {
    console.log("HELLLOOOOOOOOOO");
};
onMounted(() => {
    fetchLalaOrderData(props.lala_id, props.wc_id);
});

</script>



<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;
@use 'sass:color';

.details-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  padding: 1rem;
  padding-bottom: 10rem;

  @media (max-width: 768px) {
    padding: 0.5rem;
    padding-bottom: 5rem;
  }
}

// Status Header Styles
.status-header {
  max-width: 900px;
  margin: 0 auto 2rem auto;
  
  .status-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    margin-bottom: 1rem;
    border-left: 4px solid;
    
    @media (max-width: 768px) {
      padding: 1rem;
      border-radius: 8px;
    }
    
    &.assigning {
      border-left-color: #FFF2B1;
      background: #FEF9E1;
      border: 2px solid #FFF2B1;
    }
    
    &.ongoing {
      border-left-color: #5578B1;
      background: #E0F0FD;
      border: 2px solid #5578B1;
    }
    
    &.picked-up {
      border-left-color: #F16622;
      background: #FFEEEF;
      border: 2px solid #F16622;
    }
    
    &.completed {
      border-left-color: #05B32B;
      background: #DBFFE3;
      border: 2px solid #05B32B;
    }
    
    &.rejected, &.canceled {
      border-left-color: #CB3F49;
      background: #FFEEEF;
      border: 2px solid #CB3F49;
    }
    
    &.expired {
      border-left-color: #4C4C4C;
      background: #EDEDED;
      border: 2px solid #4C4C4C;
    }
    
    .status-indicator {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 0.5rem;
      
      .status-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        animation: pulse 2s infinite;
        
        .assigning & { background: #FFF2B1; }
        .ongoing & { background: #5578B1; }
        .picked-up & { background: #F16622; }
        .completed & { background: #05B32B; }
        .rejected &, .canceled & { background: #CB3F49; }
        .expired & { background: #4C4C4C; }
      }
      
      .status-text {
        h4 {
          margin: 0;
          font-size: 1.25rem;
          font-weight: 600;
          color: #1f2937;
        }
        
        .order-id {
          margin: 0;
          font-size: 0.875rem;
          color: #6b7280;
        }
      }
    }
    
    .schedule-date {
      font-size: 0.875rem;
      color: #4b5563;
      margin: 0;
      font-weight: 500;
    }
  }
  
  .header-content {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
    
    @media (max-width: 768px) {
      padding: 1rem;
      border-radius: 8px;
    }
    
    .status-message {
      margin-bottom: 1.5rem;
      text-align: center;
      
      h3 {
        margin: 0 0 0.5rem 0;
        font-size: 1.25rem;
        color: $txt-primary;
      }
      
      p {
        margin: 0;
        color: #6b7280;
      }
      
      &.pickup, &.delivery {
        background: #f0f9ff;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #e0f2fe;
      }
      
      &.success {
        background: #f0fdf4;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #dcfce7;
      }
      
      &.error {
        background: #fef2f2;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #fecaca;
      }
      
      &.warning {
        background: #fffbeb;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #fed7aa;
      }
    }
    
    .driver-info {
      margin-bottom: 1.5rem;
      text-align: center;
      
      h3 {
        margin: 0 0 1rem 0;
        font-size: 1.25rem;
        color: $txt-primary;
      }
      
      .driver-details {
        display: flex;
        justify-content: center;
        gap: 2rem;
        
        @media (max-width: 768px) {
          flex-direction: column;
          gap: 0.5rem;
        }
        
        p {
          margin: 0;
          color: #6b7280;
          font-size: 0.875rem;
        }
      }
    }
  }
}

// Button Styles
.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
  
  @media (max-width: 768px) {
    flex-direction: column;
  }
}

.btn-primary, .btn-secondary, .btn-back {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.2s ease;
  cursor: pointer;
  border: none;
  
  @media (max-width: 768px) {
    padding: 1rem;
    font-size: 1rem;
  }
  
  .material-symbols-outlined {
    font-size: 1.25rem;
  }
}

.btn-primary {
  background: $bg-primary;
  color: white;

  &:hover {
    background: color.adjust($bg-primary, $lightness: -10%);
    transform: translateY(-1px);
  }
}

.btn-secondary {
  background: $bg-light;
  color: $txt-primary;
  border: 1px solid $border-color;

  &:hover {
    background: color.adjust($bg-light, $lightness: -5%);
  }
}

.btn-back {
  background: $bg-light;
  color: $txt-primary;
  border: 1px solid $border-color;
  margin-left: auto;

  &:hover {
    background: color.adjust($bg-light, $lightness: -5%);
  }
}


// Main Content Styles
.main-content {
  max-width: 900px;
  margin: 0 auto;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0 0 1.5rem 0;
  font-size: 1.125rem;
  font-weight: 600;
  color: $txt-primary;
  
  .material-symbols-outlined {
    font-size: 1.25rem;
    color: $bg-primary;
  }
}

// Route Section
.route-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
  
  @media (max-width: 768px) {
    padding: 1rem;
    border-radius: 8px;
  }
}

.location-timeline {
  position: relative;
  
  .location-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem 0;
    position: relative;
    
    &:first-child {
      padding-top: 0;
    }
    
    &:last-child {
      padding-bottom: 0;
    }
    
    .location-marker {
      flex-shrink: 0;
      z-index: 2;
      background: white;
      padding: 2px;
      
      .material-symbols-outlined {
        font-size: 1.5rem;
        color: $bg-primary;
      }
    }
    
    .location-details {
      flex: 1;
      
      h4 {
        margin: 0 0 0.5rem 0;
        font-size: 1rem;
        font-weight: 600;
        color: $txt-primary;
      }
      
      .address {
        margin: 0 0 0.25rem 0;
        color: $txt-primary;
        font-weight: 500;
      }
      
      .contact {
        margin: 0;
        color: #6b7280;
        font-size: 0.875rem;
      }
    }
  }
  
  .route-line {
    position: absolute;
    left: 11px;
    top: 1.5rem;
    height: calc(100% - 3rem);
    width: 2px;
    background: $bg-primary;
    z-index: 1;
  }
}

// Info Section
.info-section {
  margin-bottom: 2rem;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  
  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
}

.info-card {
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
  
  @media (max-width: 768px) {
    padding: 1rem;
    border-radius: 8px;
  }
  
  h4 {
    margin: 0 0 0.5rem 0;
    font-size: 0.875rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }
  
  p {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 500;
    color: $txt-primary;
  }
}

// Price Section
.price-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
  
  @media (max-width: 768px) {
    padding: 1rem;
    border-radius: 8px;
  }
}

.price-list {
  .price-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f3f4f6;
    
    &:last-child {
      border-bottom: none;
    }
    
    span:first-child {
      color: $txt-primary;
    }
    
    .price {
      font-weight: 600;
      color: $txt-primary;
    }
    
    &.subtotal {
      border-top: 2px solid $border-color;
      margin-top: 0.5rem;
      padding-top: 1rem;
      
      .price {
        color: $bg-primary;
        font-size: 1.125rem;
      }
    }
    
    &.total {
      border-top: 2px solid $bg-primary;
      margin-top: 0.5rem;
      padding-top: 1rem;
      font-weight: 600;
      
      .price {
        color: $bg-primary;
        font-size: 1.25rem;
      }
    }
  }
}

// POD Section
.pod-section {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
  margin-bottom: 2rem;
  text-align: center;
  
  @media (max-width: 768px) {
    padding: 1rem;
    border-radius: 8px;
  }
}

.pod-image {
  max-width: 300px;
  margin: 0 auto;
  
  img {
    width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  }
}

// Footer
.footer {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: white;
  padding: 1rem;
  box-shadow: 0 -2px 4px -1px rgba(0, 0, 0, 0.1);
  border-top: 1px solid #e5e7eb;
  z-index: 100;
  
  @media (max-width: 768px) {
    padding: 0.75rem;
  }
}

// No Data State
.no-record {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 50vh;
}

.no-data-content {
  text-align: center;
  
  .no-data-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
  }
  
  h2 {
    margin: 0 0 0.5rem 0;
    color: #374151;
  }
  
  p {
    margin: 0;
    color: #6b7280;
  }
}

// Animations
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

// Skeleton Loader Styles
.skeleton-wrapper {
  max-width: 900px;
  margin: 0 auto;
  animation: skeleton-pulse 1.5s ease-in-out infinite alternate;
}

.skeleton-header {
  margin-bottom: 2rem;
  
  .skeleton-status-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    margin-bottom: 1rem;
    
    @media (max-width: 768px) {
      padding: 1rem;
      border-radius: 8px;
    }
    
    .skeleton-indicator {
      display: flex;
      align-items: center;
      gap: 1rem;
      margin-bottom: 1rem;
      
      .skeleton-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #e2e8f0;
      }
      
      .skeleton-text {
        flex: 1;
        
        .skeleton-line {
          margin-bottom: 0.5rem;
        }
      }
    }
  }
  
  .skeleton-content {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
    text-align: center;
    
    @media (max-width: 768px) {
      padding: 1rem;
      border-radius: 8px;
    }
    
    .skeleton-line {
      margin: 0 auto 1rem auto;
    }
    
    .skeleton-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      margin-top: 1.5rem;
      
      @media (max-width: 768px) {
        flex-direction: column;
      }
    }
  }
}

.skeleton-main {
  max-width: 900px;
  margin: 0 auto;
  
  .skeleton-section {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
    
    @media (max-width: 768px) {
      padding: 1rem;
      border-radius: 8px;
    }
  }
  
  .skeleton-timeline {
    margin-top: 1.5rem;
    
    .skeleton-location {
      display: flex;
      align-items: center;
      gap: 1rem;
      padding: 1.5rem 0;
      
      &:first-child {
        padding-top: 0;
      }
      
      &:last-child {
        padding-bottom: 0;
      }
      
      .skeleton-marker {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #e2e8f0;
        flex-shrink: 0;
      }
      
      .skeleton-details {
        flex: 1;
        
        .skeleton-line {
          margin-bottom: 0.5rem;
        }
      }
    }
  }
  
  .skeleton-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
    
    @media (max-width: 768px) {
      grid-template-columns: 1fr;
    }
  }
  
  .skeleton-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1);
    
    @media (max-width: 768px) {
      padding: 1rem;
      border-radius: 8px;
    }
    
    .skeleton-line {
      margin-bottom: 0.5rem;
    }
  }
  
  .skeleton-price-list {
    margin-top: 1.5rem;
    
    .skeleton-price-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem 0;
      border-bottom: 1px solid #f3f4f6;
      
      &:last-child {
        border-bottom: none;
      }
      
      .skeleton-line {
        margin: 0;
      }
    }
  }
}

.skeleton-button {
  height: 44px;
  width: 140px;
  border-radius: 8px;
  background: #e2e8f0;
}

.skeleton-line {
  height: 16px;
  background: #e2e8f0;
  border-radius: 4px;
  
  &.skeleton-line-sm {
    width: 100px;
    height: 14px;
  }
  
  &.skeleton-line-md {
    width: 200px;
    height: 16px;
  }
  
  &.skeleton-line-lg {
    width: 300px;
    height: 20px;
  }
}

@keyframes skeleton-pulse {
  0% {
    opacity: 1;
  }
  100% {
    opacity: 0.6;
  }
}

// Responsive adjustments
@media (max-width: 480px) {
  .details-wrapper {
    padding: 0.25rem;
  }
  
  .status-header .status-card,
  .status-header .header-content,
  .route-section,
  .price-section,
  .pod-section {
    margin-left: 0;
    margin-right: 0;
  }
  
  .driver-info .driver-details {
    text-align: left;
    
    p {
      font-size: 0.8rem;
    }
  }
  
  .action-buttons {
    gap: 0.5rem;
  }
  
  .btn-primary, .btn-secondary {
    padding: 0.875rem 1rem;
    font-size: 0.9rem;
  }
}
</style>