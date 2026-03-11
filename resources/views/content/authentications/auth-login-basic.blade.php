@extends('layouts/blankLayout')

@section('title', 'Login Basic - Pages')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    .split-layout {
        display: flex;
        min-height: 100vh;
    }

    /* ── Left Hero Panel ── */
    .split-left {
        display: none;
        position: relative;
        overflow: hidden;
        background-color: #F4F6FA;
        align-items: center;
        justify-content: center;
        padding: 4rem;
        flex: 0 0 60%;
    }

    @media (min-width: 992px) {
        .split-left { display: flex; }
    }

    /* Animated blobs */
    .blob-wrap {
        position: relative;
        width: 100%;
        max-width: 560px;
    }

    .blob {
        position: absolute;
        width: 18rem;
        height: 18rem;
        border-radius: 50%;
        mix-blend-mode: multiply;
        filter: blur(60px);
        opacity: .7;
        animation: blobMove 8s ease-in-out infinite;
    }

    .blob-1 { background-color: #AFC8F3; top: -1rem;   left: -1rem;  animation-delay: 0s; }
    .blob-2 { background-color: #1E3FA8; bottom: -2rem; right: 1rem;  animation-delay: 2s; }
    .blob-3 { background-color: #2E7D32; bottom: -2rem; left: 5rem;   animation-delay: 4s; }

    @keyframes blobMove {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33%       { transform: translate(15px, -20px) scale(1.05); }
        66%       { transform: translate(-10px, 10px) scale(0.95); }
    }

    /* Card inside hero */
    .hero-card {
        position: relative;
        background: #fff;
        border-radius: 1.5rem;
        box-shadow: 0 25px 60px rgba(0,0,0,.15);
        padding: 2rem;
    }

    .hero-card-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .hero-swatch {
        height: 12rem;
        border-radius: 1rem;
    }

    .hero-swatch-1 { background: linear-gradient(135deg, #1E3FA8 0%, #0B1F5C 100%); }
    .hero-swatch-2 { background: linear-gradient(135deg, #2E7D32 0%, #1E3FA8 100%); }
    .hero-swatch-3 { background: linear-gradient(90deg,  #AFC8F3 0%, #1E3FA8 100%); height: 8rem; border-radius: 1rem; }

    .hero-text {
        margin-top: 3rem;
        text-align: center;
    }

    .hero-text h1 {
        font-size: 2.8rem;
        font-weight: 600;
        color: #0B1F5C;
        margin-bottom: .75rem;
    }

    .hero-text p {
        font-size: 1.125rem;
        color: #1E3FA8;
    }

    /* ── Right Form Panel ── */
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
        max-width: 420px;
    }

    /* Mobile brand */
    .mobile-brand {
        display: block;
        text-align: center;
        margin-bottom: 2rem;
    }

    @media (min-width: 992px) {
        .mobile-brand { display: none; }
    }

    .mobile-brand-text {
        font-size: 1.75rem;
        font-weight: 700;
        color: #0B1F5C;
    }

    /* Welcome block */
    .welcome-block {
        margin-bottom: 2rem;
    }

    .welcome-block h2 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #0B1F5C;
        margin-bottom: .35rem;
    }

    .welcome-block p {
        color: #697a8d;
    }

    /* Form fields */
    .field-label {
        display: block;
        font-size: .875rem;
        font-weight: 500;
        color: #0B1F5C;
        margin-bottom: .4rem;
    }

    .field-input {
        display: block;
        width: 100%;
        padding: .75rem 1rem;
        border: 1px solid #D9DCE3;
        border-radius: .5rem;
        font-size: .9375rem;
        color: #3c4043;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }

    .field-input:focus {
        border-color: transparent;
        box-shadow: 0 0 0 2px #1E3FA8;
    }

    .field-input.is-invalid {
        border-color: #dc3545;
    }

    .invalid-feedback {
        display: block;
        font-size: .8125rem;
        color: #dc3545;
        margin-top: .25rem;
    }

    /* Password wrapper */
    .pw-wrap {
        position: relative;
    }

    .pw-wrap .field-input {
        padding-right: 2.75rem;
    }

    .pw-toggle {
        position: absolute;
        inset-y: 0;
        right: 0;
        display: flex;
        align-items: center;
        padding: 0 .75rem;
        background: none;
        border: none;
        cursor: pointer;
        color: #697a8d;
    }

    .pw-toggle:hover { color: #3c4043; }

    /* Remember / Forgot row */
    .form-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 1rem 0;
    }

    .form-check-label { font-size: .875rem; color: #697a8d; }

    .forgot-link {
        font-size: .875rem;
        font-weight: 500;
        color: #0B1F5C;
        text-decoration: none;
    }

    .forgot-link:hover { text-decoration: underline; }

    /* Primary button */
    .btn-primary-custom {
        width: 100%;
        padding: .875rem 1rem;
        border: none;
        border-radius: .5rem;
        font-size: .9375rem;
        font-weight: 500;
        color: #fff;
        background: linear-gradient(135deg, #0B1F5C 0%, #1E3FA8 100%);
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0,0,0,.05);
        transition: background .2s, transform .15s, box-shadow .15s;
    }

    .btn-primary-custom:hover {
        background: linear-gradient(135deg, #0B1F5C 0%, #0B1F5C 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(11,31,92,.3);
    }

    /* Divider */
    .divider {
        display: flex;
        align-items: center;
        margin: 1.5rem 0;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #D9DCE3;
    }

    .divider span {
        padding: 0 1rem;
        font-size: .875rem;
        color: #697a8d;
    }

    /* Social buttons */
    .btn-social {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .75rem;
        padding: .75rem 1rem;
        border: 1px solid #D9DCE3;
        border-radius: .5rem;
        background: #fff;
        font-size: .9375rem;
        font-weight: 500;
        color: #3c4043;
        cursor: pointer;
        transition: background-color .15s;
        text-decoration: none;
        margin-bottom: .75rem;
    }

    .btn-social:last-child { margin-bottom: 0; }

    .btn-social:hover { background-color: #F4F6FA; }

    /* Sign-up & terms */
    .signup-row {
        margin-top: 1.5rem;
        text-align: center;
        font-size: .9375rem;
        color: #697a8d;
    }

    .signup-row a {
        font-weight: 500;
        color: #0B1F5C;
        text-decoration: none;
    }

    .signup-row a:hover { text-decoration: underline; }

    .terms-text {
        margin-top: 1.5rem;
        font-size: .75rem;
        text-align: center;
        color: #697a8d;
        line-height: 1.6;
    }

    .terms-text a {
        color: inherit;
        text-decoration: underline;
    }

    .terms-text a:hover { color: #3c4043; }

    /* Responsive: stack on small screens */
    @media (max-width: 991px) {
        .split-layout { flex-direction: column; }
    }
</style>
@endsection

@section('content')
<div class="split-layout">

    {{-- ── Left Hero ── --}}
    <div class="split-left">
        <div class="blob-wrap">
            <div class="blob blob-1"></div>
            <div class="blob blob-2"></div>
            <div class="blob blob-3"></div>

            <div class="hero-card">
                <div class="hero-card-grid">
                    <div class="hero-swatch hero-swatch-1"></div>
                    <div class="hero-swatch hero-swatch-2"></div>
                </div>
                <div class="hero-swatch-3"></div>
            </div>

            <div class="hero-text">
                <h1>Welcome to <br> {{ config('variables.templateName') }}</h1>
                <p>Sign in and start the adventure</p>
            </div>
        </div>
    </div>

    {{-- ── Right Form ── --}}
    <div class="split-right">
        <div class="login-container">

            {{-- Mobile logo --}}
            <div class="mobile-brand">
                <a href="{{ url('/') }}" class="app-brand-link gap-2 d-inline-flex align-items-center text-decoration-none">
                    <span class="app-brand-logo demo">@include('_partials.macros')</span>
                    <span class="mobile-brand-text">{{ config('variables.templateName') }}</span>
                </a>
            </div>

            {{-- Desktop logo --}}
            <div class="app-brand justify-content-center mb-4 d-none d-lg-flex">
                <a href="{{ url('/') }}" class="app-brand-link gap-3">
                    <span class="app-brand-logo demo">@include('_partials.macros')</span>
                    <span class="app-brand-text demo text-heading fw-semibold">{{ config('variables.templateName') }}</span>
                </a>
            </div>

            {{-- Welcome --}}
            <div class="welcome-block">
                <h2>Welcome back</h2>
                <p>Log in to continue your journey</p>
            </div>

            {{-- Alerts --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <strong>Error!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <strong>Error!</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Login Form --}}
            <form id="formAuthentication" action="{{ route('login.post') }}" method="POST">
                @csrf

                {{-- Email --}}
                <div style="margin-bottom:1rem;">
                    <label class="field-label" for="email">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="field-input @error('email') is-invalid @enderror"
                        placeholder="you@example.com"
                        value="{{ old('email') }}"
                        autofocus
                        required
                    />
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Password --}}
                <div style="margin-bottom:.5rem;">
                    <label class="field-label" for="password">Password</label>
                    <div class="pw-wrap">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            class="field-input @error('password') is-invalid @enderror"
                            placeholder="Enter your password"
                            required
                        />
                        <button type="button" class="pw-toggle" id="togglePassword" aria-label="Toggle password visibility">
                            <i class="ri ri-eye-off-line" id="toggleIcon" style="font-size:1.1rem;"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Remember / Forgot --}}
                <div class="form-meta">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" />
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="{{ url('auth/forgot-password-basic') }}" class="forgot-link">Forgot password?</a>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-primary-custom">
                    Continue
                </button>
            </form>

            {{-- Divider --}}
            <div class="divider"><span>or</span></div>

            {{-- Social Buttons --}}
            <a href="{{ route('google.login') }}" class="btn-social">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M19.6 10.227c0-.709-.064-1.39-.182-2.045H10v3.868h5.382a4.6 4.6 0 01-1.996 3.018v2.51h3.232c1.891-1.742 2.982-4.305 2.982-7.35z" fill="#4285F4"/>
                    <path d="M10 20c2.7 0 4.964-.895 6.618-2.423l-3.232-2.509c-.895.6-2.04.955-3.386.955-2.605 0-4.81-1.76-5.595-4.123H1.064v2.59A9.996 9.996 0 0010 20z" fill="#34A853"/>
                    <path d="M4.405 11.9c-.2-.6-.314-1.24-.314-1.9 0-.66.114-1.3.314-1.9V5.51H1.064A9.996 9.996 0 000 10c0 1.614.386 3.14 1.064 4.49l3.34-2.59z" fill="#FBBC05"/>
                    <path d="M10 3.977c1.468 0 2.786.505 3.823 1.496l2.868-2.868C14.959.99 12.695 0 10 0 6.09 0 2.71 2.24 1.064 5.51l3.34 2.59C5.19 5.736 7.395 3.977 10 3.977z" fill="#EA4335"/>
                </svg>
                <span>Continue with Google</span>
            </a>

            {{-- Sign Up --}}
            <div class="signup-row">
                <span>Don't have an account? </span>
                <a href="{{ url('auth/register') }}">Sign up</a>
            </div>

            {{-- Terms --}}
            <p class="terms-text">
                By continuing, you agree to our
                <a href="#">Terms of Service</a> and
                <a href="#">Privacy Policy</a>
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const toggleBtn  = document.getElementById('togglePassword');
    const passwordEl = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    toggleBtn.addEventListener('click', () => {
        const isPassword = passwordEl.type === 'password';
        passwordEl.type  = isPassword ? 'text' : 'password';
        toggleIcon.className = isPassword
            ? 'ri ri-eye-line'
            : 'ri ri-eye-off-line';
        toggleIcon.style.fontSize = '1.1rem';
    });
</script>
@endpush
@endsection