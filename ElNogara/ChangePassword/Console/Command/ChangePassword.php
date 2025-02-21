<?php

declare(strict_types=1);

namespace ElNogara\ChangePassword\Console\Command;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Encryption\EncryptorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ChangePassword extends Command
{
    const CUSTOMER_ID = 'customer_id';
    const NEW_PASSWORD = 'new_password';

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param EncryptorInterface $encryptor
     * @param State $appState
     */
    public function __construct(
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly EncryptorInterface $encryptor,
        private readonly State $appState
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('elnogara:customer:change-password')
            ->setDescription('Change user password from ID.')
            ->addArgument(self::CUSTOMER_ID, InputArgument::REQUIRED, 'client id')
            ->addArgument(self::NEW_PASSWORD, InputArgument::REQUIRED, 'new password');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $customerId = $input->getArgument(self::CUSTOMER_ID);
        $newPassword = $input->getArgument(self::NEW_PASSWORD);

        try {
            $this->appState->setAreaCode('adminhtml');

            $customer = $this->customerRepository->getById($customerId);
            if (!$customer->getId()) {
                throw new NoSuchEntityException(__('Customer %1 not found.', $customerId));
            }

            $this->customerRepository->save($customer, $this->encryptor->getHash($newPassword, true));
            $output->writeln("<info>Password for customer $customerId changed ;D</info>");
        } catch (\Exception $e) {
            $output->writeln("<error>Error Ç.Ç : {$e->getMessage()}</error>");
        }

        return Command::SUCCESS;
    }
}
