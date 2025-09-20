<?php namespace Mercator\Siteblocks;

use System\Classes\PluginBase;
use Cms\Classes\Theme;
use Cms\Models\ThemeData;
use Illuminate\Support\Facades\File;
use Yaml;
use Log;
// use Winter\Cms\Classes\Theme;

/**
 * Siteblocks Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies.
     */
    public $require = [];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Site Bblocks',
            'description' => 'Reads a siteblocks.yaml file from the active theme to register jsonable fields.',
            'author'      => 'Mercator',
            'icon'        => 'icon-cubes'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {
        // You can register components, permissions, etc. here if needed.
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerJsonableFieldsFromYaml();
    }

    /**
     * Reads 'siteblocks.yaml' from the active theme directory and registers
     * each field listed in the file as jsonable on the ThemeData model.
     */
    public function registerJsonableFieldsFromYaml()
    {

        // 1. Get the currently active theme
        $activeTheme = Theme::getActiveTheme();
        if (!$activeTheme) {
            return;
        }
        $themeName = $activeTheme->getDirName();

        // 2. Define the path to your custom YAML file
        $fieldsYamlPath = themes_path("$themeName/siteblocks.yaml");

        // 3. Check if the file exists
        if (!File::exists($fieldsYamlPath)) {
            return;
        }

        // 4. Parse the YAML file
        $fieldNames = Yaml::parseFile($fieldsYamlPath);

        // 5. Ensure the parsed content is a valid, non-empty array
        if (empty($fieldNames) || !is_array($fieldNames)) {
            return;
        }
        // 6. Loop through each field name and extend the model
        foreach ($fieldNames as $fieldName) {
            // Ensure the field name is a non-empty string before processing
            if (is_string($fieldName) && !empty($fieldName)) {
                ThemeData::extend(function ($model) use ($fieldName) {
                $model->addJsonable($fieldName);
                });
            }
        }
    }
}
