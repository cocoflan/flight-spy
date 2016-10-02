<?php
/**
 * @author Jean Silva <me@jeancsil.com>
 * @license MIT
 */
namespace Jeancsil\FlightSpy\Api\DataTransfer;

use Jeancsil\FlightSpy\Command\Entity\Parameter;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Input\InputInterface;

class SessionParametersFactory
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var array
     */
    private $configCache;

    /**
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param string $configFile
     * @return SessionParameters[]
     */
    public function createFromConfigFile($configFile)
    {
        $configurations = json_decode(file_get_contents($configFile), true);

        $parameters = [];
        $maxPrices = [];
        foreach ($configurations as $configuration) {
            $parameter = $this->createFromArray($configuration);

            $parameters[] = $parameter;
            $maxPrices[] = $this->getValue(Parameter::MAX_PRICE);

            $this->logger->debug([$parameter]);
        }

        return $parameters;
    }

    /**
     * @param InputInterface $input
     * @return SessionParameters
     */
    public function createFromInput(InputInterface $input)
    {
        $parameters = new SessionParameters();
        $parameters->apiKey = $input->getOption(Parameter::API_KEY) ?: $this->apiKey;
        $parameters->originPlace = $input->getOption(Parameter::FROM);
        $parameters->destinationPlace = $input->getOption(Parameter::TO);
        $parameters->outboundDate = $input->getOption(Parameter::DEPARTURE_DATE);
        $parameters->inboundDate = $input->getOption(Parameter::RETURN_DATE);
        $parameters->locationSchema = $input->getOption(Parameter::LOCATION_SCHEMA);
        $parameters->country = $input->getOption(Parameter::COUNTRY);
        $parameters->currency = $input->getOption(Parameter::CURRENCY);
        $parameters->locale = $input->getOption(Parameter::LOCALE);
        $parameters->adults = $input->getOption(Parameter::ADULTS);
        $parameters->cabinClass = $input->getOption(Parameter::CABIN_CLASS);
        $parameters->children = $input->getOption(Parameter::CHILDREN);
        $parameters->infants = $input->getOption(Parameter::INFANTS);
        $parameters->groupPricing = $input->getOption(Parameter::GROUP_PRICING);

        return $parameters;
    }

    /**
     * @param array $configuration
     * @return SessionParameters
     */
    private function createFromArray(array $configuration)
    {
        $this->configCache = $configuration;

        $parameters = new SessionParameters();
        $parameters->setMaxPrice($this->getValue(Parameter::MAX_PRICE));
        $parameters->apiKey = $this->getValue(Parameter::API_KEY, $this->apiKey);
        $parameters->originPlace = $this->getValue(Parameter::FROM);
        $parameters->destinationPlace = $this->getValue(Parameter::TO);
        $parameters->outboundDate = $this->getValue(Parameter::DEPARTURE_DATE);
        $parameters->inboundDate = $this->getValue(Parameter::RETURN_DATE);
        $parameters->locationSchema = $this->getValue(Parameter::LOCATION_SCHEMA, Parameter::DEFAULT_LOCATION_SCHEMA);
        $parameters->country = $this->getValue(Parameter::COUNTRY);
        $parameters->currency = $this->getValue(Parameter::CURRENCY);
        $parameters->locale = $this->getValue(Parameter::LOCALE);
        $parameters->adults = $this->getValue(Parameter::ADULTS, Parameter::DEFAULT_ADULTS);
        $parameters->cabinClass = $this->getValue(Parameter::CABIN_CLASS, Parameter::DEFAULT_CABIN_CLASS);
        $parameters->children = $this->getValue(Parameter::CHILDREN, Parameter::DEFAULT_CHILDREN);
        $parameters->infants = $this->getValue(Parameter::INFANTS, Parameter::DEFAULT_INFANTS);
        $parameters->groupPricing = $this->getValue(Parameter::GROUP_PRICING, Parameter::DEFAULT_GROUP_PRICING);

        return $parameters;
    }

    /**
     * @param string $parameter
     * @param mixed $defaultValue
     * @return mixed
     */
    private function getValue($parameter, $defaultValue = null)
    {
        if (isset($this->configCache[$parameter])) {
            return $this->configCache[$parameter];
        }

        return $defaultValue;
    }
}