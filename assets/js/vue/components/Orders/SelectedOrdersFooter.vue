<template>
    <footer>
        <p class="note">
            Maximum of <strong>15</strong> stops only
        </p>
        <h2 class="count">
             {{ ordersCount ? ordersCount : '' }} 
        </h2>
        <button class="process" @click="toPlaceOrder()">
            Process
        </button>
    </footer>
</template>

<script setup>
import { storeToRefs } from 'pinia';
import { useWooOrderStore } from '../../store/wooOrderStore';
import { useRouter } from 'vue-router'

const wooOrders = useWooOrderStore();
const {ordersCount} = storeToRefs(wooOrders);

const router = useRouter();

const toPlaceOrder = () => {

    router.push({name: 'place-order'});
};

router
</script>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

footer {
  position: fixed; 
  bottom: 0;
  left: 0;
  right: 0;
  height: 100px;
  max-width: 100%;
  border: $border-color 1px solid;
  background-color: $bg-light;
  padding: 1rem;
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: end;
  gap: 2rem;
  padding: 0 2rem;

  .process {
    padding: .5rem 2rem;
    border-radius: 5px;
    border: none;
    background-color: $bg-primary;
    color: $txt-light;
    font-size: $font-size-md;
  }
}

strong {
    color: $txt-orange;
}

.count {
    color: $txt-orange;
}


</style>