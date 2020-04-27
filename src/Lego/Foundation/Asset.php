<?php

namespace JA\Lego\Foundation;

class Asset
{
    const PATH = 'packages/ja/lego';
    const TYPE_SCRIPT = 'script';
    const TYPE_STYLE = 'style';

    protected $all = [];
    protected $mix = [];

    public function __construct()
    {
        $manifest = @file_get_contents(public_path(self::PATH . '/mix-manifest.json'));
        $this->mix = json_decode($manifest, JSON_OBJECT_AS_ARRAY);

        $this->reset();
    }

    protected function mix($path)
    {
        return isset($this->mix[$path]) ? $this->mix[$path] : $path;
    }

    protected function add($type, $path, $prefix = self::PATH)
    {
        if (is_array($path)) {
            foreach ($path as $line) {
                $this->add($type, $line, $prefix);
            }

            return;
        }

        if (!isset($this->all[$type])) {
            $this->reset($type);
        }

        if ($prefix) {
            $path = $prefix . $this->mix('/' . ltrim($path, '/'));
        }

        if (!in_array($path, $this->all[$type])) {
            $this->all[$type][] = $path;
        }
    }

    public function css($path, $prefix = self::PATH)
    {
        $this->add(static::TYPE_STYLE, $path, $prefix);
    }

    public function styles()
    {
        return $this->get(static::TYPE_STYLE);
    }

    public function js($path, $prefix = self::PATH)
    {
        $this->add(static::TYPE_SCRIPT, $path, $prefix);
    }

    public function scripts()
    {
        return $this->get(static::TYPE_SCRIPT);
    }

    protected function get($type)
    {
        return isset($this->all[$type]) ? array_values($this->all[$type]) : [];
    }

    public function reset($type = null)
    {
        $dependencies = config('ja-lego.assets.dependencies') ?? [];
        if (is_null($type)) {
            $this->all = [];
            foreach (array_keys($dependencies) as $type) {
                $this->reset($type);
            }

            return;
        }

        $this->all[$type] = [];
        if (isset($dependencies[$type])) {
            foreach ($dependencies[$type] as $dependencyInfo) {
                if ($dependencyInfo['load']) {
                    $this->add($type, $dependencyInfo['path']);
                }
            }
        }
    }
}
