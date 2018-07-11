<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Product\Configuration\Item;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Product;

/**
 * {@inheritdoc}
 */
class ItemProductResolver implements ItemResolverInterface
{
    /**
     * Path in config to the setting which defines if parent or child product should be used to generate a thumbnail.
     */
    const CONFIG_THUMBNAIL_SOURCE = 'checkout/cart/configurable_product_image';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getFinalProduct(ItemInterface $item) : ProductInterface
    {
        /**
         * Show parent product thumbnail if it must be always shown according to the related setting in system config
         * or if child thumbnail is not available.
         */
        $parentItem = $item->getProduct();
        $config = $this->scopeConfig->getValue(
            self::CONFIG_THUMBNAIL_SOURCE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $childProduct = $this->getChildProduct($item);
        $childThumbnail = $childProduct->getData('thumbnail');
        $finalProduct =
            ($config == Thumbnail::OPTION_USE_PARENT_IMAGE) || (!$childThumbnail || $childThumbnail == 'no_selection')
            ? $parentItem
            : $childProduct;
        return $finalProduct;
    }

    /**
     * Get item configurable child product
     *
     * @param ItemInterface $item
     * @return Product
     */
    private function getChildProduct(ItemInterface $item) : Product
    {
        $option = $item->getOptionByCode('simple_product');
        $product = $item->getProduct();
        if ($option) {
            $product = $option->getProduct();
        }
        return $product;
    }
}
