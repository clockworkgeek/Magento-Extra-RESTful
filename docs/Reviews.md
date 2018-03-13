# Product Reviews

## Create

- `POST /api/rest/products/:product/reviews`
- `POST /api/rest/products/:product/reviews/store/:store`

Guests can only create reviews if enabled in _System > Configuration > Catalog > Product Reviews_.
Customer may always create reviews.
New reviews are created with a "Pending" status.

### Attributes

- `detail`: The content of the post.
- `nickname`: User supplied name.  Not restricted to customer name.
- `ratings`: Object where keys are printable labels and values are percentages.
- `title`: A summary of the post.

### Example

```http
POST /api/rest/products/555/reviews HTTP/1.1
Content-Length: 119
Content-Type: application/json
Host: example.com

{
    "detail": "This is a review",
    "nickname": "Ted",
    "ratings": {
        "Price": 100,
        "Quality": 25
    },
    "title": "This is a title"
}

HTTP/1.1 202 Accepted
Location: /api/rest/reviews/80
```

The ratings' percentage will be rounded up to the next multiple of 20 because Magento always uses five stars.
The response is "202 Accepted" because the new review isn't public yet but will be at the given location.

## Retrieve

- `GET /api/rest/reviews`
- `GET /api/rest/reviews/store/:store`
- `GET /api/rest/reviews/:id`
- `GET /api/rest/reviews/:id/store/:store`
- `GET /api/rest/products/:product/reviews`
- `GET /api/rest/products/:product/reviews/store/:store`

Guests may only access approved reviews.
Customers may access all approved reviews and their own reviews regardless of status.
Admin users may, of course, access all reviews.

### Attributes

- `created_at`: Date and time of posting.
- `customer_id`: Only visible to admin and owning customer.
- `detail`: The content of the post.
- `nickname`: User supplied name.  Not restricted to customer name.
- `product_id`: Product this is assigned to.
- `ratings`: Object where keys are printable labels and values are percentages.
- `review_id`: This review's ID.
- `status`: One of "Pending", "Approved", "Not Approved". Only visible to admin and owning customer.
- `stores`: Array of store IDs where this review has been granted approval.
- `title`: A summary of the post.

### Example

```json
{
    "created_at": "2013-06-18 17:40:05",
    "detail": "Great Candle Holders",
    "nickname": "Mosses Test",
    "ratings": {
        "Price": "60",
        "Quality": "60",
        "Val.": "60"
    },
    "review_id": "80",
    "stores": [
        "0",
        "1"
    ],
    "title": "Great Candle Holders"
}
```


## Update

- `PUT /api/rest/reviews/:id`

Only admin users may access this method.
The common use is to approve reviews that are currently pending.
A client only needs to specify the fields to change.
In this respect it is more like a `PATCH` request but Magento's API does not accept that so a `PUT` is used instead.

### Attributes

- `customer_id`: Assigning an anonymous review to a customer is possible. Reassigning from a known customer is not advised, Magento admin does not allow it.
- `detail`: The content of the post.
- `nickname`: User supplied name.  Not restricted to customer name.
- `ratings`: Object where keys are printable labels and values are percentages.
- `status`: One of "Pending", "Approved", "Not Approved".
- `stores`: Array of store IDs where this review has been enabled.
- `title`: A summary of the post.

### Example

```http
PUT /api/rest/reviews/99 HTTP/1.1
Content-Length: 22
Content-Type: application/json
Host: example.com

{
    "status": "Approved"
}
```

## Delete

- `DELETE /api/rest/reviews/:id`

Customers cannot delete their own reviews.
