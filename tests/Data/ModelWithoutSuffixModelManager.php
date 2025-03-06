<?php

declare(strict_types=1);

namespace Ecomail\Phpstan\Rules\Tests\Data;

final class ModelWithoutSuffixModelManager
{
    public function save(): void
    {
        $model = new ModelWithoutSuffix();
        $model->save();
    }
}
