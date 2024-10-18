<?php
/**
 * @author Shilpa G
 * @copyright Copyright (c) 2024 VML All rights reserved.
 * @package Vml_CustomerImport
 *  process CSV file customer data
 */
declare(strict_types=1);

namespace Vml\CustomerImport\Model\Import;

use Vml\CustomerImport\Api\ImportInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DriverInterface;

class CsvImport implements ImportInterface
{
    /**
     * @var DriverInterface
     */
    private $file;

    /**
     * @param DriverInterface $file
     */
    public function __construct(DriverInterface $file)
    {
        $this->file = $file;
    }

    /**
     * @param string $source
     * @return array
     * @throws LocalizedException
     *  process CSV file data and returns valid maapeed data
     */
    public function process(string $source): array
    {
        if (!$this->file->isExists($source) || !$this->file->isReadable($source)) {
            throw new LocalizedException(__('The file %1 not exist or cannot be read.', $source));
        }
        $file = $this->file->fileOpen($source, 'r');
        $header =  $this->file->fileGetCsv($file); // Get the headers
        $data = [];

        $headerMap = [
            'emailaddress' => 'email',
            'fname' => 'firstname',
            'lname' => 'lastname'
        ];

        // Process each row of the CSV
        while (($row = fgetcsv($file)) !== false) {
            $customerData = [];
            foreach ($header as $index => $column) {
                // Map custom header names to the expected Magento field names
                $field = $headerMap[strtolower($column)] ?? strtolower($column);
                $customerData[$field] = $row[$index];
            }
            $data[] = $customerData;
        }
        $this->file->fileClose($file);
        return $data;
    }
}
