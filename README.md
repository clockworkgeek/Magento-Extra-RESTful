# Extra RESTful

Developers are invited to include this reusable extension in their projects for better connectivity.
Extra RESTful is permissively licensed for this reason.
Suggestions for more resources to support are welcome,
just raise an [issue](https://github.com/clockworkgeek/Magento-Extra-RESTful/issues).

### Improvements

All Extra RESTful resources have the following advantages over core resources:

- No sessions.
- No cookies.
- No `Pragma` header.
- A `Content-Length` header instead of chunked encoding.

All collections in Extra RESTful have the following further advantages:

- Result is an array instead of an object.  
  (This matters to JSON where objects have no natural order.)
- An empty array when requesting a page outside of range.  
  (Magento API2 erroneously repeats the last page here.)
- A [`Link` header](https://tools.ietf.org/html/rfc5988) for easier pagination.  
  (This is similar to [GitHub's API](https://developer.github.com/v3/guides/traversing-with-pagination/) except URIs are relative to the site's base URL)

### New Attributes

- **Catalog Product / Read / Updated At** (`updated_at`)
- **Catalog Product / Read / Has Required Options** (`required_options`)
- **Customer / Read / Customer ID** (`entity_id`)
- **Customer / Read / Updated At** (`updated_at`)

### New Resources

- **Core / Store View / Retrieve**
  - `GET /api/rest/stores`
  - `GET /api/rest/stores/:id`
- **Core / Custom URL Rewrites / Retrieve**
  - `GET /api/rest/rewrites`
  - `GET /api/rest/rewrites/store/:store`
- **Catalog / Product Custom Option / Retrieve**
  - `GET /api/rest/options/:id`
  - `GET /api/rest/options/:id/store/:store`
  - `GET /api/rest/products/:product/options`
  - `GET /api/rest/products/:product/options/store/:store`
- **Catalog / Related Products / Retrieve**
  - `GET /api/rest/products/:product/related`
  - `GET /api/rest/products/:product/related/store/:store`
- **Catalog / Up-sells / Retrieve**
  - `GET /api/rest/products/:product/upsells`
  - `GET /api/rest/products/:product/upsells/store/:store`
- **Catalog / Cross-sells / Retrieve**
  - `GET /api/rest/products/:product/crosssells`
  - `GET /api/rest/products/:product/crosssells/store/:store`
- **Catalog / Associated Products / Retrieve**  
  (Only for "Grouped" and "Configurable" products)
  - `GET /api/rest/products/:product/associated`
  - `GET /api/rest/products/:product/associated/store/:store`
- **Catalog / Category / Create**
  - `POST /api/rest/categories`
  - `POST /api/rest/categories/store/:store`
- **Catalog / Category / Retrieve**
  - `GET /api/rest/categories`
  - `GET /api/rest/categories/store/:store`
  - `GET /api/rest/categories/parent/:parent`
  - `GET /api/rest/categories/parent/:parent/store/:store`
  - `GET /api/rest/categories/:id`
  - `GET /api/rest/categories/:id/store/:store`
  - `GET /api/rest/category_trees`  
  (Category tree is a convenient representation  of a normally flat list.
  Its access can be controlled as a separate resource but uses the same attributes.
  [Page size and sort order](http://devdocs.magento.com/guides/m1x/api/rest/get_filters.html) do not work on trees.)
  - `GET /api/rest/category_trees/store/:store`  
  (Categories can be filtered by the store they are assigned to.)
- **Catalog / Category / Update**
  - `PUT /api/rest/categories/:id`
  - `PUT /api/rest/categories/:id/store/:store`
- **Catalog / Category / Delete**
  - `DELETE /api/rest/categories/:id`
- **Catalog / Category Products / Retrieve**
  - `GET /api/rest/categories/:category/products`
  - `GET /api/rest/categories/:category/products/store/:store`
- **Catalog / Review / Create**
  - `POST /api/rest/products/:product/reviews`
  - `POST /api/rest/products/:product/reviews/store/:store`
- **Catalog / Review / Retrieve**
  - `GET /api/rest/reviews`
  - `GET /api/rest/reviews/store/:store`
  - `GET /api/rest/reviews/:id`
  - `GET /api/rest/reviews/:id/store/:store`
  - `GET /api/rest/products/:product/reviews`
  - `GET /api/rest/products/:product/reviews/store/:store`
- **Catalog / Review / Update**
  - `PUT /api/rest/reviews/:id`
- **Catalog / Review / Delete**
  - `DELETE /api/rest/reviews/:id`
- **CMS / Block / Create**
  - `POST /api/rest/cms/blocks`
- **CMS / Block / Retrieve**
  - `GET /api/rest/blocks`
  - `GET /api/rest/blocks/:id`
  - `GET /api/rest/blocks/:id/store/:store`  
  (`:id` can be number or 'identifier'. Will output as HTML if "text/html" is in the Accept header)
- **CMS / Block / Update**
  - `PUT /api/rest/blocks/:id`
- **CMS / Block / Delete**
  - `DELETE /api/rest/blocks/:id`
- **CMS / Page / Create**
  - `POST /api/rest/pages`
- **CMS / Page / Retrieve**
  - `GET /api/rest/pages`
  - `GET /api/rest/pages/:id`  
  (`:id` can be number or 'identifier')
- **CMS / Page / Update**
  - `PUT /api/rest/pages/:id`
- **CMS / Page / Delete**
  - `DELETE /api/rest/pages/:id`
  