<template>
    <div class="steps-actions">
        <button @click="previousStep">Previous</button>
    </div>
</template>

<script setup>
import {inject} from 'vue';

const previousStep = inject('previousStep');
</script>

<style lang="scss" scoped>

</style>