<?php
namespace Magenest\Popup\Controller\Adminhtml\Popup;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends Popup
{
    /**
     * Execute
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $widgetInstance = $this->_initWidgetInstance();

        $params = $this->_request->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirectBack = $this->getRequest()->getParam('back', false);
        $popupModel = $this->_popupFactory->create();
        $popupTemplate = $this->_popupTemplateFactory->create();

        try {
            $start_date = isset($params['start_date'])
                ? $this->_dateTime->date('Y-m-d', $params['start_date'])
                : '';
            $end_date = isset($params['end_date'])
                ? $this->_dateTime->date('Y-m-d', $params['end_date'])
                : '';

            $popup_trigger = $params['popup_trigger'] ?? 1;
            $number_x = $params['number_x'] ?? 0;
            if ($popup_trigger == 2 && $number_x > 100) {
                throw new LocalizedException(__('Please enter a number less than or equal to 100 in Number X field.'));
            }

            if (isset($params['popup_template_id'])) {
                $this->popupTemplateResources->load($popupTemplate, $params['popup_template_id']);
            }
            $html_content = $params['html_content'] ?? $popupTemplate->getHtmlContent();
            $css_style = $params['css_style'] ?? $popupTemplate->getCssStyle();

            $background_image = isset($params['background_image'])
                ? $this->json->serialize($params['background_image'])
                : '';

            $visible_stores = $params['visible_stores'] ?? '';
            if (is_array($visible_stores)) {
                $visible_stores = implode(',', $visible_stores);
            }

            $customer_group_ids = $params['customer_group_ids'] ?? '';
            if (is_array($customer_group_ids)) {
                $customer_group_ids = implode(',', $customer_group_ids);
            }

            if (empty($params['popup_name']) || empty($params['popup_type'])) {
                $this->messageManager->addErrorMessage(__('Missing required field(s)!'));
            }

            if (isset($params['popup_id']) && $params['popup_id']) {
                $this->popupResources->load($popupModel, $params['popup_id']);
            }

            $popupModel->setPopupName($params['popup_name']);
            $popupModel->setPopupType($params['popup_type']);
            $popupModel->setPopupStatus($params['popup_status'] ?? 1);
            $popupModel->setStartDate($start_date);
            $popupModel->setEndDate($end_date);
            $popupModel->setPriority($params['priority'] ?? '');
            $popupModel->setPopupTemplateId($params['popup_template_id'] ?? '');
            $popupModel->setPopupTrigger($popup_trigger);
            $popupModel->setNumberX($number_x);
            $popupModel->setPopupPositioninpage($params['popup_positioninpage'] ?? 1);
            $popupModel->setPopupAnimation($params['popup_animation'] ?? 1);
            $popupModel->setVisibleStores($visible_stores);
            $popupModel->setCustomerGroupIds($customer_group_ids);
            $popupModel->setEnableCookieLifetime($params['enable_cookie_lifetime'] ?? 0);
            $popupModel->setCookieLifetime($params['cookie_lifetime'] ?? '');
            $popupModel->setCouponCode($params['coupon_code'] ?? '');
            $popupModel->setThankyouMessage($params['thankyou_message'] ?? '');
            $popupModel->setHtmlContent($html_content);
            $popupModel->setCssStyle($css_style);
            $popupModel->setPopupLink($params['popup_link'] ?? '');
            $popupModel->setEnableFloatingButton($params['enable_floating_button'] ?? 0);
            $popupModel->setFloatingButtonDisplayPopup(
                ($params['enable_floating_button'] == 1 && $params['floating_button_display_popup'] == 1) ? 1 : 0
            );
            $popupModel->setFloatingButtonContent($params['floating_button_content'] ?? '');
            $popupModel->setFloatingButtonPosition($params['floating_button_position'] ?? '');
            $popupModel->setFloatingButtonTextColor($params['floating_button_text_color'] ?? '');
            $popupModel->setFloatingButtonTextHoverColor($params['floating_button_text_hover_color'] ?? '');
            $popupModel->setFloatingButtonBackgroundColor($params['floating_button_background_color'] ?? '');
            $popupModel->setFloatingButtonHoverColor($params['floating_button_hover_color'] ?? '');
            $popupModel->setEnableMailchimp($params['enable_mailchimp'] ?? 0);
            $popupModel->setApiKey($params['api_key'] ?? '');
            $popupModel->setAudienceId($params['audience_id'] ?? '');
            $popupModel->setBackgroundImage($background_image);

            $widgetInstanceData = $this->json->serialize($this->getRequest()->getPost('widget_instance'));
            $popupModel->setWidgetInstance($widgetInstanceData);
            $this->popupResources->save($popupModel);

            $dateValidation = $this->validDateFromTo($start_date, $end_date);
            if ($dateValidation) {
                $popupModel->setPopupStatus(0);
                $this->popupResources->save($popupModel);
                throw new LocalizedException($dateValidation);
            }

            $this->messageManager->addSuccessMessage(__('The Popup has been saved.'));
            /* Invalidate Full Page Cache */
            $this->cache->invalidate('full_page');
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        if ($redirectBack === 'edit') {
            $resultRedirect->setPath(
                '*/*/edit',
                ['id' => $popupModel->getPopupId(), 'back' => null, '_current' => true]
            );
        } else {
            $resultRedirect->setPath('*/*/index');
        }
        return $resultRedirect;
    }
}
