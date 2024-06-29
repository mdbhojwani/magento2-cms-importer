<?php  
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_CmsImporter
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Mdbhojwani\CmsImporter\Model\Block;

/**
 * Class Converter
 */
class ConverterToArray
{
    /**
     * Convert CSV format row to array
     *
     * @param array $row
     * @return array
     */
    public function convertRow($row)
    {
        $data = [];
        foreach ($row as $field => $value) {
            $data['block'][$field] = $value;
        }
        return $data;
    }
}
