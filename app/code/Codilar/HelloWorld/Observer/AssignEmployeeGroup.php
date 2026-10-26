<?php
namespace Codilar\HelloWorld\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class AssignEmployeeGroup implements ObserverInterface
{
    /**
     * Email domain that identifies company employees
     */
    private const EMPLOYEE_DOMAIN = '@codilar.com';

    /**
     * Customer group name to assign (create it in Admin > Customers > Customer Groups)
     */
    private const EMPLOYEE_GROUP_CODE = 'Employee';

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var GroupCollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        GroupCollectionFactory $groupCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * Runs after a customer registers on the storefront
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $email = strtolower((string) $customer->getEmail());

        if (substr($email, -strlen(self::EMPLOYEE_DOMAIN)) !== self::EMPLOYEE_DOMAIN) {
            return;
        }

        $group = $this->groupCollectionFactory->create()
            ->addFieldToFilter('customer_group_code', self::EMPLOYEE_GROUP_CODE)
            ->getFirstItem();

        if (!$group->getId()) {
            $this->logger->warning('Employee customer group not found, skipping group assignment.');
            return;
        }

        $customer->setGroupId((int) $group->getId());
        $this->customerRepository->save($customer);

        $this->logger->info(sprintf('Customer %s assigned to Employee group.', $email));
    }
}
