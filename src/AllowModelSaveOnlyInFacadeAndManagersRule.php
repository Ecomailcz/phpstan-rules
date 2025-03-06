<?php

declare(strict_types=1);

namespace Ecomail\Phpstan\Rules;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Type;

/**
 * @implements \PHPStan\Rules\Rule<\PhpParser\Node\Expr\MethodCall>
 */
class AllowModelSaveOnlyInFacadeAndManagersRule implements Rule
{
    /**
     * @param list<class-string> $ignoredClasses
     */
    public function __construct(
        private array $ignoredClasses = [],
    ) {
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @return list<\PHPStan\Rules\IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $calledOnType = $scope->getType($node->var);
        $methodName = $node->name instanceof Node\Identifier ? $node->name->toString() : null;

        if ($methodName !== 'save') {
            return [];
        }

        if (!$calledOnType->isObject()->yes()) {
            return [];
        }

        $classReflection = $scope->getClassReflection();

        if ($classReflection === null) {
            return [];
        }

        if (!$this->isModel($calledOnType)) {
            return [];
        }

        if ($this->isIgnoredClass($classReflection)) {
            return [];
        }

        if (str_ends_with($classReflection->getName(), 'Facade')) {
            return [];
        }

        $calledOnClassNames = $calledOnType->getObjectClassNames();

        if ($calledOnClassNames === []) {
            return [$this->createError()];
        }

        $calledOnClassName = $calledOnClassNames[0];
        $calledOnClassParts = strrchr($calledOnClassName, '\\');
        if ($calledOnClassParts === false) {
            return [];
        }
        $calledOnClassShortName = substr($calledOnClassParts, 1);

        $classNameParts = strrchr($classReflection->getName(), '\\');
        if ($classNameParts === false) {
            return [];
        }
        $classShortName = substr($classNameParts, 1);


        if (str_ends_with($classShortName, sprintf('%sModelManager', $calledOnClassShortName))) {
            return [];
        }

        if (str_ends_with($classShortName, sprintf('%sManager', $calledOnClassShortName))) {
            return [];
        }

        return [$this->createError()];
    }

    private function createError(): IdentifierRuleError
    {
        $message = 'Model method save() is allowed to be called only in Facade or related ModelManager.';

        return RuleErrorBuilder::message($message)
            ->identifier('model.save.violation')
            ->build();
    }

    private function isModel(Type $calledOnType): bool
    {
        foreach ($calledOnType->getObjectClassReflections() as $classReflection) {
            if ($classReflection->isSubclassOf(Model::class)) {
                return true;
            }
        }

        return false;
    }

    private function isIgnoredClass(ClassReflection $classReflection): bool
    {
        foreach ($this->ignoredClasses as $ignoredClass) {
            if ($classReflection->is($ignoredClass)) {
                return true;
            }
        }

        return false;
    }
}
