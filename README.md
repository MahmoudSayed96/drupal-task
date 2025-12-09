# Evaluation Task

Related to point # 2 for content type creation

- Created programmatically but i can easy make content type bundle like Article
  Structure > Content Type > Create content type.

## Cache

Using `max-age` for set the time by seconds for caching.
use `max-age => 0` for no cache
use `max-age => Cache::PERMANENT` for Cache forever

## Written Tasks

1. **Debugging Task**
   > Explain the Drupal error Infinite recursion in Cache::mergeMaxAges() and cover causes, bad vs good examples, and production debugging
   > This issue happened when set cache `max-age` with wrong value like `[]`, string value

**Debugging**:

- Enable debug render in `services.yml` file

```bash
parameters:
 renderer.config:
   debug: true
```

- Enable `logs` in `settings.php`

```php
 $settings['error_level'] = 2;
```

to show logs run `drush ws --full`

- Disable render cache

```php
  $settings['cache']['bins']['render'] = 'cache.backend.null';
```

2. **Design Reasoning Task**

> Explain caching design for a high-traffic site where output differs by user role.

- Use Cache Contexts Caching should be based on user role
  \_Anonymous user cache full-page
  \_Authenticated user cached based on user role

```php
'#cache' => [
  'contexts' => ['user.roles'],
]
```

- Using cache for each block/component/region
- Cache Tags for automatic invalidation

```php
  '#cache' => [
  'tags' => ['node_list', 'content_item:'.$entity_id],
]
```

(**Thanks for this questions i learned new information when i searched about more details** 3>)

## SETUP PROJECT

Follow `SETUP.md` file for all steps to install project.
