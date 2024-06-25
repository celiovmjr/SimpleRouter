<?php declare(strict_types=1);

namespace SimpleRouter\Application;
use InvalidArgumentException;
use stdClass;

trait RouterTrait
{
    private function uriExists(string $uri): bool
    {
        $uri = preg_replace("~{([^}]*)}~", "([^/]+)", $uri);
        return !!preg_match("@^" . $uri . "$@", $this->request->getUri());
    }

    private function getUriParameters(string $element, string $type): string
    {
        return match ($type) {
            "number" => preg_replace('/[^0-9]/', '', $element),
            "letter" => preg_replace('/[^a-zA-Zà-ú\s]/', '', $element),
            "alpha" => preg_replace('/^[a-zA-ZÀ-ÖØ-öø-ÿ0-9\s]+$/i', '', $element),
            "any" => preg_replace('/(?!)^/', '', $element),
            default => throw new InvalidArgumentException("Invalid parameter type.")
        };
    }

    private function getUriData(string $uri, int $index): mixed
    {
        return array_values(
            array_diff(
                explode("/", $this->request->getUri()),
                explode("/", $uri)
            )
        )[$index] ?? null;
    }

    private function traitUri(string $uri, ?string $path, ?string $group): string
    {
        if (substr($path . $group . $uri, -1) === "/" && strlen($path. $group . $uri) > 1) {
            return substr($path . $group . $uri, 0, -1);
        }
        
        return str_replace("//", "/", ($path . $group . $uri));
    }

    private function setParameters(string $uri, array $parameters): void
    {
        $i = 0;
        $data = [];

        foreach ($parameters["parameters"] as $index => $type) {
            $data[$index] = $this->getUriParameters($this->getUriData($uri, $i++), $type);
        }

        $this->request->setBody($data);
    }
}