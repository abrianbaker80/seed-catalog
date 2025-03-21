# API Documentation

## REST API Endpoints

### Seeds

#### List Seeds
```http
GET /wp-json/seed-catalog/v1/seeds
```

Query Parameters:
- `page` (int) - Page number
- `per_page` (int) - Items per page (1-100)
- `search` (string) - Search term
- `category` (string|int) - Category slug or ID
- `tag` (string|int) - Tag slug or ID
- `orderby` (string) - Sort field (title, date, modified)
- `order` (string) - Sort direction (asc, desc)

Response:
```json
{
    "data": [{
        "id": 123,
        "title": "Tomato - San Marzano",
        "slug": "tomato-san-marzano",
        "content": "...",
        "meta": {
            "planting_depth": "0.25",
            "days_to_germination": "7-14",
            "days_to_maturity": "80",
            "sun_requirements": "full",
            "water_needs": "moderate"
        }
    }],
    "total": 100,
    "pages": 5
}
```

#### Get Single Seed
```http
GET /wp-json/seed-catalog/v1/seeds/{id}
```

Parameters:
- `id` (int) - Seed post ID

Response:
```json
{
    "id": 123,
    "title": "Tomato - San Marzano",
    "slug": "tomato-san-marzano",
    "content": "...",
    "meta": {
        "planting_depth": "0.25",
        "days_to_germination": "7-14",
        "days_to_maturity": "80",
        "sun_requirements": "full",
        "water_needs": "moderate"
    },
    "categories": [],
    "tags": [],
    "images": []
}
```

#### Create Seed
```http
POST /wp-json/seed-catalog/v1/seeds
```

Request Body:
```json
{
    "title": "New Seed",
    "content": "Description...",
    "meta": {
        "planting_depth": "0.5",
        "days_to_germination": "10-14"
    },
    "categories": [1, 2],
    "tags": [3, 4],
    "status": "publish"
}
```

#### Update Seed
```http
PUT /wp-json/seed-catalog/v1/seeds/{id}
```

Request Body: Same as Create

#### Delete Seed
```http
DELETE /wp-json/seed-catalog/v1/seeds/{id}
```

### Categories

#### List Categories
```http
GET /wp-json/seed-catalog/v1/categories
```

Query Parameters:
- `parent` (int) - Parent category ID
- `hide_empty` (bool) - Hide categories with no seeds
- `orderby` (string) - name, count, id
- `order` (string) - asc, desc

Response:
```json
{
    "data": [{
        "id": 1,
        "name": "Vegetables",
        "slug": "vegetables",
        "description": "...",
        "parent": 0,
        "count": 50
    }]
}
```

#### Get Category
```http
GET /wp-json/seed-catalog/v1/categories/{id}
```

#### Create Category
```http
POST /wp-json/seed-catalog/v1/categories
```

Request Body:
```json
{
    "name": "New Category",
    "description": "...",
    "parent": 0
}
```

### Search

#### Search Seeds
```http
GET /wp-json/seed-catalog/v1/search
```

Query Parameters:
- `q` (string) - Search query
- `type` (string) - Search type (full, basic)
- `fields` (string) - Comma-separated fields to search
- `limit` (int) - Results limit

Response:
```json
{
    "results": [{
        "id": 123,
        "title": "...",
        "excerpt": "...",
        "score": 0.95
    }],
    "total": 10
}
```

### AI Features

#### Plant Identification
```http
POST /wp-json/seed-catalog/v1/ai/identify
```

Request Body:
```json
{
    "image": "base64_encoded_image",
    "confidence_threshold": 0.8
}
```

Response:
```json
{
    "plant": {
        "name": "Solanum lycopersicum",
        "common_name": "Tomato",
        "confidence": 0.95
    },
    "suggestions": []
}
```

#### Growing Suggestions
```http
GET /wp-json/seed-catalog/v1/ai/suggest/{id}
```

Response:
```json
{
    "suggestions": {
        "soil": "...",
        "watering": "...",
        "companion_plants": []
    }
}
```

### Export/Import

#### Export Data
```http
GET /wp-json/seed-catalog/v1/export
```

Query Parameters:
- `format` (string) - csv, json, xlsx
- `include` (string) - posts, meta, images
- `category` (int) - Filter by category

#### Import Data
```http
POST /wp-json/seed-catalog/v1/import
```

Request Body:
```json
{
    "file": "base64_encoded_file",
    "format": "csv",
    "options": {
        "duplicate_handling": "update",
        "image_handling": "download"
    }
}
```

### Utility Endpoints

#### System Status
```http
GET /wp-json/seed-catalog/v1/status
```

Response:
```json
{
    "version": "1.0.0",
    "storage": {
        "used": "100MB",
        "available": "1GB"
    },
    "cache": {
        "enabled": true,
        "hit_rate": 0.85
    }
}
```

#### Clear Cache
```http
POST /wp-json/seed-catalog/v1/cache/clear
```

## Authentication

### API Keys
```http
Authorization: Bearer your-api-key-here
```

### WordPress Nonce
```http
X-WP-Nonce: generated_nonce_here
```

## Rate Limiting

- Anonymous: 60 requests per hour
- Authenticated: 1000 requests per hour
- Bulk Operations: 10 requests per minute

## Error Responses

```json
{
    "code": "error_code",
    "message": "Human readable message",
    "status": 400,
    "details": {}
}
```

Common Error Codes:
- `invalid_request` (400)
- `not_found` (404)
- `permission_denied` (403)
- `rate_limited` (429)
- `server_error` (500)

## Webhooks

### Available Events
- `seed.created`
- `seed.updated`
- `seed.deleted`
- `import.completed`
- `export.completed`

### Webhook Format
```json
{
    "event": "seed.created",
    "timestamp": "2024-03-20T12:00:00Z",
    "data": {},
    "signature": "..."
}
```

## Code Examples

### PHP
```php
$client = new SeedCatalogClient('your-api-key');
$seeds = $client->seeds->list([
    'category' => 'vegetables',
    'per_page' => 50
]);
```

### JavaScript
```javascript
const client = new SeedCatalogAPI('your-api-key');
const seeds = await client.seeds.list({
    category: 'vegetables',
    perPage: 50
});
```

### Python
```python
client = SeedCatalogAPI('your-api-key')
seeds = client.seeds.list(
    category='vegetables',
    per_page=50
)
```

## Testing

### Sandbox Environment
```http
https://sandbox-api.seedcatalog.com/wp-json/seed-catalog/v1/
```

### Test API Keys
- `sk_test_...` - Test mode
- `sk_live_...` - Production mode

## Best Practices

1. **Rate Limiting**
   - Implement exponential backoff
   - Cache responses
   - Use bulk operations

2. **Error Handling**
   - Validate input
   - Handle all error codes
   - Log failures

3. **Performance**
   - Use compression
   - Minimize payload size
   - Implement caching

4. **Security**
   - Use HTTPS
   - Validate signatures
   - Sanitize input