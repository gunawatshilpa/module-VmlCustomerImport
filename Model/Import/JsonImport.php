<?php
/**
 * @author Shilpa G
 * @copyright Copyright (c) 2024 VML All rights reserved.
 * @package Vml_CustomerImport
 * process Json file customer data
 */
declare(strict_types=1);

namespace Vml\CustomerImport\Model\Import;

use Vml\CustomerImport\Api\ImportInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Exception\LocalizedException;

class JsonImport implements ImportInterface
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
     * process Json file data and returns valid maapeed data
     */
    public function process(string $source): array
    {
        if (!$this->file->isExists($source) || !$this->file->isReadable($source)) {
            throw new LocalizedException(__('The file %1 not exist or cannot be read.', $source));
        }
        $data = json_decode($this->file->fileGetContents($source), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new LocalizedException(__('Invalid JSON format: %1', json_last_error_msg()));
        }

        // Map the JSON keys to Magento's expected customer fields
        $mappedData = [];
        foreach ($data as $item) {
            $customerData = [
                'firstname' => $item['fname'] ?? $item['firstname'] ?? null,
                'lastname' => $item['lname'] ?? $item['lastname'] ?? null,
                'email' => $item['emailaddress'] ?? $item['email'] ?? null
            ];
            $mappedData[] = $customerData;
        }

        return $mappedData;
    }
}
