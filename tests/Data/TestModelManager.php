<?php

declare(strict_types=1);

namespace Ecomail\Phpstan\Rules\Tests\Data;

final class TestModelManager
{
    public function go(): void
    {
        $model = new TestModel();
        $model->save();
    }
}
