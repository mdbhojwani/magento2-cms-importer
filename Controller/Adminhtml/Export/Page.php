<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_CmsImporter
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types=1);

namespace Mdbhojwani\CmsImporter\Controller\Adminhtml\Export;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Page
 */
class Page extends \Magento\Backend\App\Action
{
    /**
     * Class Contants
     */
    const EXPORT_DIR    = 'export/';
    const FILE_PREFIX   = 'page-data-';
    const FILE_SUFIX    = '.csv';

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param PageRepositoryInterface $pageRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        PageRepositoryInterface $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
       parent::__construct($context);
       $this->fileFactory = $fileFactory;
       $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
       $this->pageRepository = $pageRepository;
       $this->searchCriteriaBuilder = $searchCriteriaBuilder;
       parent::__construct($context);
    }

    /**
     * Execute action
     */
    public function execute()
    {   
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $cmsBlocks = $this->pageRepository->getList($searchCriteria)->getItems();

        $fileName = self::FILE_PREFIX . date('m-d-Y-H-i-s') . self::FILE_SUFIX;
        $filepath = self::EXPORT_DIR . $fileName;
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $columns = ['title','page_layout','meta_keyword','meta_description','identifier','content_heading','content','store_id', 'is_active'];

        foreach ($columns as $column) {
            $header[] = $column;
        }

        $stream->writeCsv($header);

        foreach ($cmsBlocks as $item) {
            $itemData = [];
            $itemData[] = $item->getTitle();
            $itemData[] = $item->getPageLayout();
            $itemData[] = $item->getMetaKeywords();
            $itemData[] = $item->getMetaDescription();
            $itemData[] = $item->getIdentifier();
            $itemData[] = $item->getContentHeading();
            $itemData[] = $item->getContent();
            $itemData[] = implode(",",$item->getStoreId());
            $itemData[] = $item->getIsActive();
            $stream->writeCsv($itemData);
        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
