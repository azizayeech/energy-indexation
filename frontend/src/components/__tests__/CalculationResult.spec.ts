import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'

import CalculationResult from '../CalculationResult.vue'

describe('CalculationResult', () => {
  it('muestra el precio completo usando coma decimal', () => {
    const wrapper = mount(CalculationResult, {
      props: {
        price: 1.06528813559322,
      },
    })

    expect(wrapper.text()).toContain('1,06528813559322')
    expect(wrapper.text()).toContain('€/kWh')
  })
})
