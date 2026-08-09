<script setup lang="ts">
defineProps<{
  price: number
}>()
</script>

<template>
  <section class="card border-success shadow-sm">
    <div class="card-body p-4">
      <p class="text-body-secondary mb-1">Precio indexado</p>

      <div class="d-flex align-items-baseline gap-2">
        <strong class="display-6">
          {{ String(price).replace('.', ',') }}
        </strong>

        <span class="fs-5 text-body-secondary"> €/kWh </span>
      </div>
    </div>
  </section>
</template>
