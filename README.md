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

### Documentation

See the `docs` directory for resource specific information.