<script setup>
import { ref, onMounted, useAttrs} from 'vue';

const attrs = useAttrs();

const vehicle = defineProps({
    name: String,
    description: String,
    dimensions: {
        type: Object,
        default: () => ({
            lenght: {value: '0', unit: 'm'},
            width: {value: '0', unit: 'm'},
            height: {value: '0', unit: 'm'},
        })
    },
    load: {
        type: Object,
        default: () => ({
            value: '0',
            unit: 'kg'
        })  
    }
});

</script>

<template>
    <div class="vehicle" v-bind="$attrs">
        <div class="content">
            <img src="@images/motorcycle.png" alt="motor" style="height: 60px; width: auto;">
            <span>{{ vehicle.name }}</span>
        </div>
    </div>
</template>

<style lang="scss" scoped>
@use '@/css/scss/_variables.scss' as *;

.vehicle {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 1rem;
    width: 150px;
    border-radius: 5px;
    height: 180px;
    border: 2px solid $border-color;
}

.content{
    display: flex;  
    align-self: flex-end;
    gap: 1rem;
    flex-direction: column;
    justify-content: flex-end;
    align-items: center;
}

span {
    font-size: $font-size-sm;
}
</style>