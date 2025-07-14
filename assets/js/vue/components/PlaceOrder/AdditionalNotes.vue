<template>
  <div class="additional-notes-container">
    <p class="header">NOTES TO DRIVER</p>
    <textarea
      name="additional_notes"
      id="additional_notes"
      cols="30"
      rows="5"
    ></textarea>
  </div>
</template>

<script setup></script>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

.additional-notes-container {
  textarea {
    width: 100%;
    padding: 10px 16px;
    border: 1px solid $border-color;
    border-radius: 5px;
    font-size: $font-size-sm;
    font-family: inherit;
    resize: vertical;
    background-color: $bg-high-light;
    color: $txt-secondary;
    box-sizing: border-box;
    transition: border-color 0.2s, box-shadow 0.2s;

    &:focus {
      outline: none;
      border-color: $border-orange;
      background-color: $bg-high-light;
      box-shadow: 0 0 0 2px rgba(241, 102, 34, 0.15);
    }
  }
}

.header {
  font-size: $font-size-xs;
  font-weight: $font-weight-bold;
}

</style>
