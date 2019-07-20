<?php

namespace Xigen\Faker\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Customer helper
 */
class Customer extends AbstractHelper
{
    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerInterfaceFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressInterfaceFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepositoryInterface;

    /**
     * @var \Magento\Customer\Api\Data\RegionInterface
     */
    protected $regionInterface;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptorInterface;

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * Customer constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerInterfaceFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressInterfaceFactory
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface
     * @param \Magento\Customer\Api\Data\RegionInterface $regionInterface
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerInterfaceFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressInterfaceFactory,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface,
        \Magento\Customer\Api\Data\RegionInterface $regionInterface,
        \Magento\Framework\Encryption\EncryptorInterface $encryptorInterface,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
    ) {
        $this->customerInterfaceFactory = $customerInterfaceFactory;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->addressInterfaceFactory = $addressInterfaceFactory;
        $this->addressRepositoryInterface = $addressRepositoryInterface;
        $this->regionInterface = $regionInterface;
        $this->encryptorInterface = $encryptorInterface;
        // https://packagist.org/packages/fzaninotto/faker
        $this->faker = \Faker\Factory::create(\Xigen\Faker\Helper\Data::LOCALE_CODE);
        $this->logger = $logger;
        $this->customerCollectionFactory = $customerCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Create random customer.
     * @param int $websiteId
     * @return \Magento\Customer\Model\Data\Customer
     */
    public function createCustomer($websiteId = 1)
    {
        $customer = $this->customerInterfaceFactory
            ->create()
            ->setWebsiteId($websiteId)
            ->setEmail($this->faker->safeEmail)
            ->setFirstname($this->faker->firstName)
            ->setLastname($this->faker->lastName);

        try {
            $hashedPassword = $this->encryptorInterface->getHash($this->faker->word, true);
            $customer = $this->customerRepositoryInterface->save($customer, $hashedPassword);

            return $customer;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Create address for supplied customerId.
     * @param \Magento\Customer\Model\Data\Customer $customer
     * @return \Magento\Customer\Model\Address
     */
    public function createCustomerAddress(\Magento\Customer\Model\Data\Customer $customer)
    {
        if ($customer && $customer->getId()) {
            $address = $this->addressInterfaceFactory
                ->create()
                ->setCompany($this->faker->company)
                ->setPrefix($this->faker->title)
                ->setFirstname($customer->getFirstname())
                ->setLastname($customer->getLastname())
                ->setStreet([$this->faker->streetAddress])
                ->setCity($this->faker->city)
                ->setRegion($this->regionInterface->setRegion($this->faker->county))
                ->setPostcode($this->faker->postcode)
                ->setCountryId(\Xigen\Faker\Helper\Data::COUNTRY_CODE)
                ->setCustomerId($customer->getId())
                ->setTelephone($this->faker->phoneNumber)
                ->setIsDefaultBilling(true)
                ->setIsDefaultShipping(true);

            try {
                $address = $this->addressRepositoryInterface->save($address);

                return $address;
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }

    /**
     * Return array of random Customer IDs.
     * @param int $limit
     * @param int $websiteId
     * @return array
     */
    public function getRandomCustomerId($limit = 1, $websiteId = 1)
    {
        $customers = $this->getRandomCustomer($limit, $websiteId);
        $ids = [];
        foreach ($customers as $customer) {
            $ids[] = $customer->getId();
        }

        return $ids;
    }

    /**
     * Return collection of random products.
     * @param int $limit
     * @param int $websiteId
     * @return \Magento\Catalog\Model\ResourceModel\Customer\Collection
     */
    public function getRandomCustomer($limit = 1, $websiteId = 1)
    {
        $collection = $this->customerCollectionFactory
            ->create()
            ->addAttributeToSelect('*')
            ->setPageSize($limit);

        if ($websiteId) {
            $collection->addAttributeToFilter('website_id', ['eq' => $websiteId]);
        }

        $collection->getSelect()->order('RAND()');

        return $collection;
    }

    /**
     * Get customer by Id.
     * @param int $customerId
     * @return \Magento\Customer\Model\Data\Customer
     */
    public function getById($customerId)
    {
        try {
            return $this->customerRepositoryInterface->getById($customerId);
        } catch (\Exception $e) {
            $this->logger->critical($e);

            return false;
        }
    }
}
