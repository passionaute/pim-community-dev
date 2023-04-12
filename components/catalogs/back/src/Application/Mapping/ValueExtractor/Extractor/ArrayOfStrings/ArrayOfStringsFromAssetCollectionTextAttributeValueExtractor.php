<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ArrayOfStrings;

use Akeneo\Catalogs\Application\Mapping\ValueExtractor\Extractor\ArrayOfStringsValueExtractorInterface;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ArrayOfStringsFromAssetCollectionTextAttributeValueExtractor implements ArrayOfStringsValueExtractorInterface
{
    public function extract(
        array $product,
        string $code,
        ?string $locale,
        ?string $scope,
        ?array $parameters,
    ): null | array {
        // not supported in CE
        return null;
    }

    public function getSupportedSourceType(): string
    {
        return self::SOURCE_TYPE_ATTRIBUTE_ASSET_COLLECTION;
    }

    public function getSupportedSubSourceType(): ?string
    {
        return self::SUB_SOURCE_TYPE_ATTRIBUTE_TEXT;
    }

    public function getSupportedTargetType(): string
    {
        return self::TARGET_TYPE_ARRAY_OF_STRINGS;
    }

    public function getSupportedTargetFormat(): ?string
    {
        return null;
    }
}