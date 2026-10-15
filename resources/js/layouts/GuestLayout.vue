<template>
  <div class="guest-wrapper min-vh-100 d-flex align-items-center">
    <div class="container-fluid px-0 h-100">
      <slot />
    </div>
  </div>
</template>

<style scoped>
.guest-wrapper {
  background-color: #F4F7FE;
}
</style>