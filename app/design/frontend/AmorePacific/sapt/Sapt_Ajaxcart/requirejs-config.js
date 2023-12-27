var config = {
	map: {
        '*': {
            saptAjaxcart: 'Sapt_Ajaxcart/js/ajax',
            saptPopup: 'Sapt_Ajaxcart/js/popup',
            saptGoto: 'Sapt_Ajaxcart/js/goto'
        }
    },
    config:{
    	mixins: {
         'Magento_ConfigurableProduct/js/configurable': {
               'Sapt_Ajaxcart/js/mixin/configurable': true
           }
      }
  }
};
