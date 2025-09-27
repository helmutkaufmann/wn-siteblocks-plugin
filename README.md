# Site Blocks Plugin for Winter CMS

A simple utility plugin that reads a `siteblocks.yaml` file from the active theme's root directory to register `jsonable` fields on the `ThemeData` model. This is particularly useful for making theme customization fields, such as repeaters, work correctly without needing to create a custom plugin for each theme.

---

## 🧐 How it Works

The plugin's logic is straightforward:

1.  On boot, it identifies the currently active theme.
2.  It looks for a file named `siteblocks.yaml` in the root of that theme's directory (e.g., `themes/my-theme/siteblocks.yaml`).
3.  If the file exists, it parses the YAML content, which should be a simple list of field names.
4.  It then programmatically extends the `Cms\Models\ThemeData` model, adding each field name from the list to the model's `$jsonable` array.

This ensures that when you save your theme customization data, any fields listed in `siteblocks.yaml` will be automatically JSON-encoded for database storage and decoded back into a PHP array when you access them in your Twig templates.

---

## 🚀 Usage

Follow these simple steps to use the plugin with your theme.

### Step 1: Create `siteblocks.yaml`

In the root directory of your theme, create a new file named `siteblocks.yaml`.

Filename: `themes/your-theme/siteblocks.yaml`
```yaml
- social_links
- footer_logos
````

This file tells the plugin that the `social_links` and `footer_logos` fields from your theme's customization form should be treated as `jsonable`.

### Step 2: Define the Fields in `theme.yaml`

Now, define the corresponding fields in your theme's configuration file. Repeaters are the most common use case.

Filename: `themes/your-theme/theme.yaml`

```yaml
# ... other theme config ...

form:
  fields:
    social_links:
      label: 'Social Media Links'
      comment: 'Add links to your social media profiles.'
      type: repeater
      prompt: 'Add New Link'
      form:
        fields:
          icon:
            label: 'UIkit Icon Name'
            comment: 'Example: twitter, facebook, instagram'
            type: text
          url:
            label: 'Profile URL'
            type: text
```

### Step 3: Render the Data in Twig

Finally, you can easily loop through this data in your Twig partials or pages. The plugin ensures `this.theme.social_links` is a ready-to-use array.

Filename: `themes/your-theme/partials/site/footer.htm`

```twig
{% if this.theme.social_links %}
    <div class="uk-grid-small uk-flex-center" uk-grid>
        {% for link in this.theme.social_links %}
            <div>
                <a href="{{ link.url }}" class="uk-icon-button" uk-icon="{{ link.icon }}" target="_blank"></a>
            </div>
        {% endfor %}
    </div>
{% endif %}
```

That's it\! You no longer need to create a dedicated plugin just to manage `jsonable` attributes for your theme. This keeps your theme logic self-contained and your projects cleaner.
