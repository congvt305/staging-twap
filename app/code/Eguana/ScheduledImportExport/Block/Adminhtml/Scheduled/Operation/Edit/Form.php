<?php
declare(strict_types=1);
/**
 * @author Eguana Team
 * Created by PhpStorm
 * User: Sonia Park
 * Date: 07/26/2021
 */

namespace Eguana\ScheduledImportExport\Block\Adminhtml\Scheduled\Operation\Edit;

class Form extends \Magento\ScheduledImportExport\Block\Adminhtml\Scheduled\Operation\Edit\Form
{
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
