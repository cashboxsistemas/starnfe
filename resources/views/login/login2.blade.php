<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->

    <!-- Bootstrap CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-extended.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/login2/auth.css" />
    <title>{{env("APP_NAME")}} Login</title>
</head>

<body>
    <div id="auth">
        <div class="row">
            <div class="col-lg-5 col-12">
                @if(session()->has('flash_sucesso'))
                <div class="mt-2 mb-0" style="width: 98%; margin-left: 10px; margin-top: 0px;">
                    <div class="alert alert-success border-0 bg-success alert-dismissible fade show mb-0 py-1">
                        <div class="d-flex align-items-center">
                            <div class="font-35 text-white"><i class="bx bxs-check-circle"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0 text-white">Sucesso</h6>
                                <div class="text-white">{{ session()->get('flash_sucesso') }}</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
                @endif

                @if(session()->has('flash_erro'))
                <div class="mt-2 mb-0" style="width: 98%; margin-left: 10px; margin-top: 0px;">
                    <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show mb-0 py-1">
                        <div class="d-flex align-items-center">
                            <div class="font-35 text-white"><i class="bx bxs-message-square-x"></i>
                            </div>
                            <div class="ms-3">
                                <h6 class="mb-0 text-white">Erro</h6>
                                <div class="text-white">{{ session()->get('flash_erro') }}</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
                @endif

                <div id="auth-left">
                    <div class="auth-logo">
                        <a href="index.html">
                            <img src="/logos/slym.jpg" />
                        </a>
                    </div>
                    <div class="">
                        <h1 class="auth-title">Login</h1>
                        <p class="auth-subtitle">
                            Entre com seus dados de acesso
                        </p>
                    </div>
                    <div class="mt-3">
                        <form method="post" action="{{ route('login.request') }}" id="form-login">
                            @csrf
                            <div class="form-group position-relative has-icon-left mb-3">
                                <input autocomplete="off" type="text" class="form-control" id="login" placeholder="Login" autofocus @if(session('login') !=null) value="{{ session('login') }}" @else @if(isset($loginCookie)) value="{{$loginCookie}}" @endif @endif name="login" />
                                <div class="form-control-icon">
                                    <i class="bi bi-person"></i>
                                </div>
                            </div>

                            <div class="form-group position-relative has-icon-left mb-3">
                                <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" autocomplete="off" @if(isset($senhaCookie)) value="{{$senhaCookie}}" @endif>
                                <div class="form-control-icon">
                                    <i class="bi bi-shield-lock"></i>
                                </div>
                            </div>

                            <div class="form-check form-check-lg d-flex mt-0">
                                <input class="form-check-input me-2" type="checkbox" id="lembrar" name="lembrar" @isset($lembrarCookie) @if($lembrarCookie==true) checked @endif @endif />
                                <label class="form-check-label text-gray-600" for="flexCheckDefault">
                                    Lembrar-me
                                </label>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary btn-block shadow-lg mt-4 px-5" style="width: 100%">
                                    Entrar
                                </button>
                            </div>
                        </form>
                        <div class="text-center mt-2 form-login">
                            <p><a href="javascript:;" id="forget-password" class="col-md-6 text-sublinhado"> Esqueci minha senha </a></p>
                        </div>
                    </div>

                    <div class="row div-recuperar-senha-sicok d-none mt-5">
                        <form method="post" id="forget-form" action="{{ route('recuperarSenha') }}">
                            @csrf
                            <p>Receba uma nova senha em seu e-mail cadastrado.</p>
                            <div class="form-group">
                                <input class="form-control placeholder-no-fix input-email-recuperar-senha-sicok" type="text" autocomplete="off" placeholder="E-mail cadastrado" name="email" />
                            </div>
                            <div class="mt-2">
                                <button type="submit" class="btn btn-warning btn-recuperar-senha-sicok w-100"> Solicitar nova senha </button>
                            </div>
                        </form>
                        <div class="mt-2">
                            <button type="button" id="back-btn" class="btn btn-info">Tela de login</button>
                        </div>
                    </div>
                    <div class="mt-5">
                        <a href="/cadastro/plano" type="submit" style="width: 100%" class="btn btn-success mt-2"> Quero cadastrar minha empresa</a>
                    </div>

                    @if(env("APP_ENV") == "demo")
                    <div class="card mt-2">
                        <div class="card-body">
                            <h4 class="mt-2">Demonstração de Login</h4>
                            <button type="button" class="btn btn-success" onclick="doLogin('usuario', '123')">
                                Super Admin
                            </button>
                            <button type="button" class="btn btn-info" onclick="doLogin('mateus', '123456')">
                                Adiministrador
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            <div class="col-lg-7 col-12 d-none d-lg-block">
                <div id="auth-right"></div>
            </div>
        </div>
    </div>
    <script src="assets/js/jquery.min.js" type="text/javascript"></script>
    <script>
        $("#forget-password").on('click', function() {
            $('#form-login, .form-login').addClass('d-none');
            $('.div-recuperar-senha-sicok').removeClass('d-none');
        });

        $('#back-btn').on('click', function() {
            $('.div-recuperar-senha-sicok').addClass('d-none');
            $('#form-login, .form-login').removeClass('d-none');
        });

        function doLogin(login, senha){

            $('#login').val(login)
            $('#senha').val(senha)
            $('#form-login').submit()
        }

    </script>
</body>
</html>
