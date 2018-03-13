# CMS Blocks

## Retrieve

- `GET /api/rest/blocks/:id`
- `GET /api/rest/blocks/:id/store/:store`

If the client includes `text/html` in their `Accept` header then they will receive an HTML fragment.
A web app might use this to directly insert content into a web page.
If the `Accept` header is `*/*` or any of the JSON or XML types then the API proceeds as normal.
`:id` may be either the numeric or text ID.

Admin users may also retrieve collections with:

- `GET /api/rest/blocks`

### Attributes

- `block_id`: Numeric ID.
- `content`: HTML content with the CMS directives already processed.
- `identifier`: A short text ID.
- `is_active`: Admin only. Either "0" or "1".
- `stores`: Store IDs for which this block is enabled.
- `title`: Normally for admin use only but visible to others too.
- `update_time`: The last modified time.

## Create / Update

- `POST /api/rest/blocks`
- `PUT /api/rest/blocks/:id`

Admin only.
`:id` may be either the numeric or text ID.
The textual `identifier` must be unique for each store in `stores`.
This allows several blocks with the same identifier to exist for different stores,
each with similar content but in a different language.
The store ID "0" is synonymous with "all store views" and functions as a default block.
Stores without a block matching an `identifier` will fall back to store 0's equivalent.

### Attributes

- `content`: Partial HTML content.
- `identifier`: **Required**. A short text ID.  Unlike most IDs this can be changed after creation.  
Care should be taken to avoid leaving references to the old identifier.
- `is_active`: Either "0" or "1".
- `stores`: **Required**. Store IDs for which this block is enabled.
- `title`: **Required**. Short description displayed in admin UI.

## Delete

- `DELETE /api/rest/blocks/:id`

Admin only.
`:id` may be either the numeric or text ID.
