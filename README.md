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
- A `Content-Length` instead of chunked encoding.
- `Cache-Control` and `Vary` headers.

All collections in Extra RESTful have the following further advantages:

- Result is an array instead of an object.  
  (This matters to JSON where objects have no natural order.)
- An empty array when requesting a page outside of range.  
  (Magento API2 erroneously repeats the last page here.)
- A [`Link` header](https://tools.ietf.org/html/rfc5988) for easier pagination.  
  (This is similar to [GitHub's API](https://developer.github.com/v3/guides/traversing-with-pagination/) except URIs are relative to the site's base URL)

### Altered Resources

- [Products](https://github.com/clockworkgeek/Magento-Extra-RESTful/blob/master/docs/Products.md#products)
- [Customers](https://github.com/clockworkgeek/Magento-Extra-RESTful/blob/master/docs/Customers.md)

### New Resources

- [Product Custom Options](https://github.com/clockworkgeek/Magento-Extra-RESTful/blob/master/docs/Products.md#product-custom-options)
- [Related Products / Up-sells / Cross-sells](https://github.com/clockworkgeek/Magento-Extra-RESTful/blob/master/docs/Products.md#related-products--up-sells--cross-sells)
- [Associated Products](https://github.com/clockworkgeek/Magento-Extra-RESTful/blob/master/docs/Products.md#associated-products)
- [Product Reviews](https://github.com/clockworkgeek/Magento-Extra-RESTful/blob/master/docs/Reviews.md#product-reviews)
- [Categories and Category Trees](https://github.com/clockworkgeek/Magento-Extra-RESTful/blob/master/docs/Categories.md#categories)
- [Category Products](https://github.com/clockworkgeek/Magento-Extra-RESTful/blob/master/docs/Categories.md#category-products)
- [Stores](https://github.com/clockworkgeek/Magento-Extra-RESTful/blob/master/docs/Stores.md#stores)
- [URL Rewrites](https://github.com/clockworkgeek/Magento-Extra-RESTful/blob/master/docs/URLRewrites.md#url-rewrites)
- [CMS Blocks](https://github.com/clockworkgeek/Magento-Extra-RESTful/blob/master/docs/Blocks.md#cms-blocks)
- [CMS Pages](https://github.com/clockworkgeek/Magento-Extra-RESTful/blob/master/docs/Pages.md#cms-pages)
