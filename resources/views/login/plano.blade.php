<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8" />
    <title>Selecione o plano</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->
    <!--plugins-->
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
</head>

<!-- end::Head -->

<!-- begin::Body -->

<body>
    <div class="col-12 mr-3 ml-3">
        <div class="card">
            <div class="card-header mt-3">
                <h2 class="text-center">{!! env("TITULOPLANO") !!}</h2>
                @if(env("MENSAGEMPLANO"))
                <h4>{!! env("MENSAGEMPLANO") !!}</h4>
                @endif
                @if(env("PLANOAUTOMATICODIAS") > 0)
                <p class="text-danger">*Faça seu cadastro, e utilize grátis por {{env("PLANOAUTOMATICODIAS")}} dia(s)</p>
                @endif
            </div>

            <div class="row">
                @foreach($planos as $p)
                <div class="{{App\Models\Plano::divPlanos()}}">
                    <div class="row m-3">
                        <div class="card radius-15">
                            <div class="text-center mt-3">
                                <div class="p-4 border radius-15">
                                    @if($p->img)
                                    <img src="/uploads/planos/{{ $p->img }}" width="120" height="120" class="rounded-circle shadow" alt="">
                                    @else
                                    <img src="/imgs_planos/sem_imagem.png" width="120" height="120" class="rounded-circle shadow" alt="">
                                    @endif
                                    <div class="ms-auto">
                                        <a href="#" class="mb-1">
                                            <h5 class="mb-0 mt-3">{{$p->nome}}</h5>
                                        </a>
                                    </div>
                                </div>
                                <h2 class="mt-4"><strong style="color: rgb(27, 143, 4)">R$ {{number_format($p->valor, 2, ',', '.')}}</strong> </h2>
                            </div>
                            <p class="text-dark-75 font-weight-nirmal font-size-lg m-0 pb-7 m-3">
                                {!! $p->descricao !!}
                            </p>
                            <div class="m-3" id="" data-toggle="tooltip" title="">
                                <a class="btn btn-primary font-weight-bolder font-size-sm py-3 px-14 w-100" href="/cadastro?plano={{$p->id}}">Escolher</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
    <script src="/assets/js/bootstrap.bundle.min.js"></script>
    <!--plugins-->
    <script src="/assets/js/jquery.min.js"></script>
    <script type="text/javascript" src="/js/jquery.mask.min.js"></script>
    <script src="/js/theme.js"></script>
</body>
<!-- end::Body -->
</html>
