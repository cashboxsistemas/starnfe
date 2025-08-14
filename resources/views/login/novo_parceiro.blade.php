<!doctype html>
<html lang="pt-BR">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/bootstrap-extended.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
    <link href="/assets/css/icons.css" rel="stylesheet">
    <!-- Theme Style CSS -->
    <link rel="stylesheet" href="/assets/css/dark-theme.css" />
    <link rel="stylesheet" href="/assets/css/semi-dark.css" />
    <link rel="stylesheet" href="/assets/css/header-colors.css" />
    <link href="/assets/css/select2.min.css" rel="stylesheet" />
    <link href="/assets/css/select2-bootstrap4.css" rel="stylesheet" />
    <link href="/assets/css/style.css" rel="stylesheet" />

    <title>{{env("APP_NAME")}} Login</title>
</head>

<body>
    <div class="container">
        <div class="card mt-5">
            <div class="row">
                <div class="col-6">
                    <div class="card-body">
                        <center><img width="250" src="/assets/images/logo1.jpg"></center>
                        <div class="form-body mt-5">
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

                            <h4 class="text-center">Dados da Sua Empresa</h4>
                            {!!Form::open()
                            ->post()
                            ->route('cadastro.storeEmpresa')
                            !!}
                            <input type="hidden" name="contador" value="1">

                            <div class="row mt-4">
                                <div class="col-10">
                                    {!!Form::tel('cpf_cnpj', 'CPF / CNPJ')->attrs(['class' => 'cpf_cnpj'])
                                    !!}
                                </div>
                                <div class="col-2">
                                    <br>
                                    <button class="btn btn-dark btn-block w-100" type="button" id="btn-consulta">
                                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span><i class="bx bx-search"></i>
                                    </button>
                                </div>
                                <div class="col-8 mt-3">
                                    {!!Form::text('razao_social', 'Razão Social')
                                    ->attrs(['class' => ''])
                                    !!}
                                </div>
                                <div class="col-4 mt-3">
                                    {!!Form::tel('telefone', 'Telefone')
                                    ->attrs(['class' => 'fone'])
                                    !!}
                                </div>
                                <div class="col-12 mt-3">
                                    {!!Form::select('cidade_id', 'Cidade')
                                    ->attrs(['class' => 'select2'])
                                    !!}
                                </div>
                                <div class="col-12 mt-3">
                                    {!!Form::text('email', 'E-mail')->type('e-mail')
                                    !!}
                                </div>
                                <div class="col-8 mt-3">
                                    {!!Form::text('login', 'Login')
                                    !!}
                                </div>
                                <div class="col-4 mt-3">
                                    <label for="" class="form-label">Senha</label>
                                    <div class="input-group" id="show_hide_password" style="margin-top: -8px">
                                        <input type="password" class="form-control border-end-0  @if($errors->has('senha')) is-invalid @endif" id="senha" name="senha"> <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                                        @if($errors->has('senha'))
                                        <div class="invalid-feedback">
                                            {{ $errors->first('senha') }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="row mt-5">
                                    <div class="col-4">
                                        <button type="submit" class="btn btn-primary px-5">Cadastrar</button>
                                    </div>
                                    <div class="col-6">
                                        <a href="/login" class="btn btn-info px-5" style="text-align: center">Tela de Login</a>
                                    </div>
                                </div>
                            </div>
                            {!!Form::close()!!}
                        </div>
                    </div>
                </div>
                <div class="col-5" style="background-color: #FFF;">
                    <img src="/assets/images/img3.png" class="card" style="height: 630px; width: 637px; position: absolute" alt="">
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        let prot = window.location.protocol;
        let host = window.location.host;
        const path_url = prot + "//" + host + "/";

    </script>
    <script src="/assets/js/jquery.min.js"></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js'></script>
    <script type="text/javascript" src="/js/jquery.mask.min.js"></script>
    <script src="/assets/js/select2.min.js"></script>
    <script src="/assets/js/app.js"></script>
    <script src="/js/theme.js"></script>

    <script src="/js/cadastroEmpresa.js"></script>
    {{-- <script src="/js/main.js"></script> --}}

</body>
</html>
