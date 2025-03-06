<?php

declare(strict_types=1);

namespace Ecomail\Phpstan\Rules\Tests\Data;

final class SaveModelFacade
{
    public function void(): void
    {
        $model = new TestModel();
        $model->save();
    }
}
