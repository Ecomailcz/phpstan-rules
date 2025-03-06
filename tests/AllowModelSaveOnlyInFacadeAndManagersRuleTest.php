<?php

declare(strict_types=1);

namespace Ecomail\Phpstan\Rules\Tests;

use Ecomail\Phpstan\Rules\AllowModelSaveOnlyInFacadeAndManagersRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

final class AllowModelSaveOnlyInFacadeAndManagersRuleTest extends RuleTestCase
{
    public function testRuleShouldPass(): void
    {
        $this->analyse([
            __DIR__ . '/Data/SaveModelFacade.php',
            __DIR__ . '/Data/TestModelManager.php',
            __DIR__ . '/Data/ModelWithoutSuffixModelManager.php',
        ], []);
    }

    public function testRuleShouldFail(): void
    {
        $this->analyse([__DIR__ . '/Data/SaveModelClass.php'], [
            [
                'Model method save() is allowed to be called only in Facade or related ModelManager.',
                12,
            ],
        ]);
    }

    protected function getRule(): Rule
    {
        return new AllowModelSaveOnlyInFacadeAndManagersRule();
    }
}
