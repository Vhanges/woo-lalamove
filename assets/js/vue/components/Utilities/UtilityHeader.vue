<template>
    <div class="utility-wrapper">
        <div class="first-section">
            <input type="text" class="search-info" placeholder="Search by Order Info" />
            <div class="action-button">
                <div class="action action-refresh">
                    <span class="material-symbols-outlined">restart_alt</span>
                </div>
                <div class="pagination-button">
                    <div class="action action-previous">
                        <span class="material-symbols-outlined">chevron_left</span>
                    </div>
                    <div class="action action-next">
                        <span class="material-symbols-outlined">chevron_right</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="second-section">
            <DropDown/>
            <DateRangePicker/>
        </div>
            
    </div>
</template>

<style lang="scss" scoped>
@use '@/css/scss/_variables.scss' as *;

.search-info {
    border: 2px solid $border-color;
    background-color: $bg-high-light;
    border-radius: 5px;
    font-size: $font-size-sm;
    height: 100%;
    outline: none !important; 
    box-shadow: none !important; 
    width: 30%;
    margin: 0;
}

.search-info:focus {
    border-color: $bg-primary !important; 
    outline: none !important;
    box-shadow: none !important;
    border-radius: 5px;
}
.utility-wrapper{
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    width: 100%;
    position: relative;
    z-index: 100;
    
    .first-section{
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        height: 2rem;
        max-height: 2rem;
        width: 100%;

        .action-button{
            display: flex;
            flex-direction: row;
            height: 100%;
            gap: 3rem;

            .pagination-button {
                height: 100%;
                display: flex;
                flex-direction: row;
                gap: 0.5rem;
            }
        
            
        }
        
    }

    .second-section{
        display: flex;
        flex-direction: row;
        gap: 1.5rem;
        align-items: center;
        height: 2rem;
        max-height: 2rem;

        width: 100%;
    }
}

.action{
    display: flex;
    align-items: center;
    cursor: pointer;
    height: fit-content;
    padding: 0.5rem;
    border-radius: 3%;
    width: fit-content;
    border: 2px solid $border-color;
    background-color: $bg-high-light;
    border-radius: 5px;

    .material-symbols-outlined {
    font-size: $font-size-md;
    color: $txt-secondary;
    cursor: pointer;
    }
}
</style>

<script setup>
import DropDown from '../Controls/DropDown.vue';
import DateRangePicker from '../Controls/DateRangePicker.vue';

</script>
