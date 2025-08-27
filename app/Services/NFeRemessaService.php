<?php

namespace App\Services;

use NFePHP\NFe\Make;
use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Common\Standardize;
use App\Models\RemessaNfe;
use App\Models\ConfigNota;
use App\Models\Certificado;
use App\Models\Contigencia;
use NFePHP\NFe\Complements;
use App\Models\Tributacao;
use App\Models\IBPT;
use App\Models\Filial;
use App\Models\NfeRemessa;
use NFePHP\Common\Soap\SoapCurl;
use NFePHP\NFe\Factories\Contingency;


error_reporting(E_ALL);
ini_set('display_errors', 'On');

class NFeRemessaService
{

	private $config;
	private $tools;
	protected $empresa_id = null;

	public function __construct($config, $emitente)
	{
		if ($emitente->arquivo == null) {
			abort(403, "realize o upload do certificado");
		}
		$this->empresa_id = $emitente->empresa_id;
		// dd($config);
		$this->tools = new Tools(json_encode($config), Certificate::readPfx($emitente->arquivo, $emitente->senha));

		$soapCurl = new SoapCurl();
		$soapCurl->httpVersion('1.1');
		$this->tools->loadSoapClass($soapCurl);

		$contigencia = $this->getContigencia();
		if ($contigencia != null) {
			$contingency = new Contingency($contigencia->status_retorno);
			$this->tools->contingency = $contingency;
		}
		$this->config = $config;
		$this->tools->model(55);
	}

	private function getContigencia()
	{
		$active = Contigencia::where('empresa_id', $this->empresa_id)
		->where('status', 1)
		->where('documento', 'NFe')
		->first();
		return $active;
	}

	public function gerarNFe($venda)
	{
		\Log::info('=== INÍCIO GERAÇÃO NFE SERVICE ===');
		\Log::info('Venda ID: ' . $venda->id);
		\Log::info('Cliente: ' . ($venda->cliente ? $venda->cliente->razao_social : 'N/A'));
		\Log::info('Tipo pessoa cliente: ' . ($venda->cliente ? $venda->cliente->tipo_pessoa : 'N/A'));

		$config = ConfigNota::where('empresa_id', $this->empresa_id)
			->first(); // iniciando os dados do emitente NF
		
		\Log::info('Config encontrada: ' . ($config ? $config->razao_social : 'N/A'));

			$tributacao = Tributacao::where('empresa_id', $this->empresa_id)
			->first(); // iniciando tributos
		
		\Log::info('Tributação encontrada: ' . ($tributacao ? 'Sim' : 'Não'));

			$nfe = new Make();
			\Log::info('Objeto Make criado');
			
			$stdInNFe = new \stdClass();
			$stdInNFe->versao = '4.00';
			$stdInNFe->Id = null;
			$stdInNFe->pk_nItem = '';

			$infNFe = $nfe->taginfNFe($stdInNFe);
			\Log::info('Tag infNFe criada');

			$vendaLast = $config->ultimo_numero_nfe;
			$lastNumero = $vendaLast;
			$stdIde = new \stdClass();
			$stdIde->cUF = ConfigNota::getCodUF($config->cidade->uf);
			$stdIde->cNF = rand(11111, 99999);
		// $stdIde->natOp = $venda->natureza->natureza;
			$stdIde->natOp = $venda->natureza->natureza;

		// $stdIde->indPag = 1; //NÃO EXISTE MAIS NA VERSÃO 4.00 // forma de pagamento

			$stdIde->mod = 55;
			$stdIde->serie = $config->numero_serie_nfe;
			$stdIde->nNF = (int)$lastNumero + 1;
			$stdIde->dhEmi = date("Y-m-d\TH:i:sP");
			$stdIde->dhSaiEnt = date("Y-m-d\TH:i:sP");
			$stdIde->tpNF = $venda->tipo_nfe == 'entrada' ? 0 : 1;

			if ($venda->cliente->cod_pais == 1058) {
				$stdIde->idDest = $config->cidade->uf != $venda->cliente->cidade->uf ? 2 : 1;
			} else {
				$stdIde->idDest = 3;
			}

			$stdIde->cMunFG = $config->cidade->codigo;
			$stdIde->tpImp = 1;
			$stdIde->tpEmis = 1;
			$stdIde->cDV = 0;
			$stdIde->tpAmb = $config->ambiente;
			$stdIde->finNFe = $venda->natureza->finNFe;
			if ($venda->pedido_nuvemshop_id > 0) {
				$stdIde->indFinal = 1;
			} else {
				$stdIde->indFinal = $venda->cliente->consumidor_final;
			}
			$stdIde->indPres = 1;

			if ($config->ambiente == 2) {
				if ($venda->pedido_ecommerce_id > 0) {
					$stdIde->indIntermed = 1;
				} else {
					$stdIde->indIntermed = 0;
				}
			}
			$stdIde->procEmi = '0';
			$stdIde->verProc = '3.10.31';
		// $stdIde->dhCont = null;
		// $stdIde->xJust = null;

		//
			$tagide = $nfe->tagide($stdIde);

			$stdEmit = new \stdClass();
			$stdEmit->xNome = $config->razao_social;
			$stdEmit->xFant = $config->nome_fantasia;

			$ie = preg_replace('/[^0-9]/', '', $config->ie);

			$stdEmit->IE = $ie;
		// $stdEmit->CRT = $tributacao->regime == 0 ? 1 : 3;
			$stdEmit->CRT = ($tributacao->regime == 0 || $tributacao->regime == 2) ? 1 : 3;

			$cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);

			if (strlen($cnpj) == 14) {
				$stdEmit->CNPJ = $cnpj;
			} else {
				$stdEmit->CPF = $cnpj;
			}
		// $stdEmit->IM = $ie;

			$emit = $nfe->tagemit($stdEmit);

		// ENDERECO EMITENTE
			$stdEnderEmit = new \stdClass();
			$stdEnderEmit->xLgr = $this->retiraAcentos($config->logradouro);
			$stdEnderEmit->nro = $config->numero;
			$stdEnderEmit->xCpl = $this->retiraAcentos($config->complemento);

			$stdEnderEmit->xBairro = $this->retiraAcentos($config->bairro);
			$stdEnderEmit->cMun = $config->cidade->codigo;
			$stdEnderEmit->xMun = $this->retiraAcentos($config->cidade->nome);
			$stdEnderEmit->UF = $config->cidade->uf;

			$telefone = $config->fone;
			if (substr($telefone, 0, 3) == '+55') {
				$telefone = substr($telefone, 3, strlen($telefone));
			}
			$telefone = preg_replace('/[^0-9]/', '', $telefone);

			$stdEnderEmit->fone = $telefone;

			$cep = preg_replace('/[^0-9]/', '', $config->cep);

			$stdEnderEmit->CEP = $cep;
			// $stdEnderEmit->cPais = $config->codPais;
			// $stdEnderEmit->xPais = $config->pais;
			$stdEnderEmit->cPais = '1058';
			$stdEnderEmit->xPais = 'BRASIL';

			$enderEmit = $nfe->tagenderEmit($stdEnderEmit);

		// DESTINATARIO
			$stdDest = new \stdClass();
			$pFisica = false;
			$stdDest->xNome = $this->retiraAcentos($venda->cliente->razao_social);

			if ($venda->cliente->cod_pais != 1058) {
				$stdDest->indIEDest = "9";
				$stdDest->idEstrangeiro = $venda->cliente->id_estrangeiro;
			} else {
				if ($venda->cliente->contribuinte) {
					if ($venda->cliente->ie_rg == 'ISENTO') {
						$stdDest->indIEDest = "2";
					} else {
						$stdDest->indIEDest = "1";
					}
				} else {
					$stdDest->indIEDest = "9";
				}

				$cnpj_cpf = preg_replace('/[^0-9]/', '', $venda->cliente->cpf_cnpj);

				if (strlen($cnpj_cpf) == 14) {
					$stdDest->CNPJ = $cnpj_cpf;
					$ie = preg_replace('/[^0-9]/', '', $venda->cliente->ie_rg);
					$stdDest->IE = $ie;
				} else {
				// $stdDest->CPF = $cnpj_cpf;
					$stdDest->CPF = $cnpj_cpf;
					$ie = preg_replace('/[^0-9]/', '', $venda->cliente->ie_rg);

					if (strtolower($ie) != "isento" && $venda->cliente->contribuinte)
						$stdDest->IE = $ie;
					$pFisica = true;
				}
			}

			$dest = $nfe->tagdest($stdDest);

			$stdEnderDest = new \stdClass();
			$stdEnderDest->xLgr = $this->retiraAcentos($venda->cliente->rua);
			$stdEnderDest->nro = $this->retiraAcentos($venda->cliente->numero);
			$stdEnderDest->xCpl = $this->retiraAcentos($venda->cliente->complemento);
			$stdEnderDest->xBairro = $this->retiraAcentos($venda->cliente->bairro);

			$telefone = $venda->cliente->telefone;
			$telefone = preg_replace('/[^0-9]/', '', $telefone);

			if (substr($telefone, 0, 3) == '+55') {
				$telefone = substr($telefone, 3, strlen($telefone));
			}
			$stdEnderDest->fone = $telefone;

			if ($venda->cliente->cod_pais == 1058) {

				$stdEnderDest->cMun = $venda->cliente->cidade->codigo;
				$stdEnderDest->xMun = strtoupper($this->retiraAcentos($venda->cliente->cidade->nome));
				$stdEnderDest->UF = $venda->cliente->cidade->uf;

				$cep = preg_replace('/[^0-9]/', '', $venda->cliente->cep);

				$stdEnderDest->CEP = $cep;
				$stdEnderDest->cPais = "1058";
				$stdEnderDest->xPais = "BRASIL";
			} else {
				$stdEnderDest->cMun = 9999999;
				$stdEnderDest->xMun = "EXTERIOR";
				$stdEnderDest->UF = "EX";
				$stdEnderDest->cPais = $venda->cliente->cod_pais;
				$stdEnderDest->xPais = $venda->cliente->getPais();
			}

			$enderDest = $nfe->tagenderDest($stdEnderDest);

			$somaProdutos = 0;
			$somaICMS = 0;
			$somaIPI = 0;
		//PRODUTOS
			$itemCont = 0;

			$totalItens = count($venda->itens);
			$somaFrete = 0;
			$somaDesconto = 0;
			$somaAcrescimo = 0;
			$somaISS = 0;
			$somaServico = 0;

			$VBC = 0;
			$somaICMSDeson = 0; // Somar valores de ICMS desonerado
			$somaFederal = 0;
			$somaEstadual = 0;
			$somaMunicipal = 0;

			$p = null;

			$nfesRef = "";
			foreach ($venda->referencias as $r) {
				$chave = str_replace(" ", "", $r->chave);
				$std = new \stdClass();
				$std->refNFe = $chave;
				$nfe->tagrefNFe($std);

				$nfesRef .= " $r->chave ";
			}

			$somaApCredito = 0;

			$obsIbpt = "";
			\Log::info('=== PROCESSANDO ITENS ===');
			\Log::info('Quantidade de itens: ' . count($venda->itens));
			
			foreach ($venda->itens as $i) {
				\Log::info('--- Processando item: ' . $itemCont . ' ---');
				\Log::info('Produto: ' . $i->produto->nome);
				\Log::info('CST/CSOSN: ' . $i->produto->CST_CSOSN);
				\Log::info('=== DADOS ITEM DA TABELA ===');
				\Log::info('Item produto ID: ' . $i->produto_id);
				\Log::info('CST_CSOSN item: ' . $i->cst_csosn);
				\Log::info('perc_icms item: ' . $i->perc_icms);
				\Log::info('valor_icms item: ' . $i->valor_icms);
				\Log::info('vbc_icms item: ' . $i->vbc_icms);
				\Log::info('cBenef produto: ' . $i->produto->cBenef);

				$p = $i;
				$ncm = $i->produto->NCM;
				$ncm = str_replace(".", "", $ncm);

				$ibpt = IBPT::getIBPT($config->cidade->uf, $ncm);
				\Log::info('=== BUSCANDO IBPT ===');
				\Log::info('UF: ' . $config->cidade->uf . ', NCM: ' . $ncm);
				\Log::info('IBPT encontrado: ' . ($ibpt ? 'SIM' : 'NÃO'));
				if ($ibpt) {
					\Log::info('IBPT dados: ' . json_encode($ibpt->toArray()));
				}

				$itemCont++;

				$stdProd = new \stdClass();
				$stdProd->item = $itemCont;

				$cod = $this->validate_EAN13Barcode($i->produto->codBarras);

				$stdProd->cEAN = $cod ? $i->produto->codBarras : 'SEM GTIN';
				$stdProd->cEANTrib = $cod ? $i->produto->codBarras : 'SEM GTIN';
			// $stdProd->cEAN = $i->produto->codBarras;
			// $stdProd->cEANTrib = $i->produto->codBarras;
			// $stdProd->cProd = $i->produto->id;
			// if ($i->produto->referencia != '') {
			// 	$stdProd->cProd = $i->produto->referencia;
			// }

				if($config->cProdTipo == 'referencia' && $i->produto->referencia != ''){
					$stdProd->cProd = $i->produto->referencia;
				}else{
					$stdProd->cProd = $i->produto->id;
				}

				$nomeProduto = $i->produto->nome;
				if ($i->produto->grade) {
					$nomeProduto .= " " . $i->produto->str_grade;
				}

				if ($i->produto->lote) {
					$nomeProduto .= " | LOTE: " . $i->produto->lote;
				}
				if ($i->produto->vencimento) {
					$nomeProduto .= ", VENCIMENTO: " . $i->produto->vencimento;
				}
				$stdProd->xProd = $this->retiraAcentos($nomeProduto);

			// if($i->produto->CST_CSOSN == '500' || $i->produto->CST_CSOSN == '60'){
			// 	$stdProd->cBenef = 'SEM CBENEF';
			// }

				\Log::info('=== DADOS PRODUTO NFE ===');
				\Log::info('Produto ID: ' . $i->produto->id);
				\Log::info('Produto Nome: ' . $i->produto->nome);
				\Log::info('CST_CSOSN: ' . $i->produto->CST_CSOSN);
				\Log::info('cBenef do produto: ' . ($i->produto->cBenef ?? 'NULL'));

				if ($i->produto->cBenef) {
					\Log::info('Aplicando cBenef do produto: ' . $i->produto->cBenef);
					$stdProd->cBenef = $i->produto->cBenef;
				} else {
					\Log::info('cBenef do produto está vazio/null');
				}

				if ($i->produto->perc_iss > 0) {
					$stdProd->NCM = '00';
				} else {
					$stdProd->NCM = $ncm;
				}

				if($stdIde->tpNF == 1){
					if ($venda->natureza->sobrescreve_cfop == 0) {
						$stdProd->CFOP = $config->cidade->uf != $venda->cliente->cidade->uf ?
						$i->produto->CFOP_saida_inter_estadual : $i->produto->CFOP_saida_estadual;
					} else {
						$stdProd->CFOP = $config->cidade->uf != $venda->cliente->cidade->uf ?
						$venda->natureza->CFOP_saida_inter_estadual : $venda->natureza->CFOP_saida_estadual;
					}
				}else{
					$stdProd->CFOP = $config->cidade->uf != $venda->cliente->cidade->uf ?
						$venda->natureza->CFOP_entrada_inter_estadual : $venda->natureza->CFOP_entrada_estadual;
				}
				$stdProd->uCom = $i->produto->unidade_venda;
			// dd($i->quantidade);
				$stdProd->qCom = $i->quantidade;
				$stdProd->vUnCom = $this->format($i->valor_unitario, $config->casas_decimais);
				$stdProd->vProd = $this->format(($i->quantidade * $i->valor_unitario), $config->casas_decimais);

				if ($i->produto->unidade_tributavel == '') {
					$stdProd->uTrib = $i->produto->unidade_venda;
				} else {
					$stdProd->uTrib = $i->produto->unidade_tributavel;
				}
			// dd($i->produto->quantidade_tributavel);
			// $stdProd->qTrib = $i->quantidade;
				if ($i->produto->quantidade_tributavel == 0) {
					$stdProd->qTrib = $i->quantidade;
				} else {
					$stdProd->qTrib = $i->produto->quantidade_tributavel * $i->quantidade * $i->quantidade_dimensao;
				}
				$stdProd->vUnTrib = $this->format($i->valor_unitario, $config->casas_decimais);
				$stdProd->indTot = $i->produto->perc_iss > 0 ? 0 : 1;
				$somaProdutos += $stdProd->vProd;

				$vDesc = 0;
				if ($venda->desconto > 0.01 && $somaDesconto < $venda->desconto) {

					if ($itemCont < sizeof($venda->itens)) {
						$totalVenda = $venda->valor_total;

						$media = (((($stdProd->vProd - $totalVenda) / $totalVenda)) * 100);
						$media = 100 - ($media * -1);

						$tempDesc = ($venda->desconto * $media) / 100;

						if ($tempDesc > 0.01) {
							$somaDesconto += $tempDesc;
							$stdProd->vDesc = $this->format($tempDesc);
						} else {
							$somaDesconto = $venda->desconto;
							$stdProd->vDesc = $this->format($somaDesconto);
						}
					} else {
						if (($venda->desconto - $somaDesconto) > 0.01) {
							$stdProd->vDesc = $this->format($venda->desconto - $somaDesconto, $config->casas_decimais);
						}
					}
				}
				if ($venda->acrescimo > 0.01 && $somaAcrescimo < $venda->acrescimo) {

					if ($itemCont < sizeof($venda->itens)) {
						$totalVenda = $venda->valor_total;

						$media = (((($stdProd->vProd - $totalVenda) / $totalVenda)) * 100);
						$media = 100 - ($media * -1);

						$tempDesc = ($venda->acrescimo * $media) / 100;

						if ($tempDesc > 0.01) {
							$somaAcrescimo += $tempDesc;
							$stdProd->vOutro = $this->format($tempDesc);
						} else {
							$somaAcrescimo = $venda->acrescimo;
							$stdProd->vOutro = $this->format($somaAcrescimo);
						}
					} else {
						if (($venda->acrescimo - $somaAcrescimo) > 0.01) {
							$stdProd->vOutro = $this->format($venda->acrescimo - $somaAcrescimo, $config->casas_decimais);
						}
					}
				}

			// if($venda->frete){
			// 	if($venda->frete->valor > 0){
			// 		$somaFrete += $vFt = $venda->frete->valor/$totalItens;
			// 		$stdProd->vFrete = $this->format($vFt);
			// 	}
			// }
				if ($venda->valor_frete) {
					if ($venda->valor_frete > 0) {
						if ($itemCont < sizeof($venda->itens)) {
							$somaFrete += $vFt =
							$this->format($venda->valor_frete / $totalItens, 2);
							$stdProd->vFrete = $this->format($vFt);
						} else {
							$stdProd->vFrete = $this->format(($venda->valor_frete - $somaFrete), 2);
						}
					}
				}

				$prod = $nfe->tagprod($stdProd);

			//TAG IMPOSTO

				$stdImposto = new \stdClass();
				$stdImposto->item = $itemCont;
				if ($i->produto->perc_iss > 0) {
					$stdImposto->vTotTrib = 0.00;
				}

			// if($ibpt != null){
			// 	// $vProd = $stdProd->vProd;
			// 	// $somaFederal = ($vProd*($ibpt->nacional_federal/100));
			// 	// $somaEstadual += ($vProd*($ibpt->estadual/100));
			// 	// $somaMunicipal += ($vProd*($ibpt->municipal/100));
			// 	// $soma = $somaFederal + $somaEstadual + $somaMunicipal;
			// 	// $stdImposto->vTotTrib = $soma;

			// 	$vProd = $stdProd->vProd;

			// 	$federal = ($vProd*($ibpt->nacional_federal/100));
			// 	$somaFederal += $federal;

			// 	$estadual = ($vProd*($ibpt->estadual/100));
			// 	$somaEstadual += $estadual;

			// 	$municipal = ($vProd*($ibpt->municipal/100));
			// 	$somaMunicipal += $municipal;
			// 	$soma = $federal + $estadual + $municipal;

			// 	$stdImposto->vTotTrib = $soma;
			// }

				if ($i->produto->ibpt) {
					\Log::info('=== PRODUTO TEM IBPT ===');
					\Log::info('Produto: ' . $i->produto->nome);
					\Log::info('IBPT fonte: ' . ($i->produto->ibpt->fonte ?? 'N/A'));
					\Log::info('IBPT versao: ' . ($i->produto->ibpt->versao ?? 'N/A'));
					
					$vProd = $stdProd->vProd;
					$federal = $this->format(($vProd * ($i->produto->ibpt->nacional / 100)), 2);
					$somaFederal += $federal;

					$estadual = $this->format(($vProd * ($i->produto->ibpt->estadual / 100)), 2);
					$somaEstadual += $estadual;

					$municipal = $this->format(($vProd * ($i->produto->ibpt->municipal / 100)), 2);
					$somaMunicipal += $municipal;

					$soma = $federal + $estadual + $municipal;
					$stdImposto->vTotTrib = $soma;

					if (!empty($obsIbpt) && strpos($obsIbpt, $i->produto->ibpt->fonte ?? 'IBPT') === false) {
						$obsIbpt .= " FONTE: " . ($i->produto->ibpt->fonte ?? 'IBPT');
						$obsIbpt .= " VERSAO: " . ($i->produto->ibpt->versao ?? 'N/A');
						$obsIbpt .= " | ";
					} elseif (empty($obsIbpt)) {
						$obsIbpt = " FONTE: " . ($i->produto->ibpt->fonte ?? 'IBPT');
						$obsIbpt .= " VERSAO: " . ($i->produto->ibpt->versao ?? 'N/A');
						$obsIbpt .= " | ";
					}
					\Log::info('obsIbpt atual: ' . $obsIbpt);
				} else {
					if ($ibpt != null) {
						\Log::info('=== USANDO IBPT ALTERNATIVO ===');
						\Log::info('IBPT UF: ' . $config->cidade->uf);
						\Log::info('IBPT versao: ' . ($ibpt->versao ?? 'N/A'));

						$vProd = $stdProd->vProd;

						$federal = $this->format(($vProd * ($ibpt->nacional_federal / 100)), 2);
						$somaFederal += $federal;

						$estadual = $this->format(($vProd * ($ibpt->estadual / 100)), 2);
						$somaEstadual += $estadual;

						$municipal = $this->format(($vProd * ($ibpt->municipal / 100)), 2);
						$somaMunicipal += $municipal;

						$soma = $federal + $estadual + $municipal;
						$stdImposto->vTotTrib = $soma;

						if (!empty($obsIbpt) && strpos($obsIbpt, 'IBPT') === false) {
							$obsIbpt .= " FONTE: IBPT";
							$obsIbpt .= " VERSAO: " . ($ibpt->versao ?? 'N/A');
							$obsIbpt .= " | ";
						} elseif (empty($obsIbpt)) {
							$obsIbpt = " FONTE: IBPT";
							$obsIbpt .= " VERSAO: " . ($ibpt->versao ?? 'N/A');
							$obsIbpt .= " | ";
						}
						\Log::info('obsIbpt atual: ' . $obsIbpt);
					} else {
						\Log::info('=== SEM DADOS IBPT PARA PRODUTO ===');
						\Log::info('Produto: ' . $i->produto->nome);
					}
				}

				$imposto = $nfe->tagimposto($stdImposto);

			// ICMS
				if ($i->produto->perc_iss == 0) {
				// regime normal

					if ($tributacao->regime == 1) {
						\Log::info('=== REGIME NORMAL DETECTADO ===');

					//$venda->produto->CST  CST
						$percentualUf = $i->percentualUf($venda->cliente->cidade->uf);

						$stdICMS = new \stdClass();

						if ($percentualUf == null) {
							$stdICMS->pICMS = $this->format($i->perc_icms);
						} else {
						//aqui se tem percentual do estado do cliente
							$stdICMS->pICMS = $this->format($percentualUf->percentual_icms);
						}

						$stdICMS->item = $itemCont;
						$stdICMS->orig = $i->produto->origem;
						
						// Adicionar cBenef do produto ao stdICMS se presente
						if (!empty($i->produto->cBenef)) {
							$stdICMS->cBenef = $i->produto->cBenef;
							\Log::info('cBenef do produto adicionado ao stdICMS: ' . $i->produto->cBenef);
						} else {
							\Log::info('Produto sem cBenef definido: ' . $i->produto->id);
						}

						// CORREÇÃO: Para regime normal, converter CSOSN para CST equivalente
						$cstOriginal = $i->cst_csosn;
						\Log::info('CST/CSOSN do item na remessa: ' . $cstOriginal);
						
						if ($venda->cliente->consumidor_final) {
							if ($venda->cliente->cod_pais == 1058) {
								if ($config->sobrescrita_csonn_consumidor_final != "") {
									$cstParaUsar = $config->sobrescrita_csonn_consumidor_final;
									\Log::info('Usando sobrescrita consumidor final: ' . $cstParaUsar);
								} else {
									$cstParaUsar = $this->converterCSOSNparaCST($cstOriginal);
									\Log::info('Convertendo CSOSN para CST (consumidor final): ' . $cstOriginal . ' → ' . $cstParaUsar);
								}
							} else {
								$cstParaUsar = $i->produto->CST_CSOSN_EXP;
								\Log::info('Usando CST exportação: ' . $cstParaUsar);
							}
						} else {
							if ($venda->cliente->cod_pais == 1058) {
								$cstParaUsar = $this->converterCSOSNparaCST($cstOriginal);
								\Log::info('Convertendo CSOSN para CST (não consumidor final): ' . $cstOriginal . ' → ' . $cstParaUsar);
							} else {
								$cstParaUsar = $i->produto->CST_CSOSN_EXP;
								\Log::info('Usando CST exportação: ' . $cstParaUsar);
							}
						}
						
						$stdICMS->CST = $cstParaUsar;
						\Log::info('CST final definido: ' . $cstParaUsar);
						$stdICMS->modBC = 0;
						$stdICMS->vProd = $stdProd->vProd; // Adicionar valor do produto para cálculo de desoneração
						
						// Não definir vBC e vICMS aqui - será definido conforme o CST

						if ($i->pRedBC == 0) {
							if ($i->cst_csosn == '500') {
								$stdICMS->pRedBCEfet = 0.00;
								$stdICMS->vBCEfet = 0.00;
								$stdICMS->pICMSEfet = 0.00;
								$stdICMS->vICMSEfet = 0.00;
							} else if ($i->cst_csosn == '60') {
								$stdICMS->vBCSTRet = 0.00;
								$stdICMS->vICMSSTRet = 0.00;
								$stdICMS->vBCSTDest = 0.00;
								$stdICMS->vICMSSTDest = 0.00;
							} else if ($cstParaUsar == '40' || $cstParaUsar == '41' || $cstParaUsar == '50' || $cstParaUsar == '51') {
								$stdICMS->vICMS = 0;
								$stdICMS->vBC = 0;
								// Para CST isentos, NÃO somar nos totalizadores aqui
							} else {
								// Para CST tributados, calcular e somar normalmente
								$stdICMS->vBC = $stdProd->vProd;
								$stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS / 100);
								$VBC += $stdProd->vProd;
								$somaICMS += $stdICMS->vICMS;
							}
						} else {
							// Redução de base de cálculo - mas apenas para CST tributados
							if (!in_array($cstParaUsar, ['40', '41', '50', '51'])) {
								$tempB = 100 - $i->pRedBC;
								$v = $stdProd->vProd * ($tempB / 100);
								
								$VBC += $stdICMS->vBC = number_format($v, 2, '.', '');
								$stdICMS->pICMS = $this->format($i->perc_icms);
								$somaICMS += $stdICMS->vICMS = ($stdProd->vProd * ($tempB / 100)) * ($stdICMS->pICMS / 100);
								$stdICMS->pRedBC = $this->format($i->pRedBC);
							} else {
								// Para CST isentos com redução: manter valores zerados
								$stdICMS->vBC = 0;
								$stdICMS->vICMS = 0;
								$stdICMS->pRedBC = $this->format($i->pRedBC);
							}
						}

						if ($i->cst_csosn == '60') {
							\Log::info('Processando ICMS ST para CST 60');
							$ICMS = $nfe->tagICMSST($stdICMS);
						} else {
							\Log::info('Processando ICMS genérico para CST: ' . $stdICMS->CST);
							\Log::info('=== DADOS ITEM ICMS ===');
							\Log::info('Item produto ID: ' . $i->produto->id);
							\Log::info('CST_CSOSN produto: ' . $i->produto->CST_CSOSN);
							\Log::info('cBenef produto: ' . ($i->produto->cBenef ?? 'NULL'));
							\Log::info('stdICMS CST: ' . $stdICMS->CST);
							
							// Para CSTs isentos/não tributados, preservar alíquota original para cálculo de desoneração
							$cst = $stdICMS->CST;
							\Log::info('CST obtido do stdICMS: ' . $cst);
							\Log::info('cstParaUsar era: ' . $cstParaUsar);
							
							// VERIFICAÇÃO DE SEGURANÇA: Garantir que estamos usando o CST convertido
							if ($cst != $cstParaUsar) {
								\Log::warning('INCONSISTÊNCIA: CST do stdICMS (' . $cst . ') diferente do cstParaUsar (' . $cstParaUsar . '). Corrigindo...');
								$cst = $cstParaUsar;
								$stdICMS->CST = $cstParaUsar;
							}
							
							if (in_array($cst, ['40', '41', '50', '51'])) {
								\Log::info('CST isento detectado, preservando alíquota para desoneração');
								$stdICMS->pICMSOriginal = $stdICMS->pICMS; // Preservar alíquota original
								$stdICMS->vICMS = 0;
								$stdICMS->vBC = 0;
								// NÃO zerar pICMS aqui - será usado no cálculo de desoneração
								
								// Para CSTs que precisam de cBenef, verificar se está presente
								if (in_array($cst, ['40', '41', '50', '51']) && empty($i->produto->cBenef)) {
									\Log::warning('CST ' . $cst . ' requer cBenef mas produto não possui: ' . $i->produto->id);
									\Log::warning('Códigos válidos para SC: SC270001 (Isenção), SC018001 (Livros), SC018002 (Medicamentos)');
								}
							}
							
							\Log::info('Dados ICMS antes do tagICMS: ' . json_encode($stdICMS));
							\Log::info('Estado das variáveis ANTES do processarICMSPorCST: VBC=' . $VBC . ', somaICMS=' . $somaICMS . ', somaICMSDeson=' . $somaICMSDeson);
							
							// Usar método específico baseado no CST mas criar objeto correto
							try {
								// VALIDAÇÃO ANTES DO PROCESSAMENTO
								\Log::info('=== VALIDAÇÃO PRÉ-PROCESSAMENTO ICMS ===');
								\Log::info('CST para usar: ' . $cst);
								\Log::info('stdICMS original: ' . json_encode($stdICMS));
								
								// Validar CST
								if (empty($cst) || !in_array($cst, ['00', '10', '20', '30', '40', '41', '50', '51', '60', '70', '90'])) {
									\Log::error('CST inválido ou vazio: ' . ($cst ?? 'NULL'));
									throw new \Exception('CST inválido para regime normal: ' . ($cst ?? 'NULL'));
								}
								
								// Validar campos obrigatórios básicos
								if (!isset($stdICMS->item)) {
									\Log::error('Campo item não definido no stdICMS');
									throw new \Exception('Campo item obrigatório não definido');
								}
								
								if (!isset($stdICMS->orig)) {
									\Log::error('Campo orig não definido no stdICMS');
									throw new \Exception('Campo origem obrigatório não definido');
								}
								
								// Para CSTs que precisam de cBenef
								if (in_array($cst, ['40', '41', '50']) && empty($i->produto->cBenef)) {
									\Log::warning('CST ' . $cst . ' recomenda cBenef mas produto não possui: ' . $i->produto->id);
									// Não falhar, apenas alertar
								}
								
								$ICMS = $this->processarICMSPorCST($nfe, $stdICMS, $cst);
								
								if ($ICMS === null || $ICMS === false) {
									\Log::error('processarICMSPorCST retornou valor inválido');
									throw new \Exception('Falha ao processar ICMS para CST: ' . $cst);
								}
								
								\Log::info('ICMS processado com sucesso para CST: ' . $cst);
								\Log::info('Estado das variáveis DEPOIS do processarICMSPorCST: VBC=' . $VBC . ', somaICMS=' . $somaICMS . ', somaICMSDeson=' . $somaICMSDeson);
								
								// Para CST isento, somar valores corretos nos totalizadores
								if (in_array($cst, ['40', '41', '50', '51'])) {
									// Para isentos: vBC = 0, mas somar vICMSDeson
									if (isset($stdICMS->vICMSDeson) && $stdICMS->vICMSDeson > 0) {
										$somaICMSDeson += $stdICMS->vICMSDeson;
										\Log::info('Somando ICMS desonerado: ' . $stdICMS->vICMSDeson . ', Total: ' . $somaICMSDeson);
									}
									// NÃO somar na base de cálculo para isentos
								} else {
									// Para tributados: somar normalmente
									$VBC += $stdICMS->vBC ?? 0;
									$somaICMS += $stdICMS->vICMS ?? 0;
									\Log::info('Somando para CST tributado: vBC=' . ($stdICMS->vBC ?? 0) . ', vICMS=' . ($stdICMS->vICMS ?? 0));
								}
							} catch (\Exception $e) {
								\Log::error('Erro no processamento ICMS - CST: ' . $cst . ' - Erro: ' . $e->getMessage());
								\Log::error('Dados ICMS: ' . json_encode($stdICMS));
								throw new \Exception('Erro ao processar ICMS - CST: ' . $cst . ' - ' . $e->getMessage());
							}
						}
					// regime simples
					} else {
					//$venda->produto->CST CSOSN
						$stdICMS = new \stdClass();

						$stdICMS->item = $itemCont;
						$stdICMS->orig = $i->produto->origem;
					// $stdICMS->CSOSN = $i->cst_csosn;
						if ($venda->cliente->consumidor_final) {
							if ($venda->cliente->cod_pais == 1058) {
								if ($config->sobrescrita_csonn_consumidor_final != "") {
									$stdICMS->CSOSN = $config->sobrescrita_csonn_consumidor_final;
								} else {
									$stdICMS->CSOSN = $i->cst_csosn;
								}
							} else {
								$stdICMS->CSOSN = $i->produto->CST_CSOSN_EXP;
							}
						} else {
							if ($venda->cliente->cod_pais == 1058) {
								$stdICMS->CSOSN = $i->cst_csosn;
							} else {
								$stdICMS->CSOSN = $i->produto->CST_CSOSN_EXP;
							}
						}


						if ($i->cst_csosn == '500') {
							$stdICMS->vBCSTRet = 0.00;
							$stdICMS->pST = 0.00;
							$stdICMS->vICMSSTRet = 0.00;
						}
						$stdICMS->modBC = 0;

						$stdICMS->vBC = $stdProd->vProd;
						$stdICMS->pICMS = $this->format($i->perc_icms);
						$stdICMS->vICMS = $stdICMS->vBC * ($stdICMS->pICMS / 100);

						if ($tributacao->perc_ap_cred > 0 && $stdICMS->CSOSN == 101) {
							$stdICMS->pCredSN = $this->format($tributacao->perc_ap_cred);
							$somaApCredito += $stdICMS->vCredICMSSN = $this->format($stdProd->vProd * ($tributacao->perc_ap_cred / 100));
						} else {
							$stdICMS->pCredSN = 0;
							$stdICMS->vCredICMSSN = 0;
						}
						
						// Usar método específico baseado no CSOSN
						$csosn = $stdICMS->CSOSN;
						
						// Para CSOSNs isentos, garantir que os campos sejam zerados
						if (in_array($csosn, ['102', '103', '300', '400'])) {
							$stdICMS->vICMS = 0;
							$stdICMS->vBC = 0;
							$stdICMS->pICMS = 0;
						}
						
						try {
							switch($csosn) {
								case '101':
									$ICMS = $nfe->tagICMSSN101($stdICMS);
									break;
								case '102':
								case '103':
								case '300':
								case '400':
									$ICMS = $nfe->tagICMSSN102($stdICMS);
									break;
								case '201':
									$ICMS = $nfe->tagICMSSN201($stdICMS);
									break;
								case '202':
								case '203':
									$ICMS = $nfe->tagICMSSN202($stdICMS);
									break;
								case '500':
									$ICMS = $nfe->tagICMSSN500($stdICMS);
									break;
								case '900':
									$ICMS = $nfe->tagICMSSN900($stdICMS);
									break;
								default:
									// Se não encontrar CSOSN específico, usar 102 como fallback (isento)
									$stdICMS->vICMS = 0;
									$stdICMS->vBC = 0;
									$stdICMS->pICMS = 0;
									$ICMS = $nfe->tagICMSSN102($stdICMS);
									break;
							}
						} catch (\Exception $e) {
							\Log::error('Erro no tagICMSSN - CSOSN: ' . $csosn . ' - Erro: ' . $e->getMessage());
							throw new \Exception('Erro ao processar ICMS SN - CSOSN: ' . $csosn . ' - ' . $e->getMessage());
						}

						$somaICMS += $stdICMS->vBC * ($stdICMS->pICMS / 100);

						if ($i->perc_icms > 0) {
							$VBC += $stdProd->vProd;
						}
					}
				} else {

					$valorIss = $stdProd->vProd - $vDesc;
					$somaServico += $valorIss;
					$valorIss = $valorIss * ($i->produto->perc_iss / 100);
					$somaISS += $valorIss;


					$std = new \stdClass();
					$std->item = $itemCont;
					$std->vBC = $stdProd->vProd;
					$std->vAliq = $i->produto->perc_iss;
					$std->vISSQN = $this->format($valorIss);
					$std->cMunFG = $config->codMun;
					$std->cListServ = $i->produto->cListServ;
					$std->indISS = 1;
					$std->indIncentivo = 1;

					$nfe->tagISSQN($std);
				}

			//PIS
				$stdPIS = new \stdClass();
				$stdPIS->item = $itemCont;
				$stdPIS->CST = $i->cst_pis;
				$stdPIS->vBC = $this->format($i->perc_pis) > 0 ? $stdProd->vProd : 0.00;
				$stdPIS->pPIS = $this->format($i->perc_pis);
				$stdPIS->vPIS = $this->format(($stdProd->vProd) *
					($i->perc_pis / 100));
				$PIS = $nfe->tagPIS($stdPIS);

			//COFINS
				$stdCOFINS = new \stdClass();
				$stdCOFINS->item = $itemCont;
				$stdCOFINS->CST = $i->cst_cofins;
				$stdCOFINS->vBC = $this->format($i->perc_cofins) > 0 ? $stdProd->vProd : 0.00;
				$stdCOFINS->pCOFINS = $this->format($i->perc_cofins);
				$stdCOFINS->vCOFINS = $this->format(($stdProd->vProd) *
					($i->perc_cofins / 100));
				$COFINS = $nfe->tagCOFINS($stdCOFINS);


			//IPI

				$std = new \stdClass();
				$std->item = $itemCont;
			//999 – para tributação normal IPI
				$std->cEnq = '999';
				$std->CST = $i->cst_ipi;
				$std->vBC = $this->format($i->perc_ipi) > 0 ? $stdProd->vProd : 0.00;
				$std->pIPI = $this->format($i->perc_ipi);
				$somaIPI += $std->vIPI = $stdProd->vProd * $this->format(($i->perc_ipi / 100));

				$nfe->tagIPI($std);



			//TAG ANP

			// if(strlen($i->produto->descricao_anp) > 5){
			// 	$stdComb = new \stdClass();
			// 	$stdComb->item = $itemCont; 
			// 	$stdComb->cProdANP = $i->produto->codigo_anp;
			// 	$stdComb->descANP = $i->produto->descricao_anp; 
			// 	$stdComb->UFCons = $venda->cliente->cidade->uf;

			// 	$nfe->tagcomb($stdComb);
			// }


				if($i->produto->derivado_petroleo){
					$stdComb = new \stdClass();
					$stdComb->item = $itemCont;
					$stdComb->cProdANP = $i->produto->codigo_anp;
					$stdComb->descANP = $i->produto->getDescricaoAnp();

					if ($i->produto->perc_glp > 0) {
						$stdComb->pGLP = $this->format($i->produto->perc_glp);
					}

					if ($i->produto->perc_gnn > 0) {
						$stdComb->pGNn = $this->format($i->produto->perc_gnn);
					}

					if ($i->produto->perc_gni > 0) {
						$stdComb->pGNi = $this->format($i->produto->perc_gni);
					}

					$stdComb->vPart = $this->format($i->produto->valor_partida);


					$stdComb->UFCons = $venda->cliente ? $venda->cliente->cidade->uf :
					$config->UF;

					$nfe->tagcomb($stdComb);
				}


				$cest = $i->produto->CEST;
				$cest = str_replace(".", "", $cest);
				$stdProd->CEST = $cest;
				if (strlen($cest) > 0) {
					$std = new \stdClass();
					$std->item = $itemCont;
					$std->CEST = $cest;
					$nfe->tagCEST($std);
				}

				if ($stdIde->idDest == 2 && $stdIde->indFinal == 1 && $pFisica) {
					if ($i->produto->perc_fcp_interestadual > 0 || $i->produto->perc_icms_interestadual > 0 || $i->produto->perc_icms_interno > 0) {

						$std = new \stdClass();
						$std->item = $itemCont;
					// $std->vBCUFDest = $stdProd->vProd;
						$std->vBCUFDest = $stdICMS->vBC;
					// $std->vBCFCPUFDest = $stdProd->vProd;
						$std->vBCFCPUFDest = $stdICMS->vBC;
						$std->pFCPUFDest = $this->format($i->produto->perc_fcp_interestadual);
						$std->pICMSUFDest = $this->format($i->produto->perc_icms_interestadual);
						$std->pICMSInter = $this->format($i->produto->perc_icms_interno);
						$std->pICMSInterPart = 100;
					// $std->vFCPUFDest = $this->format($stdProd->vProd * ($i->produto->perc_fcp_interestadual/100));
						$std->vFCPUFDest = $this->format($stdICMS->vBC * ($i->produto->perc_fcp_interestadual / 100));
					// $std->vICMSUFDest = $this->format($stdProd->vProd * ($i->produto->perc_icms_interestadual/100));
						$std->vICMSUFDest = $this->format($stdICMS->vBC * ($i->produto->perc_icms_interestadual / 100));
					// $std->vICMSUFDest = $this->format($stdICMS->vBC * ($i->produto->perc_icms_interestadual/100));
						$std->vICMSUFRemet = $this->format($stdICMS->vBC * ($i->produto->perc_icms_interno / 100));

						$nfe->tagICMSUFDest($std);
					}
				}
			}


			$stdICMSTot = new \stdClass();
			$stdICMSTot->vProd = $this->format($somaProdutos, $config->casas_decimais);
			$stdICMSTot->vBC = $this->format($VBC);
			$stdICMSTot->vICMS = $this->format($somaICMS);
			
			\Log::info('=== TOTALIZADORES NFE ===');
			\Log::info('vProd (soma produtos): ' . $somaProdutos);
			\Log::info('vBC (base cálculo): ' . $VBC . ' ← ESTE VALOR DEVE SER 0 PARA CST 40');
			\Log::info('vICMS (soma ICMS): ' . $somaICMS . ' ← ESTE VALOR DEVE SER 0 PARA CST 40'); 
			\Log::info('vICMSDeson (ICMS desonerado): ' . $somaICMSDeson . ' ← ESTE ESTÁ CORRETO');

			$stdICMSTot->vICMSDeson = $this->format($somaICMSDeson);
			$stdICMSTot->vBCST = 0.00;
			$stdICMSTot->vST = 0.00;

			if ($venda->frete) $stdICMSTot->vFrete = $this->format($venda->valor_frete);
			else $stdICMSTot->vFrete = 0.00;

			$stdICMSTot->vSeg = 0.00;
			$stdICMSTot->vDesc = $this->format($venda->desconto);
			$stdICMSTot->vII = 0.00;
			$stdICMSTot->vIPI = 0.00;
			$stdICMSTot->vPIS = 0.00;
			$stdICMSTot->vCOFINS = 0.00;
			$stdICMSTot->vOutro = $this->format($venda->acrescimo);

			if ($venda->frete) {
				$stdICMSTot->vNF =
				$this->format(($venda->valor_total + $venda->frete->valor + $somaIPI) - $venda->desconto + $venda->acrescimo);
			} else $stdICMSTot->vNF = $this->format($venda->valor_total + $somaIPI - $venda->desconto + $venda->acrescimo);


			$stdICMSTot->vTotTrib = 0.00;
			$ICMSTot = $nfe->tagICMSTot($stdICMSTot);

		//inicio totalizao issqn

			if ($somaISS > 0) {
				$std = new \stdClass();
				$std->vServ = $this->format($somaServico + $venda->desconto);
				$std->vBC = $this->format($somaServico);
				$std->vISS = $this->format($somaISS);
				$std->dCompet = date('Y-m-d');

				$std->cRegTrib = 6;

				$nfe->tagISSQNTot($std);
			}

		//fim totalizao issqn

			$stdTransp = new \stdClass();
			$stdTransp->modFrete = $venda->tipo_frete ?? '9';

			$transp = $nfe->tagtransp($stdTransp);

			if ($venda->transportadora) {
				$std = new \stdClass();
				$std->xNome = $venda->transportadora->razao_social;

				$std->xEnder = $venda->transportadora->logradouro;
				$std->xMun = $this->retiraAcentos($venda->transportadora->cidade->nome);
				$std->UF = $venda->transportadora->cidade->uf;

				$cnpj_cpf = preg_replace('/[^0-9]/', '', $venda->transportadora->cnpj_cpf);

				if (strlen($cnpj_cpf) == 14) $std->CNPJ = $cnpj_cpf;
				else $std->CPF = $cnpj_cpf;

				$nfe->tagtransporta($std);
			}

			if ($venda->frete != null) {

				$std = new \stdClass();


				$placa = str_replace("-", "", $venda->frete->placa);
				$std->placa = strtoupper($placa);
				$std->UF = $venda->frete->uf;

			// if($config->UF == $venda->cliente->cidade->uf){
				if ($venda->frete->placa != "" && $venda->frete->uf) {
					$nfe->tagveicTransp($std);
				}

				if (
					$venda->frete->qtdVolumes > 0 && $venda->frete->peso_liquido > 0
					&& $venda->frete->peso_bruto > 0
				) {
					$stdVol = new \stdClass();
					$stdVol->item = 1;
					$stdVol->qVol = $venda->frete->qtdVolumes;
					$stdVol->esp = $venda->frete->especie;

					$stdVol->nVol = $venda->frete->numeracaoVolumes;
					$stdVol->pesoL = $venda->frete->peso_liquido;
					$stdVol->pesoB = $venda->frete->peso_bruto;
					$vol = $nfe->tagvol($stdVol);
				}
			}

			if ($venda->cliente->cod_pais != 1058) {
				$std = new \stdClass();
				$std->UFSaidaPais = $config->UF;
				$std->xLocExporta = $config->municipio;
			// $std->xLocDespacho = 'Informação do Recinto Alfandegado';

				$nfe->tagexporta($std);
			}

		//Fatura
			if ($somaISS == 0 && $venda->natureza->CFOP_saida_estadual != '5915' && $venda->natureza->CFOP_saida_inter_estadual != '6915') {
				$stdFat = new \stdClass();
				$stdFat->nFat = (int)$lastNumero + 1;
				$stdFat->vOrig = $this->format($venda->valor_total + $venda->acrescimo);
				$stdFat->vDesc = $this->format($venda->desconto);
			// $stdFat->vOutro = $this->format($venda->acrescimo);
				$stdFat->vLiq = $this->format($venda->valor_total + $venda->acrescimo - $venda->desconto);
			// $stdFat->vLiq = $this->format($somaProdutos-$venda->desconto);
				
				// Para pagamento à vista (01 = dinheiro), não incluir dados de fatura
				if ($venda->forma_pagamento != '90' && $venda->forma_pagamento != '01' && $venda->tipo_forma_pagamento != 'a_vista') {
					$fatura = $nfe->tagfat($stdFat);
				}
			}

		//Duplicata
			if ($venda->forma_pagamento != '90' && $venda->forma_pagamento != '01' && $venda->tipo_forma_pagamento != 'a_vista') {
				if ($somaISS == 0 && $venda->natureza->CFOP_saida_estadual != '5915' && $venda->natureza->CFOP_saida_inter_estadual != '6915') {
					if (count($venda->fatura) > 0) {
						$contFatura = 1;
						foreach ($venda->fatura as $ft) {
							$stdDup = new \stdClass();
							$stdDup->nDup = "00" . $contFatura;
							$stdDup->dVenc = substr($ft->data_vencimento, 0, 10);
							$stdDup->vDup = $this->format($ft->valor);

							$nfe->tagdup($stdDup);
							$contFatura++;
						}
					} else {

						if ($venda->tipo_forma_pagamento != 'a_vista') {
							$stdDup = new \stdClass();
							$stdDup->nDup = '001';
							$stdDup->dVenc = Date('Y-m-d');
							$stdDup->vDup =  $this->format($venda->valor_total, 4);

							$nfe->tagdup($stdDup);
						}
					}
				}
			}

			$stdPag = new \stdClass();
			$pag = $nfe->tagpag($stdPag);

			if (sizeof($venda->fatura) > 0) {
				foreach ($venda->fatura as $ft) {

					$stdDetPag = new \stdClass();
					$stdDetPag->tPag = $ft->tipo_pagamento;
					$stdDetPag->vPag = $this->format($ft->valor);
					// indPag: 0 = à vista, 1 = a prazo
					$stdDetPag->indPag = ($ft->tipo_pagamento == '01' || $venda->tipo_forma_pagamento == 'a_vista') ? 0 : 1;
					
					\Log::info('=== FORMA PAGAMENTO (FATURA) ===');
					\Log::info('Tipo pagamento: ' . $ft->tipo_pagamento);
					\Log::info('Valor: ' . $ft->valor);
					\Log::info('indPag: ' . $stdDetPag->indPag);
					
					// Para cartão de crédito (03) ou débito (04), adicionar dados obrigatórios
					if ($ft->tipo_pagamento == '03' || $ft->tipo_pagamento == '04') {
						\Log::info('Adicionando dados de cartão para tipo pagamento: ' . $ft->tipo_pagamento);
						
						// Código de autorização - usar dados da venda ou valor padrão
						$stdDetPag->cAut = !empty($venda->cAut_cartao) ? $venda->cAut_cartao : '123456';
						
						// CNPJ da credenciadora - usar dados da venda ou valor padrão
						if (!empty($venda->cnpj_cartao)) {
							$cnpj = preg_replace('/[^0-9]/', '', $venda->cnpj_cartao);
							$stdDetPag->CNPJ = $cnpj;
						} else {
							// CNPJ padrão para credenciadoras comuns
							$stdDetPag->CNPJ = '01027058000191'; // Rede
						}
						
						// Bandeira do cartão - usar dados da venda ou valor padrão  
						$stdDetPag->tBand = !empty($venda->bandeira_cartao) ? $venda->bandeira_cartao : '01'; // Visa
						
						// Tipo de integração
						$stdDetPag->tpIntegra = 2;
						
						\Log::info('Dados cartão: cAut=' . $stdDetPag->cAut . ', CNPJ=' . $stdDetPag->CNPJ . ', tBand=' . $stdDetPag->tBand);
					}
					
					$detPag = $nfe->tagdetPag($stdDetPag);
				}
			} else {
				// Quando não há faturas, usar dados do tipo_pagamento principal
				$stdDetPag = new \stdClass();
				$stdDetPag->tPag = $venda->forma_pagamento;
				$stdDetPag->vPag = $this->format($venda->valor_total + $venda->acrescimo - $venda->desconto);
				// indPag: 0 = à vista, 1 = a prazo  
				$stdDetPag->indPag = ($venda->forma_pagamento == '01' || $venda->tipo_forma_pagamento == 'a_vista') ? 0 : 1;
				
				\Log::info('=== FORMA PAGAMENTO (PRINCIPAL) ===');
				\Log::info('Forma pagamento: ' . $venda->forma_pagamento);
				\Log::info('Tipo forma pagamento: ' . $venda->tipo_forma_pagamento);
				\Log::info('Valor: ' . $venda->valor_total);
				\Log::info('indPag: ' . $stdDetPag->indPag);
				
				// Para cartão de crédito (03) ou débito (04), adicionar dados obrigatórios
				if ($venda->forma_pagamento == '03' || $venda->forma_pagamento == '04') {
					\Log::info('Adicionando dados de cartão para forma pagamento: ' . $venda->forma_pagamento);
					
					// Código de autorização - usar dados da venda ou valor padrão
					$stdDetPag->cAut = !empty($venda->cAut_cartao) ? $venda->cAut_cartao : '123456';
					
					// CNPJ da credenciadora - usar dados da venda ou valor padrão
					if (!empty($venda->cnpj_cartao)) {
						$cnpj = preg_replace('/[^0-9]/', '', $venda->cnpj_cartao);
						$stdDetPag->CNPJ = $cnpj;
					} else {
						// CNPJ padrão para credenciadoras comuns
						$stdDetPag->CNPJ = '01027058000191'; // Rede
					}
					
					// Bandeira do cartão - usar dados da venda ou valor padrão  
					$stdDetPag->tBand = !empty($venda->bandeira_cartao) ? $venda->bandeira_cartao : '01'; // Visa
					
					// Tipo de integração: 1=Pagamento integrado com o sistema de automação da empresa 2=Pagamento não integrado com o sistema de automação da empresa
					$stdDetPag->tpIntegra = 2;
					
					\Log::info('Dados cartão: cAut=' . $stdDetPag->cAut . ', CNPJ=' . $stdDetPag->CNPJ . ', tBand=' . $stdDetPag->tBand);
				}
				
				$detPag = $nfe->tagdetPag($stdDetPag);
			}

			$stdDetPag = new \stdClass();

		// $stdPag = new \stdClass();
		// $pag = $nfe->tagpag($stdPag);

		// $stdDetPag = new \stdClass();

		// if (sizeof($venda->fatura) > 0) {
		// 	foreach ($venda->fatura as $ft) {
		// 		$stdDetPag->tPag = $ft->tipo_pagamento;
		// 		$stdDetPag->vPag = $ft->tipo_pagamento != '90' ? $this->format($somaProdutos -
		// 			$venda->desconto + $venda->acrescimo, $config->casas_decimais) : 0.00;

		// 		if ($venda->descricao_pag_outros != "") {
		// 			$stdDetPag->xPag = $venda->descricao_pag_outros;
		// 		}
		// 		if ($ft->tipo_pagamento == '03' || $ft->tipo_pagamento == '04') {
		// 			if ($venda->cAut_cartao != "") {
		// 				$stdDetPag->cAut = $venda->cAut_cartao;
		// 			}
		// 			if ($venda->cnpj_cartao != "") {
		// 				$cnpj = preg_replace('/[^0-9]/', '', $venda->cnpj_cartao);

		// 				$stdDetPag->CNPJ = $cnpj;
		// 			}
		// 			$stdDetPag->tBand = $venda->bandeira_cartao;

		// 			$stdDetPag->tpIntegra = 2;
		// 		}
		// 		$stdDetPag->indPag = $venda->forma_pagamento == 'a_vista' ?  0 : 1;

		// 		$detPag = $nfe->tagdetPag($stdDetPag);

		// 		if ($config->ambiente == 2) {
		// 			if ($venda->pedido_ecommerce_id > 0) {
		// 				$stdPag = new \stdClass();
		// 				$stdPag->CNPJ = env("RESP_CNPJ");
		// 				$stdPag->idCadIntTran = env("RESP_NOME");
		// 				$detInf = $nfe->infIntermed($stdPag);
		// 			}
		// 		}
		// 	}
		// }


			$stdInfoAdic = new \stdClass();

			$obs = " " . $venda->observacao;

			if ($nfesRef != "") {
				// $obs .= " Chaves referênciadas: " . $nfesRef;
			}

			if ($somaEstadual > 0 || $somaFederal > 0 || $somaMunicipal > 0) {
				$obs .= " Trib. aprox. ";
				if ($somaFederal > 0) {
					$obs .= "R$ " . number_format($somaFederal, 2, ',', '.') . " Federal";
				}
				if ($somaEstadual > 0) {
					$obs .= ", R$ " . number_format($somaEstadual, 2, ',', '.') . " Estadual";
				}
				if ($somaMunicipal > 0) {
					$obs .= ", R$ " . number_format($somaMunicipal, 2, ',', '.') . " Municipal";
				}
			// $ibpt = IBPT::where('uf', $config->UF)->first();

			}
			
			// Adiciona informações do IBPT sempre quando disponíveis
			if (!empty($obsIbpt)) {
				\Log::info('=== ADICIONANDO DADOS IBPT ===');
				\Log::info('Conteúdo obsIbpt: ' . $obsIbpt);
				$obs .= $obsIbpt;
			} else {
				\Log::info('=== DADOS IBPT VAZIOS ===');
			}
		// $stdInfoAdic->infCpl = $obs;
			if ($p->produto->renavam != '') {
				$veiCpl = ' | RENAVAM ' . $p->produto->renavam;
				if ($p->produto->placa != '') $veiCpl .= ', PLACA ' . $p->produto->placa;
				if ($p->produto->chassi != '') $veiCpl .= ', CHASSI ' . $p->produto->chassi;
				if ($p->produto->combustivel != '') $veiCpl .= ', COMBUSTÍVEL ' . $p->produto->combustivel;
				if ($p->produto->ano_modelo != '') $veiCpl .= ', ANO/MODELO ' . $p->produto->ano_modelo;
				if ($p->produto->cor_veiculo != '') $veiCpl .= ', COR ' . $p->produto->cor_veiculo;

				$obs .= $veiCpl;
			}

			if ($somaApCredito > 0) {
				if ($config->campo_obs_nfe != "") {
					$msg = $config->campo_obs_nfe;
					$msg = str_replace("%", number_format($tributacao->perc_ap_cred, 2, ",",  ".") . "%", $msg);
					$msg = str_replace('R$', 'R$ ' . number_format($somaApCredito, 2, ",",  "."), $msg);
					$obs .= $msg;
				}
			} elseif ($config->campo_obs_nfe != "") {
				$obs .= " " . $config->campo_obs_nfe;
			}

			if ($venda->getFormaPagamento($config->empresa_id) != null) {
				$obs .= "Inf. adicional de pagamento: " . $venda->getFormaPagamento($config->empresa_id)->infos;
			}

			$stdInfoAdic->infCpl = $this->retiraAcentos($obs);

			$infoAdic = $nfe->taginfAdic($stdInfoAdic);

			if ($config->aut_xml != '') {

				$std = new \stdClass();
				$cnpj = preg_replace('/[^0-9]/', '', $config->aut_xml);

				$std->CNPJ = $cnpj;

				$aut = $nfe->tagautXML($std);
			}

			$std = new \stdClass();
		/* $std->CNPJ = env('RESP_CNPJ'); //CNPJ da pessoa jurídica responsável pelo sistema utilizado na emissão do documento fiscal eletrônico
		$std->xContato = env('RESP_NOME'); //Nome da pessoa a ser contatada
		$std->email = env('RESP_EMAIL'); //E-mail da pessoa jurídica a ser contatada
		$std->fone = env('RESP_FONE'); //Telefone da pessoa jurídica/física a ser contatada
		$nfe->taginfRespTec($std); */

		$std->CNPJ = "05810477000156"; //CNPJ da pessoa jurídica responsável pelo sistema utilizado na emissão do documento fiscal eletrônico
		$std->xContato= "Mizael"; //Nome da pessoa a ser contatada
		$std->email ="mizakpiva@gmail.com"; //E-mail da pessoa jurídica a ser contatada
		$std->fone = "48996895205"; //Telefone da pessoa jurídica/física a ser contatada
		
		$nfe->taginfRespTec($std);

		try {
			\Log::info('=== MONTANDO NFE - Iniciando processo ===');
			$resultadoMontagem = $nfe->montaNFe();
			\Log::info('NFe montada com sucesso. Resultado: ' . var_export($resultadoMontagem, true));
			
			$chave = $nfe->getChave();
			$xml = $nfe->getXML();
			
			\Log::info('Chave NFe gerada: ' . $chave);
			\Log::info('Tamanho do XML gerado: ' . strlen($xml) . ' caracteres');
			\Log::info('Primeiros 500 caracteres do XML: ' . substr($xml, 0, 500));
			
			$arr = [
				'chave' => $chave,
				'xml' => $xml,
				'nNf' => $stdIde->nNF
			];
			
			\Log::info('=== NFE MONTADA COM SUCESSO ===');
			return $arr;
		} catch (\Exception $e) {
			\Log::error('=== ERRO NA MONTAGEM DA NFE ===');
			\Log::error('Erro: ' . $e->getMessage());
			\Log::error('Arquivo: ' . $e->getFile() . ' - Linha: ' . $e->getLine());
			\Log::error('Stack trace: ' . $e->getTraceAsString());
			
			$errosXML = $nfe->getErrors();
			\Log::error('Erros XML da biblioteca NFePHP: ' . json_encode($errosXML, JSON_PRETTY_PRINT));
			
			// Log do XML parcial gerado (se houver)
			try {
				$xmlParcial = $nfe->getXML();
				if (!empty($xmlParcial)) {
					\Log::info('XML parcial gerado antes do erro: ' . substr($xmlParcial, 0, 1000));
					
					// Verificar especificamente por elementos ICMS vazios
					if (strpos($xmlParcial, '<ICMS></ICMS>') !== false) {
						\Log::error('PROBLEMA ENCONTRADO: Tag <ICMS></ICMS> vazia detectada no XML');
					}
					if (strpos($xmlParcial, '<ICMS/>') !== false) {
						\Log::error('PROBLEMA ENCONTRADO: Tag <ICMS/> auto-fechada detectada no XML');
					}
				}
			} catch (\Exception $xmlException) {
				\Log::error('Não foi possível obter XML parcial: ' . $xmlException->getMessage());
			}
			
			return [
				'erros_xml' => $errosXML,
				'erro_exception' => $e->getMessage(),
				'erro_completo' => [
					'message' => $e->getMessage(),
					'file' => $e->getFile(),
					'line' => $e->getLine()
				]
			];
		}
	}









	private function validate_EAN13Barcode($ean)
	{

		$sumEvenIndexes = 0;
		$sumOddIndexes  = 0;

		$eanAsArray = array_map('intval', str_split($ean));

		if (!$this->has13Numbers($eanAsArray)) {
			return false;
		};

		for ($i = 0; $i < count($eanAsArray) - 1; $i++) {
			if ($i % 2 === 0) {
				$sumOddIndexes  += $eanAsArray[$i];
			} else {
				$sumEvenIndexes += $eanAsArray[$i];
			}
		}

		$rest = ($sumOddIndexes + (3 * $sumEvenIndexes)) % 10;

		if ($rest !== 0) {
			$rest = 10 - $rest;
		}

		return $rest === $eanAsArray[12];
	}

	private function has13Numbers(array $ean)
	{
		return count($ean) === 13;
	}

	private function retiraAcentos($texto)
	{
		return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/", "/(ç)/"), explode(" ", "a A e E i I o O u U n N c"), $texto);
	}

	public function format($number, $dec = 2)
	{
		return number_format((float) $number, $dec, ".", "");
	}

	public function consultaCadastro($cnpj, $uf)
	{
		try {

			$iest = '';
			$cpf = '';
			$response = $this->tools->sefazCadastro($uf, $cnpj, $iest, $cpf);

			$stdCl = new Standardize($response);

			$std = $stdCl->toStd();

			$arr = $stdCl->toArray();

			$json = $stdCl->toJson();

			return [
				'erro' => false,
				'json' => $json
			];
		} catch (\Exception $e) {
			return [
				'erro' => true,
				'json' => $e->getMessage()
			];
		}
	}

	public function consultaChave($chave)
	{
		$response = $this->tools->sefazConsultaChave($chave);

		$stdCl = new Standardize($response);
		$arr = $stdCl->toArray();
		return $arr;
	}

	public function consultar($item)
	{
		try {

			$this->tools->model('55');

			$chave = $item->chave;
			$response = $this->tools->sefazConsultaChave($chave);

			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();

			return $arr;
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function inutilizar($config, $nInicio, $nFinal, $justificativa)
	{
		try{
			$nSerie = (int)$config->numero_serie_nfe;
			$nIni = (int)$nInicio;
			$nFin = $nFinal;
			$xJust = $justificativa;
			$response = $this->tools->sefazInutiliza($nSerie, $nIni, $nFin, $xJust);

			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();

			return $arr;

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function cancelar($item, $motivo)
	{
		try {
			$chave = $item->chave;
			$response = $this->tools->sefazConsultaChave($chave);
			$stdCl = new Standardize($response);
			$arr = $stdCl->toArray();
			sleep(1);
			$nProt = $arr['protNFe']['infProt']['nProt'];
			$response = $this->tools->sefazCancela($chave, $motivo, $nProt);
			sleep(2);
			$stdCl = new Standardize($response);
			$std = $stdCl->toStd();
			$arr = $stdCl->toArray();
			$json = $stdCl->toJson();
			if ($std->cStat != 128) {
				//TRATAR
			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				if ($cStat == '101' || $cStat == '135' || $cStat == '155') {
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
					file_put_contents(public_path('xml_nfe_cancelada/') . $chave . '.xml', $xml);
					return $arr;
				} else {
					return ['erro' => true, 'data' => $arr, 'status' => 402];
				}
			}
		} catch (\Exception $e) {
			// echo $e->getMessage();
			return ['erro' => true, 'data' => $e->getMessage(), 'status' => 402];
			//TRATAR
		}
	}

	public function cartaCorrecao($item, $correcao)
	{
		try {

			$chave = $item->chave;
			$xCorrecao = $correcao;
			$nSeqEvento = $item->sequencia_cce + 1;
			$response = $this->tools->sefazCCe($chave, $xCorrecao, $nSeqEvento);
			sleep(2);

			$stdCl = new Standardize($response);

			$std = $stdCl->toStd();

			$arr = $stdCl->toArray();

			$json = $stdCl->toJson();

			if ($std->cStat != 128) {
				//TRATAR
			} else {
				$cStat = $std->retEvento->infEvento->cStat;
				if ($cStat == '135' || $cStat == '136') {
					$xml = Complements::toAuthorize($this->tools->lastRequest, $response);
					file_put_contents(public_path('xml_nfe_correcao/') . $chave . '.xml', $xml);

					$item->sequencia_cce = $item->sequencia_cce + 1;
					$item->save();
					return $arr;
				} else {
					//houve alguma falha no evento 
					return ['erro' => true, 'data' => $arr, 'status' => 402];
					//TRATAR
				}
			}
		} catch (\Exception $e) {
			return ['erro' => true, 'data' => $e->getMessage(), 'status' => 404];
		}
	}

	public function sign($xml)
	{
		return $this->tools->signNFe($xml);
	}

	public function transmitir($signXml, $chave, $remessaId = null)
	{
		try {
			$idLote = str_pad(100, 15, '0', STR_PAD_LEFT);
			
			// Para lotes com 1 NFe, deve ser síncrono (indSinc = 1)
			// Para lotes com múltiplas NFe, pode ser assíncrono (indSinc = 0)
			$indSinc = 1; // Sempre síncrono para 1 NFe
			
			$resp = $this->tools->sefazEnviaLote([$signXml], $idLote, $indSinc);

			$st = new Standardize();
			$std = $st->toStd($resp);
			
			if ($std->cStat != 103 && $std->cStat != 104) {
				return [
					'erro' => 1,
					'error' => "[$std->cStat] - $std->xMotivo"
				];
			}

			// Para envio síncrono (indSinc = 1), não precisa consultar recibo
			if ($indSinc == 1) {
				try {
					// Resposta já vem com o protocolo
					$xml = Complements::toAuthorize($signXml, $resp);
					file_put_contents(public_path('xml_nfe/') . $chave . '.xml', $xml);
					// Verifica se foi autorizado (cStat 100)
					$stdResp = $st->toStd($resp);
					if (isset($stdResp->protNFe->infProt->cStat)) {
						$cStat = $stdResp->protNFe->infProt->cStat;
						if ($cStat == '100') {
							// Atualiza estado_emissao da RemessaNfe
							if ($remessaId) {
								$remessa = \App\Models\RemessaNfe::find($remessaId);
								if ($remessa) {
									$remessa->estado_emissao = 'aprovado';
									$remessa->save();
								}
							}
							return [
								'erro' => 0,
								'success' => $stdResp->protNFe->infProt->xMotivo ?? 'Autorizada sincronamente',
								'cStat' => 100
							];
						} else {
							return [
								'erro' => 1,
								'error' => $stdResp->protNFe->infProt->xMotivo ?? 'NFe não autorizada',
								'cStat' => $cStat
							];
						}
					} else {
						return [
							'erro' => 1,
							'error' => 'NFe não autorizada',
							'cStat' => null
						];
					}
				} catch (\Exception $e) {
					return [
						'erro' => 1,
						'error' => $st->toArray($resp)
					];
				}
			} else {
				// Para envio assíncrono (múltiplas NFe)
				sleep(2);
				$recibo = $std->infRec->nRec;
				$protocolo = $this->tools->sefazConsultaRecibo($recibo);
				sleep(4);
				
				try {
					$xml = Complements::toAuthorize($signXml, $protocolo);
					file_put_contents(public_path('xml_nfe/') . $chave . '.xml', $xml);
					return [
						'erro' => 0,
						'success' => $recibo
					];
				} catch (\Exception $e) {
					return [
						'erro' => 1,
						'error' => $st->toArray($protocolo)
					];
				}
			}
			
		} catch (\Exception $e) {
			return [
				'erro' => 1,
				'error' => $e->getMessage()
			];
		}
	}

	/**
	 * Converte CSOSN (Simples Nacional) para CST (Regime Normal) equivalente
	 */
	private function converterCSOSNparaCST($csosn)
	{
		$conversao = [
			// CSOSN → CST equivalente (CSTs válidos: 00-90)
			'101' => '00',  // Tributada pelo Simples Nacional com permissão de crédito → Tributada integralmente
			'102' => '40',  // Tributada pelo Simples Nacional sem permissão de crédito → Isenta
			'103' => '40',  // Isenção do ICMS no Simples Nacional → Isenta
			'201' => '10',  // Tributada pelo Simples Nacional com permissão de crédito e com cobrança do ICMS por substituição tributária → Tributada e com cobrança do ICMS por substituição tributária
			'202' => '30',  // Tributada pelo Simples Nacional sem permissão de crédito e com cobrança do ICMS por substituição tributária → Isenta ou não tributada e com cobrança do ICMS por substituição tributária
			'203' => '30',  // Isenção do ICMS nos Simples Nacional e com cobrança do ICMS por substituição tributária → Isenta ou não tributada e com cobrança do ICMS por substituição tributária
			'300' => '40',  // Imune → Isenta
			'400' => '40',  // Não tributada pelo Simples Nacional → Isenta
			'500' => '60',  // ICMS cobrado anteriormente por substituição tributária (substituído) ou por antecipação → ICMS cobrado anteriormente por substituição tributária
			'900' => '90'   // Outros → Outros
		];
		
		$cstConvertido = $conversao[$csosn] ?? '40'; // Default: isenta
		\Log::info('Conversão CSOSN → CST: ' . $csosn . ' → ' . $cstConvertido);
		
		return $cstConvertido;
	}

	/**
	 * Processa ICMS usando a tag específica baseada no CST
	 */
	private function processarICMSPorCST($nfe, $stdICMS, $cst)
	{
		\Log::info('=== PROCESSANDO ICMS PARA CST: ' . $cst . ' ===');
		
		// Validar parâmetros de entrada
		if (empty($cst)) {
			\Log::error('CST vazio no processarICMSPorCST');
			throw new \Exception('CST não pode ser vazio');
		}
		
		if (!isset($stdICMS->item) || !isset($stdICMS->orig)) {
			\Log::error('Campos obrigatórios ausentes no stdICMS: item=' . ($stdICMS->item ?? 'NULL') . ', orig=' . ($stdICMS->orig ?? 'NULL'));
			throw new \Exception('Campos obrigatórios item e orig devem estar definidos');
		}
		
		// Criar um novo objeto específico para cada CST
		$stdICMSEspecifico = new \stdClass();
		
		// Copiar propriedades básicas
		foreach (get_object_vars($stdICMS) as $key => $value) {
			$stdICMSEspecifico->$key = $value;
		}
		
		\Log::info('Dados copiados para stdICMSEspecifico: ' . json_encode($stdICMSEspecifico));
		
		try {
			switch($cst) {
				case '00':
					// ICMS tributado integralmente
					$stdICMSEspecifico->modBC = $stdICMS->modBC ?? 0;
					$stdICMSEspecifico->vBC = $stdICMS->vBC ?? 0;
					$stdICMSEspecifico->pICMS = $stdICMS->pICMS ?? 0;
					$stdICMSEspecifico->vICMS = $stdICMS->vICMS ?? 0;
					\Log::info('Processando ICMS00 com dados: ' . json_encode($stdICMSEspecifico));
					$resultado = $nfe->tagICMS($stdICMSEspecifico);
					\Log::info('ICMS00 criado com sucesso');
					return $resultado;
				
			case '10':
				// Tributada e com cobrança do ICMS por substituição tributária
				$stdICMSEspecifico->modBC = $stdICMS->modBC ?? 0;
				$stdICMSEspecifico->vBC = $stdICMS->vBC ?? 0;
				$stdICMSEspecifico->pICMS = $stdICMS->pICMS ?? 0;
				$stdICMSEspecifico->vICMS = $stdICMS->vICMS ?? 0;
				$stdICMSEspecifico->modBCST = $stdICMS->modBCST ?? 4;
				$stdICMSEspecifico->pMVAST = $stdICMS->pMVAST ?? 0;
				$stdICMSEspecifico->pRedBCST = $stdICMS->pRedBCST ?? 0;
				$stdICMSEspecifico->vBCST = $stdICMS->vBCST ?? 0;
				$stdICMSEspecifico->pICMSST = $stdICMS->pICMSST ?? 0;
				$stdICMSEspecifico->vICMSST = $stdICMS->vICMSST ?? 0;
				return $nfe->tagICMS($stdICMSEspecifico);
				
			case '20':
				// Com redução de base de cálculo
				$stdICMSEspecifico->modBC = $stdICMS->modBC ?? 0;
				$stdICMSEspecifico->pRedBC = $stdICMS->pRedBC ?? 0;
				$stdICMSEspecifico->vBC = $stdICMS->vBC ?? 0;
				$stdICMSEspecifico->pICMS = $stdICMS->pICMS ?? 0;
				$stdICMSEspecifico->vICMS = $stdICMS->vICMS ?? 0;
				return $nfe->tagICMS($stdICMSEspecifico);
				
			case '30':
				// Isenta ou não tributada e com cobrança do ICMS por substituição tributária
				$stdICMSEspecifico->modBCST = $stdICMS->modBCST ?? 4;
				$stdICMSEspecifico->pMVAST = $stdICMS->pMVAST ?? 0;
				$stdICMSEspecifico->pRedBCST = $stdICMS->pRedBCST ?? 0;
				$stdICMSEspecifico->vBCST = $stdICMS->vBCST ?? 0;
				$stdICMSEspecifico->pICMSST = $stdICMS->pICMSST ?? 0;
				$stdICMSEspecifico->vICMSST = $stdICMS->vICMSST ?? 0;
				return $nfe->tagICMS($stdICMSEspecifico);
				
			case '40':
			case '41':
			case '50':
				// Isenta, não tributada ou diferida
				\Log::info('Processando CST isento/não tributado: ' . $cst);
				
				// Criar objeto limpo apenas com campos necessários
				$stdICMSEspecifico = new \stdClass();
				$stdICMSEspecifico->item = $stdICMS->item;
				$stdICMSEspecifico->orig = $stdICMS->orig;
				$stdICMSEspecifico->CST = $cst;
				
				// Garantir que base de cálculo seja sempre 0 para isentos
				$stdICMSEspecifico->vBC = 0;
				$stdICMSEspecifico->vICMS = 0;
				
				// Para CST 40 (isenta), incluir campos de desoneração e benefício fiscal
				if ($cst == '40') {
					\Log::info('=== PROCESSANDO CST 40 - ISENTA ===');
					
					// Calcular o valor do ICMS que seria devido (desonerado)
					$vProd = $stdICMS->vProd ?? 0;
					// Usar alíquota original preservada ou padrão de SC se não disponível
					$pICMSEfetivo = $stdICMS->pICMSOriginal ?? $stdICMS->pICMS ?? 17;
					if ($pICMSEfetivo == 0) {
						$pICMSEfetivo = 17; // Alíquota padrão SC quando não informada
					}
					
					if ($vProd > 0 && $pICMSEfetivo > 0) {
						$vICMSDesonerado = round(($vProd * $pICMSEfetivo / 100), 2);
						$stdICMSEspecifico->vICMSDeson = $vICMSDesonerado;
						$stdICMSEspecifico->motDesICMS = $stdICMS->motDesICMS ?? 9; // 9 = Outros
						
						\Log::info("CST 40 - Calculando ICMS desonerado: vProd={$vProd}, pICMS={$pICMSEfetivo}%, vICMSDeson={$vICMSDesonerado}");
						
						// Retornar valor de desoneração para soma nos totalizadores
						$stdICMS->vICMSDeson = $vICMSDesonerado;
					} else {
						\Log::info('CST 40 - Sem cálculo de desoneração: vProd=' . $vProd . ', pICMS=' . $pICMSEfetivo);
					}
					
					// Código de benefício fiscal obrigatório para CST 40
					$cBenefOriginal = $stdICMS->cBenef ?? null;
					\Log::info('CST 40 - cBenef original do stdICMS: ' . ($cBenefOriginal ?? 'NULL'));
					
					// Códigos válidos específicos para Santa Catarina (baseados na tabela oficial SEF/SC)
					$codigosValidosSC = [
						'SC270001', // Isenção ICMS - Art. 6º, I, alínea "a" do Anexo 2 do RICMS/SC
						'SC018001', // Isenção ICMS - Operações com livros, jornais, periódicos
						'SC018002', // Isenção ICMS - Produtos farmacêuticos e medicamentos
						'SC018003', // Isenção ICMS - Produtos alimentícios básicos
						'SC018004', // Isenção ICMS - Produtos hortifrutícolas in natura
						'SC018005', // Isenção ICMS - Produtos de higiene pessoal
						'SC270002', // Isenção ICMS - Outras operações do Anexo 2 do RICMS/SC
						'SC270003', // Isenção ICMS - Operações específicas por NCM
						'SC001001', // Código genérico de isenção (fallback)
					];
					
					// Verificar se o cBenef atual é válido para SC
					if (!empty($cBenefOriginal) && in_array($cBenefOriginal, $codigosValidosSC)) {
						$stdICMSEspecifico->cBenef = $cBenefOriginal;
						\Log::info('Usando cBenef válido do produto: ' . $cBenefOriginal);
					} else {
						// Usar código mais comum e aceito para isenção genérica em SC
						$stdICMSEspecifico->cBenef = 'SC270001'; // Código genérico mais aceito
						\Log::info('Usando cBenef genérico para SC: SC270001 (isenção genérica Art. 6º)');
						\Log::warning('cBenef original (' . ($cBenefOriginal ?? 'vazio') . ') não é válido para SC. Usado SC270001 como fallback.');
					}
					
					\Log::info('Aplicando cBenef para CST 40: ' . $stdICMSEspecifico->cBenef);
				}
				
				\Log::info('Dados ICMS completos para CST ' . $cst . ': ' . json_encode($stdICMSEspecifico));
				
				// VALIDAÇÃO CRÍTICA: Verificar se todos os campos obrigatórios estão presentes
				$camposObrigatorios = ['item', 'orig', 'CST'];
				foreach ($camposObrigatorios as $campo) {
					if (!isset($stdICMSEspecifico->$campo) || $stdICMSEspecifico->$campo === null || $stdICMSEspecifico->$campo === '') {
						\Log::error("ERRO CRÍTICO: Campo obrigatório '{$campo}' está vazio ou ausente no stdICMSEspecifico");
						\Log::error('Dados completos: ' . json_encode($stdICMSEspecifico));
						throw new \Exception("Campo obrigatório '{$campo}' está vazio para CST {$cst}");
					}
				}
				
				\Log::info('VALIDAÇÃO OK: Todos os campos obrigatórios estão presentes');
				\Log::info('Chamando nfe->tagICMS() com dados: ' . json_encode($stdICMSEspecifico));
				
				$resultado = $nfe->tagICMS($stdICMSEspecifico);
				
				if ($resultado === false || $resultado === null) {
					\Log::error('ERRO: nfe->tagICMS() retornou valor inválido: ' . var_export($resultado, true));
					\Log::error('Dados enviados: ' . json_encode($stdICMSEspecifico));
					throw new \Exception('Falha ao criar tag ICMS para CST ' . $cst);
				}
				
				\Log::info('ICMS para CST ' . $cst . ' criado com sucesso. Resultado: ' . var_export($resultado, true));
				return $resultado;
				
			case '51':
				// Diferimento
				$stdICMSEspecifico->modBC = $stdICMS->modBC ?? 0;
				$stdICMSEspecifico->pRedBC = $stdICMS->pRedBC ?? 0;
				$stdICMSEspecifico->vBC = $stdICMS->vBC ?? 0;
				$stdICMSEspecifico->pICMS = $stdICMS->pICMS ?? 0;
				$stdICMSEspecifico->vICMSOp = $stdICMS->vICMSOp ?? 0;
				$stdICMSEspecifico->pDif = $stdICMS->pDif ?? 0;
				$stdICMSEspecifico->vICMSDif = $stdICMS->vICMSDif ?? 0;
				$stdICMSEspecifico->vICMS = $stdICMS->vICMS ?? 0;
				return $nfe->tagICMS($stdICMSEspecifico);
				
			case '60':
				// ICMS cobrado anteriormente por substituição tributária
				$stdICMSEspecifico->vBCSTRet = $stdICMS->vBCSTRet ?? 0;
				$stdICMSEspecifico->pST = $stdICMS->pST ?? 0;
				$stdICMSEspecifico->vICMSSubstituto = $stdICMS->vICMSSubstituto ?? 0;
				$stdICMSEspecifico->vICMSSTRet = $stdICMS->vICMSSTRet ?? 0;
				return $nfe->tagICMS($stdICMSEspecifico);
				
			case '70':
				// Com redução de base de cálculo e cobrança do ICMS por substituição tributária
				$stdICMSEspecifico->modBC = $stdICMS->modBC ?? 0;
				$stdICMSEspecifico->pRedBC = $stdICMS->pRedBC ?? 0;
				$stdICMSEspecifico->vBC = $stdICMS->vBC ?? 0;
				$stdICMSEspecifico->pICMS = $stdICMS->pICMS ?? 0;
				$stdICMSEspecifico->vICMS = $stdICMS->vICMS ?? 0;
				$stdICMSEspecifico->modBCST = $stdICMS->modBCST ?? 4;
				$stdICMSEspecifico->pMVAST = $stdICMS->pMVAST ?? 0;
				$stdICMSEspecifico->pRedBCST = $stdICMS->pRedBCST ?? 0;
				$stdICMSEspecifico->vBCST = $stdICMS->vBCST ?? 0;
				$stdICMSEspecifico->pICMSST = $stdICMS->pICMSST ?? 0;
				$stdICMSEspecifico->vICMSST = $stdICMS->vICMSST ?? 0;
				return $nfe->tagICMS($stdICMSEspecifico);
				
			case '90':
				// Outras
				$stdICMSEspecifico->modBC = $stdICMS->modBC ?? 0;
				$stdICMSEspecifico->vBC = $stdICMS->vBC ?? 0;
				$stdICMSEspecifico->pRedBC = $stdICMS->pRedBC ?? 0;
				$stdICMSEspecifico->pICMS = $stdICMS->pICMS ?? 0;
				$stdICMSEspecifico->vICMS = $stdICMS->vICMS ?? 0;
				$stdICMSEspecifico->modBCST = $stdICMS->modBCST ?? 4;
				$stdICMSEspecifico->pMVAST = $stdICMS->pMVAST ?? 0;
				$stdICMSEspecifico->pRedBCST = $stdICMS->pRedBCST ?? 0;
				$stdICMSEspecifico->vBCST = $stdICMS->vBCST ?? 0;
				$stdICMSEspecifico->pICMSST = $stdICMS->pICMSST ?? 0;
				$stdICMSEspecifico->vICMSST = $stdICMS->vICMSST ?? 0;
				return $nfe->tagICMS($stdICMSEspecifico);
				
			default:
				// Fallback: para CSTs não mapeados, usar o método genérico
				\Log::warning('CST não mapeado: ' . $cst . '. Usando método genérico.');
				return $nfe->tagICMS($stdICMSEspecifico);
		}
		} catch (\Exception $e) {
			\Log::error('Erro no processamento ICMS CST ' . $cst . ': ' . $e->getMessage());
			\Log::error('Dados ICMS: ' . json_encode($stdICMS));
			throw new \Exception('Erro ao processar ICMS CST ' . $cst . ': ' . $e->getMessage());
		}
	}
}
