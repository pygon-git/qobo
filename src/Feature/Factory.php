<?php
namespace App\Feature;

use App\Feature\Config;
use Cake\Controller\Component\AuthComponent;
use Cake\Core\Configure;
use Cake\Http\ServerRequest;
use Cake\Log\Log;
use InvalidArgumentException;
use RuntimeException;

class Factory
{
    const FEATURE_INTERFACE = 'App\\Feature\\FeatureInterface';
    const FEATURE_SUFFIX = 'Feature';

    protected static $initialized = false;

    protected static $defaultOptions = ['name' => 'Base', 'active' => true];

    /**
     * Initialize feature.
     *
     * @return void
     */
    public static function init()
    {
        if (static::$initialized) {
            return;
        }
        // set factory as initialized.
        static::$initialized = true;

        $features = Configure::read('Features');

        foreach ($features as $feature => $options) {
            $config = static::getConfig($feature);
            $class = static::getFeatureClass($config);
            $feature = new $class($config);
            $feature->isActive() ? $feature->enable() : $feature->disable();
        }
    }

    /**
     * Get feature method.
     *
     * @param string $name Feature name
     * @return \App\Feature\FeatureInterface
     */
    public static function get($name)
    {
        if (!static::$initialized) {
            static::init();
        }

        if (!is_string($name)) {
            throw new InvalidArgumentException('Feature name must be a string.');
        }

        $config = static::getConfig($name);
        $class = static::getFeatureClass($config);

        return new $class($config);
    }

    /**
     * Feature Config getter method.
     *
     * @param string $feature Feature name
     * @return \App\Feature\Config
     */
    protected static function getConfig($feature)
    {
        $options = Configure::read('Features.' . $feature);

        if (!empty($options)) {
            $options['name'] = $feature;
        }

        if (empty($options)) {
            Log::debug('Feature [' . $feature . '] does not exist.');
            $options = static::$defaultOptions;
        }

        return new Config($options);
    }

    /**
     * Feature class name getter.
     *
     * @param \App\Feature\Config $config Config instance
     * @return string
     */
    protected static function getFeatureClass(Config $config)
    {
        $name = explode(DS, $config->get('name'));
        $name = implode('\\', $name);

        $class = __NAMESPACE__ . '\\Type\\' . $name . static::FEATURE_SUFFIX;
        if (!class_exists($class)) {
            throw new RuntimeException(
                'Class [' . $class . '] does not exist.'
            );
        }

        if (!in_array(static::FEATURE_INTERFACE, class_implements($class))) {
            throw new RuntimeException(
                'Feature class [' . $class . '] does not implement [' . static::FEATURE_INTERFACE . '].'
            );
        }

        return $class;
    }
}
