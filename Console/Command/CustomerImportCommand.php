<?php
/**
 * @author Shilpa G
 * @copyright Copyright (c) 2024 VML All rights reserved.
 * @package Vml_CustomerImport
 */
declare(strict_types=1);

namespace Vml\CustomerImport\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Vml\CustomerImport\Api\CustomerImportInterface;

class CustomerImportCommand extends Command
{
    private const PROFILE = 'profile';
    private const SOURCE = 'source';

    /**
     * @var CustomerImportInterface
     */
    protected $customerImport;

    /**
     * CustomerImportCommand constructor
     * @param CustomerImportInterface $customerImport
     */
    public function __construct(CustomerImportInterface $customerImport)
    {
        $this->customerImport = $customerImport;
        parent::__construct();
    }

    /**
     * configure function to set import command and arguments
     * @return void
     */
    protected function configure()
    {
        $this->setName('customer:import')
            ->setDescription('Import customers from different profiles')
            ->addArgument(self::PROFILE, InputArgument::REQUIRED, 'Profile Name')
            ->addArgument(self::SOURCE, InputArgument::REQUIRED, 'Source File');
        parent::configure();
    }

    /**
     * to execute customer:import command from terminal
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Take profile argument sample-json or sample-csv inour case
        $profile = $input->getArgument(self::PROFILE);
        //Take input file to import
        $source = $input->getArgument(self::SOURCE);

        try {
            // Import customers using the specified profile and source file
            $this->customerImport->import($profile, $source);
            $output->writeln("<info>Customer data imported successfully!</info>");
        } catch (\Exception $e) {
            $output->writeln("<error>Error during customer data import: " . $e->getMessage() . "</error>");
        }

        return Cli::RETURN_SUCCESS;
    }
}
