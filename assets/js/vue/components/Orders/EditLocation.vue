<template>
    <div class="location">
      <span class="material-symbols-outlined icon edit-location-icon">edit_location_alt</span> 
      <div class="label">Pick at &quot;Location Name&quot;</div>
      <p class="address">Unit C-D Ortiga Center High.sdaffffffffffffffsaddddddddf..</p>
      <p class="contact">+644315873</p>
    </div>
</template>

<style lang="scss" scoped>
@use '@/css/scss/_variables.scss' as *;

.location {
    box-sizing: border-box;
    background-color: $bg-high-light;
    border: 2px solid $border-color;
    border-radius: 5px;
    height: 100%;
    display: grid;
    grid-template-columns: 80% 20%;
    grid-template-rows: 20% 40% 40%;
    grid-template-areas: 
    'location-name icon'
    'Address icon'
    'Number icon'
    ;
    width: 22%;
    padding: 1%;
    row-gap: 4px;
}

.location .edit-location-icon {
    grid-area: icon;
    color: $bg-primary;
    justify-self: center;
    align-self: center;
}

.location .label {
    font-family: $font-primary;
    font-size: $font-size-xs;
    font-weight: $font-weight-bold;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    align-self: center;
}

.location .address {
    height: max-content;
    font-size: $font-size-xs;
    font-weight: $font-weight-light;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    align-self: center;
}

.location .contact {
    height: max-content;
    font-size: $font-size-xs;
    font-weight: $font-weight-light;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    align-self: center;
}

</style>

<script setup>

</script>
  