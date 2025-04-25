<template>
    <div class="details-wrapper">
        <header v-if="status == 'assigning driver'">

            <div class="transaction-info" style="background-color: #FEF9E1; border: 2px solid #FFF2B1;">
                    <p>
                        Status Assigning Driver  ●  231243215
                    </p>
                    <p>
                        Schedule Date: 10 Dec 2024 5:55PM
                    </p>
            </div>

            <div class="header-content">

                <h2>Finding Nearby</h2>
                <p>Please wait a moment</p>

                <button class="action-button">
                    <span class="material-symbols-outlined">add</span>
                    Add Priority Fee
                </button>

            </div>
            
        </header>
        <header v-if="status == 'in transit'">

            <div class="transaction-info" style="background-color: #FFEEEF; border-color: 2px solid #F16622; color: #F16622;">
                    <p>
                        In Transit  ●  231243215
                    </p>
                    <p>
                        Schedule Date: 10 Dec 2024 5:55PM
                    </p>
            </div>

            <div class="header-content">

                <h2>Driver Name</h2>
                <p>Plate Number: 123124</p>
                <p>Contanct Number: 123124</p>

                <div class="action-message">
                    <button onclick="window.open('https://example.com', '_blank')" class="action-button">
                        <span class="material-symbols-outlined">location_searching</span>
                        Track Order
                    </button>
                    <p class="delivery message">The Driver is now on transit</p>
                </div>

            </div>

        </header>
        <header v-if="status == 'item collected'">
            <h2>
                item collected
            </h2>
        </header>
        <header v-if="status == 'delivered successfully'">
            <h2>
                delivered successfully
            </h2>
        </header>
        <header v-if="status == 'rejected'">
            <h2>
                rejected
            </h2>
        </header>
        <header v-if="status == 'ordered canceled'">
            <h2>
                canceled
            </h2>
        </header>
        <header v-if="status == 'expired'">
            <h2>
                expired
            </h2>
        </header>

        <main>
            <p>{{ lala_id }}</p><br>
            <p>{{ wc_id }}</p>
            <p>hello</p>
        </main>

        <footer>
            <h3>
                Hello 
            </h3>
            <h3>
                Hello 
            </h3>
        </footer>

    </div>
</template>

<script setup>
import {ref} from 'vue';
import axios from 'axios';

const status = ref();

status.value = 'in transit';



const params = defineProps({
    lala_id: Number,
    wc_id: Number
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
            border-bottom: 2px solid $border-color;

            .action-message {
                display: flex;
                flex-direction: row;
                gap: 1rem;
            }

        }
    }
    main{
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        width: 100%;


    }
    footer {
        display: flex;
        justify-content: space-around;
        align-items: center;
        width: 100%;
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

.material-symbols-outlined {
    font-size: $font-size-xs;
}

</style>