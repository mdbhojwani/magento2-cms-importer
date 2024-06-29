<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_CmsImporter
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Mdbhojwani\CmsImporter\Controller\Adminhtml\Import;

use Magento\Backend\App\Action\Context;
use Magento\Store\Model\Store;
use Magento\Framework\File\Csv;
use Mdbhojwani\CmsImporter\Model\Block\ConverterToArray;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\PageFactory;

/**
 * Class Save
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var Csv
     */
    protected $csv;

    /**
     * @var ConverterToArray
     */
    protected $converterToArray;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * Allowed Extensions
     */
    protected $allowedExtensions = ['csv'];
    protected $fileId = 'file';

    /**
     * @param Context $context
     * @param Csv $csv
     * @param ConverterToArray $converterToArray
     * @param BlockFactory $blockFactory
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        Csv $csv,
        ConverterToArray $converterToArray,
        BlockFactory $blockFactory,
        PageFactory $pageFactory
    ) {
        $this->csv = $csv;
        $this->converterToArray = $converterToArray;
        $this->blockFactory = $blockFactory;
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        try {
            if(@$_FILES['import_file']) {
                $file = $_FILES['import_file'];
                if ($data['import_type'] == 1) {
                    $this->getBlock($file);
                } elseif ($data['import_type'] == 2) {
                    $this->getPage($file);
                } else {
                    $this->messageManager->addError(__("Please Select Import Type"));
                    $this->_redirect('*/*/');
                }
            }else{
                $this->messageManager->addError(__("Please Upload CSV file"));
            }
            
            $this->_redirect('*/*/');
        }catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            $this->_redirect('*/*/');
        }
    }

    /**
     * @param array $data
     * @return \Magento\Cms\Model\Block
     */
    protected function saveCmsBlock($data)
    {
        $cmsBlock = $this->blockFactory->create();
        $cmsBlock->getResource()->load($cmsBlock, $data['identifier']);
        if (!$cmsBlock->getData()) {
            $cmsBlock->setData($data);
        } else {
            $cmsBlock->addData($data);
        }
        $cmsBlock->setStores([Store::DEFAULT_STORE_ID]);
        $cmsBlock->setIsActive(1);
        $cmsBlock->save();
        return $cmsBlock;
    }

    /**
     * 
     * @param array $data
     * @return void
     *
     */
    protected function getBlock($file)
    {
        if($file['size'] && $file){
            if (!isset($file['tmp_name'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
            }
            $rows = $this->csv->getData($file['tmp_name']);
            $header = array_shift($rows);
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;
                $data = $this->converterToArray->convertRow($row);
                $cmsBlock = $this->saveCmsBlock($data['block']);
                $cmsBlock->unsetData();
            }
            $this->messageManager->addSuccess(__("Content Data Updated"));
        }else{
            $this->messageManager->addError(__("File is empty"));
        }
    }

    /**
     * 
     * @param array $data
     * @return void
     *
     */
    protected function getPage($file)
    {
        if ($file['size'] && $file) {
            if (!isset($file['tmp_name'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid file upload attempt.'));
            }
            $rows = $this->csv->getData($file['tmp_name']);
            $header = array_shift($rows);
            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$header[$key]] = $value;
                }
                $row = $data;
                $this->pageFactory->create()
                        ->load($row['identifier'], 'identifier')
                        ->addData($row)
                        ->setStores([Store::DEFAULT_STORE_ID])
                        ->save();
            }
            $this->messageManager->addSuccess(__("Content Data Updated"));
        }else{
            $this->messageManager->addError(__("File is empty"));
        }
    }
}
