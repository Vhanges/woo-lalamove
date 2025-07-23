<template>

    <div class="details-wrapper">
        <header v-if="status == 'ASSIGNING_DRIVER'">

            <div class="transaction-info" style="background-color: #FEF9E1; border: 2px solid #FFF2B1;">
                    <p>
                        Assigning Driver  ●  {{ lalaOrderId }}
                    </p>
                    <p>
                        Schedule Date: {{ scheduleData }}
                    </p>
            </div>

            <div class="header-content">

                <h3>Finding Nearby</h3>
                <p>Please wait a moment</p>

                <div class="action-container">
                    <button @click="openShareLink" class="action-button">
                        <span class="material-symbols-outlined header-icon">location_searching</span>
                        Track Order
                    </button>
                    <button v-if="isCancelAvailable" @click="cancelOrder(lalaOrderId)" class="action-button">
                        <span class="material-symbols-outlined footer-icon">close</span>
                        Cancel Order
                    </button>
                    <!-- <button @click="addPriorityFee" class="action-button">
                        <span class="material-symbols-outlined header-icon">add</span>
                        Add Priority Fee
                    </button> -->
                </div>

            </div>
            
        </header>
        <header v-if="status == 'ON_GOING'">

            <div class="transaction-info" style="background-color: #E0F0FD; border-color: 2px solid #5578B1; color: #5578B1;">
                    <p>
                        Awaiting Driver  ●  {{ lalaOrderId }}
                    </p>
                    <p>
                        Schedule Date: {{ scheduleData }} 
                    </p>
            </div>

            <div class="header-content">

                <h3>{{ driverName }}</h3>
                <p>Plate Number: {{ driverPlateNumber }}</p>
                <p>Contact Number: {{ driverContactNo }}</p>

                <div class="action-container">
                    <button @click="openShareLink"  class="action-button">
                        <span class="material-symbols-outlined header-icon">location_searching</span>
                        Track Order
                    </button>
                    <!-- <button onclick="window.open('https://example.com', '_blank')" class="action-button">
                        <span class="material-symbols-outlined header-icon">swap_driving_apps</span>
                        Change Driver
                    </button> -->
                    <button v-if="isCancelAvailable"  @click="cancelOrder(lalaOrderId)" class="action-button">
                        <span class="material-symbols-outlined footer-icon">close</span>
                        Cancel Order
                    </button>
                    <p class="delivery message">The driver is on its way to pick up the order</p>
                </div>

            </div>

        </header>
        <header v-if="status == 'PICKED_UP'">

            <div class="transaction-info" style="background-color: #FFEEEF; border-color: 2px solid #F16622; color: #F16622;">
                    <p>
                        Picked Up  ●  {{ lalaOrderId }}
                    </p>
                    <p>
                        Schedule Date: 10 Dec 2024 5:55PM
                    </p>
            </div>

            <div class="header-content">

                <h3>{{ driverName }}</h3>
                <p>Plate Number: {{ driverPlateNumber }}</p>
                <p>Contact Number: {{ driverContactNo }}</p>

                <div class="action-container">
                    <button @click="openShareLink" class="action-button">
                        <span class="material-symbols-outlined header-icon">location_searching</span>
                        Track Order
                    </button>
                    <!-- <button onclick="window.open('https://example.com', '_blank')" class="action-button">
                        <span class="material-symbols-outlined header-icon">swap_driving_apps</span>
                        Change Driver
                    </button> -->
                    <p class="delivery message">The driver picked up the order</p>
                </div>

            </div>

        </header>
        <header v-if="status == 'COMPLETED'">
            <div class="transaction-info" style="background-color: #DBFFE3; border-color: 2px solid #05B32B; color: #05B32B;">
                    <p>
                        Completed ●  {{ lalaOrderId }}
                    </p>
                    <p>
                        Schedule Date: 10 Dec 2024 5:55PM
                    </p>
            </div>

            <div class="header-content">

                <h3>{{ driverName }}</h3>
                <p>Plate Number: {{ driverPlateNumber }}</p>
                <p>Contact Number: {{ driverContactNo }}</p>

                <div class="action-container">
                    <button @click="openShareLink" class="action-button">
                        <span class="material-symbols-outlined header-icon">location_searching</span>
                        Track Order
                    </button>
                </div>

            </div>
        </header>
        <header v-if="status == 'REJECTED'">
            <div class="transaction-info" style="background-color: #FFEEEF; border-color: 2px solid #CB3F49; color: #CB3F49;">
                    <p>
                        Rejected ●  {{ lalaOrderId }}
                    </p>
                    <p>
                        Schedule Date: 10 Dec 2024 5:55PM
                    </p>
            </div>
-
            <div class="header-content">

                <h3>Ordered Rejected</h3>
                <p>The order was rejected twice.</p>

            </div>
        </header>
        <header v-if="status == 'CANCELED'">
            <div class="transaction-info" style="background-color: #FFEEEF; border-color: 2px solid #CB3F49; color: #CB3F49;">
                    <p>
                        Canceled ●  {{ lalaOrderId }}
                    </p>
                    <p>
                        Schedule Date: 10 Dec 2024 5:55PM
                    </p>
            </div>
-
            <div class="header-content">

                <h3>Ordered Canceled</h3>
                <p>The order has been canceled or rejected by the driver twice.</p>

            </div>
        </header>
        <header v-if="status == 'EXPIRED'">
            <div class="transaction-info" style="background-color: #EDEDED; border-color: 2px solid #4C4C4C; color: #4C4C4C;">
                    <p>
                        Expired ●  {{ lalaOrderId }}
                    </p>
                    <p>
                        Schedule Date: 10 Dec 2024 5:55PM
                    </p>
            </div>  
-
            <div class="header-content">

                <h3>Order Expired</h3>
                <p>The order expired after the driver failed to accept it within two hours.</p>

            </div>
        </header>

        <div v-if="noData" class="no-record">
            <h1>
                No records found
            </h1>
        </div>

        <main v-else>
            <h3>ROUTE</h3>
            <div class="location-wrapper">
                <div class="box">
                    <span class="material-symbols-outlined body-icon">brightness_1</span>
                    <span class="location">
                        <p>
                            Pickup Location
                        </p>
                        <p>
                            {{ senderAddress }}
                        </p>
                        <p>
                            {{ senderName }} - {{ senderPhoneNo }}
                        </p>
                    </span>
                </div>

                    <div class="line"></div>

                <div class="box">
                    <span class="material-symbols-outlined body-icon">location_on</span>

                    <span class="location">
                        <p>
                            Drop Off Location
                        </p>
                        <p>
                            {{ customerAddress }}
                        </p>
                        <p>
                            {{ customerName }} - {{ customerPhoneNo }}
                        </p>
                    </span>
                </div>

            </div>

            <div class="additional-info-wrapper">

                
                <div class="additional-info">
                <strong>
                    Placed By
                </strong>
                
                <p>
                    {{ senderName }}
                </p>
            </div>
            <div class="additional-info">
                <strong>
                    Service Type
                </strong>
                
                <p>
                    {{ serviceType }}
                </p>
            </div>
            <!-- <div class="additional-info">
                <strong>
                    Additional Services
                </strong>
                
                <p>
                    dummy data
                </p>
            </div> -->


        </div>


        <div class="price-breakdown">
            <h3>PRICE</h3>

                <span v-if="basePrice" class="cost">
                    <p>Base Price</p>
                    <p class="cost-price">
                        {{ basePrice }}
                    </p>
                </span>
                <span v-if="extraMileage" class="cost">
                    <p>Additional Distance Fee</p>
                    <p class="cost-price">
                        {{ extraMileage }}
                    </p>
                </span>
                <span v-if="specialRequest" class="cost">
                    <p>Special Request Fee</p>
                    <p class="cost-price">
                        {{ specialRequest }}
                    </p>
                </span>
                <span v-if="priorityFee" class="cost">
                    <p>Priority Fee</p>
                    <p class="cost-price">
                        {{ priorityFee }}
                    </p>
                </span>
                <span v-if="surcharge" class="cost"> 
                    <p>Surcharge</p>
                    <p class="cost-price">
                        {{ surcharge }}
                    </p>
                </span>
                <span v-if="totalExcludedPriorityFee" class="cost">
                    <p>Total Exluded Priority Fee</p>
                    <p class="total-price">
                        {{ totalExcludedPriorityFee }}
                    </p>
                </span>
                <span  class="cost"> 
                    <p>Total</p>
                    <p class="total-price">
                        {{ total }}
                    </p>
                </span>
        </div>

        <div v-if="podImage" class="pod">
            <img src="{{podImage}}" alt="">
        </div>




            
        </main>

        <footer>
            <div class="action-container">
                <button @click="$router.push({ name: 'records' })" class="action-button" style="margin-right: auto;">
                    <span class="material-symbols-outlined footer-icon">chevron_left</span>
                    Return
                </button>


                    <!-- <button v-if="isOrderAgainAvailable" onclick="window.open('https://example.com', '_blank')" class="action-button">
                        <span class="material-symbols-outlined footer-icon">reply</span>
                        Order Again
                    </button> -->
            </div>
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
    wc_id: String,
});

const fetchLalaOrderData = async (lala_id, wc_id) => {
    
    try {
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
        
        const driver = driverResponse.data;
        
        if(driver.errors){
            return;
        } else {
            console.log("DRIVER RESPONSE: ", driver);

            driverName.value = driver.name;
            driverContactNo.value = driver.phone;
            driverPlateNumber.value = driver.plateNumber;
        }


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
        scheduleData.value = lalaOrderBody.data[0].scheduled_on;
        lalaOrderBody.value = lalaOrderBody.data[0].order_json_body;


        console.log(serviceType.value);
        console.log(scheduleData.value);
        console.log(lalaOrderBody.value);




    } catch (error) {
      console.error('Error fetching Woo Lalamove Orders:', error.response?.data || error.message);
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

.details-wrapper{
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-direction: column;
    height: 100%;
    background-color: $bg-light;
    overflow-y: scroll;

    header {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        width: 100%;
        margin-top: 1rem;
        padding: 0 1%;

        .transaction-info{
            width: 800px;
            height: 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1%;
            border-radius: 3px;
        }
        
        .header-content{

            width: 800px;
            padding: 1rem;
            border-bottom: 1px solid $border-color;

        }
    }
    main{
        flex: 1;
        display: flex;
        justify-content: start;
        align-items: start;
        flex-direction: column;
        width: 800px;
        padding: 1%;
        gap: 10px;

        .location-wrapper {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: start; 
            gap: 5px; 
            position: relative;
            
            .box {
                height: fit-content;
                background: transparent;
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: center;
                gap: 1rem;

                .location {
                    max-width: fit-content;

                    p {
                        line-height: .5;
                    }

                    strong {
                        line-height: .5;
                    }
                }

            }

            .line {
                width: 2px; 
                height: 35%; 
                background: $txt-primary;
                position: absolute; 
                left: 12px; 
                top: 50%; 
                transform: translate(-100%, -50%);
                z-index: 1; 
            }

        }

        .additional-info-wrapper {

            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;

            .additional-info p {
                margin-top: 0px;   
            }
            
        }

        .price-breakdown {
            width: 100%;

            .cost {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                width: 100%;

                .cost-price {
                    font-weight: $font-weight-bold;
                    color: $header-active;
                }

                .total-price {
                    font-weight: $font-weight-bold;
                    font-size: $font-size-lg;
                    color: $header-active;
                }
            }
        }

        .pod {
            width: 20%; 
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .pod img {
            max-width: 100%;
            height: auto;
        }


        
    }
    footer {
        display: flex;
        justify-content: flex-end;  
        align-items: center;
        width: 800px;
        gap: 1rem;
        margin: 1rem 1rem 6rem 1rem;
    }
}

.action-button {
    display: flex;
    align-items: center;
    gap: 4px; 
    padding: 7px 14px;
    border: 2px solid $bg-gray;
    color: $txt-primary;
    border-radius: 3px;
    font-size: $font-size-xs;
    background-color: $bg-light;
    cursor: pointer;

}


.action-button:hover {
    border-color: $bg-primary;
    color: $bg-primary;
}

.header-icon {
    font-size: $font-size-xs;
}

.footer-icon {
    font-size: $font-size-md;
}

.body-icon {
    font-size: $font-size-xl;
    color: $bg-primary;
}

.cost {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

h3 {
    color: $txt-primary;
    margin-bottom: 5px;
}
strong {
    color: $txt-primary;
    margin-bottom: 5px;
}

.action-container {
    display: flex;
    flex-direction: row;
    gap: 1rem;
}

.no-record {
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>