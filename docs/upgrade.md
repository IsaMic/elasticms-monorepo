# Upgrade

  * [Switch to CK Editor 5](#switch-to-ck-editor-5)
  * [version 6.0.x](#version-60x)
  * [version 5.23.x](#version-523x)
  * [version 5.22.x](#version-522x)
  * [version 5.21.x](#version-521x)
  * [version 5.19.x](#version-519x)
  * [version 5.17.x](#version-517x)
  * [version 5.15.x](#version-515x)
  * [version 5.14.x](#version-514x)
  * [version 5.7.x](#version-57x)
  * [version 5.3.x](#version-53x)
  * [version 4.2.x](#version-42x)
  * [version 4.x](#version-4x)
  * [Tips and tricks](#tips-and-tricks)

## General remarks

 * It's always a good idea to rebuild indexes on upgrade: `emsco:environment:rebuild --all`

## Switch to CK Editor 5 (still in beta)

Require ElasticMS version >= 6.0.0

First activate Bootstrap 5 theme with the environment variable `EMSCO_TEMPLATE_NAMESPACE=EMSAdminUI/bootstrap5`

Then go to your `WYSIWYG` > `WYSIWYG styles sets` configs and change the `attributes.class` by a `classes` attribute

So

```json
[{
    "name": "Dekstop only",
    "element": "div",
    "attributes": {
      "class": "desktop-only row"
    }
}]
```

becomes

```json
[{
    "name": "Dekstop only",
    "element": "div",
    "classes": [
      "desktop-only",
      "row"
    ]
}]
```

Check then [CK Editor styles configuration](https://ckeditor.com/docs/ckeditor5/latest/features/style.html#configuration) for more details.
But defining other HTML attributes than the class attribute is not as easy as it was with CKE4.


And for the `WYSIWYG` > `WYSIWYG profiles` the config must be recreate from scratch.
But basically you can override every default [CK Editor config](https://github.com/ems-project/elasticms/blob/1ea0749ec813ac7bd3afd29a8ce9520654d9a97c/EMS/admin-ui-bundle/assets/js/core/helpers/editor.js#L80. 
Check the [CK Editor builder](https://ckeditor.com/ckeditor-5/online-builder/).
Here is an example.

```json
{
  "ems": {
    "paste": true
  },
  "toolbar": {
    "items": [
      "undo",
      "redo",
      "style",
      "heading",
      "|",
      "bold",
      "italic",
      "bulletedList",
      "numberedList",
      "removeFormat",
      "|",
      "outdent",
      "indent",
      "|",
      "link",
      "imageUpload",
      "insertTable",
      "mediaEmbed",
      "specialCharacters",
      "|",
      "findAndReplace",
      "sourceEditing"
    ],
    "shouldNotGroupWhenFull": true
  }
}
```

## version 6.0.x

### Postgres 17

The `demo/docker-compose.yml` and the `docker/docker-compose.yml` files have been upgraded to use Postgres 17 insterad of Postgres 12.
Unfortunately, you will have to dump your schemas, delete the Postgres's docker volume with the command `docker volume rm ems-mono_postgres`.
And finally, recreate Postgres schemas and reload you dumps.

### Symfony request inputBag

Since symfony 6 the inputBag becomes stricter.

```twig
{# request example http://localhost?filters[]=value1&filters[]=value2&filters[]=value3 #}

{%- set selectedFilters = app.request.query.get('filters') -%} {# can only work with scalar values #}
Expected a scalar value as a 2nd argument to "Symfony\Component\HttpFoundation\InputBag::get()", "array" given."

{%- set selectedFilters = app.request.query.all('filters') -%} {# correct #}
```

### Renamed embed methods in web/skeleton templates

All controller methods have lost any trailing `Action`

* `emsch.controller.embed::renderBlockAction` must be replaced by `emsch.controller.embed::renderEmbed`
* `emsch.controller.embed::renderHierarchyAction` must be replaced by `emsch.controller.embed::renderHierarchy`

E.g.:

```twig
{{ render(controller('emsch.controller.embed::renderHierarchy', {
    'template': '@EMSCH/template/menu.html.twig',
    'parent': 'emsLink',
    'field': 'children',
    'depth': 5,
    'sourceFields': [],
    'args': {'activeChild': emsLink, 'extra': 'test'}
} )) }}
```

### Routes removed

* `template.index` must be replaced by `ems_core_action_index`
* `template.add` must be replaced by `ems_core_action_add`
* `template.edit` must be replaced by `ems_core_action_edit`
* `template.remove` must be replaced by `ems_core_action_delete`

### Deprecated twig filters

* `array_key` must be replaced by `ems_array_key`
* `format_bytes` must be replaced by `ems_format_bytes`
* `locale_attr` must be replaced by `ems_locale_attr`
* `data` must be replaced by `emsco_get`
* `url_generator` must be replaced by `ems_webalize`
* `get_environment` must be replaced by `emsco_get_environment`
* `get_content_type` must be replaced by `emsco_get_contentType`
* `data_label` must be replaced by `emsco_display`
* `emsch_ouuid` must be replaced by `ems_ouuid`
* `array_intersect` must be replaced by `ems_array_intersect`
* `merge_recursive` must be replaced by `ems_array_merge_recursive`
* `inArray` must be replaced by `ems_in_array`
* `soapRequest` must be replaced by `emsco_soap_request`
* `all_granted` must be replaced by `emsco_all_granted`
* `one_granted` must be replaced by `emsco_one_granted`
* `in_my_circles` must be replaced by `emsco_in_my_circles`
* `data_link` must be replaced by `emsco_data_link`
* `i18n` must be replaced by `emsco_i18n`
* `internal_links` must be replaced by `emsco_internal_links`
* `displayname` must be replaced by `emsco_display_name`
* `get_field_by_path` must be replaced by `emsco_get_field_by_path`
* `get_revision_id` must be replaced by the function `emsco_get_revision_id`

### Deprecated twig function

* `cant_be_finalized` must be replaced by `emsco_cant_be_finalized`
* `get_default_environments` must be replaced by `emsco_get_default_environment_names`
* `get_content_types` must be replaced by `emsco_get_content_types`
* `sequence` deprecated and must be replaced by `emsco_sequence`

### New dynamic mapping config which change the elasticsearch indexes

Before version 6 it was not possible to define elasticsearch dynamic mapping config. In other words, before version 6, every fields present in a document, that aren't strictly defined in the content type, a mapping was automatically guessed by elasticsearch.

Since version 6 the default dynamic mapping config has changed. New fields are ignored. These fields will not be indexed or searchable, but will still appear in the _source field of returned hits. These fields will not be added to the mapping, and new fields must be added explicitly into the content type.

You can reactivate the dynamic mapping with this environment variable:  `EMSCO_DYNAMIC_MAPPING='true'`. But it's not recommended. Check the [EMSCO_DYNAMIC_MAPPING documentation](elasticms-admin/environment-variables.md#emscodynamicmapping)

## version 5.25.x

- The `emsco:environment:align` will after publication also unpublish documents that are not aligned.

## version 5.24.x

There is breaking changes in the options of the [File Reader Import](elasticms-cli/commands?id=file-reader) command. 
Command's options must be defined in a json format now, and that JSON can be passed to the `--config` option as:
 * a JSON string
 * a path to a JSON file
 * the hash of a JSON file in the storage services

Please update your worker's jobs.


## version 5.23.x

From this version, the upload of web's assets via the command `emsch:local:upload-assets` wont upload a zip anymore but each assets independently.
The hash provided at the end of the command, is the hash of a JSON containing the structure of the assets within the asset folder, we called those JSON an ElasticMS archive or EMS Archive. E.g.:

```json
[
  {
    "filename": "css/index.css",
    "hash": "9408821ad2bd8f65b7cd7d3913c01218532fc6b2",
    "type": "text/css",
    "size": 244030
  },
  {
    "filename": "img/head/icon.png",
    "hash": "cf4effd785abdb6b58e560c7645cedda5c9fda16",
    "type": "image/png",
    "size": 74640
  },
  {
    "filename": "img/logos/ems-logo.svg",
    "hash": "10b8fa0d6c1e1b1a21b713341424820d379b0a6b",
    "type": "image/svg+xml",
    "size": 24638
  },
  {
    "filename": "img/logos/full-logo.svg",
    "hash": "1f59b7246eb4f6856d42128ad17c4fb59d15f038",
    "type": "image/svg+xml",
    "size": 17415
  },
  {
    "filename": "js/index.js",
    "hash": "010a2066374e5980be0b68d628acd1b624602ab5",
    "type": "text/javascript",
    "size": 190044
  }
]
```
Using those EMS Archive has a huge impact on the performances. Especially at the website warming up.
You can use that EMS Archive's hash where ever you want instead of the old ZIP's hash. E.g. in the Twig function `emsch_assets_version`: 
```twig
{% do emsch_assets_version(include('@EMSCH/template/asset_hash.twig'), null) %}
```

If, for some reason you want, you can continue to use ZIP archives. Or by active the option `--archive=zip` int the `emsch:local:upload-assets` command. Or by manually uploading the ZIP file in the Admin UI. ElasticMS detects if it's a EMR archive or a zip archive. 

It's not required, but warmly recommended to re-upload your assets and update the asset's hash in the website templates.

## version 5.22.x

* Updates on json menu nested template (copy/paste functionality)
* Removed environment variable: `EMSCO_FALLBACK_LOCALE`
* Add new method `getLanguage` on user object

  preferred locale 'nl_FR' returns 'nl'
  ```twig
  {% set language = app.user.localePreferred[0:2] %} //before 
  {% set language = app.user.language %} //now
  
  {# sort based on user language #}
  {% set languages = ['fr', 'nl']|sort((a, b) => a == app.user.language ? -1 : b == app.user.language ? 1 : 0) %}
  ```

## version 5.21.x

* Core twig component Media library: Removed the option `fieldPathOrder`, use new option `sort` (defining all possible sorts)

## version 5.19.x

* The core command ```emsco:release:publish``` has been removed, ```emsco:job:run``` will now publish releases
* All indexes must be rebuilt (as a new field `_image_resized_hash` as been defined in file field's mapping): `emsco:environment:rebuild --all`
* The function `emsch_unzip` is deprecated and should not be used anymore. use the function ems_file_from_archive or the route EMS\CommonBundle\Controller\FileController::assetInArchive instead
  * If the `emsch_unzip` function is used to serve assets via the web server you should use the route [EMS\CommonBundle\Controller\FileController::assetInArchive](dev/client-helper-bundle/routing.md#route-to-assets-in-archive)
  * If the `emsch_unzip` function is used to get local path to an asset you should use the [`ems_file_from_archive`](dev/common-bundle/twig.md#emsfilefromarchive) function
* Xliff command options have been updated
  * The `--filename` option in the `emsco:xliff:extract` command has been replaced by a `--basename` option and does not contains a path anymore, just a file basename.

    Example replace ```emsco:xliff:extract live '{}' nl de title --filename=/tmp/pages-nl-to-de.xlf```
     by ```emsco:xliff:extract live '{}' nl de title --basename=pages-nl-to-de.xlf```
  * In case of warning or error in the `emsco:xliff:update` command the report file is no more available locally. The report is upladed in the admin's storages. The directly get a link to the report you need to specify a `--base-url` option.

    Example ```emsco:xliff:update /tmp/pages-nl-to-de.xlf --base-url=https://my-admin.my-project.tld```
* You should not specify a folder where to expand website assets in the `emsch_assets_version` twig function, in this case the function returns `null`.
  * By default, if you specify `null` (e.g. `{% do emsch_assets_version(include('@EMSCH/template/asset_hash.twig'), null) %}`) as second arguments, the `emsch` assets will have a an url like `/bundles/253b903b1fb3ac30975ae9844a0352a65cdcfa3d/site.css` which urls will be resolved by the route `EMS\CommonBundle\Controller\FileController::assetInArchive`
  * It's also possible the defined you own route for assets in archive, if the route is not immutable (does not contain the archive hash) you must specify the `maxAge` argument (by default it's set to one week): 
```yaml
emsch_demo_asset_in_archive:
  config:
    path: '/assets_in_archive/{path}'
    requirements: { path: .* }
    defaults: { hash: 253b903b1fb3ac30975ae9844a0352a65cdcfa3d, maxAge: 3600 }
    controller: 'EMS\CommonBundle\Controller\FileController::assetInArchive'
```
* Check if you can refactor the use of the `_file_names` attribute in [processor config](dev/common-bundle/processors.md#processor). You should refer to file in an archive (e.g. `8ef54d1e170aede4fa78687f466f35bb6292f4ad:img/banners/banner-home.jpg`) instead of file on the local file system.

## version 5.17.x

* Check routes single colon is deprecated

  Example replace ```emsch.controller.router:redirect``` by ```emsch.controller.router::redirect```

## version 5.15.x

* Form routes are available inside the elasticms-admin
  * Skeleton no longer need to proxy the form routes for making form working inside channels. [#848](https://github.com/ems-project/elasticms/pull/848)
* The form debug routes are no longer 'dev' mode only
* The form debug routes are available inside the elasticms-admin

## version 5.14.x

* All tasks records will be **deleted** after deployment
  * Because we had to upgrade the database schema. [#778](https://github.com/ems-project/elasticms/pull/778)

* If you are using revision versions, you should run ```ems:environment:updatemetafield``` after deployment.

## version 5.7.x

* Added twig function [ems_template_exists](./site-building/twig.md#ems_template_exists)
* Added probe routes `/_readiness` and `/_liveness` for admin and web
* Added header support for [Redirect controller](./dev/client-helper-bundle/routing.md#redirect-controller).
* Added multipart support for [s3](./dev/common-bundle/storages.md#s3)
* Added infinity scrolling for [MediaLibrary](./dev/core-bundle/twig/component.md#media-library)
* Added draggable file upload for [MediaLibrary](./dev/core-bundle/twig/component.md#media-library)
* Added `ems:admin:restore` command
  ```bash
  ems:admin:restore --configs --force
  ems:admin:restore --documents --force
  ```
* Added `filename` option for upload assets command
  ```bash
  emsch:local:upload-assets --filename=/opt/src/local/skeleton/template/asset_hash.twig
  ```
  
  ```twig
  {% set assetPath = emsch_assets_version(include('@EMSCH/template/asset_hash.twig')) %}
  ```
* Deprecated ~~cant_be_finalized~~ use `emsco_cant_be_finalized`

## version 5.3.x

### Deprecated emsch_add_environment 

In dashboards/views and action, we call `emsch_add_environment` for rendering a template from emsch.
If elasticms-admin defines `EMSCH_ENV` and `EMSCH_ENVS`, this is not needed anymore.

```.env
EMSCH_ENV='preview'
EMSCH_ENVS='{"preview":{"alias":"example_preview", "router": false}}' 
```

EMSCH_ENV will mark the preview environment as default, the following can also be done:
```.env
EMSCH_ENVS='{"preview":{"alias":"example_preview", "default": true, "router": false}}' 
```

`Router` false, will disable the clientHelperBundle router the default environment. 
Maybe the skeleton has a match all route defined.

After defining remove the following line from all contentType(s) and dashboard(s).
```twig
{% do emsch_add_environment('preview'|get_environment.alias) %} 
```

## version 4.2.x

### Content type roles in twig
Replace `is_granted(contentType.createRole)` → `is_granted(contentType.roles.create)`
* createRole → roles.create
* editRole → roles.edit

## version 4.x

### Deprecated twig functions
* replace `{% spaceless %}` by `{% apply spaceless %}`
* replace `{% endspaceless %}` by `{% endapply %}`
* replace `{% for key, item in array if test %}` by  `{% for key, item in array|filter(key, item => test) %}`
* replace `transchoice` by `trans`
  * I.e. replace `{{ 'search.results'|transchoice(results.hits.total.value|default(response.total)) -}}`
  * by `{{ 'search.results'|trans({'%count%': results.hits.total.value|default(response.total)}) -}}`

### Asset custom twig functions
* replace `{{ emsch_assets(assets) }}` or `{%- do emsch_assets(assets) -%}` by `{%- set assetPath = emsch_assets_version(assets) -%}`
* replace `{{ assets('resource') }}?{{ assets_hash }}` by `{{ assets('resource', 'emsch') }}`

### Email custom twig functions
```twig
{%- set email = emsco_generate_email(subjectMail) -%}
{%- set email = email.setTo(toMail) -%}
{%- set email = email.setBody(bodyMail, 'text/html') -%}
{%- set email = email.setFrom(fromMail) -%}
{{- emsco_send_email(email) -}}
```
→
```twig
{%- set email = emsco_generate_email(subjectMail) -%}
{%- set email = email.to(toMail) -%}
{%- set email = email.html(bodyMail) -%}
{%- set email = email.from(fromMail) -%}
{{- emsco_send_email(email) -}}
```

### Misc
* replace `/\.hits\.total/` by `{% var.hits.total.value|default(var.hits.total) %}`
  * replace `/\[\'hits\'\][\'total\']/` by `var['hits']['total']['value']|default(var['hits']['total'])`
* remove the template environment
  * align template and preview for route, template and label
  * switch default environment `emsco:content:swith template preview`
* Do a force push to override the document
  * Keep in mind that all ouuids have changed, check in your content types for datalink to template documents
  * Rollback, in the routes.yaml, static templates have been replaced by their OUUID

## Tips and tricks

### Backward compatibility route to old school assets path

New route to redirect to the new asset's url. Route:

```yaml
redirect_asset:
    config:
        path: 'bundles/emsch_assets/{slug}'
        requirements: { slug: '^.+$' }
        controller: 'emsch.controller.router::redirect'
    template_static: template/redirects/asset.json.twig
```

Template (template/redirects/asset.json.twig):

```twig
{% extends '@EMSCH/template/variables.twig' %}

{% block request -%}
{% apply spaceless %}
    {{ { url: asset(app.request.get('slug'), 'emsch') }|json_encode|raw }}
{% endapply %}
{% endblock -%}
```

### Create an old school "Corresponding revision" in the action menu

Create an action for the content types you want with those parameters:

 * Name: `corresponding-revision`
 * Label: `Corresponding revision`
 * Icon: `Archive`
 * Public: unchecked
 * Environment: empty
 * EDit with WYSIWYG: unchecked
 * Role: `User`
 * Render option: `Raw HTML`
 * Body:

```twig
<a href="{{ path('emsco_data_revision_in_environment', {
    environment: environment.name,
    type: contentType.name,
    ouuid: object._id,
}) }}">
	<i class="fa fa-archive"></i> Corresponding revision
</a>
```
