# CMS Pages

Admin only.
Guests and customers can already access a fully rendered page at it's URL.
That's how CMS works.

## Retrieve

- `GET /api/rest/pages`
- `GET /api/rest/pages/:id`

`:id` may be either the numeric or text ID.

### Attributes

- `content`: HTML content.
- `content_heading`: Normally displayed as a H1 above the content.
- `custom_layout_update_xml`
- `custom_root_template`
- `custom_theme`
- `custom_theme_from`
- `custom_theme_to`
- `identifier`: A short text ID.
- `is_active`: Boolean.
- `layout_update_xml`
- `meta_description`
- `meta_keywords`
- `page_id`: A numeric ID.
- `root_template`
- `stores`: Store IDs for which this block is enabled.
- `title`: Used as a meta title and generally appears in a browser's title bar.
- `update_time`: The last modified time.

## Create / Update

- `POST /api/rest/pages`
- `PUT /api/rest/pages/:id`

`:id` may be either the numeric or text ID.
The textual `identifier` must be unique for each store in `stores`.
This allows several pages with the same identifier to exist for different stores,
each with similar content but in a different language.
The store ID "0" is synonymous with "all store views" and functions as a default page.
Stores without a page matching an `identifier` will fall back to store 0's equivalent.

### Attributes

- `content`: Partial HTML content.
- `content_heading`: Normally displayed as a H1 above the content.
- `custom_layout_update_xml`
- `custom_root_template`: One of "empty", "one_column", "two_columns_left", "two_columns_right", "three_columns", or any custom page layout handle.
- `custom_theme`
- `custom_theme_from`
- `custom_theme_to`
- `identifier`: **Required**. A short text ID.  Unlike most IDs this can be changed after creation.  Care should be taken to avoid leaving references to the old identifier.
- `is_active`: Boolean.
- `layout_update_xml`
- `meta_description`
- `meta_keywords`
- `root_template`: **Required**. One of "empty", "one_column", "two_columns_left", "two_columns_right", "three_columns", or any custom page layout handle.
- `stores`: **Required**. Store IDs for which this block is enabled.
- `title`: Used as a meta title and generally appears in a browser's title bar.

## Delete

- `DELETE /api/rest/pages/:id`

`:id` may be either the numeric or text ID.
