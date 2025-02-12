<template>
  <div>
    <button @click="notifySuccess">Show Success Toast</button>
    <button @click="notifyError">Show Error Toast</button>
    <button @click="notifyInfo">Show Info Toast</button>
    <button @click="notifyWarning">Show Warning Toast</button>
    <button @click="notifyDefault">Show Default Toast</button>
    <button @click="notifyCustom">Show Custom Toast</button>
  </div>

</template>

<script setup>
import { toast, ToastifyContainer } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const notifySuccess = () => {
  toast.success('This is a success message!', { autoClose: 2000, });
};

const notifyError = () => {
  toast.error('This is an error message!', { autoClose: 2000 });
};

const notifyInfo = () => {
  toast.info('This is an info message!', { autoClose: 2000 });
};

const notifyWarning = () => {
  toast.warning('This is a warning message!', { autoClose: 2000 });
};

const notifyDefault = () => {
  toast('This is a default toast!', { autoClose: 2000 });
};

const notifyCustom = () => {
  toast('This is a custom toast!', {
    autoClose: 3000,
    style: {
      top: '5%',
      color: 'white',
      fontSize: '16px',
      fontFamily: 'Arial, sans-serif'
    },
    icon: '🚀', // Custom icon
    position: 'top-right',
    closeOnClick: true,
    pauseOnHover: true,
  });
};
</script>
