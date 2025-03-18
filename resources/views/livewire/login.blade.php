<div>
    <div class="login-content">
        <h2 class="form-title">HELLO!</h2>
        <p class="form-subtitle mb-4">Selamat datang kembali! Silakan masuk ke akun Anda.</p>

        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form wire:submit="login" class="login-form-inner">
            <div class="form-group mb-3">
                <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                    placeholder="Email" id="yourUsername">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group mb-3">
                <input type="password" wire:model="password" 
                    class="form-control @error('password') is-invalid @enderror" 
                    placeholder="Password" id="yourPassword">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input wire:model="remember" class="form-check-input" type="checkbox" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>
                <a href="#" class="forgot-password">Forgot password?</a>
            </div>

            <button class="btn-login" type="submit" wire:loading.class="loading" wire:target="login">
                <span wire:loading.remove wire:target="login">LOGIN</span>
                <span wire:loading wire:target="login" class="loading-text">
                    <svg class="spinner" viewBox="0 0 50 50">
                        <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5"></circle>
                    </svg>
                    LOADING...
                </span>
            </button>

            <p class="text-center mt-4">
                Belum punya akun? <a href="#" class="create-account">Buat akun</a>
            </p>
        </form>
    </div>

    <style>
        .login-content {
            max-width: 400px;
            width: 100%;
        }

        .form-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            color: #666;
            font-size: 0.95rem;
        }

        .login-form-inner {
            width: 100%;
        }

        .form-control {
            background: #f8f9fa;
            border: 2px solid transparent;
            padding: 15px;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: #fff;
            border-color: #1e88e5;
            box-shadow: 0 0 0 4px rgba(30, 136, 229, 0.1);
        }

        .form-check-input:checked {
            background-color: #1e88e5;
            border-color: #1e88e5;
        }

        .forgot-password {
            color: #1e88e5;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #1565c0;
            text-decoration: underline;
        }

        .btn-login {
            background: #1e88e5;
            color: #fff;
            border: none;
            padding: 15px;
            border-radius: 10px;
            width: 100%;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: #1565c0;
            transform: translateY(-2px);
        }

        .create-account {
            color: #1e88e5;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .create-account:hover {
            color: #1565c0;
            text-decoration: underline;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .form-check-label {
            font-size: 0.9rem;
            color: #666;
        }
        @media (max-width: 768px) {
            .login-content {
                max-width: 320px;
                padding: 20px;
            }

            .form-title {
                font-size: 2rem;
            }

            .form-subtitle {
                font-size: 0.9rem;
            }

            .form-control {
                padding: 12px;
                font-size: 0.9rem;
            }

            .btn-login {
                padding: 12px;
            }

            .form-check-label, 
            .forgot-password {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 380px) {
            .login-content {
                max-width: 280px;
                padding: 15px;
            }
        }

        .btn-login {
            /* ... existing button styles ... */
            position: relative;
            overflow: hidden;
        }

        .loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .loading-text {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .spinner {
            animation: rotate 2s linear infinite;
            width: 20px;
            height: 20px;
        }

        .path {
            stroke: #ffffff;
            stroke-linecap: round;
            animation: dash 1.5s ease-in-out infinite;
        }

        @keyframes rotate {
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes dash {
            0% {
                stroke-dasharray: 1, 150;
                stroke-dashoffset: 0;
            }
            50% {
                stroke-dasharray: 90, 150;
                stroke-dashoffset: -35;
            }
            100% {
                stroke-dasharray: 90, 150;
                stroke-dashoffset: -124;
            }
        }
    </style>
</div>