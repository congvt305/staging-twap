var config = {
	map: {
        '*': {
            satpAjaxcart: 'Satp_Ajaxcart/js/ajax',
            satpPopup: 'Satp_Ajaxcart/js/popup',
            satpGoto: 'Satp_Ajaxcart/js/goto'
        }
    },
    config:{
    	mixins: {
         'Magento_ConfigurableProduct/js/configurable': {
               'Satp_Ajaxcart/js/mixin/configurable': true
           }
      }
  }
};
