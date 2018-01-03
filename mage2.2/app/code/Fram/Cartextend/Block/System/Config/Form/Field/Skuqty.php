<?php
namespace Fram\Cartextend\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class Skuqty  extends AbstractFieldArray {
    protected $_addAfter = TRUE;

    /**
     * @var
     */
    protected $_addButtonLabel;
    /**
     * Construct
     */
    protected function _construct() {
        parent::_construct();
        $this->_addButtonLabel = __('Add More');
    }

    /**
     * Prepare to render the columns
     */
    protected function _prepareToRender() {
        $this->addColumn('sku', ['label' => __('SKU')]);
        $this->addColumn('qty', ['label' => __('QTY')]);
        $this->_addAfter       = FALSE;
        $this->_addButtonLabel = __('Add More');
    }
}