<script setup lang="ts">
import { onMounted, ref } from 'vue'

import CalculationForm from './components/CalculationForm.vue'
import CalculationResult from './components/CalculationResult.vue'
import EnergyDataTable from './components/EnergyDataTable.vue'

import { ApiError, calculateIndexedPrice, getConsumptions, getPrices } from './services/api'

import type { CalculateRequest, EnergyData } from './types/energy'

const consumptions = ref<EnergyData[]>([])
const prices = ref<EnergyData[]>([])

const isLoadingData = ref(true)
const dataErrorMessage = ref<string | null>(null)

const isSubmitting = ref(false)
const calculationResult = ref<number | null>(null)
const calculationErrorMessage = ref<string | null>(null)

const validationErrors = ref<Record<string, string[]>>({})

async function loadEnergyData(): Promise<void> {
  isLoadingData.value = true
  dataErrorMessage.value = null

  try {
    const [consumptionResponse, priceResponse] = await Promise.all([getConsumptions(), getPrices()])

    consumptions.value = consumptionResponse.data
    prices.value = priceResponse.data
  } catch (error) {
    if (error instanceof ApiError) {
      dataErrorMessage.value = error.data.message
    } else {
      dataErrorMessage.value =
        'No se ha podido conectar con el servidor. Inténtalo de nuevo más tarde.'
    }
  } finally {
    isLoadingData.value = false
  }
}

async function handleCalculation(payload: CalculateRequest): Promise<void> {
  isSubmitting.value = true

  calculationResult.value = null
  calculationErrorMessage.value = null
  validationErrors.value = {}

  try {
    const response = await calculateIndexedPrice(payload)

    calculationResult.value = response.price_indexed
  } catch (error) {
    if (error instanceof ApiError) {
      if (error.status === 400 && error.data.errors) {
        validationErrors.value = error.data.errors
      } else {
        calculationErrorMessage.value = error.data.message
      }
    } else {
      calculationErrorMessage.value =
        'No se ha podido conectar con el servidor. Inténtalo de nuevo más tarde.'
    }
  } finally {
    isSubmitting.value = false
  }
}

onMounted(loadEnergyData)
</script>

<template>
  <main class="min-vh-100 bg-body-tertiary py-5">
    <div class="container">
      <header class="mb-4">
        <h1 class="h2 mb-2">Indexado de Energía</h1>

        <p class="text-body-secondary mb-0">
          Consulta de consumos, precios horarios y cálculo del precio indexado.
        </p>
      </header>

      <div class="d-grid gap-4">
        <CalculationForm
          :is-submitting="isSubmitting"
          :errors="validationErrors"
          @submit="handleCalculation"
        />

        <div v-if="calculationErrorMessage" class="alert alert-danger mb-0" role="alert">
          {{ calculationErrorMessage }}
        </div>

        <CalculationResult v-if="calculationResult !== null" :price="calculationResult" />

        <div v-if="dataErrorMessage" class="alert alert-danger mb-0" role="alert">
          {{ dataErrorMessage }}
        </div>

        <div v-if="isLoadingData" class="d-flex align-items-center gap-2 py-4">
          <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>

          <span>Cargando datos...</span>
        </div>

        <template v-else-if="!dataErrorMessage">
          <EnergyDataTable title="Consumos horarios" unit="kWh" :rows="consumptions" />

          <EnergyDataTable title="Precios horarios" unit="€/kWh" :rows="prices" />
        </template>
      </div>
    </div>
  </main>
</template>
