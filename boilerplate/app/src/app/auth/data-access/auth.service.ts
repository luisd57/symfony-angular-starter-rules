import { HttpClient } from '@angular/common/http';
import { Injectable, Signal, WritableSignal, computed, inject, signal } from '@angular/core';
import { Router } from '@angular/router';
import { EMPTY, Observable, catchError, finalize, map, tap } from 'rxjs';
import { environment } from '../../../environments/environment';
import { ApiResponse } from '../../shared/utils/api-response.model';
import { AuthUser, LoginData, LoginRequest } from '../utils/auth.model';

/**
 * State is signals; HTTP methods return observables (see angular-apis.md).
 *
 * The user is mirrored into localStorage because `authGuard` runs synchronously
 * on navigation, before `/auth/me` can return. Drop the mirror and every
 * protected route bounces to /login on a fresh page load — and the e2e
 * storageState stops working.
 */
const USER_KEY: string = 'auth_user';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly http: HttpClient = inject(HttpClient);
  private readonly router: Router = inject(Router);

  readonly user: WritableSignal<AuthUser | null> = signal<AuthUser | null>(this.loadUser());
  readonly isAuthenticated: Signal<boolean> = computed((): boolean => this.user() !== null);
  readonly initialized: WritableSignal<boolean> = signal(false);

  /** Revalidates the mirrored user against the API on app start. */
  init(): Observable<void> {
    if (!this.loadUser()) {
      this.initialized.set(true);

      return EMPTY;
    }

    return this.http.get<ApiResponse<AuthUser>>(`${environment.apiUrl}/auth/me`).pipe(
      tap((response: ApiResponse<AuthUser>): void => {
        if (response.success && response.data) {
          this.setUser(response.data);
        } else {
          this.clearLocal();
        }
      }),
      catchError((): Observable<never> => {
        this.clearLocal();

        return EMPTY;
      }),
      map((): undefined => undefined),
      finalize((): void => {
        this.initialized.set(true);
      }),
    );
  }

  login(request: LoginRequest): Observable<AuthUser> {
    return this.http
      .post<ApiResponse<LoginData>>(`${environment.apiUrl}/auth/login`, request)
      .pipe(
        map((response: ApiResponse<LoginData>): LoginData => {
          if (!response.success || !response.data) {
            throw new Error(response.error?.message ?? 'Login failed');
          }

          return response.data;
        }),
        tap((data: LoginData): void => {
          this.setUser(data.user);
          // {{FILL: this project's post-login landing route}}
          void this.router.navigate(['/']);
        }),
        map((data: LoginData): AuthUser => data.user),
      );
  }

  logout(): void {
    this.http
      .post<ApiResponse<unknown>>(`${environment.apiUrl}/auth/logout`, {})
      .pipe(catchError((): Observable<never> => EMPTY))
      .subscribe();

    this.clearLocal();
    void this.router.navigate(['/login']);
  }

  private setUser(user: AuthUser): void {
    localStorage.setItem(USER_KEY, JSON.stringify(user));
    this.user.set(user);
  }

  private clearLocal(): void {
    localStorage.removeItem(USER_KEY);
    this.user.set(null);
  }

  private loadUser(): AuthUser | null {
    const raw: string | null = localStorage.getItem(USER_KEY);

    if (!raw) {
      return null;
    }

    try {
      return JSON.parse(raw) as AuthUser;
    } catch {
      return null;
    }
  }
}
