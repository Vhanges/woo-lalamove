<template>
    <div class="steps-actions">

        <button @click="nextStep">Next</button>
    </div>
</template>

<script setup>
import {inject} from 'vue'; 

const nextStep = inject('nextStep');
</script>

<style lang="scss" scoped>

</style>