# Products

Extra RESTful replaces [Magento's products resource](http://devdocs.magento.com/guides/m1x/api/rest/Resources/Products/products.html) to provide the following benefits:

- Dropdown and multiselect type attributes are replaced with and filtered by their localised values.
- Product URLs are correct for the specified store.
- Flat tables are used for performance.
- Non-visible attributes are not loaded in the background, again for performance.
- Boolean and integer attributes are cast to native types for the convenience of JSON users.
- No `buy_now_url` because it's not RESTful.
- `updated_at` attribute so clients may filter the most recent and update their local copies efficiently.
- `has_options` and `required_options` attributes so clients may know if a product can be ordered immediately without further input.

Extra RESTful products are accessed at the same URI, `/api/rest/products`, but are designated "Version 2".
Admin users still access "Version 1" for now.
If they prefer, frontend users may access the old resource by adding a "Version" HTTP header in their client, e.g.

```http
GET /api/rest/products HTTP/1.1
Accept: */*
Host: example.com
Version: 1
```

# Category Products

- `GET /api/rest/products/category/:category_id`
- `GET /api/rest/products/category/:category_id/store/:store`
- `GET /api/rest/products/store/:store/category/:category_id`
- `GET /api/rest/products?category_id=:category_id`

These routes filter the products list by `:category_id` but only considers products which are immediate children of the target category.
Unless you specifically need this behaviour you are advised to use [these routes](https://github.com/clockworkgeek/Magento-Extra-RESTful/blob/master/docs/Categories.md#category-products) instead:

- `GET /api/rest/categories/:category/products`
- `GET /api/rest/categories/:category/products/store/:store`

# Product Custom Options

## Retrieve

- `GET /api/rest/products/:product/options`
- `GET /api/rest/products/:product/options/store/:store`

This resource exposes the "Custom Options" as entered by admin for a particular product.
The product specified by `:product` must exist.
Options and values are already sorted by `sort_order` then `title`.

### Attributes

- `file_extension`: Comma- or space-separated list of allowed file extensions. Only applicable if `type` is `file`.
- `image_size_x`: An integer measured in pixels.  Only applicable if `type` is `file` and the uploaded file is an image.
- `image_size_y`: An integer measured in pixels.  Only applicable if `type` is `file` and the uploaded file is an image.
- `is_require`: Boolean.
- `max_characters`: Integer.  Only applicable if `type` is `field` or `area`.
- `option_id`: Use this ID when adding the product to cart.
- `price`: An optional float to be added to the final price.  Not applicable if `type` is `drop_down`, `radio`, `checkbox`, or `multiple`.
- `price_type`: Either `fixed` or `percent`.
- `sku`: A string that will be appended to the product's SKU if this option is used.
- `sort_order`: Integer.
- `title`: Store-specific text to be displayed to end user.
- `type`: One of these values; `field`, `area`, `file`, `drop_down`, `radio`, `checkbox`, `multiple`, `date`, `date_time`, `time`.
- `values`: A list of objects if `type` is `drop_down`, `radio`, `checkbox`, or `multiple`.  Each has these attributes:
  - `price`: An optional float to be added to the final price.
  - `price_type`: Either `fixed` or `percent`.
  - `sku`: A string that will be appended to the product's SKU if this value is selected.
  - `sort_order`: Integer.
  - `title`: Store-specific text to be displayed to end user.
  - `value_id`: Use this ID when adding product to cart.

### Example

```json
[
    {
        "is_require": false,
        "option_id": "2",
        "product_id": "410",
        "sku": null,
        "sort_order": 3,
        "title": "Test Custom Options",
        "type": "drop_down",
        "values": [
            {
                "price": 59.95,
                "price_type": "fixed",
                "sku": "m1",
                "sort_order": 0,
                "title": "model 1",
                "value_id": "1"
            },
            {
                "price": 60,
                "price_type": "fixed",
                "sku": "m2",
                "sort_order": 0,
                "title": "model 2",
                "value_id": "2"
            }
        ]
    }
]
```

## Create/Update

- `POST /api/rest/products/:product/options`
- `POST /api/rest/products/:product/options/store/:store`
- `PUT /api/rest/products/options/:id`
- `PUT /api/rest/products/options/:id/store/:store`

The product specified by `:product` must exist.
The custom option specified by `:id` must exist.

If `values` is specified it must contain all values to be preserved.
Any existing values not included will be deleted from the database.
Values are identified by `value_id` so it cannot be changed.
If `value_id` is not recognised then a new value record is created.
Before updating consider loading the latest values with one of:

- `GET /api/rest/products/options/:id`
- `GET /api/rest/products/options/:id/store/:store`

### Attributes

- `file_extension`: Comma- or space-separated list of allowed file extensions. Only applicable if `type` is `file`.
- `image_size_x`: An integer measured in pixels.  Only applicable if `type` is `file` and the uploaded file is an image.
- `image_size_y`: An integer measured in pixels.  Only applicable if `type` is `file` and the uploaded file is an image.
- `is_require`: Boolean. Defaults to `true`.
- `max_characters`: Integer.  Only applicable if `type` is `field` or `area`.
- `price`: An optional float to be added to the final price.  Not applicable if `type` is `drop_down`, `radio`, `checkbox`, or `multiple`.
- `price_type`: Either `fixed` or `percent`. Defaults to `fixed`.
- `sku`: A string that will be appended to the product's SKU if this option is used.
- `sort_order`: Integer.
- `title`: **Required**. Store-specific text to be displayed to end user.
- `type`: **Required**. One of; `field`, `area`, `file`, `drop_down`, `radio`, `checkbox`, `multiple`, `date`, `date_time`, `time`.
- `values`: A list of objects if `type` is `drop_down`, `radio`, `checkbox`, or `multiple`.  Each may have these attributes:
  - `price`: An optional float to be added to the final price.
  - `price_type`: Either `fixed` or `percent`. Defaults to `fixed`.
  - `sku`: A string that will be appended to the product's SKU if this value is selected.
  - `sort_order`: Integer.
  - `title`: **Required**. Store-specific text to be displayed to end user.
  - `value_id`: Use this to identify existing values to be modified.

## Delete

- `PUT /api/rest/products/options/:id`

The custom option specified by `:id` must exist.

# Related Products / Up-sells / Cross-sells

- `GET /api/rest/products/:product/related`
- `GET /api/rest/products/:product/related/store/:store`
- `GET /api/rest/products/:product/upsells`
- `GET /api/rest/products/:product/upsells/store/:store`
- `GET /api/rest/products/:product/crosssells/`
- `GET /api/rest/products/:product/crosssells/store/:store`

Lists products set as "Related Products", "Up-sells", or "Cross-sells", and in order of "Position" as set by the admin.
These lists behave exactly like `/api/rest/products` so can be filtered, ordered, and paged too.

# Associated Products

- `GET /api/rest/products/:product/associated`
- `GET /api/rest/products/:product/associated/store/:store`

Only for "Grouped" and "Configurable" products.
Simple products associated with a "Grouped" product contain an extra `qty` field which is the admin entered default quantity value.
"Configurable" products requested at `/api/rest/products/:id` contains an extra `super_attributes` object with keys that are attribute names and values that are printable labels.
A typical exchange might go like this:

```
GET /api/rest/products/410?attrs=name,description,super_attributes HTTP/1.1
Accept: */*
Host: example.com

HTTP/1.1 200 OK
Content-Length: 151
Content-Type: application/json; charset=utf-8


{
    "name": "Chelsea Tee",
    "description": "Ultrasoft, lightweight V-neck tee. 100% cotton. Machine wash.",
    "super_attributes": {
        "color": "Color",
        "size": "Size"
    }
}

GET /api/rest/products/410/associated?attrs=color,size,regular_price_without_tax HTTP/1.1
Accept: */*
Host: example.com

HTTP/1.1 200 OK
Content-Length: 241
Content-Type: application/json; charset=utf-8

[
    {
        "color": "Black",
        "size": "S",
        "regular_price_without_tax": 55
    },
    {
        "color": "Black",
        "size": "L",
        "regular_price_without_tax": 75
    },
    {
        "color": "White",
        "size": "M",
        "regular_price_without_tax": 65
    },
    {
        "color": "White",
        "size": "L",
        "regular_price_without_tax": 75
    }
]
```

This is enough information to display a form with fields for "Color" ("Black" or "White") and "Size" ("S", "M" or "L") and to update the price dynamically.
