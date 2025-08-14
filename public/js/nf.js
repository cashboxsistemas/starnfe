$(function () {
	$('.btn-action').attr('disabled', 'disabled')
	// $('.checkbox').each(function(i, e){
	// 	e.checked = false
	// })

	validLineSelect()
})
$('.checkbox').click(function () {
	$value = $(this).val()
	console.log($value)
	$('.checkbox').each(function (i, e) {
		if (e.value != $value) {
			e.checked = false
		}

		validLineSelect()
	})
})



function validLineSelect() {
	$('.btn-action').attr('disabled', 'disabled')

	$('.checkbox').each(function (i, e) {
		if ($(this).is(':checked')) {
			let status = $(this).data('status')
			console.log(status)
			if (status == 'novo' || status == 'rejeitado') {
				$('#btn-enviar').removeAttr('disabled')
				$('#btn-danfe-temp').removeAttr('disabled')
			} else if (status == 'aprovado') {
				$('#btn-imprimir').removeAttr('disabled')
				$('#btn-imprimir-cce').removeAttr('disabled')
				$('#btn-consultar').removeAttr('disabled')
				$('#btn-cancelar').removeAttr('disabled')
				$('#btn-corrigir').removeAttr('disabled')
				$('#btn-baixar-xml').removeAttr('disabled')
				$('#btn-enviar-email').removeAttr('disabled')
			} else if (status == 'cancelado') {
				$('#btn-imprimir-cancela').removeAttr('disabled')
			}
		}
	})
}

function getChecked(call) {
	let id = null
	$('.checkbox').each(function (i, e) {
		if (e.checked) {
			id = e.value
		}
	})
	call(id)
}

function getCheckedElement(call) {
	$el = null
	$('.checkbox').each(function (i, e) {
		if (e.checked) {
			$el = $(this)
		}
	})
	call($el)
}

$('.btn-consulta-status').click(() => {
	let token = $('#_token').val();
	let empresa_id = $("#empresa_id").val();

	$.post(path_url + 'api/nfe/consulta-status-sefaz',{ empresa_id: empresa_id })
	.done((res) => {
		console.log(res)
		let msg = "cStat: " + res.cStat
		msg += "\nMotivo: " + res.xMotivo
		msg += "\nAmbiente: " + (res.tpAmb == 2 ? "Homologação" : "Produção")
		msg += "\nverAplic: " + res.verAplic
		
		swal("Sucesso", msg, "success")
	})
	.fail((err) => {
		console.log(err)
		try{
			swal("Erro", err.responseText, "error")
		}catch{
			swal("Erro", "Algo deu errado", "error")
		}
	})
})

$('#btn-enviar').click(() => {
	console.clear()
	getChecked((id) => {
		$("#btn-consulta-cnpj span").removeClass("d-none");
		let empresa_id = $("#empresa_id").val();

		$.post(path_url + "api/nfe/transmitir", {
			id: id,
			empresa_id: empresa_id,
		})
		.done((success) => {
			console.log(success)

			swal("Sucesso", "NFe emitida " + success, "success")
			.then(() => {
				window.open(path_url + 'nfe/imprimir/' + id, "_blank")
				setTimeout(() => {
					location.reload()
				}, 100)
			})
		})
		.fail((err) => {
			console.log(err)
			try{
				if (err.status == 403) {
					let infProt = err.responseJSON.protNFe.infProt
					swal("Algo deu errado", infProt.cStat + " - " + infProt.xMotivo + " | " + infProt.chNFe, "error")
				} else {
					swal("Algo deu errado", err.responseJSON, "error")
				}
			}catch{
				swal("Algo deu errado", err.responseJSON, "error")
			}
		})
	})
})

$('#btn-imprimir').click(function () {
	getChecked((id) => {
		window.open(path_url + 'nfe/imprimir/' + id, "_blank")
	})
})

$('#btn-imprimir-cce').click(function () {
	getChecked((id) => {
		window.open(path_url + 'nfe/imprimir-cce/' + id, "_blank")
	})
})

$('#btn-imprimir-cancela').click(function () {
	getChecked((id) => {
		window.open(path_url + 'nfe/imprimir-cancela/' + id, "_blank")
	})
})

$('#btn-baixar-xml').click(function () {
	getChecked((id) => {
		window.open(path_url + 'nfe/baixar-xml/' + id, "_blank")
	})
})

$('#btn-danfe-temp').click(function () {
	getChecked((id) => {
		let href = $(this).data('href')
		window.open(href + "/" + id, "_blank")
	})
})

$('#btn-consultar').click(() => {
	console.clear()
	getChecked((id) => {
		if (id) {
			let empresa_id = $("#empresa_id").val();

			$.post(path_url + "api/nfe/consulta-nfe", {
				id: id,
				empresa_id: empresa_id,
			})
			.done((success) => {
				console.log(success)
				if(success.protNFe){
					let infProt = success.protNFe.infProt
					swal("Sucesso", "[" + infProt.chNFe + "] " + infProt.xMotivo, "success")
				}else{
					swal("Alerta", success.xMotivo, "warning")
				}

			})
			.fail((err) => {
				console.log(err)
				swal("Algo deu errado", err.responseJSON, "error")

			})
		} else {
			swal("Alerta", "Selecione uma venda!", "warning")
		}
	})
})

$('#btn-cancelar').click(() => {
	console.clear()
	getCheckedElement((el) => {
		console.log(el)
		let numero_nfe = el.data('numero_nfe')
		if (numero_nfe > 0) {
			$('.numero_nfe').text(numero_nfe)
			$('#modal-cancelar').modal('show')
		}
	})
})

$('#btn-inutilizar').click(() => {
	console.clear()

	$('#modal-inutilizar').modal('show')
})

$('#btn-cancelar-send').click(() => {
	getChecked((id) => {
		if (id) {
			let empresa_id = $("#empresa_id").val();
			let motivo = $('#inp-motivo-cancela').val()
			if (motivo.length >= 15) {
				$.post(path_url + "api/nfe/cancelar-nfe", {
					id: id,
					empresa_id: empresa_id,
					motivo: motivo
				})
				.done((success) => {
					console.log(success)
					let infEvento = success.retEvento.infEvento
					swal("Sucesso", "[" + infEvento.cStat + "] " + infEvento.xMotivo, "success")
					.then(() => {
						window.open(path_url + 'nfe/imprimir-cancela/' + id, "_blank")
						setTimeout(() => {
							location.reload()
						}, 100)
					})

				})
				.fail((err) => {
					console.log(err)
					try {
						swal("Algo deu errado", err.responseJSON.retEvento.infEvento.xMotivo, "error")
					} catch {
						swal("Algo deu errado", err.responseJSON, "error")
					}
				})
			} else {
				swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
			}
		} else {
			swal("Alerta", "Selecione uma venda!", "warning")
		}
	})
})

$('#btn-corrigir').click(() => {
	console.clear()
	getCheckedElement((el) => {
		console.log(el)
		let numero_nfe = el.data('numero_nfe')
		if (numero_nfe > 0) {
			$('.numero_nfe').text(numero_nfe)
			$('#modal-corrigir').modal('show')
		}
	})
})

$('#btn-corrige-send').click(() => {
	getChecked((id) => {
		if (id) {
			let empresa_id = $("#empresa_id").val();
			let motivo = $('#inp-motivo-corrige').val()
			if (motivo.length >= 15) {
				$.post(path_url + "api/nfe/corrigir-nfe", {
					id: id,
					empresa_id: empresa_id,
					motivo: motivo
				})
				.done((success) => {
					console.log(success)
					let infEvento = success.retEvento.infEvento
					swal("Sucesso", "[" + infEvento.cStat + "] " + infEvento.xMotivo, "success")
					.then(() => {
						window.open(path_url + 'nfe/imprimir-cce/' + id, "_blank")
						$('#modal-corrigir').modal('hide')
					})

				})
				.fail((err) => {
					console.log(err)
					try {
						swal("Algo deu errado", err.responseJSON.retEvento.infEvento.xMotivo, "error")
					} catch {
						swal("Algo deu errado", err.responseJSON, "error")
					}
				})
			} else {
				swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
			}
		} else {
			swal("Alerta", "Selecione uma venda!", "warning")
		}
	})
})

$('#btn-inutiliza-send').click(() => {
	let empresa_id = $("#empresa_id").val();
	let motivo = $('#inp-motivo-inutiliza').val()
	let numero_inicial = $('#inp-numero_inicial').val()
	let numero_final = $('#inp-numero_final').val()
	if (motivo.length >= 15) {
		$.post(path_url + "api/nfe/inutiliza-nfe", {
			empresa_id: empresa_id,
			motivo: motivo,
			numero_inicial: numero_inicial,
			numero_final: numero_final
		})
		.done((success) => {

			console.log(success)
			let infInut = success.infInut
			if (infInut.cStat == "102") {
				$('#modal-inutilizar').modal('hide')
				swal("Sucesso", "[" + infInut.nProt + "] " + infInut.xMotivo, "success")
			} else {
				swal("Erro", "[" + infInut.cStat + "] " + infInut.xMotivo, "error")
			}

		})
		.fail((err) => {
			console.log(err)

			swal("Algo deu errado", err.responseJSON, "error")

		})
	} else {
		swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
	}
})


$('.checkbox').click(function () {
	let email = $(this).data('email')
	$('#inp-email').val(email)
})