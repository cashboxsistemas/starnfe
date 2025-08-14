<!doctype html>
<html lang="pt-BR">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->

    <!--plugins-->
    <link href="/assets/css/simplebar.css" rel="stylesheet" />
    {{-- <link href="/assets/css/perfect-scrollbar.css" rel="stylesheet" /> --}}
    <link href="/assets/css/highcharts.css" rel="stylesheet" />
    <link href="/assets/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet" />
    <link href="/assets/css/metisMenu.min.css" rel="stylesheet" />
    <!-- loader-->
    <link href="/assets/css/pace.min.css" rel="stylesheet" />
    <script src="/assets/js/pace.min.js"></script>
    <!-- Bootstrap CSS -->
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-extended.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
    <link href="/assets/css/icons.css" rel="stylesheet">
    <!-- Theme Style CSS -->
    <link rel="stylesheet" href="/assets/css/dark-theme.css" />
    <link rel="stylesheet" href="/assets/css/semi-dark.css" />
    <link rel="stylesheet" href="/assets/css/header-colors.css" />
    <title>{{env("APP_NAME")}} Login</title>
</head>

<body>
    <div class="authentication-header"></div>
    <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
        <div class="container-fluid">
            <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                <div class="col mx-auto">
                    <div class="mb-4 text-center">
                        <img src="logos/slym.jpg" width="180" alt="" />
                    </div>
                    <div class="card">
                        <div class="card-body">
                            @if(session()->has('flash_login'))
                            <div class="mt-2 mb-0" style="width: 98%; margin-left: 10px; margin-top: 0px;">
                                <div class="alert alert-danger border-0 bg-danger alert-dismissible fade show mb-0 py-1">
                                    <div class="d-flex align-items-center">
                                        <div class="font-35 text-white"><i class="bx bxs-message-square-x"></i>
                                        </div>
                                        <div class="ms-3">

                                            <div class="text-white">{{ session()->get('flash_login') }}</div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            @endif
                            <center><img width="280" src="assets/images/logo1.jpg"></center>
                            <div class="login-separater text-center mb-4"> <span>{{env("APP_NAME")}}</span>
                                <hr />
                            </div>
                            <div class="form-body" id="form-login">
                                <form class="row g-3" method="post" action="{{ route('login.request') }}">
                                    @csrf
                                    <div class="col-12">
                                        <label for="inputEmailAddress" class="form-label">Login</label>
                                        <input autocomplete="off" type="text" class="form-control" id="login" placeholder="Login" autofocus @if(session('login') !=null) value="{{ session('login') }}" @else @if(isset($loginCookie)) value="{{$loginCookie}}" @endif @endif name="login">
                                    </div>

                                    <div class="col-12">
                                        <label for="inputChoosePassword" class="form-label">Senha</label>
                                        <div class="input-group" id="show_hide_password">
                                            <input type="password" class="form-control border-end-0" id="senha" name="senha" placeholder="Senha" autocomplete="off" @if(isset($senhaCookie)) value="{{$senhaCookie}}" @endif> <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="lembrar" name="lembrar" @isset($lembrarCookie) @if($lembrarCookie==true) checked @endif @endif>
                                            <label class="form-check-label" for="flexSwitchCheckChecked">Lembrar-me</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-end"> <a href="javascript:;" id="forget-password">Esqueceu a senha?</a>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-success"><i class="bx bxs-check-circle"></i>Acessar</button>

                                            <a href="/cadastro" type="submit" class="btn btn-info mt-2 text-white"><i class="bx bxs-layer-plus"></i>Quero cadastrar minha empresa</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="div-recuperar-senha-sicok d-none">
                                <form method="post" action="{{ route('recuperarSenha') }}" id="forget-form">
                                    @csrf
                                    <h3>Esqueceu a senha?</h3>
                                    <p>Receba uma nova senha em seu e-mail cadastrado.</p>
                                    <div class="form-group">
                                        <input class="form-control placeholder-no-fix input-email-recuperar-senha-sicok" type="text" autocomplete="off" placeholder="E-mail cadastrado" name="email" />
                                    </div>
                                    <div class="form-actions mt-3">
                                        <button type="button" id="back-btn" class="btn btn-primary"><i class="bx bx-home-alt"></i> Tela de login</button>
                                        <button type="submit" class="btn btn-success"><i class="bx bx-check"></i> Solicitar nova senha </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->
    </div>
    </div>
    </div>
    <!--end wrapper-->
    <!-- Bootstrap JS -->
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <!--plugins-->
    <script src="/assets/js/jquery.min.js"></script>

    <!--Password show & hide js -->
    <script>
        $(document).ready(function() {
            $("#show_hide_password a").on('click', function(event) {
                event.preventDefault();
                if ($('#show_hide_password input').attr("type") == "text") {
                    $('#show_hide_password input').attr('type', 'password');
                    $('#show_hide_password i').addClass("bx-hide");
                    $('#show_hide_password i').removeClass("bx-show");
                } else if ($('#show_hide_password input').attr("type") == "password") {
                    $('#show_hide_password input').attr('type', 'text');
                    $('#show_hide_password i').removeClass("bx-hide");
                    $('#show_hide_password i').addClass("bx-show");
                }
            });
        });

    </script>
    <!--app JS-->
    {{-- <script src="assets/js/app.js"></script> --}}
    <script src="assets/login1/login.js" type="text/javascript"></script>
    {{-- <script src="assets/login3/js/jquery.backstretch.min.js" type="text/javascript"></script> --}}
    {{-- <script src="assets/login3/js/jquery-3.3.1.min.js" type="text/javascript"></script> --}}

</body>

</html>
