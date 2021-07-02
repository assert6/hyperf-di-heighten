<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperf\Di\Heighten;

use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Aop\PropertyHandlerTrait;
use Hyperf\Di\Definition\PropertyHandlerManager;

class PropertyHandler
{
    use PropertyHandlerTrait;

    protected $object;

    public function handle(object $object)
    {
        $this->object = $object;
        $this->__handlePropertyHandler(get_class($object));
    }

    protected function __handle(string $currentClassName, string $targetClassName, array $properties): array
    {
        $handled = [];
        foreach ($properties as $propertyName) {
            $propertyMetadata = AnnotationCollector::getClassPropertyAnnotation($targetClassName, $propertyName);
            if (! $propertyMetadata) {
                continue;
            }
            foreach ($propertyMetadata as $annotationName => $annotation) {
                if ($callbacks = PropertyHandlerManager::get($annotationName)) {
                    foreach ($callbacks as $callback) {
                        call($callback, [$this->object, $currentClassName, $targetClassName, $propertyName, $annotation]);
                    }
                    $handled[] = $propertyName;
                }
            }
        }

        return $handled;
    }
}
