import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'

import EnergyDataTable from '../EnergyDataTable.vue'

import type { EnergyData, HourlyValues } from '../../types/energy'

function createHourlyValues(): HourlyValues {
  return Object.fromEntries(
    Array.from({ length: 25 }, (_, index) => [`h${index + 1}`, index + 1]),
  ) as HourlyValues
}

describe('EnergyDataTable', () => {
  it('muestra los datos desde H1 hasta H25', () => {
    const row: EnergyData = {
      id: 1,
      date: '2025-03-01',
      ...createHourlyValues(),
    }

    const wrapper = mount(EnergyDataTable, {
      props: {
        title: 'Consumos horarios',
        unit: 'kWh',
        rows: [row],
      },
    })

    expect(wrapper.text()).toContain('Consumos horarios')
    expect(wrapper.text()).toContain('2025-03-01')
    expect(wrapper.text()).toContain('H1')
    expect(wrapper.text()).toContain('H25')
    expect(wrapper.text()).toContain('kWh')
  })

  it('muestra un mensaje cuando no existen datos', () => {
    const wrapper = mount(EnergyDataTable, {
      props: {
        title: 'Consumos horarios',
        unit: 'kWh',
        rows: [],
      },
    })

    expect(wrapper.text()).toContain('No hay datos disponibles.')
  })
})
