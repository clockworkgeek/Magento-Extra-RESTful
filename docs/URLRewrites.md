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
- `options`: Type of redirect.  One of "" (internal), "R" (temporary), "RP" (permanent).
- `product_id`: If not null, this rewrite will be used to build canonical URLs for products.
- `request_path`: Incoming requests are matched against this string.
- `store_id`: The store where this rewrite applies.
- `target_path`: Matching requests are redirected to this full or partial URI.
- `url_rewrite_id`: ID of this rewrite.

## Create / Update

- `POST /api/rest/rewrites`
- `PUT /api/rest/rewrites/:id`

### Attributes

- `category_id`: If not null, this rewrite will be used to build canonical URLs for categories.
- `options`: Type of redirect.  One of "" (internal), "R" (temporary), "RP" (permanent).
- `product_id`: If not null, this rewrite will be used to build canonical URLs for products.
- `request_path`: Incoming requests are matched against this string.
- `store_id`: **Required**. The store where this rewrite applies.
- `target_path`: Matching requests are redirected to this full or partial URI.

### Examples

In the first example an HTTP redirect is created to an external site.
It is not necessary to specify the type of redirect because it defaults to a temporary redirect when the target path is a fully qualified URL.
There is an `Authorization` header because this is an admin action.

```http
POST /api/rest/rewrites HTTP/1.1
Authorization: XXXXXXXXX
Content-Length: 75
Content-Type: application/json
Host: example.com

{
    "request_path": "readme",
    "store_id": 1,
    "target_path": "https://readme.com/"
}

HTTP/1.1 202 Accepted
Location: /api/rest/rewrites/555
```

Now consider the task of converting a menu item into a link to an external site.
The popular method would be to create a category as appropriate,
then delete it's rewrite entry (because it is "system" and cannot be altered),
then create a replacement record with a duplicate "ID Path".
Here you may simply update the rewrite's target and it will automatically be changed from "system" to "custom".

```http
PUT /api/rest/rewrites/777 HTTP/1.1
Authorization: XXXXXXXXX
Content-Length: 38
Content-Type: application/json
Host: example.com

{
    "target_path": "https://readme.com/"
}

HTTP/1.1 200 OK
```

## Delete

- `DELETE /api/rest/rewrites/:id`
