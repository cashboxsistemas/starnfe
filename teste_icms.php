<?php

// Carregar autoloader do Composer
require_once 'vendor/autoload.php';

// Carregar aplicação Laravel
$app = require_once 'bootstrap/app.php';

// Inicializar kernel para ter acesso aos models
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\NFeRemessaService;
use App\Models\RemessaNfe;
use App\Models\ConfigNota;
use Illuminate\Support\Facades\Log;

// Configurar logs detalhados
Log::info('=== INICIANDO TESTE ICMS ===');

try {
    // Buscar uma venda para teste (primeira venda disponível)
    $venda = RemessaNfe::with(['cliente', 'itens', 'itens.produto'])
        ->where('estado_emissao', 'novo')
        ->orWhere('estado_emissao', 'rejeitado')
        ->first();
    
    if (!$venda) {
        echo "Nenhuma venda encontrada para teste\n";
        exit(1);
    }
    
    echo "Venda encontrada: ID {$venda->id}\n";
    echo "Cliente: " . ($venda->cliente ? $venda->cliente->razao_social : 'N/A') . "\n";
    echo "Qtd itens: " . count($venda->itens) . "\n";
    
    // Buscar configuração da empresa
    $config = ConfigNota::where('empresa_id', $venda->empresa_id)->first();
    
    if (!$config) {
        echo "Configuração da empresa não encontrada\n";
        exit(1);
    }
    
    echo "Empresa: {$config->razao_social}\n";
    
    // Criar service NFe
    $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
    $nfe_service = new NFeRemessaService([
        "atualizacao" => date('Y-m-d h:i:s'),
        "tpAmb" => (int)$config->ambiente,
        "razaosocial" => $config->razao_social,
        "siglaUF" => $config->cidade->uf,
        "cnpj" => $cnpj,
        "schemes" => "PL_009_V4",
        "versao" => "4.00",
        "tokenIBPT" => "AAAAAAA",
        "CSC" => $config->csc,
        "CSCid" => $config->csc_id
    ], $config);
    
    echo "Service NFe criado\n";
    echo "Ambiente: " . ($config->ambiente == 1 ? 'Produção' : 'Homologação') . "\n";
    
    // Tentar gerar NFe
    echo "\n=== GERANDO NFE ===\n";
    $resultado = $nfe_service->gerarNFe($venda);
    
    if (isset($resultado['erros_xml'])) {
        echo "ERRO na geração:\n";
        echo "Erros XML: " . json_encode($resultado['erros_xml'], JSON_PRETTY_PRINT) . "\n";
        
        if (isset($resultado['erro_exception'])) {
            echo "Exceção: " . $resultado['erro_exception'] . "\n";
        }
        
        if (isset($resultado['erro_completo'])) {
            echo "Erro completo: " . json_encode($resultado['erro_completo'], JSON_PRETTY_PRINT) . "\n";
        }
    } else {
        echo "SUCESSO na geração!\n";
        echo "Chave: " . $resultado['chave'] . "\n";
        echo "Número NFe: " . $resultado['nNf'] . "\n";
        echo "Tamanho XML: " . strlen($resultado['xml']) . " bytes\n";
        
        // Verificar se há elementos ICMS vazios
        if (strpos($resultado['xml'], '<ICMS></ICMS>') !== false) {
            echo "ATENÇÃO: Elemento <ICMS></ICMS> vazio encontrado!\n";
        } elseif (strpos($resultado['xml'], '<ICMS/>') !== false) {
            echo "ATENÇÃO: Elemento <ICMS/> auto-fechado encontrado!\n";
        } else {
            echo "OK: Elementos ICMS parecem estar preenchidos corretamente\n";
        }
    }
    
} catch (Exception $e) {
    echo "EXCEÇÃO CAPTURADA:\n";
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . " - Linha: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== FIM DO TESTE ===\n";
