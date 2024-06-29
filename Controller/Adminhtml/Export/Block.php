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
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Block
 */
class Block extends \Magento\Backend\App\Action
{
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
     * @var BlockRepositoryInterface
     */
    protected $blockRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param Filesystem $filesystem
     * @param BlockRepositoryInterface $blockRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        Filesystem $filesystem,
        BlockRepositoryInterface $blockRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
       parent::__construct($context);
       $this->fileFactory = $fileFactory;
       $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
       $this->blockRepository = $blockRepository;
       $this->searchCriteriaBuilder = $searchCriteriaBuilder;
       parent::__construct($context);
    }

    /**
     * Execute action
     */
    public function execute()
    {   
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $cmsBlocks = $this->blockRepository->getList($searchCriteria)->getItems();

        $name = date('m-d-Y-H-i-s');
        $filepath = 'export/block-data-' .$name. '.csv'; // at Directory path Create a Folder Export and FIle
        $this->directory->create('export');

        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $columns = ['title','identifier','content','store_id','is_active'];

        foreach ($columns as $column) 
        {
            $header[] = $column; //storecolumn in Header array
        }

        $stream->writeCsv($header);

        foreach ($cmsBlocks as $item) {
            $itemData = [];
            $itemData[] = $item->getTitle();
            $itemData[] = $item->getIdentifier();
            $itemData[] = $item->getContent();
            $itemData[] = implode(",",$item->getStoreId());
            $itemData[] = $item->getIsActive();
            $stream->writeCsv($itemData);
        }

        $content = [];
        $content['type'] = 'filename'; // must keep filename
        $content['value'] = $filepath;
        $content['rm'] = '1'; //remove csv from var folder

        $csvfilename = 'blocks-data-'.$name.'.csv';
        return $this->fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
    }
}
