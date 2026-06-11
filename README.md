# New Angeles — Audio Gate

A single PHP endpoint that returns an MP3 file when the correct access code is provided. The access code matching is fuzzy — casing, diacritics, spaces, and punctuation are all ignored.

## Deployment

Upload the contents of `www/` to your web root (`public_html/` or equivalent), and upload the `audio/` directory one level **above** the web root so it is not URL-accessible:

```
public_html/        ← contents of www/
  index.html
  api/
    index.php
    config.php
audio/              ← sibling of public_html/, not reachable by URL
  track.mp3
```

`config.php` is gitignored. Copy the template and fill in your values:

```bash
cp www/api/config.example.php www/api/config.php
```

create `www/api/config.php`:

```php
const VALID_CODE = 'your-secret-passphrase';
const MP3_PATH   = __DIR__ . '/../../audio/track.mp3';
```

The `../../` path walks up from `public_html/api/` to the directory containing `audio/`. Adjust if your host's layout differs.

## API

**POST** `/api/`

Request:
```json
{ "code": "your-secret-passphrase" }
```

Success — returns the MP3 as `audio/mpeg`.

Error responses:

| Status | Meaning |
|--------|---------|
| 400 | Invalid JSON or missing `code` field |
| 401 | Wrong code |
| 405 | Non-POST request |
| 500 | Audio file not found on server |

## Local development

Requires Docker with Compose. Run from the project root:

```bash
docker compose up
```

App at `http://localhost:8080`. Files in `www/` and `audio/` are volume-mounted — edits are live without restarting.

```bash
# Test correct code
curl -X POST http://localhost:8080/api/ -H "Content-Type: application/json" -d '{"code":"your-secret-passphrase"}' -o out.mp3

# Test wrong code
curl -X POST http://localhost:8080/api/ -H "Content-Type: application/json" -d '{"code":"wrong"}'
```
