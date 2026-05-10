<script setup>
// Componente raíz de la aplicación.
// Solo contiene el <router-view> que muestra la vista actual según la URL.
</script>

<template>
  <div id="aplicacion">
    <router-view />
  </div>
</template>

<style scoped>
#aplicacion {
  min-height: 100vh;
}
</style>
