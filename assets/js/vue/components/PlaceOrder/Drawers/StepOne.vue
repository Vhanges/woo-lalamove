<template>
    <div class="wrapper">
        <div class="address-wrapper">
            <form action="">
                <div class="text-container">
                    <input type="text" name="" id="pickup" class="text-box" placeholder="Add Pick up addresss">
                </div>
                <div class="text-container">
                    <input type="text" name="" id="drop-off" class="text-box" placeholder="Add Drop off address">
                </div>
            </form>
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

<style scoped lang="scss">
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