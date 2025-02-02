<template>
    <div class="wrapper">
        <div class="address-wrapper">

        </div>
    </div>
</template>

<script>
    export default {
        name: "StepOne",
        setup (){
            
        },
    }
</script>

<style scoped>
.wrapper{
    display: flex !important;
    flex-direction: column;
    justify-content: start;
    align-items: center;
    box-sizing: border-box;  
    padding: 5%;
    height: 100%;
    width: 100%;
}
.address-wrapper{
    height: 30%;
    width: 100%;
    border: 1px solid red;
    border-radius: 5px ;
}
</style>