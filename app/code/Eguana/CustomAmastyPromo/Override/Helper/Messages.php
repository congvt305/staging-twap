<?php
namespace Eguana\CustomAmastyPromo\Override\Helper;

use Amasty\Promo\Helper\Messages as MessagesAlias;

/**
 * Promo Messages for customer
 * Class Messages
 */
class Messages extends MessagesAlias
{
    /**
     * @param string|\Magento\Framework\Phrase $message
     * @param bool $isError
     * @param bool $showEachTime
     * @param bool $isSuccess
     */
    public function showMessage($message, $isError = true, $showEachTime = false, $isSuccess = false)
    {
        $displayErrors = $this->scopeConfig->isSetFlag(
            'ampromo/messages/display_error_messages',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$displayErrors && $isError) {
            return;
        }

        $displaySuccess = $this->scopeConfig->isSetFlag(
            'ampromo/messages/display_success_messages',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (!$displaySuccess && !$isError) {
            return;
        }

        $all = $this->messageManager->getMessages(false);

        foreach ($all as $existingMessage) {
            if ($message == $existingMessage->getText()) {
                return;
            }
        }

        if ($isError && $this->_request->getParam('debug')) {
            // method addErrorMessage is not applicable because of html escape
            $this->messageManager->addError($message);
        } elseif ($showEachTime || !$this->isMessageWasShown($message)) {
            if ($isSuccess) {
                $this->messageManager->addSuccess($message);
            } else {
                // method addNoticeMessage is not applicable because of html escape
                $this->messageManager->addNotice($message);
            }
        }
    }

    /**
     * @param string|\Magento\Framework\Phrase $message
     *
     * @return bool
     */
    private function isMessageWasShown($message)
    {
        if ($message instanceof \Magento\Framework\Phrase) {
            $messageText = $message->getText();
            $messageArguments = $message->getArguments();
            if ($messageArguments) {
                if(isset($messageArguments[0])) {
                    $messageText .= $messageArguments[0];
                }
            }
        } else {
            $messageText = $message;
        }
        $arr = $this->_checkoutSession->getAmpromoMessages();
        if (!is_array($arr)) {
            $arr = [];
        }
        if (!in_array($messageText, $arr)) {
            $arr[] = $messageText;
            $this->_checkoutSession->setAmpromoMessages($arr);

            return false;
        }

        return true;
    }
}
