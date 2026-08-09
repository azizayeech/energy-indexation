export type HourlyValues = {
  h1: number
  h2: number
  h3: number
  h4: number
  h5: number
  h6: number
  h7: number
  h8: number
  h9: number
  h10: number
  h11: number
  h12: number
  h13: number
  h14: number
  h15: number
  h16: number
  h17: number
  h18: number
  h19: number
  h20: number
  h21: number
  h22: number
  h23: number
  h24: number
  h25: number
}

export type EnergyData = HourlyValues & {
  id: number
  date: string
}

export interface EnergyDataResponse {
  data: EnergyData[]
}

export interface CalculateRequest {
  start_date: string
  end_date: string
  formula: string
}

export interface CalculateResponse {
  price_indexed: number
}

export interface ApiErrorResponse {
  message: string
  errors?: Record<string, string[]>
}
