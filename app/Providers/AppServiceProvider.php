<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Usuario;
use App\Models\VideoAjuda;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        view()->composer('*', function ($view) {

            $ultimoAcesso = null;

            if (!get_id_user()) {
                return redirect()->route('logoff');
            }

            $user = Usuario::find(get_id_user());

            if ($user) {
                $theme = $user->theme;
                $casasDecimais = 2;
                $colorDefault = '';

                if ($theme) {
                    if ($theme->cabecalho == 'headercolor1') {
                        $colorDefault = '#0727D7';
                    }
                    if ($theme->cabecalho == 'headercolor2') {
                        $colorDefault = '#23282C';
                    }
                    if ($theme->cabecalho == 'headercolor3') {
                        $colorDefault = '#E10A1F';
                    }
                    if ($theme->cabecalho == 'headercolor4') {
                        $colorDefault = '#157D4C';
                    }
                    if ($theme->cabecalho == 'headercolor5') {
                        $colorDefault = '#673AB7';
                    }
                    if ($theme->cabecalho == 'headercolor6') {
                        $colorDefault = '#795548';
                    }
                    if ($theme->cabecalho == 'headercolor7') {
                        $colorDefault = '#D3094E';
                    }
                    if ($theme->cabecalho == 'headercolor8') {
                        $colorDefault = '#FF9800';
                    }
                }

                $video_url = $this->getVideoUrl();

                $ultimoAcesso = null;

                $usuario = Usuario::find(get_id_user());

                if($usuario){
                    $ultimoAcesso = $usuario->ultimoAcesso();
                }

                $rotaAtiva = $this->rotaAtiva();

                $view->with('casasDecimais', $casasDecimais);
                $view->with('user', $user);
                $view->with('ultimoAcesso', $ultimoAcesso);
                $view->with('colorDefault', $colorDefault);
                $view->with('theme', $theme);
                $view->with('rotaAtiva', $rotaAtiva);
                $view->with('video_url', $video_url);
                $view->with('audio', $user->aviso_sonoro);
            }
        });
    }

    private function getVideoUrl(){
        if (url()->full()){ 
            $url = url()->full();
            try{
                $video = VideoAjuda::where('url_sistema', $url)->first();
                if($video == null) return "";
                return $video->url_video;
            }catch(\Exception $e){
                return "";
            }
        }
    }


private function rotaAtiva(){
    if (isset($_SERVER['REQUEST_URI'])){ 
        $uri = $_SERVER['REQUEST_URI'];
        $uri = explode("/", $uri);
        $uri = $uri[1];

        $rotaSuper = [
            'empresas', 'planos', 'ibpt', 'contrato', 'financeiro', 'cidades', 'representantes',
            'online', 'etiquetas', 'relatorioSuper', 'ticketsSuper', 'cidadeDelivery', 
            'categoriaMasterDelivery', 'produtosDestaque', 'planosPendentes', 'pesquisa', 'alertas', 
            'errosLog', 'config', 'appUpdate'
        ];

        $rotaDeCadastros = [
            'categorias', 'produtos', 'clientes', 'fornecedores', 'transportadoras', 'categoria-servico', 'servicos', 
            'categoriasConta', 'veiculos', 'usuarios', 'marcas', 'contaBancaria', 'acessores', 'gruposCliente',
            'listaDePrecos', 'formasPagamento'
        ];

        $rotaDeEntradas = [
            'compraFiscal', 'compraManual', 'compras', 'cotacao', 'dfe', 'devolucao'
        ];

        $rotaDeGestaoPessoal = [
            'funcionarios', 'eventosFuncionario', 'funcionarioEventos', 'apuracaoMensal'
        ];

        $rotaDeEstoque = [
            'estoque', 'inventario', 'transferencia'
        ];

        $rotaFinanceiro = [
            'conta-pagar', 'conta-receber', 'fluxoCaixa', 'graficos'
        ];

        $rotaConfig = [
            'configNF', 'escritorio', 'naturezas', 'tributos', 'enviarXml', 'tickets', 'configEmail',
            'filial'
        ];

        $rotaPedidos = [
            'pedidos', 'deliveryComplemento', 'telasPedido', 'controleCozinha', 'mesas'
        ];

        $rotaVenda = [
            'caixa', 'vendas', 'frenteCaixa', 'orcamentoVenda', 'ordemServico', 'vendasEmCredito', 'agendamentos', 'trocas', 'nfse', 'nferemessa'
        ];

        $rotaCTe = [
            'cte', 'categoriaDespesa'
        ];

        $rotaCTeOs = [
            'cteos'
        ];

        $rotaMDFe = [
            'mdfe'
        ];

        $rotaEvento = [
            'eventos'
        ];

        $rotaLocacao = [
            'locacao'
        ];

        $rotaRelatorio = [
            'relatorios',
            'dre'
        ];

        $rotaEcommerce = [
            'categoriaEcommerce', 'produtoEcommerce', 'configEcommerce', 
            'carrosselEcommerce', 'pedidosEcommerce', 'autorPost', 'categoriaPosts',
            'postBlog', 'contatoEcommerce', 'clienteEcommerce', 'informativoEcommerce', 
            'cuponsEcommerce'
        ];

        $rotaNuvemShop = [
            'nuvemshop', 'nuvemshop-pedidos', 'nuvemshop-produtos', 'nuvemshop-clientes'
        ];

        $rotaIfood = [
            'ifood'
        ];

        $rotaDelivery = [
            'deliveryCategoria', 'configDelivery', 'deliveryProduto', 'deliveryComplemento', 
            'funcionamentoDelivery', 'push', 'tamanhosPizza', 'clientesDelivery', 'categoriaDeLoja',
            'pedidosDelivery', 'bairrosDeliveryLoja', 'codigoDesconto', 'carrosselDelivery',
            'motoboys', 'pedidosMesa', 'mesas'
        ];

        if(in_array($uri, $rotaSuper)) return 'SUPER';
        if(in_array($uri, $rotaDeCadastros)) return 'Cadastros';
        if(in_array($uri, $rotaDeEntradas)) return 'Entradas';
        if(in_array($uri, $rotaDeGestaoPessoal)) return 'Gestão Pessoal';
        if(in_array($uri, $rotaDeEstoque)) return 'Estoque';
        if(in_array($uri, $rotaFinanceiro)) return 'Financeiro';
        if(in_array($uri, $rotaConfig)) return 'Configurações';
        if(in_array($uri, $rotaVenda)) return 'Vendas';
        if(in_array($uri, $rotaCTe)) return 'CTe';
        if(in_array($uri, $rotaCTeOs)) return 'CTe Os';
        if(in_array($uri, $rotaMDFe)) return 'MDFe';
        if(in_array($uri, $rotaEvento)) return 'Eventos';
        if(in_array($uri, $rotaRelatorio)) return 'Relatórios';
        if(in_array($uri, $rotaLocacao)) return 'Locação';
        if(in_array($uri, $rotaPedidos)) return 'Pedidos';
        if(in_array($uri, $rotaEcommerce)) return 'Ecommerce';
        if(in_array($uri, $rotaNuvemShop)) return 'Nuvem Shop';
        if(in_array($uri, $rotaIfood)) return 'iFood';
        if(in_array($uri, $rotaDelivery)) return 'Delivery';

    }else{
        return "";
    }
}
}
