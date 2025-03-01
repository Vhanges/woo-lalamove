<!-- Modal structure -->
<div id="customModal" class="custom-modal">
  <div class="custom-modal-content">
    <span class="custom-close">&times;</span>
    <p>Modal content loaded from an external file.</p>
  </div>
</div>

<!-- Modal styles -->
<style>
  .custom-modal {
    display: none; 
    position: fixed;
    z-index: 1; 
    left: 0;
    top: 0;
    width: 100%; 
    height: 100%; 
    overflow: auto;
    background-color: rgb(0,0,0); 
    background-color: rgba(0,0,0,0.4);
  }
  .custom-modal-content {
    background-color: #fefefe;
    margin: 15% auto; 
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
  }
  .custom-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
  }
  .custom-close:hover,
  .custom-close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
  }
</style>
