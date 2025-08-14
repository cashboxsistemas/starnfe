<!doctype html>
    <html lang="pt-BR" class="{{ $theme != null ? $theme->tema : '' }} {{ $theme != null ? $theme->cabecalho : '' }} {{ $theme != null ? 'color-sidebar ' . $theme->plano_fundo : '' }}">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!--favicon-->

        <!--plugins-->
        <link href="/assets/css/simplebar.css" rel="stylesheet" />
        <link href="/assets/css/perfect-scrollbar.css" rel="stylesheet" />
        <link href="/assets/css/tagsinput.css" rel="stylesheet" />
        <link href="/assets/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet" />
        <link href="/assets/css/highcharts.css" rel="stylesheet" />
        <!-- loader-->
        <link href="/assets/css/metisMenu.min.css" rel="stylesheet" />
        <link href="assets/css/pace.min.css" rel="stylesheet" />
        <script src="assets/js/pace.min.js"></script>
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

        <link href="/assets/css/select2.min.css" rel="stylesheet" />
        <link href="/assets/css/select2-bootstrap4.css" rel="stylesheet" />
        <link href="/assets/css/style.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="/assets/css/toastr.min.css">

        <title>Frente de Caixa</title>

        <style type="text/css">
            .mousetrap{
                width: 0;
                border:none;
                outline:none;
                background: #f3f6f9;
                position: relative;
            }

            #mousetrapTitle{
                position: absolute;
                left: 80px;
                color: green;
                background-color: #fff;
                box-shadow: 0 0 5px #fff;
                align-items: center;
                gap: 3px;
            }

            .pr-2{
                background:#F3F6F9;
                border: 1px solid #ccc;
                border-radius: 2px;
                height: 35px;
            }


            .mousetrap:focus{
                border:none;outline:none;border-right: 3px solid #000;
                animation: fadebarcode .5s infinite ease-in-out;
            }
        </style>
        @yield('css')
    </head>

    <body>
        <input type="hidden" value="{{session('user_logged')['empresa']}}" id="empresa_id">
        <input type="hidden" value="{{session('user_logged')['id']}}" id="usuario_id">
        @if(isset($config))
        <input type="hidden" id="pass" value="{{ $config->senha_remover }}">
        @endif
        <input type="hidden" value="{{$config->percentual_max_desconto}}" id="percentual_max_desconto">
       
        <!--wrapper-->
        <div class="wrapper">
            
            {!! Form::open()->post()->route('frenteCaixa.store')->id('form-pdv') !!}
            @include('frontBox._forms')
            {!! Form::close() !!}
        </div>

        <div class="page">
            <div class="row">

                @yield('modal')

            </div>
        </div>

        <div class="modal-loading"></div>

        @include('modals.frontBox._fluxo_diario', ['not_submit' => true])
        @include('modals.frontBox._lista_pre_venda', ['not_submit' => true])
        @include('modals.frontBox._suprimento_caixa', ['not_submit' => true])
        @include('modals.frontBox._comanda_pdv', ['not_submit' => true])
        @include('modals.frontBox._sangria_caixa', ['not_submit' => true])
        @include('modals._abrir_caixa')


        <script type="text/javascript">
            var casas_decimais = 2;
            casas_decimais = {{$casasDecimais}}
            let prot = window.location.protocol;
            let host = window.location.host;
            const path_url = prot + "//" + host + "/";
        </script>

        <script>

        </script>
        <script src="/assets/js/bootstrap.bundle.min.js"></script>
        <!--plugins-->
        <script src="/assets/js/jquery.min.js"></script>
        {{-- <script src="/assets/js/simplebar.min.js"></script>
        <script src="/assets/js/metisMenu.min.js"></script> --}}
        {{-- <script src="/assets/js/perfect-scrollbar.js"></script> --}}
        {{-- <script src="/assets/vectormap/jquery-jvectormap-2.0.2.min.js"></script>
        <script src="/assets/vectormap/jquery-jvectormap-world-mill-en.js"></script> --}}
        {{-- <script src="/assets/js/highcharts.js"></script>
        <script src="/assets/js/exporting.js"></script>
        <script src="/assets/js/variable-pie.js"></script>
        <!-- <script src="/assets/js/export-data.js"></script> -->
        <script src="/assets/js/accessibility.js"></script>
        <script src="/assets/js/apexcharts.min.js"></script> --}}
        <!-- <script src="/assets/js/index2.js"></script> -->
        <script src='https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js'></script>
        <script type="text/javascript" src="/js/jquery.mask.min.js"></script>
        <script src="/assets/js/select2.min.js"></script>
        {{-- <script src="/assets/js/app.js"></script> --}}
        <script src="/js/main.js"></script>
        <script src="/js/frontBox.js"></script>
        <script src="/js/theme.js"></script>
        <script src="/assets/js/toastr.min.js"></script>

        <script type="text/javascript">
            toastr.options = {
                "progressBar": true, 
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "10000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            }

            @if(session()->has('flash_sucesso'))
            toastr.success('{{ session()->get('flash_sucesso') }}');
            @endif

            @if(session()->has('flash_erro'))
            toastr.error('{{ session()->get('flash_erro') }}');
            @endif

            @if(session()->has('flash_warning'))
            toastr.warning('{{ session()->get('flash_warning') }}');
            @endif

        </script>

        @yield('js')
    </body>
    </html>
