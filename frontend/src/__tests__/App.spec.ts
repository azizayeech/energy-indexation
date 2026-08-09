import { describe, expect, it, vi } from 'vitest'
import { mount } from '@vue/test-utils'

import App from '../App.vue'

import type { CalculateRequest, CalculateResponse, EnergyDataResponse } from '../types/energy'

vi.mock('../services/api', () => ({
  getConsumptions: vi.fn<() => Promise<EnergyDataResponse>>().mockResolvedValue({
    data: [],
  }),

  getPrices: vi.fn<() => Promise<EnergyDataResponse>>().mockResolvedValue({
    data: [],
  }),

  calculateIndexedPrice: vi.fn<(payload: CalculateRequest) => Promise<CalculateResponse>>(),

  ApiError: class ApiError extends Error {},
}))

describe('App', () => {
  it('muestra la aplicación correctamente', () => {
    const wrapper = mount(App)

    expect(wrapper.text()).toContain('Indexado de Energía')
    expect(wrapper.text()).toContain('Cálculo del precio indexado')
  })
})
