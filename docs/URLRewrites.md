# URL Rewrites

Admin only.

A redirect's `request_path` is compared against incoming requests.
If a match is found then:
- If `target_path` is a fully qualified URL an HTTP redirect is performed,
  either a "Permanent (301)" response if `options` is `RP` or a "Temporary (302)" response.
- If `target_path` is a path and `options` is not empty an HTTP redirect is performed to the store's base URL plus target path.
- Otherwise the target path is routed internally either as "module/controller/action" or some other pattern such as CMS pages.

## Retrieve

- `GET /api/rest/rewrites`
- `GET /api/rest/rewrites/:id`

### Attributes

- `category_id`: If not null, this rewrite will be used to build canonical URLs for categories.
- `options`: Type of redirect.  One of "" (no), "R" (temporary), "RP" (permanent).
- `product_id`: If not null, this rewrite will be used to build canonical URLs for products.
- `request_path`: Incoming requests are matched against this string.
- `store_id`: The store where this rewrite applies.
- `target_path`: Matching requests are redirected to this full or partial URI.
- `url_rewrite_id`: ID of this rewrite.
