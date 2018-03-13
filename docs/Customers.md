# Customers

At this time the only changes to `/api/rest/customers` is to reveal two attributes.
`entity_id` allows a logged in customer to find the ID needed for accessing `/api/rest/customers/:id/addresses`.
`updated_at` allows an admin client to query recent record changes and so update their local copies efficiently.
