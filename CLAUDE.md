# CLAUDE.md

## Project overview

Artist portfolio and audio-gate for **New Angeles** (Kefry). Visitors enter a secret access code; on success the backend streams an MP3 that the frontend plays with a canvas visualizer.

## Structure

```
/                        ← project root
  docker-compose.yml     ← mounts www/ and audio/ into the container
  www/                   ← web root (deploy contents to public_html/ on hosting)
    index.html           ← frontend (plain HTML/JS/CSS, no build step)
    img/
    api/
      index.php          ← single endpoint: POST / → MP3 or JSON error
      config.php         ← VALID_CODE and MP3_PATH constants
  audio/                 ← outside web root; not URL-accessible
    placeholder.mp3      ← rename/replace with the real track
```

## Frontend (`www/index.html`)

Single-page portfolio with four sections: hero (access-code gate), Movement, O nás, Kontakt & Streams.

**Access-code flow** — `handleCodeSubmit()` (line ~642) POSTs `{"code": "<value>"}` to `api/`. On 200 it creates a Blob URL and passes it to `playAudio()`; on 401 it shows "INVALID CODE"; on other errors it shows "ERROR".

## Backend (`www/api/`)

Single PHP endpoint (PHP 8.4). Accepts a JSON POST with a `code` field, returns the MP3 on success or a JSON error.

**Code matching is fuzzy** — `normalize_code()` in `index.php` lowercases, strips Czech diacritics, and removes all whitespace and punctuation before comparing, so casing, spaces, and diacritics are all forgiven.

**Quick reference:**

```
POST /api/
Content-Type: application/json

Body:   { "code": "ve stínu mrakodrapu" }

200 → audio/mpeg binary
400 → { "error": "Invalid JSON or missing code field" }
401 → { "error": "Invalid code" }
405 → { "error": "Method not allowed" }
500 → { "error": "Audio file not found" }
```

## Key config

| File | Constant | Purpose |
|------|----------|---------|
| `www/api/config.php` | `VALID_CODE` | The secret passphrase |
| `www/api/config.php` | `MP3_PATH` | Path to audio; `__DIR__ . '/../../audio/placeholder.mp3'` points outside the web root |

## Running locally

From the project root:

```bash
docker compose up
```

- App at `http://localhost:8080`
- Files in `www/` and `audio/` are volume-mounted — edits are live immediately, no rebuild needed.

```bash
# Test correct code
curl -X POST http://localhost:8080/api/ -H "Content-Type: application/json" -d '{"code":"ve stinu mrakodrapu"}' -o out.mp3

# Test wrong code
curl -X POST http://localhost:8080/api/ -H "Content-Type: application/json" -d '{"code":"wrong"}'
```

## Deployment

Upload the contents of `www/` to `public_html/` (or the host's web root) and upload `audio/` as a sibling directory one level above it. `MP3_PATH` already points to `../../audio/` so no config change is needed as long as the directory layout matches.
