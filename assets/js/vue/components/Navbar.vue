<template>
    <nav>
      <ul>
        <li><router-link to="/">Dashboard</router-link></li>
        <li><router-link to="/Orders">Orders</router-link></li>
        <li><router-link to="/settings">Settings</router-link></li>
      </ul>
    </nav>
  </template>
  
  <script>
  export default {
    name: 'Navbar',
  };
  </script>
  
  <style>
  nav ul {
    list-style: none;
    display: flex;
    gap: 1rem;
    padding: 0;
  }
  nav ul li {
    display: inline;
  }
  </style>
  