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
            $activeTheme = Theme::getActiveThemeCode();
            if (!$activeTheme) {
                return;
            }
	    $themeName=$activeTheme;

            $fieldNames = [];

            // 2. Fields listed explicitly in the theme's siteblocks.yaml
            $fieldsYamlPath = themes_path("$activeTheme/siteblocks.yaml");
            if (File::exists($fieldsYamlPath)) {
                $listed = Yaml::parseFile($fieldsYamlPath);
                if (is_array($listed)) {
                    $fieldNames = $listed;
                }
            }

            // 3. Fields of type blocks/repeater found in the theme's form file
            $fieldNames = array_merge($fieldNames, $this->detectBlockFields($activeTheme));

            // 4. Register each field as jsonable on ThemeData
            foreach (array_unique($fieldNames) as $fieldName) {
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

    /**
     * Returns the names of all blocks/repeater fields in the theme's form file.
     */
    protected function detectBlockFields(string $themeName): array
    {
        $themeYaml = themes_path("$themeName/theme.yaml");
        if (!File::exists($themeYaml)) {
            return [];
        }

        $form = (Yaml::parseFile($themeYaml)['form'] ?? null);
        $formPath = is_string($form) ? themes_path("$themeName/$form") : null;
        if (!$formPath || !File::exists($formPath)) {
            return [];
        }

        $config = Yaml::parseFile($formPath);
        $fields = array_merge(
            $config['fields'] ?? [],
            $config['tabs']['fields'] ?? [],
            $config['secondaryTabs']['fields'] ?? []
        );

        $names = [];
        foreach ($fields as $name => $definition) {
            if (is_array($definition) && in_array(strtolower($definition['type'] ?? ''), ['blocks', 'repeater'], true)) {
                $names[] = $name;
            }
        }
        return $names;
    }
}
