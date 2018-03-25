# Stores

- `GET /api/rest/stores`
- `GET /api/rest/stores/:id`

The following attributes are available:

- `code`: A text identifier.
- `general_locale_code`: Entity only. An ISO 639-1 compatible code.
- `general_locale_timezone`: Entity only. A TZ compatible name such as "Europe/London".
- `is_active`: Boolean. Admin only.
- `name`: Used in various places throught the admin UI but has limited purpose elsewhere.
- `secure_base_url`: Part of an URL, usually beginning with "https".
- `store_id`: A numeric identifier.
- `unsecure_base_url`: Part of an URL, usually beginning with "http".

The configuration values `general_locale_code` and `general_locale_timezone` are only accessible from the entity route `/api/rest/stores/:id` and might be used for mobile apps to coordinate their localisation.
