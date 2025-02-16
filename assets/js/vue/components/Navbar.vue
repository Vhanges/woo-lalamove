<template>
  <nav class="navbar">
    <div class="navbar-logo" />

    <router-link 
      to="/place-order" 
      class="nav-item nav-item--order"
    >
      <span class="nav-item__label">Place Order</span>
    </router-link>

    <router-link
      to="/orders"
      class="nav-item nav-item--woocommerce"
    >
      <span class="nav-item__label">WooCommerce Orders</span>
    </router-link>

    <router-link 
      to="/records" 
      class="nav-item nav-item--records"
    >
      <span class="nav-item__label">Records</span>
    </router-link>

    <router-link 
      to="/dashboard" 
      class="nav-item nav-item--dashboard"
    >
      <span class="nav-item__label">Dashboard</span>
    </router-link>

    <div class="nav-controls">
      <router-link 
        to="/info" 
        class="navbar-icon navbar-icon--info"
      >
      <span class="material-symbols-outlined icon">info</span> <!-- Information Symbol -->
      </router-link>

      <router-link 
        to="/settings" 
        class="navbar-icon navbar-icon--settings"
      >
        <span class="material-symbols-outlined icon">settings</span> <!-- Information Symbol -->
      </router-link>
  
    </div>

  </nav>
</template>

<script>

export default {
  name: "Navbar",
  data() {
    return {
    };
  },
};
</script>

<style lang="scss" scoped>
@use '@/css/scss/_variables.scss' as *;

*,
*::before,
*::after {
  box-sizing: border-box;
}

.navbar {
  background-color: #ffffff;
  border-bottom: 1px solid $bg-primary;
  height: 60px;
  width: 100%;
  display: flex;
  align-items: center;
  padding: 0 10px;
}

.navbar-logo {
  background: url('@images/logo.png') no-repeat center center;
  background-size: contain;
  height: 60px;
  width: 80px;
  flex-shrink: 0;
  image-rendering: -webkit-optimize-contrast; /* For Chrome */
}


.nav-item {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  margin: 0 20px;
  text-decoration: none;
  transition: background-color 0.3s ease;
}

.nav-item__label {
  color: $txt-primary;
  font-size: $font-size-sm;
  font-weight: 500;
  letter-spacing: 0;
  white-space: nowrap;
}

.navbar-icon {
  display: flex;
  align-items: center;
  justify-content: start;
  width: 30px;
  height: 30px;
  margin-left: 20px;
}


.navbar-icon--settings {
  margin-right: 20px;
}

.material-symbols-outlined {
  font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' ;
  outline: none;

}

.icon{
  color:#f16622;
}



.nav-controls{
  display: flex;
  flex: 1;
  width: auto;
  flex-direction: row;
  justify-content: end;
  padding: 0 2%;
}

a{
  text-decoration: none;
}
</style>