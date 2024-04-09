<?php

/**
 * Biz
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Biz.com license that is
 * available through the world-wide-web at this URL:
 * https://www.bizcommerce.com.br/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Biz
 * @package     Biz_Blog
 * @copyright   Copyright (c) Biz (https://www.bizcommerce.com.br/)
 * @license     https://www.bizcommerce.com.br/LICENSE.txt
 */

namespace Koin\Payment\Controller\Adminhtml\Installments\Rules;

use Koin\Payment\Helper\Data as HelperData;
use Koin\Payment\Model\ResourceModel\InstallmentsRulesRepository;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Koin\Payment\Controller\Adminhtml\Installments\Rule;
use Koin\Payment\Model\InstallmentsRulesFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Save
 * @package Biz\Blog\Controller\Adminhtml\Topic
 */
class Save extends Rule
{
    /**
     * Page factory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Result Json Factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        InstallmentsRulesRepository $ruleRepository,
        InstallmentsRulesFactory $rulesFactory,
        Registry $coreRegistry,
        HelperData $helperData,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct(
            $context,
            $ruleRepository,
            $rulesFactory,
            $coreRegistry,
            $helperData
        );
    }

    /**
     * @throws LocalizedException
     */
    public function execute()
    {
        $installmentsRulePostData = $this->getRequest()->getPostValue();
        if ($installmentsRulePostData) {
            $installmentsRule = $this->initRule();
            $installmentsRulePostData['store_ids'] = implode(',', $installmentsRulePostData['store_ids']);
            if (isset($installmentsRulePostData['entity_id']) && !$installmentsRulePostData['entity_id']) {
                unset($installmentsRulePostData['entity_id']);
            }

            $installmentsRule->addData($installmentsRulePostData);

            try {
                $this->ruleRepository->save($installmentsRule);
                $this->messageManager->addSuccessMessage(__('Rule saved.'));

                $resultRedirect = $this->resultRedirectFactory->create();
                if ($this->getRequest()->getParam('back') === 'close') {
                    return $resultRedirect->setPath('*/*/');
                }

                return $resultRedirect->setPath('*/*/edit', ['id' => $installmentsRule->getId()]);
            } catch (\Exception $e) {
                $this->helperData->log($e->getMessage());
                $this->messageManager->addErrorMessage(__('There was an error saving the rule.'));
            }
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
