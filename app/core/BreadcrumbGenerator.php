<?php

namespace App\Core;

/**
 * BreadcrumbGenerator class is responsible for generating breadcrumb navigation data.
 */
class BreadcrumbGenerator
{
    /**
     * Generates breadcrumbs based on the provided path segments.
     *
     * @param array $segments An array of path segments.
     * @param string $basePath The base path of the application.
     * @return array An array of breadcrumbs with titles and paths.
     */
    public function generate(array $segments, string $basePath = ''): array
    {
        $breadcrumbs = [
            [
                "title" => "Home",
                "path" => $basePath ?: "/",
            ],
        ];

        $currentPath = $basePath;
        foreach ($segments as $segment) {
            if ($segment === "") {
                continue;
            }
            $currentPath .= "/" . $segment;
            $title = ucwords(str_replace("-", " ", $segment));
            $breadcrumbs[] = [
                "title" => $title,
                "path" => $currentPath,
            ];
        }

        return $breadcrumbs;
    }
}
