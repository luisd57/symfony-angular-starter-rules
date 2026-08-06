import { ApiError, type ApiResponse } from '../types/api';

// Overridden to http://nginx/api inside the e2e container; see docker-compose.yml.
const API_BASE: string = import.meta.env.PUBLIC_API_BASE_URL ?? 'http://localhost:8080/api';

/**
 * Single place the response envelope is unwrapped. Every exported call below
 * goes through it, so callers see either data or an ApiError — never the
 * envelope itself.
 */
async function apiRequest<T>(path: string, options?: RequestInit): Promise<T> {
  const res = await fetch(`${API_BASE}${path}`, {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      ...options?.headers,
    },
  });

  const json: ApiResponse<T> = await res.json();

  if (!json.success || json.error) {
    throw new ApiError(
      json.error?.code ?? 'UNKNOWN_ERROR',
      json.error?.message ?? 'An unexpected error occurred',
      json.error?.details,
    );
  }

  return json.data as T;
}

// {{FILL: one exported function per endpoint this site calls, each delegating
//  to apiRequest<T>. Consume snake_case field names as-is.}}
export async function fetchHealth(): Promise<unknown> {
  return apiRequest<unknown>('/health');
}
