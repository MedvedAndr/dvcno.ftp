<?php

namespace App\Helpers;

class AssetsManager {
    protected static array $styles          = [];
    protected static array $scripts         = [];
    protected static array $bundles         = [];
    protected static array $global_options  = [];

    public static function setGlobalOptions(array $options): void
    {
        self::$global_options = array_merge(self::$global_options, $options);
    }

    protected static function applyGlobalOptions(array $options): array
    {
        return array_merge(self::$global_options, $options);
    }

    public static function setStyle(array $options = []): void {
        $options = array_merge([
            'href' => null,
            'version' => null,
            'version_format' => '?v=',
            'attributes' => [],
            'priority' => 100
        ], self::applyGlobalOptions($options));

        if (!$options['href']) {
            return;
        }

        // Убираем GET-параметры для проверки дубликатов
        $clean_href = preg_replace('/\?.*/', '', $options['href']);

        // Проверяем, есть ли уже такой стиль (без учёта версии)
        foreach (self::$styles as $style) {
            if (preg_replace('/\?.*/', '', $style['href']) === $clean_href) {
                return; // Не добавляем повторно
            }
        }

        // Если в href есть "?", удаляем параметры
        if (strpos($options['href'], '?') !== false) {
            $options['href'] = preg_replace('/\?.*/', '', $options['href']);
        }

        // Если версия задана, добавляем её
        if (!in_array($options['version'], [null, false], true)) {
            $options['href'] .= $options['version_format'] . $options['version'];
        }

        self::$styles[] = $options;
    }

    public static function unsetStyle(string $href): void
    {
        self::$styles = array_filter(self::$styles, function ($style) use ($href) {
            return preg_replace('/\?.*/', '', $style['href']) !== preg_replace('/\?.*/', '', $href);
        });
    }

    public static function setScript(array $options): void {
        $options = array_merge([
            'src' => null,
            'version' => null,
            'version_format' => '?v=',
            'attributes' => [],
            'position' => 'head',
            'priority' => 100
        ], self::applyGlobalOptions($options));

        if (!$options['src']) {
            return;
        }

        // Убираем GET-параметры для проверки дубликатов
        $clean_src = preg_replace('/\?.*/', '', $options['src']);

        // Проверяем, есть ли уже такой скрипт (без учёта версии)
        foreach (self::$scripts as $script) {
            if (preg_replace('/\?.*/', '', $script['src']) === $clean_src) {
                return; // Не добавляем повторно
            }
        }

        // Если в href есть "?", удаляем параметры
        if (strpos($options['src'], '?') !== false) {
            $options['src'] = preg_replace('/\?.*/', '', $options['src']);
        }

        // Добавляем версию, если указана
        if (!in_array($options['version'], [null, false], true)) {
            $options['src'] .= $options['version_format'] . $options['version'];
        }

        self::$scripts[] = $options;
    }

    public static function unsetScript(string $src): void
    {
        self::$scripts = array_filter(self::$scripts, function ($script) use ($src) {
            return preg_replace('/\?.*/', '', $script['src']) !== preg_replace('/\?.*/', '', $src);
        });
    }

    public static function setBundle(string $name, array $styles = [], array $scripts = []): void
    {
        self::$bundles[$name] = [
            'styles' => $styles,
            'scripts' => $scripts
        ];
    }

    public static function useBundle(string $name): void
    {
        if (!isset(self::$bundles[$name])) {
            return;
        }

        foreach (self::$bundles[$name]['styles'] as $style) {
            self::setStyle($style);
        }

        foreach (self::$bundles[$name]['scripts'] as $script) {
            self::setScript($script);
        }
    }

    public static function get() {
        return [
            'styles' => self::sortByPriority(self::$styles),
            'scripts' => [
                'head' => self::sortByPriority(array_filter(self::$scripts, fn($script) => $script['position'] === 'head')),
                'body' => self::sortByPriority(array_filter(self::$scripts, fn($script) => $script['position'] === 'body'))
            ]
        ];
    }

    protected static function sortByPriority(array $items): array
    {
        usort($items, fn($a, $b) => $a['priority'] <=> $b['priority']);
        return $items;
    }
}