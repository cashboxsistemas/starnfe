$(function(){
	$('.loading-class').removeClass('modal-loading')
	let empresa_id = $('#empresa_id').val()
	$.get(path_url+'api/notificacoes', {empresa_id: empresa_id})
	.done((success) => {
		$('.loading-class').addClass('modal-loading')
		$('.header-notifications-list').html(success)
		setTimeout(() => {
			$('.alert-count').text($('.alert-item').length)
		}, 10)
	}).fail((err) => {
		console.log(err)
		$('.loading-class').addClass('modal-loading')
		
	})
})