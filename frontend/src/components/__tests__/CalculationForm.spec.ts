import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'

import CalculationForm from '../CalculationForm.vue'

describe('CalculationForm', () => {
  it('emite los datos introducidos al enviar el formulario', async () => {
    const wrapper = mount(CalculationForm, {
      props: {
        isSubmitting: false,
      },
    })

    await wrapper.get('#start-date').setValue('2025-03-01')

    await wrapper.get('#end-date').setValue('2025-03-02')

    await wrapper.get('#formula').setValue('([OMIE_MD] * 0.6) + 0.88')

    await wrapper.get('form').trigger('submit')

    expect(wrapper.emitted('submit')).toEqual([
      [
        {
          start_date: '2025-03-01',
          end_date: '2025-03-02',
          formula: '([OMIE_MD] * 0.6) + 0.88',
        },
      ],
    ])
  })

  it('muestra los errores de validación recibidos de la API', () => {
    const wrapper = mount(CalculationForm, {
      props: {
        isSubmitting: false,
        errors: {
          formula: ['La fórmula debe contener la variable [OMIE_MD].'],
        },
      },
    })

    expect(wrapper.text()).toContain('La fórmula debe contener la variable [OMIE_MD].')

    expect(wrapper.get('#formula').classes()).toContain('is-invalid')
  })
})
