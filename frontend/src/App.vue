<script setup>
import { RouterView } from 'vue-router';
</script>

<template>
  <RouterView />
</template>

<style>
/* Reset mutlak agar layout memenuhi layar */
html, body, #app {
  margin: 0 !important;
  padding: 0 !important;
  width: 100vw !important;
  min-height: 100vh !important;
  background-color: #ffffff !important;
}
</style>