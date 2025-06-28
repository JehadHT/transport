<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Raleway:wght@400;500;600;700&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('css/styleAuth.css') }}">
    <title>MHD Moaz Kuzez</title>
</head>

<body>
    <h2>Login For Public Transport</h2>
    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <form action="#">
                <h1>Create Account</h1>
                <div class="social-container">
                    <a href="https://www.facebook.com/" class="social"><img
                            src="{{ asset('css/images/facebook.jpeg') }}" alt="facebook"
                            class="fab fa-facebook-f" /></a>
                    <a href="https://accounts.google.com/v3/signin/identifier?dsh=S1812573153%3A1655944654029516&flowEntry=ServiceLogin&flowName=WebLiteSignIn&ifkv=AX3vH39E0iYVTmn-NoMNM_C35EPrno8LWsRx2Qhr0HApkVLZ-Zc_Vql8ouaSQOiXzEmthrpOPAV5"
                        class="social"><img src="{{ asset('css/images/google.jpg') }}" alt="google"
                            class="fab fa-google-plus-g" /></a>
                    <a href="https://www.linkedin.com/company/login" class="social"><img
                            src="{{ asset('css/images/linkedin.png') }}" alt="linkedin"
                            class="fab fa-linkedin-in" /></a>
                </div>
                <span>or use your email for registration</span>
                <input type="text" placeholder="Name" />
                <input type="email" placeholder="Email" />
                <input type="password" placeholder="Password" />
                <button>Sign Up</button>
            </form>
        </div>
        <div class="form-container sign-in-container">
            <form action="#">
                <h1>Login</h1>
                <div class="social-container">
                    <a href="https://www.facebook.com/" class="social"><img
                            src="{{ asset('css/images/facebook.jpeg') }}" alt="facebook"
                            class="fab fa-facebook-f" /></a>
                    <a href="https://accounts.google.com/v3/signin/identifier?dsh=S1812573153%3A1655944654029516&flowEntry=ServiceLogin&flowName=WebLiteSignIn&ifkv=AX3vH39E0iYVTmn-NoMNM_C35EPrno8LWsRx2Qhr0HApkVLZ-Zc_Vql8ouaSQOiXzEmthrpOPAV5"
                        class="social"><img src="{{ asset('css/images/google.jpg') }}" alt="google"
                            class="fab fa-google-plus-g" /></a>
                    <a href="https://www.linkedin.com/company/login" class="social"><img
                            src="{{ asset('css/images/linkedin.png') }}" alt="linkedin"
                            class="fab fa-linkedin-in" /></a>
                </div>
                <span>or use your account</span>
                <input type="email" placeholder="Email" />
                <input type="password" placeholder="Password" />
                <a href="#">Forgot your password?</a>
                <button>Login</button>
            </form>
        </div>
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1>Welcome Back!</h1>
                    <p>To keep connected with us please login with your personal info</p>
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


    <script src="{{ asset('js/CalculateAuth.js') }}"></script>
</body>

</html>