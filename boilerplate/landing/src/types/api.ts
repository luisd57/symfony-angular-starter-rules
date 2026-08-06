export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  error?: ApiErrorBody;
}

export interface ApiErrorBody {
  code: string;
  message: string;
  details?: Record<string, string>;
}

/** Carries the API's error `code` so callers can branch without parsing text. */
export class ApiError extends Error {
  constructor(
    public readonly code: string,
    message: string,
    public readonly details?: Record<string, string>,
  ) {
    super(message);
  }
}

// {{FILL: the response shapes this landing site consumes.}}
