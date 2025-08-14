$(function(){

})

$('.btn-consulta-status').click(() => {
	let token = $('#_token').val();
	let empresa_id = $("#empresa_id").val();

	$.post(path_url + 'api/nfce/consulta-status-sefaz',{ empresa_id: empresa_id })
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
function emitirNFCe(id){
	console.clear()
	let empresa_id = $("#empresa_id").val();
	$.post(path_url + "api/nfce/transmitir", {
		id: id,
		empresa_id: empresa_id,
	})
	.done((success) => {
		console.log(success)
		if(success == 'OFFL'){
			swal("Alerta", "NFCe gerada em contigência!", "success").then(() => {
				window.open(path_url + 'nfce/imprimir/'+vendaId, '_blank');
				location.reload()
			})
		}else{
			swal("Sucesso", "NFCe emitida " + success, "success")
			.then(() => {
				window.open(path_url+'nfce/imprimir/'+id, "_blank")
				setTimeout(() => {
					location.reload()
				}, 100)
			})
		}
	})
	.fail((err) => {
		console.log(err)
		if(err.status == 403){
			try{
				let infProt = err.responseJSON.protNFe.infProt
				swal("Algo deu errado", infProt.cStat + " - " + infProt.xMotivo, "error")
			}catch{
				swal("Algo deu errado", err.responseJSON, "error")
			}
		}else{
			swal("Algo deu errado", err.responseJSON, "error")
		}
	})
}

function consultarNFCe(id){
	let empresa_id = $("#empresa_id").val();
	$.post(path_url + "api/nfce/consultar", {
		id: id,
		empresa_id: empresa_id,
	})
	.done((success) => {
		console.log(success)
		if(success.protNFe){
			let infProt = success.protNFe.infProt
			swal("Sucesso", "[" + infProt.chNFe + "] " + infProt.xMotivo, "success")
		}else{
			swal("Erro", "[" + success.chNFe + "] " + success.xMotivo, "error")
		}

	})
	.fail((err) => {
		console.log(err)
		swal("Algo deu errado", err.responseJSON, "error")

	})
}

function modalCancelar(id, numero){
	$('#modal-cancelar').modal('show')
	$('.numero_nfce').text(numero)
	$('#numero_venda').val(id)
}

$('#btn-cancelar-send').click(() => {
	let id = $('#numero_venda').val()

	if(id){
		let empresa_id = $("#empresa_id").val();
		let motivo = $('#inp-motivo-cancela').val()
		if(motivo.length >= 15){
			$.post(path_url + "api/nfce/cancelar", {
				id: id,
				empresa_id: empresa_id,
				motivo: motivo
			})
			.done((success) => {
				console.log(success)
				let infEvento = success.retEvento.infEvento
				swal("Sucesso", "[" + infEvento.cStat + "] " + infEvento.xMotivo, "success")
				.then(() => {
					window.open(path_url+'nfe/imprimir-cancela/'+id, "_blank")
					setTimeout(() => {
						location.reload()
					}, 100)
				})

			})
			.fail((err) => {
				console.log(err)
				try{
					swal("Algo deu errado", err.responseJSON.retEvento.infEvento.xMotivo, "error")
				}catch{
					swal("Algo deu errado", err.responseJSON, "error")
				}
			})
		}else{
			swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
		}
	}else{
		swal("Alerta", "Selecione uma venda!", "warning")
	}
})

$('#btn-inutilizar-send').click(() => {
	let empresa_id = $("#empresa_id").val();
	let motivo = $('#inp-justificativa').val()
	let numero_serie = $('#inp-numero_serie').val()
	let numero_inicial = $('#inp-numero_nfce_inicial').val()
	let numero_final = $('#inp-numero_nfce_final').val()
	if(motivo.length >= 15){
		$.post(path_url + "api/nfce/inutilizar", {
			empresa_id: empresa_id,
			motivo: motivo,
			numero_inicial: numero_inicial,
			numero_final: numero_final
		})
		.done((success) => {

			console.log(success)
			let infInut = success.infInut
			if(infInut.cStat == "102"){
				$('#modal-inutilizar_nfce').modal('hide')
				swal("Sucesso", "[" + infInut.nProt + "] " + infInut.xMotivo, "success")
			}else{
				swal("Erro", "[" + infInut.cStat + "] " + infInut.xMotivo, "error")
			}

		})
		.fail((err) => {
			console.log(err)
			
			swal("Algo deu errado", err.responseJSON, "error")

		})
	}else{
		swal("Alerta", "Informe no mínimo 15 caracteres", "warning")
	}
})


