<script setup lang="ts">
import type { EnergyData, HourlyValues } from '../types/energy'

defineProps<{
  title: string
  unit: string
  rows: EnergyData[]
}>()

type HourKey = keyof HourlyValues

const hours = Array.from({ length: 25 }, (_, index) => `h${index + 1}` as HourKey)
</script>

<template>
  <section class="card shadow-sm border-0">
    <div class="card-body p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">
          {{ title }}
        </h2>

        <span class="badge text-bg-secondary">
          {{ unit }}
        </span>
      </div>

      <div v-if="rows.length === 0" class="alert alert-secondary mb-0">
        No hay datos disponibles.
      </div>

      <div v-else class="table-responsive">
        <table class="table table-sm table-striped table-hover align-middle mb-0">
          <thead>
            <tr>
              <th scope="col" class="text-nowrap">Fecha</th>

              <th v-for="hour in hours" :key="hour" scope="col" class="text-end text-nowrap">
                {{ hour.toUpperCase() }}
              </th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="row in rows" :key="row.id">
              <th scope="row" class="text-nowrap">
                {{ row.date }}
              </th>

              <td v-for="hour in hours" :key="hour" class="text-end text-nowrap">
                {{ row[hour] }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
</template>
