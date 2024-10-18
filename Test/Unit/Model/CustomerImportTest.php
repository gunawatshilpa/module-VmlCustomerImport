<?php
namespace Vml\CustomerImport\Test\Unit\Model;

use Vml\CustomerImport\Model\CustomerImport;
use Vml\CustomerImport\Api\ImportInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use PHPUnit\Framework\TestCase;

class CustomerImportTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $objectManagerMock;

    /** @var ImportInterface|MockObject */
    private $profileProcessorMock;

    /**
     * @var customerRepositoryInterface||MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var CustomerInterfaceFactory|MockObject
     */
    protected $customerDataFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var CustomerImport
     */
    private $customerImport;

    /**
     * @var ProfileInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $importMock;

    protected function setUp(): void
    {
        // Mock the Object Manager
        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $this->profileProcessorMock = $this->createMock(ImportInterface::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->customerDataFactoryMock = $this->createMock(CustomerInterfaceFactory::class);

        // Create mock profile
        $this->importMock = $this->createMock(ImportInterface::class);
        $profiles = [
            'sample-csv' => $this->importMock,
            'sample-json' => $this->importMock
        ];
        $this->customerImport = new CustomerImport(
            $this->customerRepositoryMock,
            $this->dataObjectHelperMock,
            $this->customerDataFactoryMock,
            $profiles
        );
    }

    public function testImportWithValidProfile()
    {
        // Prepare parameters
        $profileType = 'sample-csv';
        $sourceFile = 'var/import/sample.csv';

        // Mock the process method of the profile to ensure it's called
        $this->importMock->expects($this->once())
            ->method('process')
            ->with($sourceFile);

        // Call the method under test
        $this->customerImport->import($profileType, $sourceFile);
        $this->assertTrue(true);
    }

    public function testImportWithInvalidProfile()
    {
        $profile = 'xml-profile';
        $source = 'sample.csv';
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage("Profile 'xml-profile' is not supported.");
        $this->customerImport->import($profile, $source);
    }

}
