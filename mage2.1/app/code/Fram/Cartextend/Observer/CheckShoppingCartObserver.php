<?php
namespace Fram\Cartextend\Observer;


class CheckShoppingCartObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Fram\Cartextend\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * CheckShoppingCartObserver constructor.
     * @param \Magento\Checkout\Model\Session $_checkoutSession
     * @param \Fram\Cartextend\Helper\Data $helper
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct (
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Fram\Cartextend\Helper\Data $helper,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_checkoutSession = $_checkoutSession;
        $this->helper = $helper;
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;

    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->helper->isModuleEnable())
        {
            return ;
        }
        $validatedSku = null;
        $errorProducts = null;
        $cartData = $this->_checkoutSession->getQuote()->getAllVisibleItems();
        foreach($cartData as $item)
        {
            $validatedSku = $this->helper->validateItem($item);
            if($validatedSku == true && !is_array($validatedSku))
            {
                continue;
            }else{
                $errorProducts[] = $validatedSku;
            }
        }
        if(is_array($errorProducts))
        {
            $controller = $observer->getControllerAction();
            foreach($errorProducts as $item)
            {
                $this->messageManager->addError(sprintf(__('%s need at least %d quantity in cart before go to checkout'),$item['product_name'],(int)$item['qty']));
            }
            $this->redirect->redirect($controller->getResponse(), 'checkout/cart/');
        }

        return;
    }
}