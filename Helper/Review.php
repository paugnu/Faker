<?php

namespace Xigen\Faker\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Review helper
 */
class Review extends AbstractHelper
{
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Xigen\Faker\Helper\Customer $customerHelper,
        \Xigen\Faker\Helper\Product $productHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
    ) {
        // https://packagist.org/packages/fzaninotto/faker
        $this->faker = \Faker\Factory::create(\Xigen\Faker\Helper\Data::LOCALE_CODE);
        $this->logger = $logger;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->reviewFactory = $reviewFactory;
        $this->customerHelper = $customerHelper;
        $this->productHelper = $productHelper;
        $this->storeManagerInterface = $storeManagerInterface;
        parent::__construct($context);
    }

    /**
     * Create random review.
     *
     * @param int $storeId
     *
     * @return \Magento\Review\Model\Data\Review
     */
    public function createReview($storeId = 1)
    {
        $product = $this->productHelper
            ->getRandomIds(1);
        
        $stores = $this->storeManagerInterface->getStores(true, false);
        foreach ($stores as $store) {
            if ($store->getStoreId() == $storeId) {
                $website = $store->getWebsiteId();
            }
        }
        
        $customer = $this->customerHelper
            ->getRandomCustomerId(1, $website);

        $review = $this->reviewFactory
            ->create()
            ->setEntityPkValue($product[0])
            ->setStatusId(rand(1, 3))
            ->setTitle(ucwords($this->faker->words(rand(1, 5), true)))
            ->setDetail($this->faker->paragraphs(rand(1, 4), true))
            ->setEntityId(1)
            ->setStoreId($storeId)
            ->setStores(1)
            ->setCustomerId($customer[0])
            ->setNickname($this->faker->firstName)
            ->save();

        try {
            $review->save();

            return $review;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
