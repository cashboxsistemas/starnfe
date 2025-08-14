<!doctype html>
    <html lang="pt-BR" class="{{ $theme != null ? $theme->tema : ''}} {{ $theme != null ? $theme->cabecalho : ''}} {{ $theme != null ? 'color-sidebar ' . $theme->plano_fundo : ''}}">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!--favicon-->

        <link href="/assets/css/simplebar.css" rel="stylesheet" />
        <link href="/assets/css/tagsinput.css" rel="stylesheet" />
        <link href="/assets/css/perfect-scrollbar.css" rel="stylesheet" />
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
        <link href="/assets/css/select2.min.css" rel="stylesheet" />
        <link href="/assets/css/select2-bootstrap4.css" rel="stylesheet" />
        <link href="/assets/css/style.css" rel="stylesheet" />
        <link rel="stylesheet" type="text/css" href="/assets/css/toastr.min.css">

        <title>{{$title}}</title>

        @yield('css')

        @if($colorDefault != '')
        <style type="text/css">
            :root {
                --color-default: {{$colorDefault}};
            }
            
        </style>
        <link href="/assets/css/extend.css" rel="stylesheet" />
        @endif
    </head>

    <body>
        <div class="wrapper">
            <!--sidebar wrapper -->
            <div class="sidebar-wrapper" data-simplebar="true">
                <div class="sidebar-header">
                    <div>
                        <a href="{{ route('graficos.index') }}">
                            <img src="/logos/default.png" class="logo-icon" alt="logo icon">
                        </a>
                    </div>
                    <div class="toggle-icon ms-auto"><i class='bx bx-first-page'></i>
                    </div>
                </div>
                <!--navigation-->
                <ul class="metismenu" id="menu">
                    @if(isSuper(session('user_logged')['super']))
                    <li>
                        <a href="javascript:;" class="has-arrow bg-dark">
                            <div class="parent-icon"><i class='bx bx-star'></i>
                            </div>
                            <div class="menu-title text-white">Super</div>
                        </a>
                        <ul>
                            <li><a href="/empresas"><i class="bx bx-right-arrow-alt"></i>Empresas</a></li>
                            <li><a href="/planos"><i class="bx bx-right-arrow-alt"></i>Planos</a></li>
                            <li><a href="/ibpt"><i class="bx bx-right-arrow-alt"></i>IBPT</a></li>
                            <li><a href="/cidades"><i class="bx bx-right-arrow-alt"></i>Cidades</a></li>
                            <li><a href="/representantes"><i class="bx bx-right-arrow-alt"></i>Representantes</a></li>
                            <li><a href="/etiquetas"><i class="bx bx-right-arrow-alt"></i>Etiquetas</a></li>
                            <li><a href="/relatorioSuper"><i class="bx bx-right-arrow-alt"></i>Relatórios</a></li>
                            <li><a href="/ticketsSuper"><i class="bx bx-right-arrow-alt"></i>Tickets</a></li>
                            <li><a href="/contadores"><i class="bx bx-right-arrow-alt"></i>Contador</a></li>
                            <li><a href="/planosPendentes"><i class="bx bx-right-arrow-alt"></i>Planos pendentes</a></li>
                            <li><a href="/pesquisa"><i class="bx bx-right-arrow-alt"></i>Pesquisa de satisfação</a></li>
                            <li><a href="/alertas"><i class="bx bx-right-arrow-alt"></i>Alertas para empresa</a></li>
                            <li><a href="/errosLog"><i class="bx bx-right-arrow-alt"></i>Erros do sistema</a></li>
                            <li><a href="/videos"><i class="bx bx-right-arrow-alt"></i>Vídeos do sistema</a></li>
                        </ul>
                    </li>
                    @endif

                    @include('default/menu')

                </ul>
                
            </div>
            <!--end sidebar wrapper -->
            <!--start header -->
            <header>
                <div class="topbar d-flex align-items-center">
                    <nav class="navbar navbar-expand">
                        <div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
                        </div>

                        @if(!session('user_contador'))
                        <div class="top-menu-left d-none d-lg-block">
                            <ul class="nav">
                                <li class="nav-item">
                                    <a href="{{ route('configNF.index')}}" class="btn btn-dark position-relative me-lg-2 btn-sm"> <i class="bx bx-certification align-middle"></i> Ambiente: {{session('user_logged')['ambiente']}} </span></span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="top-menu">
                            <a class="btn btn-primary btn-sm px-3" href="{{ route('frenteCaixa.index') }}"><i class="bx bx-cart"> </i> PDV</a>

                            @if($ultimoAcesso != null)
                            <button type="button" class="btn btn-light btn-sm float-right btn-ip">Endereço do IP: <span class="badge bg-secondary">{{ $ultimoAcesso->ip_address }}</span>
                            </button>
                            @endif
                        </div>
                        @endif

                        

                        @if(session('user_contador'))
                        <div class="top-menu">
                            <a data-bs-toggle="modal" href="#!" data-bs-target="#modal-empresa_contador" class="btn btn-success">
                                Empresa selecionada: {{ session('empresa_selecionada') ? session('empresa_selecionada')['nome'] : ' -- '  }}
                            </a>
                        </div>
                        @endif

                        <div class="search-bar flex-grow-1">
                            <div class="position-relative search-bar-box">
                                <input type="text" class="form-control search-control" placeholder="Pesquise no sistema"> <span class="position-absolute top-50 search-show translate-middle-y"><i class='bx bx-search'></i></span>
                                <span class="position-absolute top-50 search-close translate-middle-y"><i class='bx bx-x'></i></span>
                            </div>
                        </div>
                        <div class="top-menu ms-auto">
                            <ul class="navbar-nav align-items-center">
                                <!-- <li class="nav-item mobile-search-icon">
                                    <a class="nav-link" href="javascript:;"> <i class='bx bx-search'></i>
                                    </a>
                                </li> -->

                                @if($video_url != null)

                                <a style="width: 100%" target="_blank" href="{{$video_url}}" class="btn btn-sm btn-info">
                                    <i class="bx bx-video"></i>
                                    Video Ajuda
                                </a>

                                @endif
                                <li class="nav-item dropdown dropdown-large">
                                    <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> <span class="alert-count"></span>
                                        <i class='bx bx-bell'></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:;">
                                            <div class="msg-header">
                                                <p class="msg-header-title">Notificações</p>
                                            </div>
                                        </a>
                                        <div class="header-notifications-list">

                                        </div>

                                    </div>
                                </li>
                                <li class="nav-item dropdown dropdown-large">
                                    <!-- nao remover -->
                                    <div class="dropdown-menu">
                                        <div class="header-message-list">
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="user-box dropdown">
                            <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                @if(session('user_logged')['img'] == '')
                                <img src="/logos/user.png" class="user-img" alt="foto usuário">
                                @else
                                <img src="/logos/user.png" class="user-img" alt="foto usuário">
                                @endif
                                <div class="user-info ps-3">
                                    <p class="user-name mb-0">{{session('user_logged')['nome']}}</p>
                                    <p class="designattion mb-0">{{session('user_logged')['empresa_nome']}}</p>
                                </div>
                                <input type="hidden" value="{{session('user_logged')['empresa']}}" id="empresa_id">
                                <input type="hidden" value="{{session('user_logged')['id']}}" id="usuario_id">

                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                            <!--  <li><a class="dropdown-item" href="javascript:;"><i class="bx bx-user"></i><span>Profile</span></a>
                            </li>
                            <li><a class="dropdown-item" href="javascript:;"><i class="bx bx-cog"></i><span>Settings</span></a>
                            </li>
                        -->
                        <li><a class="dropdown-item" href="/login/logoff"><i class='bx bx-log-out-circle'></i><span>Sair</span></a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </header>
</div>

<div class="page-wrapper">
    <div class="row">


        @yield('content')

    </div>
</div>

@if(!isset($not_loading))
<div class="modal-loading loading-class"></div>
@endif

<div class="overlay toggle-icon"></div>
<a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
<footer class="page-footer">
    <p class="mb-0">
        Copyright © {{date('Y')}}. todos os direitos reservados {{env("APP_NAME")}}.
        @if($ultimoAcesso != null)
        <strong class="text-primary">Último acesso: {{ \Carbon\Carbon::parse($ultimoAcesso->created_at)->format('d/m/Y H:i:s') }}</strong>
        @endif
    </p>
</footer>
</div>

<div class="switcher-wrapper">
    <div class="switcher-btn"> <i class='bx bx-cog bx-spin'></i>
    </div>
    <div class="switcher-body">
        <div class="d-flex align-items-center">
            <h5 class="mb-0 text-">Customização do Sistema</h5>
            <button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
        </div>
        <hr />
        <h6 class="mb-0">Temas</h6>
        <hr />
        <div class="d-flex align-items-center justify-content-between">
            <div class="form-check">
                <input class="form-check-input click-theme" type="radio" value="light-theme" name="flexRadioDefault" id="lightmode" @isset($theme->tema) @if($theme->tema == 'light-theme') checked @endif @endif>
                <label class="form-check-label" for="lightmode">Claro</label>
            </div>
            <div class="form-check">
                <input class="form-check-input click-theme" value="dark-theme" type="radio" name="flexRadioDefault" id="darkmode" @isset($theme->tema) @if($theme->tema == 'dark-theme') checked @endif @endif>
                <label class="form-check-label" for="darkmode">Escuro</label>
            </div>
            <div class="form-check">
                <input class="form-check-input click-theme" type="radio" value="semi-dark" name="flexRadioDefault" id="semidark" @isset($theme->tema) @if($theme->tema == 'semi-dark') checked @endif @endif>
                <label class="form-check-label" for="semidark">Semi Escuro</label>
            </div>
        </div>
        <hr />
        <div class="form-check">
            <input class="form-check-input click-theme" type="radio" id="minimal-theme" value="minimaltheme" name="flexRadioDefault" @isset($theme->tema) @if($theme->tema == 'minimaltheme') checked @endif @endif>
            <label class="form-check-label" for="minimaltheme">Tema básico</label>
        </div>
        <hr />
        <h6 class="mb-0">Cores do Cabeçalho</h6>
        <hr />
        <div class="header-colors-indigators">
            <div class="row row-cols-auto g-3">
                <div class="col">
                    <div class="indigator headercolor1" onclick="setHeaderColor('headercolor1')" id="headercolor1"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor2" onclick="setHeaderColor('headercolor2')" id="headercolor2"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor3" onclick="setHeaderColor('headercolor3')" id="headercolor3"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor4" onclick="setHeaderColor('headercolor4')" id="headercolor4"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor5" onclick="setHeaderColor('headercolor5')" id="headercolor5"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor6" onclick="setHeaderColor('headercolor6')" id="headercolor6"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor7" onclick="setHeaderColor('headercolor7')" id="headercolor7"></div>
                </div>
                <div class="col">
                    <div class="indigator headercolor8" onclick="setHeaderColor('headercolor8')" id="headercolor8"></div>
                </div>
            </div>
        </div>
        <hr />
        <h6 class="mb-0">Planos de fundo da barra lateral</h6>
        <hr />
        <div class="header-colors-indigators">
            <div class="row row-cols-auto g-3">
                <div class="col">
                    <div class="indigator sidebarcolor1" onclick="setSidebar('sidebarcolor1')" id="sidebarcolor1"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor2" onclick="setSidebar('sidebarcolor2')" id="sidebarcolor2"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor3" onclick="setSidebar('sidebarcolor3')" id="sidebarcolor3"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor4" onclick="setSidebar('sidebarcolor4')" id="sidebarcolor4"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor5" onclick="setSidebar('sidebarcolor5')" id="sidebarcolor5"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor6" onclick="setSidebar('sidebarcolor6')" id="sidebarcolor6"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor7" onclick="setSidebar('sidebarcolor7')" id="sidebarcolor7"></div>
                </div>
                <div class="col">
                    <div class="indigator sidebarcolor8" onclick="setSidebar('sidebarcolor8')" id="sidebarcolor8"></div>
                </div>
            </div>
        </div>
        <hr>
        <div class="header-colors-indigators">
            <div class="row row-cols-auto g-3">
                @if($audio == 0)
                <div class="col indigator text-info m-2">
                    <i class="bx bx-bell font-30 aviso-on" onclick="avisoSonoro('1')"></i>
                </div>
                @else
                <div class="col indigator text-info m-2">
                    <i class="bx bx-bell-off font-30 aviso-off" onclick="avisoSonoro('0')"></i>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var casas_decimais = 2;
    casas_decimais = {{$casasDecimais}}
    let prot = window.location.protocol;
    let host = window.location.host;
    const path_url = prot + "//" + host + "/";
    const hash = '{{session('user_logged')['hash_empresa']}}';

</script>

<script src="/assets/js/bootstrap.bundle.min.js"></script>
<!--plugins-->
<script src="/assets/js/jquery.min.js"></script>
<script src="/assets/js/simplebar.min.js"></script>
<script src="/assets/js/metisMenu.min.js"></script>
<script src="/assets/js/perfect-scrollbar.js"></script>
<script src="/assets/vectormap/jquery-jvectormap-2.0.2.min.js"></script>
<script src="/assets/vectormap/jquery-jvectormap-world-mill-en.js"></script>
<script src="/assets/js/toastr.min.js"></script>

<script src="/assets/js/highcharts.js"></script>
<script src="/assets/js/exporting.js"></script>
<script src="/assets/js/variable-pie.js"></script>
<!-- <script src="/assets/js/export-data.js"></script> -->
<script src="/assets/js/accessibility.js"></script>
<script src="/assets/js/apexcharts.min.js"></script>
<!-- <script src="/assets/js/index2.js"></script> -->
<script src='https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js'></script>
<script type="text/javascript" src="/js/jquery.mask.min.js"></script>
<script src="/assets/js/select2.min.js"></script>
<script src="/assets/js/app.js"></script>
<script src="/js/main.js"></script>
<script src="/js/theme.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/35.1.0/classic/ckeditor.js"></script>
<script type="text/javascript" src="/js/noticacao.js"></script>

@yield('js')

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

@if(session('user_contador'))
@include('modals._empresa_contador', ['not_submit' => true])
@endif

@if($audio == 1)
@if(session()->has('flash_sucesso'))
<script type="text/javascript">
    var audio = new Audio('/audio/success.mp3');
    audio.addEventListener('canplaythrough', function() {
        audio.play();
    });
</script>
@endif

@if(session()->has('flash_erro'))
<script type="text/javascript">
    var audio = new Audio('/audio/error.mp3');
    audio.addEventListener('canplaythrough', function() {
        audio.play();
    });
</script>
@endif

@if(session()->has('flash_warning'))
<script type="text/javascript">
    var audio = new Audio('/audio/warning.mp3');
    audio.addEventListener('canplaythrough', function() {
        audio.play();
    });
</script>
@endif
@endif

</body>
</html>
