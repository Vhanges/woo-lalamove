<template>
  <div class="schedule-wrapper">
    <div class="schedule-input-group">
      <label for="schedule-date" class="schedule-label">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" class="schedule-icon">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
          <line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2"/>
          <line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2"/>
          <line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>
        </svg>
        Preferred Delivery Time
      </label>
      <input 
        type="datetime-local"
        name="schedule"
        id="schedule-date"
        :min="minDate"
        :max="maxDate"
        v-model="localSchedule"
        @change="validateScheduleDate"
        class="schedule-input"
      />
      <p class="schedule-hint">Available: 8:00 AM - 4:00 PM (Next 30 days)</p>
    </div>
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

  console.log("date: ", selectedHour);

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
  if(scheduleAt.value !== ""){
    localSchedule.value = scheduleAt.value.replace(' ', 'T').slice(0, 16);
  } else {
    scheduleAt.value = startDate.toISOString();
    console.log("BOWM: ", scheduleAt.value);
    localSchedule.value = startDate.format('YYYY-MM-DDTHH:mm');
}
  
  minDate.value = startDate.format('YYYY-MM-DDTHH:mm');
  maxDate.value = endDate.format('YYYY-MM-DDTHH:mm');
});

// Sync with external store changes
watch(scheduleAt, (newValue) => {
  if (newValue) {
    localSchedule.value = isoToLocal(newValue);
  }
  if (!newValue) {
    scheduleAt.value = localToISO(localSchedule);
  }
});
</script>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;
@use "@/css/scss/_mixins.scss" as *;

.schedule-wrapper {
  width: 100%;
}

.schedule-input-group {
  width: 100%;
}

.schedule-label {
  @include small-text;
  font-weight: $font-weight-medium;
  color: $txt-secondary;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
}

.schedule-icon {
  color: $bg-primary;
}

.schedule-input {
  @include form-input;
  width: 100%;
  font-family: $font-primary;
  
  &::-webkit-calendar-picker-indicator {
    background: transparent;
    color: $bg-primary;
    cursor: pointer;
    filter: brightness(0) saturate(100%) invert(35%) sepia(85%) saturate(1376%) hue-rotate(345deg) brightness(96%) contrast(96%);
  }
  
  &::-webkit-datetime-edit {
    color: $txt-secondary;
  }
  
  &::-webkit-datetime-edit-fields-wrapper {
    padding: 0;
  }
  
  &::-webkit-datetime-edit-text {
    color: rgba($txt-primary, 0.6);
  }
  
  &::-webkit-datetime-edit-month-field,
  &::-webkit-datetime-edit-day-field,
  &::-webkit-datetime-edit-year-field,
  &::-webkit-datetime-edit-hour-field,
  &::-webkit-datetime-edit-minute-field {
    color: $txt-secondary;
  }
}

.schedule-hint {
  @include small-text;
  color: rgba($txt-primary, 0.6);
  margin: 0.5rem 0 0 0;
  font-style: italic;
}

// Mobile adjustments
@media (max-width: 767px) {
  .schedule-input {
    padding: 0.75rem;
    font-size: $font-size-sm;
  }
  
  .schedule-label {
    margin-bottom: 0.5rem;
  }
}

// High contrast mode
@media (prefers-contrast: high) {
  .schedule-input {
    border: 2px solid $border-color;
    
    &:focus {
      border: 2px solid $bg-primary;
    }
  }
}
</style>
