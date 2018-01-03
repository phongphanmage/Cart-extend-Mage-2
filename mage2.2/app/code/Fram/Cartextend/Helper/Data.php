<?php
namespace Fram\Cartextend\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
/**
 * Class Data
 * @package Fram\Cartextend\Helper
 */
class Data extends AbstractHelper
{

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;
    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    protected $storeCode;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @const ENABLE MODULE
     */
    CONST ENABLE_MODULE = 'cartextend/general/enable';
    /**
     * @const SKU QTY DATA
     */
    CONST SKU_QTY_DATA = 'cartextend/general/skuqty';
    /**
     * Data constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param Context $context
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        Context $context
    )
    {
        parent::__construct($context);
        $this->storeCode = $storeManager->getStore();
        $this->productRepository = $productRepository;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->serializer = $serializer;

    }
    /**
     * @return mixed
     */
    public function isModuleEnable()
    {
        return $this->scopeConfig->getValue(self::ENABLE_MODULE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeCode
        );
    }

    /**
     * @return mixed
     */
    public function getValidatedData()
    {
        return $this->scopeConfig->getValue(self::SKU_QTY_DATA,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeCode
        );
    }

    /**
     * @param $item
     * @return array|bool
     */
    public function validateItem($item)
    {

        $sku = $item->getSku();
        $qty = $item->getQty();
        if(in_array($sku,$this->getAllValidatedSku()))
        {
            $validatedData = $this->serializer->unserialize($this->getValidatedData());
            $filtered = array_filter($validatedData, function($element) use($sku) {return $element['sku'] == $sku;});
            $qtyNeeded = array_values($filtered)[0]['qty'];
            if($qty >= $qtyNeeded)
            {
                $result = true;
            }else{
                $product = $this->loadMyProduct($sku);
                $result = array(
                    'product_name'=> $product->getName(),
                    'qty'=> $qtyNeeded
                );

            }
        }else{
            $result = true;
        }
        return $result;
    }

    /**
     * @param $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function loadMyProduct($sku)
    {
        return $this->productRepository->get($sku);
    }

    /**
     * @return array
     */
    public function getAllValidatedSku()
    {
        $sku = [];
        $validatedData = $this->serializer->unserialize($this->getValidatedData());

        foreach($validatedData as $data)
        {
            $sku[] = $data['sku'];
        }
        return $sku;
    }
}