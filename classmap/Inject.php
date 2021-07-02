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
namespace Hyperf\Di\Annotation;

use Hyperf\Di\BetterReflectionManager;
use Hyperf\Di\Exception\AnnotationException;
use Hyperf\Di\ReflectionManager;
use Hyperf\Di\TypesFinderManager;
use phpDocumentor\Reflection\Types\Object_;
use PhpDocReader\PhpDocReader;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionProperty;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Inject extends AbstractAnnotation
{
    /**
     * @var string
     */
    public $value;

    /**
     * @var bool
     */
    public $required = true;

    /**
     * @var bool
     */
    public $lazy = false;

    /**
     * @var PhpDocReader
     */
    private $docReader;

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->docReader = make(PhpDocReader::class);
    }

    public function collectProperty(string $className, ?string $target): void
    {
        try {
            $reflectionClass = BetterReflectionManager::reflectClass($className);
            $properties = $reflectionClass->getImmediateProperties();
            $reflectionProperty = $properties[$target] ?? null;
            if (! $reflectionProperty && ! $reflectionProperty = ReflectionManager::reflectClass($className)->getProperty($target)) {
                $this->value = '';
                return;
            }

            $this->value = $this->getRealClass($reflectionProperty, $reflectionClass);

            if (empty($this->value)) {
                throw new AnnotationException("The @Inject value is invalid for {$className}->{$target}");
            }

            if ($this->lazy) {
                $this->value = 'HyperfLazy\\' . $this->value;
            }
            AnnotationCollector::collectProperty($className, $target, static::class, $this);
        } catch (AnnotationException $exception) {
            if ($this->required) {
                throw $exception;
            }
            $this->value = '';
        } catch (\Throwable $exception) {
            throw new AnnotationException("The @Inject value is invalid for {$className}->{$target}. Because {$exception->getMessage()}");
        }
    }

    /**
     * @param ReflectionProperty|\ReflectionProperty $reflectionProperty
     */
    private function getRealClass($reflectionProperty, ReflectionClass $reflectionClass): string
    {
        if ($reflectionProperty instanceof \ReflectionProperty) {
            return $this->docReader->getPropertyClass($reflectionProperty);
        }

        if ($reflectionProperty->hasType()) {
            return $reflectionProperty->getType()->getName();
        } else {
            $reflectionTypes = TypesFinderManager::getPropertyFinder()->__invoke($reflectionProperty, $reflectionClass->getDeclaringNamespaceAst());
            if (isset($reflectionTypes[0]) && $reflectionTypes[0] instanceof Object_) {
                return ltrim((string) $reflectionTypes[0], '\\');
            }
        }
        return '';
    }
}
