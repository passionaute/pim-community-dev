<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\FeatureFlag;

use Akeneo\Pim\Structure\Component\Factory\FamilyFactory;
use Akeneo\Pim\Structure\Component\Updater\FamilyUpdater;
use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\InMemoryFeatureFlags;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FeatureFlagContext implements Context
{
    public function __construct(
        private InMemoryFeatureFlags $featureFlags
    ) {
    }

    /**
     * @BeforeScenario
     */
    public function enabledFeatureFlags(BeforeScenarioScope $scope)
    {
        $tags = $scope->getScenario()->getTags();
        $featureFlagTags = array_filter($tags, fn(string $tag) => preg_match('/-feature-enabled$/', $tag));
        $featureFlagsTagsWithoutSuffix = array_map(fn($tag) => str_replace('-feature-enabled', '', $tag), $featureFlagTags);
        $featureFlags = array_map(fn($tag) => str_replace('-', '_', $tag), $featureFlagsTagsWithoutSuffix);

        foreach ($featureFlags as $featureFlag) {
            $this->featureFlags->enable($featureFlag);
        }
    }
}
