<script setup>
import { provide, ref, reactive } from "vue";
import StepOne from "./Steps/StepOne.vue";
import StepTwo from "./Steps/StepTwo.vue";
import StepOneActions from "./StepsActions/StepOneActions.vue";
import StepTwoActions from "./StepsActions/StepTwoActions.vue";

const step = ref(1);

const nextStep = () => {
  step.value += 1;
};

const previousStep = () => {
  step.value -= 1;
};

provide("nextStep", nextStep);
provide("previousStep", previousStep);
</script>

<template>
  <div class="forms-wrapper">
    <StepOne v-if="step === 1" />
    <StepTwo v-if="step === 2" />

    <!-- 
        <div class="actions">
          <StepOneActions v-if="step === 1"/>
          <StepTwoActions v-if="step === 2"/>
        </div> -->
  </div>
</template>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

.forms-wrapper {
  display: flex;
  position: relative;
  overflow-y: auto;
  height: 100%;
  background-color: $bg-light;
}

.forms-wrapper::-webkit-scrollbar {
  display: none;
}

.actions {
  position: absolute;
  height: auto;
  width: 100%;
  bottom: 0;
  left: 0;
  display: flex;
  justify-content: flex-end;
  box-sizing: border-box;
  padding: 1rem;
}
</style>
