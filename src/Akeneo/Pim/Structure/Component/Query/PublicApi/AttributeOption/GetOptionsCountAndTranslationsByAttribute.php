<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption;

/**
 * @author    Adrien Migaire <adrien.migaire@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetOptionsCountAndTranslationsByAttribute
{
    public function search(SearchAttributeOptionsParameters $searchParameters): array;
}
