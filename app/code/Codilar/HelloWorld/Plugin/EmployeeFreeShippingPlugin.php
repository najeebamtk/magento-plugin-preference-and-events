<?php
namespace Codilar\HelloWorld\Plugin;

use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use Magento\OfflineShipping\Model\Carrier\Flatrate;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;

class EmployeeFreeShippingPlugin
{
    /**
     * Customer group that gets free Flat Rate shipping
     */
    private const EMPLOYEE_GROUP_CODE = 'Employee';

    /**
     * @var GroupCollectionFactory
     */
    private $groupCollectionFactory;

    public function __construct(GroupCollectionFactory $groupCollectionFactory)
    {
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * After plugin: runs after Flatrate::collectRates() has calculated the rate
     *
     * @param Flatrate $subject
     * @param Result|bool $result
     * @param RateRequest $request
     * @return Result|bool
     */
    public function afterCollectRates(Flatrate $subject, $result, RateRequest $request)
    {
        if (!$result instanceof Result) {
            return $result;
        }

        $items = $request->getAllItems();
        if (empty($items)) {
            return $result;
        }

        $customerGroupId = (int) reset($items)->getQuote()->getCustomerGroupId();
        if ($customerGroupId !== $this->getEmployeeGroupId()) {
            return $result;
        }

        foreach ($result->getAllRates() as $rate) {
            $rate->setPrice(0);
            $rate->setCost(0);
        }

        return $result;
    }

    /**
     * Finds the Employee group ID by its name (0 if the group does not exist)
     *
     * @return int
     */
    private function getEmployeeGroupId()
    {
        return (int) $this->groupCollectionFactory->create()
            ->addFieldToFilter('customer_group_code', self::EMPLOYEE_GROUP_CODE)
            ->getFirstItem()
            ->getId();
    }
}
