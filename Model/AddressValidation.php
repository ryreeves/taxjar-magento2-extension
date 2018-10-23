<?php
/**
 * Taxjar_SalesTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Taxjar
 * @package    Taxjar_SalesTax
 * @copyright  Copyright (c) 2017 TaxJar. TaxJar is a trademark of TPS Unlimited, Inc. (http://www.taxjar.com)
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace Taxjar\SalesTax\Model;

use Taxjar\SalesTax\Api\AddressValidationInterface;
use Taxjar\SalesTax\Model\Configuration as TaxjarConfig;

class AddressValidation implements AddressValidationInterface
{
    /**
     * @var \Taxjar\SalesTax\Model\Client $client
     */
    protected $client;

    /**
     * @var \Taxjar\SalesTax\Model\Logger $logger
     */
    protected $logger;

    protected $regionFactory;

    public function __construct(
        \Taxjar\SalesTax\Model\ClientFactory $clientFactory,
        \Taxjar\SalesTax\Model\Logger $logger,
        \Magento\Directory\Model\RegionFactory $regionFactory
    )
    {
        $this->logger = $logger->setFilename(TaxjarConfig::TAXJAR_ADDRVALIDATION_LOG);
        $this->client = $clientFactory->create();
        $this->client->showResponseErrors(true);

        $this->regionFactory = $regionFactory;
    }

    public function validateAddress($street0 = null, $street1 = null, $city = null, $region = null, $country = null, $postcode = null)
    {
        $addr = [$street0, $street1, $city, $region, $country, $postcode];

        // Validate address data locally
        $addr = $this->validateInput($addr);

        if ($addr === false) {
            return json_encode(['error' => true, 'error_msg' => 'Unable to validate your address.']);
        }

        // Send address to Taxjar for validation
        $response = $this->validateWithTaxjar($addr);

        // Respond with:  suggestions, already correct, error
        if ($response !== false && isset($response->status)) {
            if ($response->status == 'error') {
                return json_encode(['error' => true, 'error_msg' => $response->error_msg]);
            }
            if ($response->status == 'no_changes') {
                return json_eoncode(['no_changes' => 'Address is already valid!']);
            }
            if ($response->status == 'suggestions') {
                return json_encode(['suggested_addresses' => $response->addresses]);
            }
        }

        return json_encode(['error' => true, 'error_msg' => 'Unable to validate your address.']);
    }


    //
    protected function validateInput($addr)
    {
        //TODO: confirm which values are necessary for address validation
        if (empty($addr['street0']) && (empty($addr['city']) || empty($addr['postcode']))) {
            return false;
        }

        return $addr;
    }

    protected function validateWithTaxjar($data)
    {
        try {
            $response = $this->client->postResource('addressValidation', $data);
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage());
            $response = false;
        }
        return $response;
    }

    public function getRegionById($regionId)
    {
        /** @var \Magento\Directory\Model\Region $region */
        $region = $this->regionFactory->create();
        $region->load($regionId);
        return $region;
    }
}
