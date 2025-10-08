<?php namespace Mercator\Siteblocks;

use System\Classes\PluginBase;
use Cms\Classes\Theme as Theme; // Using explicit Winter namespace
use Cms\Models\ThemeData; // Using explicit Winter namespace
use Illuminate\Support\Facades\File;
use Yaml;
use Log;
use Exception;

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
            "name" => "Site Blocks",
            "description" => "Reads a siteblocks.yaml file from the active theme to register jsonable fields.",
            "author" => "Mercator",
            "icon" => "icon-cubes",
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
        try {
            // 1. Get the currently active theme
            // dd(config());
            $activeTheme = config("cms.activeTheme");
            if (!$activeTheme) {
                return;
            }

            // 2. Define the path to your custom YAML file
            $fieldsYamlPath = themes_path("$themeName/siteblocks.yaml");

            // 3. Check if the file exists
            if (!File::exists($fieldsYamlPath)) {
                return;
            }

            // 4. Parse the YAML file and catch parsing errors

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
        } catch (Exception $e) {
            Log::error("siteblocks: Error " . $e->getMessage());
            return;
        }
    }
}