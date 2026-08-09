import type {
  ApiErrorResponse,
  CalculateRequest,
  CalculateResponse,
  EnergyDataResponse,
} from '../types/energy'

const API_URL = import.meta.env.VITE_API_URL ?? 'http://127.0.0.1:8000'

export class ApiError extends Error {
  constructor(
    public readonly status: number,
    public readonly data: ApiErrorResponse,
  ) {
    super(data.message)
    this.name = 'ApiError'
  }
}

async function request<T>(path: string, options: RequestInit = {}): Promise<T> {
  const headers = new Headers(options.headers)

  headers.set('Accept', 'application/json')

  if (options.body && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json')
  }

  const response = await fetch(`${API_URL}${path}`, {
    ...options,
    headers,
  })

  const data = await response.json()

  if (!response.ok) {
    throw new ApiError(response.status, data as ApiErrorResponse)
  }

  return data as T
}

export function getConsumptions(): Promise<EnergyDataResponse> {
  return request<EnergyDataResponse>('/consumptions')
}

export function getPrices(): Promise<EnergyDataResponse> {
  return request<EnergyDataResponse>('/prices')
}

export function calculateIndexedPrice(payload: CalculateRequest): Promise<CalculateResponse> {
  return request<CalculateResponse>('/calculate', {
    method: 'POST',
    body: JSON.stringify(payload),
  })
}
