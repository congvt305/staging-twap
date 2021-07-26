<?php
declare(strict_types=1);
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 07/27/2021
 */

namespace Eguana\ScheduledImportExport\Block\Adminhtml\Scheduled\Operation\Edit\Form;

use Magento\ImportExport\Model\Import as ImportModel;

/**
 * Scheduled import create/edit form
 *
 * @method Import setGeneralSettingsLabel() setGeneralSettingsLabel(string $value)
 * @method Import setFileSettingsLabel() setFileSettingsLabel(string $value)
 * @method Import setEmailSettingsLabel() setEmailSettingsLabel(string $value)
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */

class Import extends \Magento\ScheduledImportExport\Block\Adminhtml\Scheduled\Operation\Edit\Form
{
    /**
     * Basic import model
     *
     * @var ImportModel
     */
    protected $_importModel;

    /**
     * @var \Magento\Config\Model\Config\Source\Email\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Option\ArrayPool $optionArrayPool
     * @param \Magento\Config\Model\Config\Source\Email\Method $emailMethod
     * @param \Magento\Config\Model\Config\Source\Email\Identity $emailIdentity
     * @param \Magento\ScheduledImportExport\Model\Scheduled\Operation\Data $operationData
     * @param \Magento\Config\Model\Config\Source\Yesno $sourceYesno
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Config\Model\Config\Source\Email\TemplateFactory $templateFactory
     * @param ImportModel $importModel
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Option\ArrayPool $optionArrayPool,
        \Magento\Config\Model\Config\Source\Email\Method $emailMethod,
        \Magento\Config\Model\Config\Source\Email\Identity $emailIdentity,
        \Magento\ScheduledImportExport\Model\Scheduled\Operation\Data $operationData,
        \Magento\Config\Model\Config\Source\Yesno $sourceYesno,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Config\Model\Config\Source\Email\TemplateFactory $templateFactory,
        ImportModel $importModel,
        array $data = []
    ) {
        $this->_templateFactory = $templateFactory;
        $this->_importModel = $importModel;
        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $optionArrayPool,
            $emailMethod,
            $emailIdentity,
            $operationData,
            $sourceYesno,
            $string,
            $data
        );
    }

    /**
     * Prepare form for import operation
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)+
     */
    protected function _prepareForm()
    {
        $this->setGeneralSettingsLabel(__('Import Settings'));
        $this->setFileSettingsLabel(__('Import File Information'));
        $this->setEmailSettingsLabel(__('Import Failed Emails'));

        parent::_prepareForm();
        $form = $this->getForm();

        /** @var $fieldset \Magento\Framework\Data\Form\Element\AbstractElement */
        $fieldset = $form->getElement('operation_settings');

        // add behaviour fields
        $uniqueBehaviors = $this->_importModel->getUniqueEntityBehaviors();
        foreach ($uniqueBehaviors as $behaviorCode => $behaviorClass) {
            $fieldset->addField(
                $behaviorCode,
                'select',
                [
                    'name' => 'behavior',
                    'title' => __('Import Behavior'),
                    'label' => __('Import Behavior'),
                    'required' => true,
                    'disabled' => true,
                    'values' => $this->_optionArrayPool->get($behaviorClass)->toOptionArray()
                ],
                'entity'
            );
        }

        $fieldset->addField(
            ImportModel::FIELD_FIELD_SEPARATOR,
            'text',
            [
                'name' => 'file_info[' . ImportModel::FIELD_FIELD_SEPARATOR . ']',
                'label' => __('Field separator'),
                'title' => __('Field separator'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            ImportModel::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR,
            'text',
            [
                'name' => 'file_info[' . ImportModel::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR . ']',
                'label' => __('Multiple value separator'),
                'title' => __('Multiple value separator'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'force_import',
            'select',
            [
                'name' => 'force_import',
                'title' => __('On Error'),
                'label' => __('On Error'),
                'required' => true,
                'values' => $this->_operationData->getForcedImportOptionArray()
            ],
            'freq'
        );

        $form->getElement(
            'email_template'
        )->setValues(
            $this->_templateFactory->create()->setPath('magento_scheduledimportexport_import_failed')->toOptionArray()
        );

        $fieldset = $form->getElement('file_settings');
        $fieldset->addField(
            'file_name',
            'text',
            [
                'name' => 'file_info[file_name]',
                'title' => __('File Name'),
                'label' => __('File Name'),
                'required' => true
            ],
            'file_path'
        );

        $note = 'For Type "Local Server" use relative path to Magento installation,
                                e.g. var/export, var/import, var/export/some/dir';
        $fieldset->addField(
            ImportModel::FIELD_NAME_IMG_FILE_DIR,
            'text',
            [
                'name' => 'file_info[' .ImportModel::FIELD_NAME_IMG_FILE_DIR . ']',
                'label' => __('Images File Directory'),
                'title' => __('Images File Directory'),
                'required' => false,
                'class' => 'input-text',
                'note' => __($note),
            ],
            'file_name'
        );

        /** @var $element \Magento\Framework\Data\Form\Element\AbstractElement */
        $element = $form->getElement('entity');
        $element->setData('onchange', 'varienImportExportScheduled.handleEntityTypeSelector();');

        /** @var $operation \Magento\ScheduledImportExport\Model\Scheduled\Operation */
        $operation = $this->_coreRegistry->registry('current_operation');
        $defaultFormValues = [
            ImportModel::FIELD_FIELD_SEPARATOR => ',',
            ImportModel::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR =>
                ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
        ];
        $this->_setFormValues(array_merge($defaultFormValues, $operation->getData()));

        return $this;
    }

    /**
     * Add file information fieldset to form
     *
     * @param \Magento\Framework\Data\Form $form
     * @param \Magento\ScheduledImportExport\Model\Scheduled\Operation $operation
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _addFileSettings($form, $operation)
    {
        $fieldset = $form->addFieldset('file_settings', ['legend' => $this->getFileSettingsLabel()]);

        $fieldset->addField(
            'server_type',
            'select',
            [
                'name' => 'file_info[server_type]',
                'title' => __('Server Type'),
                'label' => __('Server Type'),
                'required' => true,
                'values' => $this->_operationData->getServerTypesOptionArray()
            ]
        );

        $fieldset->addField(
            'file_path',
            'text',
            [
                'name' => 'file_info[file_path]',
                'title' => __('File Directory'),
                'label' => __('File Directory'),
                'required' => true,
                'note' => __(
                    'For Type "Local Server" use relative path to Magento installation, '
                    . ' e.g. var/export, var/import, var/export/some/dir'
                )
            ]
        );

        $fieldset->addField(
            'host',
            'text',
            [
                'name' => 'file_info[host]',
                'title' => __('FTP Host[:Port]'),
                'label' => __('FTP Host[:Port]'),
                'class' => 'ftp-server sftp-server server-dependent'
            ]
        );

        $fieldset->addField(
            'user',
            'text',
            [
                'name' => 'file_info[user]',
                'title' => __('User Name'),
                'label' => __('User Name'),
                'class' => 'ftp-server sftp-server server-dependent'
            ]
        );

        $fieldset->addField(
            'password',
            'password',
            [
                'name' => 'file_info[password]',
                'title' => __('Password'),
                'label' => __('Password'),
                'class' => 'ftp-server sftp-server server-dependent'
            ]
        );

        $fieldset->addField(
            'file_mode',
            'select',
            [
                'name' => 'file_info[file_mode]',
                'title' => __('File Mode'),
                'label' => __('File Mode'),
                'values' => $this->_operationData->getFileModesOptionArray(),
                'class' => 'ftp-server sftp-server server-dependent'
            ]
        );

        $fieldset->addField(
            'passive',
            'select',
            [
                'name' => 'file_info[passive]',
                'title' => __('Passive Mode'),
                'label' => __('Passive Mode'),
                'values' => $this->_sourceYesno->toOptionArray(),
                'class' => 'ftp-server sftp-server server-dependent'
            ]
        );

        return $this;
    }

}
