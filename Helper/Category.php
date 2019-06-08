<?php

namespace Xigen\Faker\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Category helper
 */
class Category extends AbstractHelper
{
    const COUNTRY_CODE = 'GB';
    const LOCALE_CODE = 'en_GB';

    private $faker;
    private $logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Api\Data\CategoryInterfaceFactory $categoryInterfaceFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        // https://packagist.org/packages/fzaninotto/faker
        $this->faker = \Faker\Factory::create(self::LOCALE_CODE);
        $this->logger = $logger;
        $this->categoryInterfaceFactory = $categoryInterfaceFactory;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context);
    }

    /**
     * Create random category.
     *
     * @param int $storeId
     *
     * @return \Magento\Category\Model\Data\Category
     */
    public function createCategory($storeId = 0)
    {
        $parent = $this->getRandomCategory(1);
        if ($parent && $parent->getSize() > 0) {
            $parent = $parent->getFirstItem();

            $category = $this->categoryFactory
                ->create()
                ->setName(ucwords($this->faker->words(rand(1, 5), true)))
                ->setDecription($this->faker->paragraphs(rand(1, 4), true))
                ->setIsActive(rand(1, 2))
                ->setIncludeInMenu(rand(1, 2))
                ->setParentId($parent->getId())
                ->setPath($parent->getPath())
                ->setStoreId($storeId);

            try {
                $category->save();

                return $category;
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }

    /**
     * Return array of random IDs.
     *
     * @param int $limit
     *
     * @return array
     */
    public function getRandomCategoryId($limit = 1)
    {
        $categories = $this->getRandomCategory($limit);
        $ids = [];
        foreach ($categories as $category) {
            $ids[$category->getId()] = $category->getId();
        }

        return $ids;
    }

    /**
     * Return collection of random categories.
     *
     * @param int $limit
     *
     * @return \Magento\Catalog\Model\ResourceModel\Category\Collection
     */
    public function getRandomCategory($limit = 1)
    {
        $collection = $this->categoryCollectionFactory
            ->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', ['gt' => 2])
            ->setPageSize($limit);

        $collection->getSelect()->order('RAND()');

        return $collection;
    }
}
