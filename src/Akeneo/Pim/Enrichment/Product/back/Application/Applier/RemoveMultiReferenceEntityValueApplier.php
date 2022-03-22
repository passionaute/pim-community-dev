<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Application\Applier;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\RemoveMultiReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RemoveMultiReferenceEntityValueApplier implements UserIntentApplier
{
    public function __construct(
        private ObjectUpdaterInterface $productUpdater,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function apply(UserIntent $userIntent, ProductInterface $product, int $userId): void
    {
        Assert::isInstanceOf($userIntent, RemoveMultiReferenceEntityValue::class);
        Assert::allString($userIntent->recordCodes());
        Assert::allStringNotEmpty($userIntent->recordCodes());

        $formerRecordCodeCollection = $product->getValue(
            $userIntent->attributeCode(),
            $userIntent->localeCode(),
            $userIntent->channelCode(),
        );

        if (null === $formerRecordCodeCollection) {
            return;
        }

        $formerRecordCodesAsString = array_map(fn ($value) => $value->__toString(), $formerRecordCodeCollection->getData());
        $updatedRecordCodes = \array_values(\array_unique(array_diff($formerRecordCodesAsString, $userIntent->recordCodes())));

        if ([] === array_diff($formerRecordCodeCollection->getData(), $updatedRecordCodes)) {
            return;
        }

        $this->productUpdater->update(
            $product,
            [
                'values' => [
                    $userIntent->attributeCode() => [
                        [
                            'locale' => $userIntent->localeCode(),
                            'scope' => $userIntent->channelCode(),
                            'data' => $updatedRecordCodes,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getSupportedUserIntents(): array
    {
        return [RemoveMultiReferenceEntityValue::class];
    }
}
