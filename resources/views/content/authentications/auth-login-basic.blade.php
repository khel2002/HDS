@extends('layouts/blankLayout')

@section('title', 'Login Basic - Pages')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
<style>
    .split-layout {
        display: flex;
        min-height: 100vh;
    }

    .split-left {
        flex: 1;
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(168, 85, 247, 0.1) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        position: relative;
        overflow: hidden;
    }

    .split-left img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
        border-radius: 1rem;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .split-right {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        background: #fff;
    }

    .login-container {
        width: 100%;
        max-width: 450px;
    }

    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 1.5rem 0;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #d9dee3;
    }

    .divider span {
        padding: 0 1rem;
        color: #697a8d;
        font-size: 0.875rem;
    }

    .btn-google {
        background-color: #fff;
        border: 1px solid #dadce0;
        color: #3c4043;
        font-weight: 500;
        transition: all 0.2s;
    }

    .btn-google:hover {
        background-color: #f8f9fa;
        border-color: #dadce0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .btn-google img {
        width: 20px;
        height: 20px;
        margin-right: 12px;
    }

    @media (max-width: 991px) {
        .split-layout {
            flex-direction: column;
        }

        .split-left {
            min-height: 300px;
            max-height: 400px;
        }

        .split-right {
            flex: 1;
        }
    }

    @media (max-width: 576px) {
        .split-left {
            min-height: 200px;
            max-height: 250px;
        }
    }
</style>
@endsection

@section('content')
<div class="split-layout">
    <!-- Left Side - Image -->
    <div class="split-left">
        <img src="{{ asset('assets/img/login/332453497_1430352321038172_2805964747262411697_n.jpg') }}" alt="Login Visual" />
    </div>

    <!-- Right Side - Login Form -->
    <div class="split-right">
        <div class="login-container">
            <div class="card border-0 shadow-none">
                <!-- Logo -->
                <div class="app-brand justify-content-center mb-5">
                    <a href="{{ url('/') }}" class="app-brand-link gap-3">
                        <span class="app-brand-logo demo">@include('_partials.macros')</span>
                        <span class="app-brand-text demo text-heading fw-semibold">{{ config('variables.templateName') }}</span>
                    </a>
                </div>
                <!-- /Logo -->

                <div class="card-body p-0">
                    <h4 class="mb-1">Welcome to {{ config('variables.templateName') }}! 👋🏻</h4>
                    <p class="mb-5">Please sign-in to your account and start the adventure</p>

                    <!-- Display Success Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>Success!</strong> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Display Error Messages -->
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Validation Errors -->
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Traditional Login Form -->
                    <form id="formAuthentication" class="mb-4" action="{{ route('login.post') }}" method="POST">
                        @csrf
                        <div class="form-floating form-floating-outline mb-4">
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                placeholder="Enter your email"
                                value="{{ old('email') }}"
                                autofocus
                                required
                            />
                            <label for="email">Email</label>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <div class="form-password-toggle">
                                <div class="input-group input-group-merge">
                                    <div class="form-floating form-floating-outline">
                                        <input
                                            type="password"
                                            id="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            name="password"
                                            placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                            aria-describedby="password"
                                            required
                                        />
                                        <label for="password">Password</label>
                                    </div>
                                    <span class="input-group-text cursor-pointer">
                                        <i class="icon-base ri ri-eye-off-line icon-20px"></i>
                                    </span>
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="remember" name="remember" />
                                <label class="form-check-label" for="remember">Remember Me</label>
                            </div>
                            <a href="{{ url('auth/forgot-password-basic') }}" class="text-primary">
                                <span>Forgot Password?</span>
                            </a>
                        </div>

                        <div class="mb-4">
                            <button class="btn btn-primary d-grid w-100" type="submit">
                                <span>Sign In</span>
                            </button>
                        </div>
                    </form>

                    <!-- Divider -->
                    <div class="divider">
                        <span>OR</span>
                    </div>

                    <!-- Google SSO Button -->
                    <div class="mb-4">
                        <a href="{{ route('google.login') }}" class="btn btn-google d-grid w-100 d-flex align-items-center justify-content-center">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M19.6 10.227c0-.709-.064-1.39-.182-2.045H10v3.868h5.382a4.6 4.6 0 01-1.996 3.018v2.51h3.232c1.891-1.742 2.982-4.305 2.982-7.35z" fill="#4285F4"/>
                                <path d="M10 20c2.7 0 4.964-.895 6.618-2.423l-3.232-2.509c-.895.6-2.04.955-3.386.955-2.605 0-4.81-1.76-5.595-4.123H1.064v2.59A9.996 9.996 0 0010 20z" fill="#34A853"/>
                                <path d="M4.405 11.9c-.2-.6-.314-1.24-.314-1.9 0-.66.114-1.3.314-1.9V5.51H1.064A9.996 9.996 0 000 10c0 1.614.386 3.14 1.064 4.49l3.34-2.59z" fill="#FBBC05"/>
                                <path d="M10 3.977c1.468 0 2.786.505 3.823 1.496l2.868-2.868C14.959.99 12.695 0 10 0 6.09 0 2.71 2.24 1.064 5.51l3.34 2.59C5.19 5.736 7.395 3.977 10 3.977z" fill="#EA4335"/>
                            </svg>
                            <span>Sign in with Google</span>
                        </a>
                    </div>

                    <!-- Info Text -->
                    <p class="text-center text-muted mb-0">
                        <small>
                            <i class="ri-information-line"></i>
                            Sign in with your Google account to automatically sync with HRMIS
                        </small>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
