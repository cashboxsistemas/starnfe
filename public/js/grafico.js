VENDAS = []

$(function(){
	setTimeout(() => {
		getDataCards()
		graficoContaPagar()
		graficoContaReceber()
	}, 100)
})

$('#locais').change(() => {
	getDataCards()
	graficoContaPagar()
	graficoContaReceber()
})

function getDataCards(){
	let empresa_id = $('#empresa_id').val();
	let local_id = $('#locais').val();
	console.log("local_id", local_id)
	$.get(path_url + 'api/graficos/getDataCards', {
		local_id: local_id,
		empresa_id: empresa_id
	}).done((success) => {
		console.log(success)
		if(success){
			$('.total_vendas').text("R$ " + convertFloatToMoeda(success.vendas))
			$('.total_produtos').text(success.produtos)
			$('.total_pagar').text("R$ " + convertFloatToMoeda(success.conta_pagar))
			$('.total_receber').text("R$ " + convertFloatToMoeda(success.conta_receber))
		}

	})
	.fail((err) => {
		console.log(err)
	})

}

$('#set-location').click(() => {
	let filial_id = $('#locais').val()
	$.get(path_url + 'usuarios/set-location', {filial_id: filial_id})
	.done((success) => {
		console.log(success)
		swal("Sucesso", "Local definido como padrão!", "success")

	})
	.fail((err) => {
		console.log(err)
		swal("Opss", "Algo deu errado!", "error")
	})
})

$('#filial_id').change(() => {
	setTimeout(() => {
		$('#seteDias').trigger('click')
		filtrar()
		buscaProdutos()
	}, 10)
})


function getVendasAnual(call){
	let empresa_id = $('#empresa_id').val();
	$.get(path_url + 'api/graficos/vendasAnual', {empresa_id: empresa_id})
	.done((success) => {
		call(success)
	}).fail((err) => {
		console.log(err)
		call([])

	})
}

function getProdutos(call){
	let empresa_id = $('#empresa_id').val();
	let filial_id = $('#locais').val()
	$.get(path_url + 'api/graficos/produtos', {empresa_id: empresa_id, filial_id: filial_id})
	.done((success) => {
		console.log(success)
		call(success)
	}).fail((err) => {
		console.log(err)
		call([])

	})
}

function getContasReceber(call){
	let empresa_id = $('#empresa_id').val();
	let filial_id = $('#locais').val()

	$.get(path_url + 'api/graficos/contasReceber', {empresa_id: empresa_id, filial_id: filial_id})
	.done((success) => {
		console.log(success)
		call(success)
	}).fail((err) => {
		console.log(err)
		call([])

	})
}

function getContasPagar(call){

	let empresa_id = $('#empresa_id').val();
	let filial_id = $('#locais').val()

	$.get(path_url + 'api/graficos/contasPagar', {empresa_id: empresa_id, filial_id: filial_id})
	.done((success) => {
		console.log(success)
		call(success)
	}).fail((err) => {
		console.log(err)
		call([])

	})
}

function filtroBox(dias){
	let empresa_id = $('#empresa_id').val();

	$.get(path_url + 'api/graficos/boxConsulta', {dias: dias, empresa_id: empresa_id})
	.done((success) => {

		console.log(success)
		// $('.total_vendas').text("R$ " + success.totalDeVendas)
		// $('.total_receber').text("R$ " + success.totalDeContaReceber)
		// $('.total_pagar').text("R$ " + success.totalDeContaPagar)
	})
	.fail((err) => {
	})
}

$(function () {
	"use strict";

	setTimeout(() => {
		// filtroBox(7)
	},20)

	// chart1
	getVendasAnual((res) => {
		console.log(res)
		var options = {
			series: [{
				name: 'Valor',
				data: res.somaVendas
			}],
			chart: {
				foreColor: '#9a9797',
				type: 'area',
				height: 380,
				zoom: {
					enabled: false
				},
				toolbar: {
					show: false
				},
				dropShadow: {
					enabled: false,
					top: 3,
					left: 14,
					blur: 4,
					opacity: 0.10,
				}
			},
			stroke: {
				width: 4,
				curve: 'smooth'
			},
			xaxis: {
				categories: res.meses,
			},
			dataLabels: {
				enabled: false
			},
			fill: {
				type: 'gradient',
				gradient: {
					shade: 'light',
					gradientToColors: ['#8833ff'],
					shadeIntensity: 1,
					type: 'vertical',
					opacityFrom: 0.8,
					opacityTo: 0.3,

				},
			},
			colors: ["#8833ff"],
			yaxis: {
				labels: {
					formatter: function (value) {
						return "R$ " + value;
					}
				},
			},
			markers: {
				size: 4,
				colors: ["#8833ff"],
				strokeColors: "#fff",
				strokeWidth: 2,
				hover: {
					size: 7,
				}
			},
			grid: {
				show: true,
				borderColor: '#ededed',
				strokeDashArray: 4,
			}
		};
		var chart = new ApexCharts(document.querySelector("#chart1"), options);
		chart.render();
	})

	//grafico de produtos
	// chart 2
	getProdutos((res) => {
		var options = {
			series: [{
				name: 'Cadastrado no mês',
				data: res.somaCadastradoMes
			}, {
				name: 'Vendidos no mês',
				data: res.somaVendidosNoDia
			}, {
				name: 'Sem venda no mês',
				data: res.somaNaoVendidos
			}],
			chart: {
				foreColor: '#9a9797',
				type: 'bar',
				height: 320,
				stacked: true,
				toolbar: {
					show: false
				},
			},
			plotOptions: {
				bar: {
					horizontal: false,
					columnWidth: '18%',
				},
			},
			legend: {
				show: false,
				position: 'top',
				horizontalAlign: 'left',
				offsetX: -20
			},
			dataLabels: {
				enabled: false
			},
			stroke: {
				show: true,
				width: 2,
				colors: ['transparent']
			},
			colors: ["#e62e2e", "#29cc39", "#0dcaf0"],
			xaxis: {
				categories: res.meses,
			},
			fill: {
				opacity: 1
			},
			grid: {
				show: true,
				borderColor: '#ededed',
				strokeDashArray: 4,
			},
			responsive: [{
				breakpoint: 480,
				options: {
					chart: {
						height: 310,
					},
					plotOptions: {
						bar: {
							columnWidth: '30%'
						}
					}
				}
			}]
		};
		var chart = new ApexCharts(document.querySelector("#chart2"), options);
		chart.render();
	});
	
});

function graficoContaReceber(){
	getContasReceber((res) => {
		console.log(res)

		$('.cr-recebido').text("R$"+res.recebidas)
		$('.cr-receber').text("R$"+res.receber)
		var options = {
			series: [res.percentual],
			chart: {
				height: 380,
				type: 'radialBar',
				offsetY: -10
			},
			plotOptions: {
				radialBar: {
					startAngle: -135,
					endAngle: 135,
					hollow: {
						margin: 0,
						size: '70%',
						background: 'transparent',
					},
					track: {
						strokeWidth: '100%',
						dropShadow: {
							enabled: false,
							top: -3,
							left: 0,
							blur: 4,
							opacity: 0.12
						}
					},
					dataLabels: {
						name: {
							fontSize: '16px',
							color: '#212529',
							offsetY: 5
						},
						value: {
							offsetY: 20,
							fontSize: '30px',
							color: '#212529',
							formatter: function (val) {
								return val + "%";
							}
						}
					}
				}
			},
			fill: {
				type: 'gradient',
				gradient: {
					shade: 'dark',
					shadeIntensity: 0.15,
					gradientToColors: ['#4a00e0'],
					inverseColors: false,
					opacityFrom: 1,
					opacityTo: 1,
					stops: [0, 50, 65, 91]
				},
			},
			colors: ["#8e2de2"],
			stroke: {
				dashArray: 4
			},
			labels: ['Recebido'],
			responsive: [{
				breakpoint: 480,
				options: {
					chart: {
						height: 300,
					}
				}
			}]
		};
		var chart = new ApexCharts(document.querySelector("#chart4"), options);
		chart.render();
	})
}

function graficoContaPagar(){
	getContasPagar((res) => {
		console.log(res)

		$('.cp-pago').text("R$"+res.pagos)
		$('.cp-pagar').text("R$"+res.pagar)
		var options = {
			series: [res.percentual],
			chart: {
				height: 380,
				type: 'radialBar',
				offsetY: -10
			},
			plotOptions: {
				radialBar: {
					startAngle: -135,
					endAngle: 135,
					hollow: {
						margin: 0,
						size: '70%',
						background: 'transparent',
					},
					track: {
						strokeWidth: '100%',
						dropShadow: {
							enabled: false,
							top: -3,
							left: 0,
							blur: 4,
							opacity: 0.12
						}
					},
					dataLabels: {
						name: {
							fontSize: '16px',
							color: '#212529',
							offsetY: 5
						},
						value: {
							offsetY: 20,
							fontSize: '30px',
							color: '#212529',
							formatter: function (val) {
								return val + "%";
							}
						}
					}
				}
			},
			fill: {
				type: 'gradient',
				gradient: {
					shade: 'dark',
					shadeIntensity: 0.15,
					gradientToColors: ['#4a00e0'],
					inverseColors: false,
					opacityFrom: 1,
					opacityTo: 1,
					stops: [0, 50, 65, 91]
				},
			},
			colors: ["#8e2de2"],
			stroke: {
				dashArray: 4
			},
			labels: ['Pago'],
			responsive: [{
				breakpoint: 480,
				options: {
					chart: {
						height: 300,
					}
				}
			}]
		};
		var chart = new ApexCharts(document.querySelector("#chart9"), options);
		chart.render();
	});
}


