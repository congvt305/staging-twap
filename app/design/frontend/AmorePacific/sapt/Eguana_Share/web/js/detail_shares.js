require(['jquery','Magento_Ui/js/modal/modal'], function($, modal){
	$(function(){
		var options = {
			type: 'popup',
			responsive: true,
			innerScroll: true,
			modalClass: 'modal-share-content',
			title: ''
		};
		modal(options, $('#modal-share'));
		$(".modal-share-button").on('click',function(){
			$("#modal-share").modal("openModal");
		});
	})
});
