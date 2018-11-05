<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\Value;

use Akeneo\Pim\Enrichment\Component\Product\Channel\Query\FindActivatedCurrenciesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\PriceFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\PriceCollection;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Factory that creates price collection product values.
 *
 * @internal  Please, do not use this class directly.
 * You must use \Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PriceCollectionValueFactory implements ValueFactoryInterface
{
    /** @var PriceFactory */
    protected $priceFactory;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $supportedAttributeType;

    /** @var FindActivatedCurrenciesInterface */
    protected $findActivatedCurrenciesForChannel;

    /**
     * @param PriceFactory                     $priceFactory
     * @param string                           $productValueClass
     * @param string                           $supportedAttributeType
     * @param FindActivatedCurrenciesInterface $findActivatedCurrenciesForChannel
     */
    public function __construct(
        PriceFactory $priceFactory,
        $productValueClass,
        $supportedAttributeType,
        FindActivatedCurrenciesInterface $findActivatedCurrenciesForChannel
    ) {
        $this->priceFactory = $priceFactory;
        $this->productValueClass = $productValueClass;
        $this->supportedAttributeType = $supportedAttributeType;
        $this->findActivatedCurrenciesForChannel = $findActivatedCurrenciesForChannel;
    }

    /**
     * {@inheritdoc}
     */
    public function create(
        AttributeInterface $attribute,
        $channelCode,
        $localeCode,
        $data,
        bool $ignoreUnknownData = false
    ) {
        $this->checkData($attribute, $data);

        if (null === $data) {
            $data = [];
        }

        $value = new $this->productValueClass(
            $attribute,
            $channelCode,
            $localeCode,
            $this->getPrices($attribute, $data, $channelCode)
        );

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return $attributeType === $this->supportedAttributeType;
    }

    /**
     * Checks if data are valid.
     *
     * @param AttributeInterface $attribute
     * @param mixed              $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkData(AttributeInterface $attribute, $data)
    {
        if (null === $data || [] === $data) {
            return;
        }

        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $attribute->getCode(),
                static::class,
                $data
            );
        }

        foreach ($data as $price) {
            if (!is_array($price)) {
                throw InvalidPropertyTypeException::arrayOfArraysExpected(
                    $attribute->getCode(),
                    static::class,
                    $data
                );
            }

            if (!array_key_exists('amount', $price)) {
                throw InvalidPropertyTypeException::arrayKeyExpected(
                    $attribute->getCode(),
                    'amount',
                    static::class,
                    $data
                );
            }

            if (!array_key_exists('currency', $price)) {
                throw InvalidPropertyTypeException::arrayKeyExpected(
                    $attribute->getCode(),
                    'currency',
                    static::class,
                    $data
                );
            }
        }
    }

    /**
     * Gets a collection of price from prices in standard format.
     *
     * @param AttributeInterface $attribute
     * @param array              $data
     *
     * @return PriceCollection
     */
    protected function getPrices(AttributeInterface $attribute, array $data, ?string $channelCode)
    {
        $prices = new PriceCollection();

        $filteredData = $this->filterByCurrency($data);
        $filteredData = $this->filterByActivatedCurrencies($channelCode, $filteredData);
        $sortedData = $this->sortByCurrency($filteredData);
        foreach ($sortedData as $price) {
            try {
                $newPrice = $this->priceFactory->createPrice($price['amount'], $price['currency']);
            } catch (InvalidPropertyException $e) {
                throw InvalidPropertyException::expectedFromPreviousException($attribute->getCode(), self::class, $e);
            }

            $prices->add($newPrice);
        }

        return $prices;
    }

    /**
     * Sorts the array of prices data by their currency.
     *
     * For example:
     *
     * [
     *     [
     *         'amount'   => 20,
     *         'currency' => 'USD',
     *     ],
     *     [
     *         'amount'   => 100,
     *         'currency' => 'EUR',
     *     ],
     * ]
     *
     * will become:
     *
     * [
     *     [
     *         'amount'   => 100,
     *         'currency' => 'EUR',
     *     ],
     *     [
     *         'amount'   => 20,
     *         'currency' => 'USD',
     *     ],
     * ]
     *
     * @param array $arrayPrices
     *
     * @return array
     */
    protected function sortByCurrency(array $arrayPrices)
    {
        $amounts = [];
        $currencies = [];

        foreach ($arrayPrices as $price) {
            $amounts[] = $price['amount'];
            $currencies[] = $price['currency'];
        }

        $sort = array_multisort($currencies, SORT_ASC, $amounts, SORT_ASC, $arrayPrices);

        if (false === $sort) {
            throw new \LogicException(
                sprintf('Impossible to perform multisort on the following array: %s', json_encode($arrayPrices)),
                0,
                static::class
            );
        }

        return $arrayPrices;
    }

    /**
     * Checks that for each currency there is only one value. If it's the case, this method keeps the last value
     * for the duplicated currency in the given array
     *
     * @param array $data
     *
     * @return array
     */
    protected function filterByCurrency(array $data)
    {
        $uniqueData = [];
        $filteredData = [];

        foreach ($data as $price) {
            $uniqueData[$price['currency']] = $price['amount'];
        }

        foreach ($uniqueData as $currency => $price) {
            $filteredData[] = ['currency' => $currency, 'amount' => $price];
        }

        return $filteredData;
    }

    /**
     * filters out the currencies
     *
     * @param null|string $channelCode
     * @param array       $data
     *
     * @return array
     */
    private function filterByActivatedCurrencies(?string $channelCode, array $data): array
    {
        $activatedCurrencies = $this->getActivatedCurrencies($channelCode);

        return array_values(
            array_filter(
                $data,
                function (array $price) use ($activatedCurrencies) {
                    return in_array($price['currency'], $activatedCurrencies);
                }
            )
        );
    }

    /**
     * @param null|string $channelCode
     *
     * @return array
     */
    private function getActivatedCurrencies(?string $channelCode): array
    {
        if (null === $channelCode) {
            return $this->findActivatedCurrenciesForChannel->forAllChannels();
        }

        return $this->findActivatedCurrenciesForChannel->forChannel($channelCode);
    }
}
