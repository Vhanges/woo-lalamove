<template>
  <div id="picked-date-wrapper">

    <p class="date-range">
        {{ formattedStartDate }} 
            <span class="material-symbols-outlined arrow_right_alt">
                arrow_right_alt
            </span>
        {{ formattedEndDate }}
    </p>

    <span class="material-symbols-outlined">
        calendar_month
    </span>

  </div>
</template>

<script setup>
import { onMounted, ref, computed } from "vue";

const startDate = ref(moment().subtract(1, "months")); 
const endDate = ref(moment().add(1, "days")); 

const emit = defineEmits(['dateRangeSelected']);

const formattedStartDate = computed(() => startDate.value.format("MMM D, YYYY"));
const formattedEndDate = computed(() => endDate.value.format("MMM D, YYYY"));

function callback(start, end) {
  console.log("Selected date range:", start.format("YYYY-MM-DD"), end.format("YYYY-MM-DD"));  
  startDate.value = moment(start);
  endDate.value = moment(end);

  emit("dateRangeSelected", {
    startDate: startDate.value.format("YYYY-MM-DD"),
    endDate: endDate.value.format("YYYY-MM-DD"),
  });
}

onMounted(() => {
  const date = jQuery.noConflict();
  date("#picked-date-wrapper").daterangepicker({
    startDate: startDate.value,
    endDate: endDate.value,
    singleDatePicker: false,
    timePicker: false,
    timePicker24Hour: false,
    timePickerSeconds: false,
    timePickerIncrement: 15,
    autoApply: false,
    opens: "right",
    drops: "bottom",
    showDropdowns: true,
    locale: {
      format: "MMM D, YYYY",
      applyLabel: "Apply",
      cancelLabel: "Cancel",
    },
    ranges: {
      "Last Year": [moment().subtract(1, "year"), moment()],
      "Last Month": [moment().subtract(1, "month"), moment()],
      "Last Week": [moment().subtract(7, "days"), moment()],
    },
  });

  date("#picked-date-wrapper").on("apply.daterangepicker", function (ev, picker) {
    callback(picker.startDate, picker.endDate);
  });

  callback(startDate.value, endDate.value);
});

</script>

<style lang="scss">
@use "@/css/scss/_variables.scss" as *;

#picked-date-wrapper {
  display: flex;
  justify-content: space-between;
  align-content: center;
  padding: 0 0.5rem;
  width: fit-content;
  height: 2rem;
  align-items: center;
  border: 2px solid $border-color;
  border-radius: 5px;
  background-color: $bg-high-light;
  color: $txt-secondary;
  gap: 0.5rem;
  cursor: pointer;

  .material-symbols-outlined {
    color: $txt-secondary;
    cursor: pointer;
    font-size: $font-size-lg;
  }

  .date-range {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0;
  }
}

.daterangepicker td.active,
.daterangepicker td.active:hover {
  background-color: $bg-primary !important;
  color: $txt-light !important;
}

.daterangepicker td.in-range {
  background-color: $bg-primary-light !important;
  color: $txt-secondary !important;
}

.daterangerpicker td.start-date,
.daterangerpicker td.end-date {
  background-color: $bg-primary !important;
  color: $txt-light !important;
}

.schedule-date {
  width: 100%;
  border-radius: 3%;
  background-color: $bg-light;
  font-size: $font-size-sm;
}
</style>
