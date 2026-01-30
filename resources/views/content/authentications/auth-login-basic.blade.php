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

                    <form id="formAuthentication" class="mb-5" action="{{ url('/') }}" method="GET">
                        <div class="form-floating form-floating-outline mb-5 form-control-validation">
                            <input type="text" class="form-control" id="email" name="email-username" placeholder="Enter your email or username" autofocus />
                            <label for="email">Email or Username</label>
                        </div>
                        <div class="mb-5">
                            <div class="form-password-toggle form-control-validation">
                                <div class="input-group input-group-merge">
                                    <div class="form-floating form-floating-outline">
                                        <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                                        <label for="password">Password</label>
                                    </div>
                                    <span class="input-group-text cursor-pointer"><i class="icon-base ri ri-eye-off-line icon-20px"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-5 pb-2 d-flex justify-content-between pt-2 align-items-center">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="remember-me" />
                                <label class="form-check-label" for="remember-me"> Remember Me </label>
                            </div>
                            <a href="{{ url('auth/forgot-password-basic') }}" class="float-end mb-1">
                                <span>Forgot Password?</span>
                            </a>
                        </div>
                        <div class="mb-5">
                            <button class="btn btn-primary d-grid w-100" type="submit">login</button>
                        </div>
                    </form>


                </div>
            </div>
        </div>
    </div>
</div>
@endsection
