{{-- <x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox"
                    class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                    name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                href="{{ route('password.request') }}">
                {{ __('Forgot your password?') }}
            </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout> --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Raleway:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/styleAuth.css') }}">
    <title>Auth - Public Transport</title>
</head>

<body>
    <div class="auth-background">
        <div class="container" id="container">
            <h2>Login/Register For Public Transport</h2>

            <div class="container" id="container">
                {{-- ✅ تسجيل الحساب --}}
                <div class="form-container sign-up-container">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <h1>Create Account</h1>

                        <div class="social-container">
                            <a href="#" class="social"><img src="{{ asset('css/images/facebook.jpeg') }}"
                                    alt="facebook" /></a>
                            <a href="#" class="social"><img src="{{ asset('css/images/google.jpg') }}"
                                    alt="google" /></a>
                            <a href="#" class="social"><img src="{{ asset('css/images/linkedin.png') }}"
                                    alt="linkedin" /></a>
                        </div>

                        <span>or use your email for registration</span>

                        <input type="text" name="name" placeholder="Name" value="{{ old('name') }}" required />
                        @error('name')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror

                        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required />
                        @error('email')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror

                        <input type="password" name="password" placeholder="Password" required />
                        @error('password')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror

                        <input type="password" name="password_confirmation" placeholder="Confirm Password" required />

                        <button type="submit">Sign Up</button>
                    </form>
                </div>

                {{-- ✅ تسجيل الدخول --}}
                <div class="form-container sign-in-container">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <h1>Login</h1>

                        {{-- حالة الجلسة --}}
                        <x-auth-session-status class="mb-4" :status="session('status')" />

                        <div class="social-container">
                            <a href="#" class="social"><img src="{{ asset('css/images/facebook.jpeg') }}"
                                    alt="facebook" /></a>
                            <a href="#" class="social"><img src="{{ asset('css/images/google.jpg') }}"
                                    alt="google" /></a>
                            <a href="#" class="social"><img src="{{ asset('css/images/linkedin.png') }}"
                                    alt="linkedin" /></a>
                        </div>

                        <span>or use your account</span>

                        <input type="email" name="email" placeholder="Email" value="{{ old('email') }}" required
                            autofocus />
                        @error('email')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror

                        <input type="password" name="password" placeholder="Password" required />
                        @error('password')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror

                        <div style="margin: 10px 0;">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember">Remember me</label>
                        </div>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">Forgot your password?</a>
                        @endif

                        <button type="submit">Login</button>
                    </form>
                </div>

                {{-- ✅ التنقل بين Login/Register --}}
                <div class="overlay-container">
                    <div class="overlay">
                        <div class="overlay-panel overlay-left">
                            <h1>Welcome Back!</h1>
                            <p>To keep connected with us please login</p>
                            <button class="ghost" id="login">Login</button>
                        </div>
                        <div class="overlay-panel overlay-right">
                            <h1>Hello, Friend!</h1>
                            <p>Enter your personal details and start journey with us</p>
                            <button class="ghost" id="signUp">Sign Up</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/CalculateAuth.js') }}"></script>
</body>

</html>