<template>
  <div class="schedule-container">
    <p class="header">SCHEDULE DATE</p>
    <input type="datetime-local" name="schedule" id="schedule-date" />
  </div>
</template>

<script setup></script>

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
