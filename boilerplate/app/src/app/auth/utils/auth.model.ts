export interface LoginRequest {
  email: string;
  password: string;
}

/** Mirrors UserOutputDTO - snake_case, consumed as-is. */
export interface AuthUser {
  id: string;
  email: string;
  full_name: string;
  role: string;
}

export interface LoginData {
  user: AuthUser;
}

// {{FILL: request/response shapes for whatever onboarding this project adds -
//  registration, invitations, password reset.}}
