<?php
/**
 * @author Shilpa G
 * @copyright Copyright (c) 2024 VML All rights reserved.
 * @package Vml_CustomerImport
 */
declare(strict_types=1);

namespace Vml\CustomerImport\Model;

use Vml\CustomerImport\Api\CustomerImportInterface;
use Vml\CustomerImport\Api\ImportInterface;
use Vml\CustomerImport\Model\Import\CsvImport;
use Vml\CustomerImport\Model\Import\JsonImport;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\AlreadyExistsException;

class CustomerImport implements CustomerImportInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var array
     */
    protected $profiles;

    /**
     * CustomerImport Constructor
     * @param CustomerRepositoryInterface $customerRepository
     * @param DataObjectHelper $dataObjectHelper
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param array $profiles
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        DataObjectHelper $dataObjectHelper,
        CustomerInterfaceFactory $customerDataFactory,
        $profiles = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->customerDataFactory = $customerDataFactory;
        $this->profiles = $profiles;
    }

    /**
     * get the appropriat profile class and import data
     * @param string $profile
     * @param string $source
     * @return void
     * @throws LocalizedException
     */
    public function import(string $profileType, string $sourceFile)
    {
        //get profile class
        if (!isset($this->profiles[$profileType])) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Profile '%1' is not supported.", $profileType));
        }
        $profile = $this->profiles[$profileType];
        $customers = $profile->process($sourceFile);
        foreach ($customers as $customerData) {
            try {
                $this->saveOrUpdateCustomer($customerData);
            } catch (\Exception $e) {
                throw new LocalizedException(__("Error importing customer: %1", $e->getMessage()));
            }
        }
    }

    /**
     * @param array $customerData
     * @return void
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    protected function saveOrUpdateCustomer(array $customerData)
    {
        // Check if the customer exists by email
        try {
            $existingCustomer = $this->customerRepository->get($customerData['email']);
            $this->updateCustomer($existingCustomer, $customerData);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // If the customer doesn't exist, create a new customer
            $this->createCustomer($customerData);
        }
    }

    /**
     * save new customer
     * @param array $customerData
     * @return void
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    protected function createCustomer(array $customerData)
    {
        $customer = $this->customerDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customer,
            $customerData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $this->customerRepository->save($customer);
    }

    /**
     * update exiting customer
     * @param $existingCustomer
     * @param array $customerData
     * @return void
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    protected function updateCustomer($existingCustomer, array $customerData)
    {
        $this->dataObjectHelper->populateWithArray(
            $existingCustomer,
            $customerData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $this->customerRepository->save($existingCustomer);
    }
}
