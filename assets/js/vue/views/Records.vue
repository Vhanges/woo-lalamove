<template>
  <div class="records-wrapper">

    <header>
      <h2>RECORDS</h2>
    </header>

    <nav >
      <utilityNav />
    </nav>

    <main>
      <div class="table-wrapper">
        <table class="woo-order-table">
            <thead>
                <tr>
                    <th>Woo ID</th>
                    <th>Order Date</th>
                    <th>Schedule Date</th>
                    <th>Drop Off Location</th>
                    <th>Contact</th>
                    <th>Quantity</th>
                    <th>Status</th>
            </tr>
            </thead>
            <tbody>
              
              <tr @click="$router.push({ name: 'records-details', params: { lala_id: '21', wc_id: '21' } })" style="cursor: pointer;">
                  <td>123</td>
                  <td>123</td>
                  <td>123</td>
                  <td>123</td>
                  <td>123</td>
                  <td>123</td>
                  <td>123</td>
                </tr>

            </tbody>
        </table>    
      </div>

      
    </main>
    
  </div>
  <RecordDetails/>
  
</template>


<script setup>
import { ref, defineAsyncComponent } from 'vue';

const utilityNav = defineAsyncComponent(
  () => import ('../components/Utilities/UtilityHeader.vue'),
);
// const RecordDetails = defineAsyncComponent(
//   () => import ('../components/Records/RecordsDetails.vue'),
// );

async function toggleDrawer($lalaId, $wc_id){

}
</script>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

.records-wrapper{
    display: flex; 
    flex-direction: column;
    position: relative;
    gap: 2vh;
    height: fit-content;
    margin: 2rem;
    
    header {
      display: flex;
      justify-content: space-between;
      height: 10vh;
      width: 100%;
      align-items: center;
    }

    nav{
      height: fit-content;
      width: 100%;
    }

    main {
      .table-wrapper{
        border: 2px solid $border-color;
        width: 100%;
        border-radius: 5px;
        background-color: $bg-high-light;


        .woo-order-table {
            width: 100%;
            border-collapse: collapse;
            
            th, td {
                padding: 1rem;
                text-align: left;
                border-bottom: 1px solid $bg-gray;
            }
            
            th {
                background-color:  $bg-gray;
                font-weight: bold;
            }
            

            tr:hover {
                background-color: $bg-primary-light;
                box-shadow: inset 2px 0 0 0 $bg-primary;
            }

            .selected-row{
                background-color: $bg-primary-light;
                box-shadow: inset 2px 0 0 0 $bg-primary;
            }
        }
      }
    }




}
</style>
