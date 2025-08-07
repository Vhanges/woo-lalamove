<template>
  <transition name="slide-up">
    <footer v-if="ordersCount" class="order-footer">
      <p class="note">
        Maximum of <strong>15</strong> stops only
      </p>
      <h2 class="count">{{ ordersCount }}</h2>
      <button class="process" @click="toPlaceOrder()">
        Process
      </button>
    </footer>
  </transition>
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

.order-footer {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 100px;
  background-color: $bg-light;
  border-top: $border-color 1px solid;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 2rem;
  padding: 0 2rem;
  z-index: 1000;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.3s ease-out, opacity 0.3s ease-out;
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}

.slide-up-enter-to,
.slide-up-leave-from {
  transform: translateY(0);
  opacity: 1;
}

.note strong {
  color: $txt-orange;
}

.count {
  color: $txt-orange;
}

.process {
  padding: 0.5rem 2rem;
  border: none;
  border-radius: 5px;
  background-color: $bg-primary;
  color: $txt-light;
  font-size: $font-size-md;
}
</style>