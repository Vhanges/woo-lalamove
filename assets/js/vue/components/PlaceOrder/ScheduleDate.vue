<template>
  <div class="schedule-container">
    <p class="header">SCHEDULE DATE</p>
    <input 
      type="datetime-local"
      name="schedule"
      id="schedule-date"
      :min="minDate"
      :max="maxDate"
      v-model="localSchedule"
      @change="validateScheduleDate"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import { storeToRefs } from 'pinia';
import { useLalamoveStore } from '../../store/lalamoveStore';

const lalamove = useLalamoveStore();
const { scheduleAt } = storeToRefs(lalamove);

const minDate = ref('');
const maxDate = ref('');
const BUSINESS_HOURS = { START: 8, END: 16 };

// Local representation for input (YYYY-MM-DDTHH:mm)
const localSchedule = ref('');

// Convert ISO to local format for input binding
const isoToLocal = (iso) => iso ? moment(iso).format('YYYY-MM-DDTHH:mm') : '';

// Convert local format to ISO
const localToISO = (local) => moment(local).toISOString();

function validateScheduleDate() {
  const date = moment(localSchedule.value);
  const selectedHour = date.hour();

  if (selectedHour < BUSINESS_HOURS.START || selectedHour > BUSINESS_HOURS.END) {
    toast.error("Delivery hours are 8 AM - 4 PM. Please select a valid time.");
    localSchedule.value = minDate.value;
    scheduleAt.value = localToISO(minDate.value);
  } else {
    scheduleAt.value = localToISO(localSchedule.value);
  }
}

onMounted(() => {
  const startDate = moment().add(1, 'days').startOf('day').add(BUSINESS_HOURS.START, 'hours');
  const endDate = moment().add(30, 'days').startOf('day').add(BUSINESS_HOURS.END, 'hours');

  // Set ISO format in store
  scheduleAt.value = startDate.toISOString();
  
  // Set local format for input
  localSchedule.value = startDate.format('YYYY-MM-DDTHH:mm');
  minDate.value = startDate.format('YYYY-MM-DDTHH:mm');
  maxDate.value = endDate.format('YYYY-MM-DDTHH:mm');
});

// Sync with external store changes
watch(scheduleAt, (newValue) => {
  if (newValue) {
    localSchedule.value = isoToLocal(newValue);
  }
});
</script>

<style lang="scss" scoped>
/* Existing styles remain unchanged */
</style>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

.schedule-container {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;

  #schedule-date {
    width: 100%;
    padding: 7px 14px;
    border: 1px solid $border-color;
    border-radius: 5px;
    font-size: $font-size-sm;
    background: $bg-high-light;
    color: $txt-secondary;
    transition: border-color 0.2s, box-shadow 0.2s;
    margin-bottom: 8px;
    box-sizing: border-box;
  }

  #schedule-date:focus {
    border-color: $border-orange;
    outline: none;
    background: #fff;
    box-shadow: 0 0 0 2px rgba(241, 102, 34, 0.15);
  }
}

.header {
  font-size: $font-size-xs;
  font-weight: $font-weight-bold;
}
</style>
