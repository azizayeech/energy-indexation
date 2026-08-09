<script setup lang="ts">
import { reactive } from 'vue'

import type { CalculateRequest } from '../types/energy'

const props = defineProps<{
  isSubmitting: boolean
  errors?: Record<string, string[]>
}>()

const emit = defineEmits<{
  submit: [payload: CalculateRequest]
}>()

const form = reactive<CalculateRequest>({
  start_date: '2025-03-01',
  end_date: '2025-03-02',
  formula: '([OMIE_MD] * 0.6) + 0.88',
})

function submitForm(): void {
  emit('submit', {
    ...form,
  })
}
</script>

<template>
  <section class="card shadow-sm border-0">
    <div class="card-body p-4">
      <div class="mb-4">
        <h2 class="h5 mb-1">Cálculo del precio indexado</h2>

        <p class="text-body-secondary mb-0">
          Introduce el período y la fórmula que se aplicará al precio OMIE.
        </p>
      </div>

      <form @submit.prevent="submitForm">
        <div class="row g-3">
          <div class="col-md-6">
            <label for="start-date" class="form-label"> Fecha de inicio </label>

            <input
              id="start-date"
              v-model="form.start_date"
              type="date"
              class="form-control"
              :class="{ 'is-invalid': props.errors?.start_date }"
              required
            />

            <div v-if="props.errors?.start_date" class="invalid-feedback">
              {{ props.errors.start_date[0] }}
            </div>
          </div>

          <div class="col-md-6">
            <label for="end-date" class="form-label"> Fecha de fin </label>

            <input
              id="end-date"
              v-model="form.end_date"
              type="date"
              class="form-control"
              :min="form.start_date"
              :class="{ 'is-invalid': props.errors?.end_date }"
              required
            />

            <div v-if="props.errors?.end_date" class="invalid-feedback">
              {{ props.errors.end_date[0] }}
            </div>
          </div>

          <div class="col-12">
            <label for="formula" class="form-label"> Fórmula </label>

            <input
              id="formula"
              v-model.trim="form.formula"
              type="text"
              class="form-control font-monospace"
              :class="{ 'is-invalid': props.errors?.formula }"
              maxlength="500"
              placeholder="([OMIE_MD] * 0.6) + 0.88"
              required
            />

            <div v-if="props.errors?.formula" class="invalid-feedback">
              {{ props.errors.formula[0] }}
            </div>

            <div class="form-text">
              La fórmula debe contener la variable
              <code>[OMIE_MD]</code>.
            </div>
          </div>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary" :disabled="props.isSubmitting">
            <span
              v-if="props.isSubmitting"
              class="spinner-border spinner-border-sm me-2"
              aria-hidden="true"
            ></span>

            {{ props.isSubmitting ? 'Calculando...' : 'Calcular' }}
          </button>
        </div>
      </form>
    </div>
  </section>
</template>
