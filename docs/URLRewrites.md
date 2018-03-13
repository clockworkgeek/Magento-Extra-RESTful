# URL Rewrites

Admin only.

## Retrieve

- `GET /api/rest/rewrites`
- `GET /api/rest/rewrites/:id`

### Attributes

- `category_id`
- `description`
- `id_path`: Begins with `product/` for product URLs and `category/` for category URLs.
- `options`: Type of redirect.  One of "" (no), "R" (temporary), "RP" (permanent).
- `product_id`
- `request_path`: Incoming requests are matched against this string.
- `store_id`
- `target_path`: Matching requests are redirected to this module/controller/action.
- `url_rewrite_id`
