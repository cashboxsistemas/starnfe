<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-extended.css" rel="stylesheet">
    
    <!-- Custom Login CSS -->
    <link rel="stylesheet" href="/assets/login2/auth-modern.css" />
    
    <title>StarNFe - Sistema de Gestão Fiscal</title>
</head>

<body>
    <div class="login-container">
        <!-- Alerts Section -->
        @if(session()->has('flash_sucesso'))
        <div class="alert-floating alert-success">
            <div class="alert-content">
                <div class="alert-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
                        <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm45.66,85.66-56,56a8,8,0,0,1-11.32,0l-24-24a8,8,0,0,1,11.32-11.32L112,148.69l50.34-50.35a8,8,0,0,1,11.32,11.32Z"></path>
                    </svg>
                </div>
                <div class="alert-text">
                    <strong>Sucesso!</strong>
                    <span>{{ session()->get('flash_sucesso') }}</span>
                </div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
        </div>
        @endif

        @if(session()->has('flash_erro'))
        <div class="alert-floating alert-error">
            <div class="alert-content">
                <div class="alert-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 256 256">
                        <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm37.66,130.34a8,8,0,0,1-11.32,11.32L128,139.31l-26.34,26.35a8,8,0,0,1-11.32-11.32L116.69,128,90.34,101.66a8,8,0,0,1,11.32-11.32L128,116.69l26.34-26.35a8,8,0,0,1,11.32,11.32L139.31,128Z"></path>
                    </svg>
                </div>
                <div class="alert-text">
                    <strong>Erro!</strong>
                    <span>{{ session()->get('flash_erro') }}</span>
                </div>
            </div>
            <button type="button" class="alert-close" onclick="this.parentElement.style.display='none'">×</button>
        </div>
        @endif

        <!-- Left Panel - Login Form -->
        <div class="login-panel">
            <div class="login-content">
                <!-- Logo and Branding -->
                <div class="brand-section">
                    <div class="brand-logo">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#6366F1" viewBox="0 0 256 256">
                            <path d="M213.66,82.34l-56-56A8,8,0,0,0,152,24H56A16,16,0,0,0,40,40V216a16,16,0,0,0,16,16H200a16,16,0,0,0,16-16V88A8,8,0,0,0,213.66,82.34ZM160,51.31,188.69,80H160ZM200,216H56V40h88V88a8,8,0,0,0,8,8h48V216Z"></path>
                        </svg>
                    </div>
                    <h1 class="brand-title">StarNFe</h1>
                    <p class="brand-subtitle">Sistema de Gestão Fiscal Completo</p>
                </div>

                <!-- Login Form -->
                <div class="form-section">
                    <div class="form-tabs">
                        <button class="tab-button active" data-tab="login">Entrar</button>
                        <button class="tab-button" data-tab="register">Cadastrar</button>
                    </div>

                    <!-- Login Tab -->
                    <div class="tab-content active" id="login-tab">
                        <form method="post" action="{{ route('login.request') }}" id="form-login" class="login-form">
                            @csrf
                            <div class="form-group">
                                <label for="login" class="form-label">Usuário</label>
                                <input 
                                    autocomplete="off" 
                                    type="text" 
                                    class="form-input" 
                                    id="login" 
                                    placeholder="digite eu nome de usuário" 
                                    autofocus 
                                    @if(session('login') !=null) value="{{ session('login') }}" @else @if(isset($loginCookie)) value="{{$loginCookie}}" @endif @endif 
                                    name="login" 
                                />
                            </div>

                            <div class="form-group">
                                <label for="senha" class="form-label">Senha</label>
                                <div class="password-input">
                                    <input 
                                        type="password" 
                                        class="form-input" 
                                        id="senha" 
                                        name="senha" 
                                        placeholder="••••••••" 
                                        autocomplete="off" 
                                        @if(isset($senhaCookie)) value="{{$senhaCookie}}" @endif
                                    >
                                    <button type="button" class="password-toggle" onclick="togglePassword()">
                                        <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 256 256">
                                            <path d="M247.31,124.76c-.35-.79-8.82-19.58-27.65-38.41C194.57,61.26,162.88,48,128,48S61.43,61.26,36.34,86.35C17.51,105.18,9,124,8.69,124.76a8,8,0,0,0,0,6.5c.35.79,8.82,19.57,27.65,38.4C61.43,194.74,93.12,208,128,208s66.57-13.26,91.66-38.34c18.83-18.83,27.3-37.61,27.65-38.4A8,8,0,0,0,247.31,124.76ZM128,192c-30.78,0-57.67-11.19-79.93-33.25A133.47,133.47,0,0,1,25,128,133.33,133.33,0,0,1,48.07,97.25C70.33,75.19,97.22,64,128,64s57.67,11.19,79.93,33.25A133.46,133.46,0,0,1,231.05,128C223.84,141.46,192.43,192,128,192Zm0-112a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,80Zm0,80a32,32,0,1,1,32-32A32,32,0,0,1,128,160Z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="form-options">
                                <label class="checkbox-container">
                                    <input 
                                        type="checkbox" 
                                        id="lembrar" 
                                        name="lembrar" 
                                        @isset($lembrarCookie) @if($lembrarCookie==true) checked @endif @endif 
                                    />
                                    <span class="checkmark"></span>
                                    Lembrar-me
                                </label>
                                <a href="javascript:;" id="forget-password" class="forgot-link">Esqueci minha senha</a>
                            </div>

                            <button type="submit" class="btn-primary">
                                Entrar
                            </button>
                        </form>
                    </div>

                    <!-- Register Tab -->
                    <div class="tab-content" id="register-tab">
                        <div class="register-content">
                            <div class="register-info">
                                <h3>Sistema de gestão comercial completo</h3>
                                <p>v2025.01.03.005</p>
                            </div>
                            <a href="/cadastro/plano" class="btn-secondary">
                                Quero cadastrar minha empresa
                            </a>
                        </div>
                    </div>

                    <!-- Forgot Password Form -->
                    <div class="forgot-password-form d-none">
                        <form method="post" id="forget-form" action="{{ route('recuperarSenha') }}">
                            @csrf
                            <h3>Recuperar senha</h3>
                            <p>Receba uma nova senha em seu e-mail cadastrado.</p>
                            <div class="form-group">
                                <label for="email-recover" class="form-label">E-mail cadastrado</label>
                                <input 
                                    class="form-input" 
                                    type="email" 
                                    autocomplete="off" 
                                    placeholder="seu@email.com" 
                                    name="email" 
                                    id="email-recover"
                                />
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn-primary">Solicitar nova senha</button>
                                <button type="button" id="back-btn" class="btn-secondary">Voltar ao login</button>
                            </div>
                        </form>
                    </div>

                    @if(env("APP_ENV") == "demo")
                    <div class="demo-section">
                        <h4>Demonstração de Login</h4>
                        <div class="demo-buttons">
                            <button type="button" class="btn-demo btn-demo-admin" onclick="doLogin('usuario', '123')">
                                Super Admin
                            </button>
                            <button type="button" class="btn-demo btn-demo-user" onclick="doLogin('mateus', '123456')">
                                Administrador
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Panel - Visual -->
        <div class="visual-panel">
            <div class="visual-content">
                <div class="visual-grid">
                    <div class="grid-item"></div>
                    <div class="grid-item"></div>
                    <div class="grid-item"></div>
                    <div class="grid-item"></div>
                </div>
                <div class="visual-overlay">
                    <h2>Gestão fiscal moderna e intuitiva</h2>
                    <p>Emissão de NFe, NFCe, CTe e muito mais em uma única plataforma</p>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/jquery.min.js" type="text/javascript"></script>
    <script>
        // Tab functionality
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                const tabName = button.getAttribute('data-tab');
                
                // Remove active class from all tabs and contents
                document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Add active class to clicked tab and corresponding content
                button.classList.add('active');
                document.getElementById(tabName + '-tab').classList.add('active');
            });
        });

        // Forgot password functionality
        $("#forget-password").on('click', function() {
            $('.form-section .tab-content, .form-tabs').addClass('d-none');
            $('.forgot-password-form').removeClass('d-none');
        });

        $('#back-btn').on('click', function() {
            $('.forgot-password-form').addClass('d-none');
            $('.form-section .tab-content, .form-tabs').removeClass('d-none');
        });

        // Demo login functionality
        function doLogin(login, senha){
            $('#login').val(login);
            $('#senha').val(senha);
            $('#form-login').submit();
        }

        // Password toggle functionality
        function togglePassword() {
            const passwordInput = document.getElementById('senha');
            const eyeIcon = document.querySelector('.eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = '<path d="M53.92,34.62A8,8,0,1,1,42.08,45.38L61.32,66.55C25,88.84,9.38,123.2,8.69,124.76a8,8,0,0,0,0,6.5c.35.79,8.82,19.57,27.65,38.4C61.43,194.74,93.12,208,128,208a127.11,127.11,0,0,0,52.07-10.83l22,24.21a8,8,0,1,0,11.84-10.76Zm47.33,75.84,41.67,45.85a33.06,33.06,0,0,1-41.67-45.85ZM128,192c-30.78,0-57.67-11.19-79.93-33.25A133.16,133.16,0,0,1,25,128c4.69-8.79,19.66-33.39,47.35-49.38l18,19.75a48,48,0,0,0,63.66,70l14.73,16.2A112,112,0,0,1,128,192Zm6-95.43a8,8,0,0,1,3-15.72,48.16,48.16,0,0,1,38.77,42.64,8,8,0,0,1-7.22,8.71,6.39,6.39,0,0,1-.75,0,8,8,0,0,1-8-7.26A32.09,32.09,0,0,0,134,96.57Zm113.28,34.69c-.42.94-10.55,23.37-33.36,43.8a8,8,0,1,1-10.67-11.92A132.77,132.77,0,0,0,231.05,128a133.15,133.15,0,0,0-23.12-30.77C185.67,75.19,158.78,64,128,64a118.37,118.37,0,0,0-19.36,1.57A8,8,0,1,1,106,49.79,134,134,0,0,1,128,48c34.88,0,66.57,13.26,91.66,38.35,18.83,18.82,27.3,37.6,27.65,38.39A8,8,0,0,1,247.31,131.26Z"></path>';
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = '<path d="M247.31,124.76c-.35-.79-8.82-19.58-27.65-38.41C194.57,61.26,162.88,48,128,48S61.43,61.26,36.34,86.35C17.51,105.18,9,124,8.69,124.76a8,8,0,0,0,0,6.5c.35.79,8.82,19.57,27.65,38.4C61.43,194.74,93.12,208,128,208s66.57-13.26,91.66-38.34c18.83-18.83,27.3-37.61,27.65-38.4A8,8,0,0,0,247.31,124.76ZM128,192c-30.78,0-57.67-11.19-79.93-33.25A133.47,133.47,0,0,1,25,128,133.33,133.33,0,0,1,48.07,97.25C70.33,75.19,97.22,64,128,64s57.67,11.19,79.93,33.25A133.46,133.46,0,0,1,231.05,128C223.84,141.46,192.43,192,128,192Zm0-112a48,48,0,1,0,48,48A48.05,48.05,0,0,0,128,80Zm0,80a32,32,0,1,1,32-32A32,32,0,0,1,128,160Z"></path>';
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert-floating').forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-100%)';
                setTimeout(() => alert.style.display = 'none', 300);
            });
        }, 5000);
    </script>
</body>
</html>
