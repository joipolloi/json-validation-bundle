<?php

namespace Mrsuh\JsonValidationBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;

trait AnnotationReader
{
    public function getAnnotation(Request $request, string $className): ?object
    {
        if ($request->attributes->has($className)) {
            return $request->attributes->get($className);
        }

        $parts = explode('::', $request->attributes->get('_controller', ''));
        if (count($parts) !== 2) {
            return null;
        }

        $annotationReader = new \Doctrine\Common\Annotations\AnnotationReader();

        $readerAnnotations = $annotationReader->getMethodAnnotations(new \ReflectionMethod($parts[0], $parts[1]));
        foreach ($readerAnnotations as $readerAnnotation) {
            if ($readerAnnotation::class === $className) {
                return $readerAnnotation;
            }
        }

        return null;
    }
}
