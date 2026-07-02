# Basket

The basket is a simple shopping-cart-like feature: users (anonymous or authenticated) collect `Media` items into a basket, then download all of them at once as a ZIP file. It's enabled by default — see [Configuration](./Configuration.md#basket) to disable it or change how baskets are claimed on login.

## Creating a basket

```
POST /api/baskets
```

- **No authenticated user**: a fresh anonymous basket is created (random UUID token, no owner) and returned.
- **Authenticated user**: any existing basket already owned by that user is deleted and a new one is created, so a user only ever has one basket at a time.
- **Claiming an anonymous basket on login**: pass `anonymous_token` in the request body. If that token points to a real, still-anonymous basket, its contents are merged into the new basket according to `basket.claim_strategy` (`merge`, `replace`, or discarded), and the anonymous basket is deleted either way.

You generally don't need to call this manually right after login — see below.

### Automatic basket on login

When a user logs in, the login response is automatically enriched with a `basket_token` field (via a login enricher registered by this package). This calls `findOrCreateForOwner()` internally, so an authenticated user always has a basket token available without an extra request.

## Adding / removing media

```
POST   /api/baskets/{token}/medias      { "media_ids": [1, 2, 3] }
DELETE /api/baskets/{token}/medias/{mediaId}
```

Adding is idempotent (no duplicates). Both actions require basket "modify" authorization (see below).

## Viewing a basket

```
GET /api/baskets/{token}
```

Returns the basket, including its associated media items, if the current user is authorized to view it.

## Authorization rules

- **Anonymous basket** (no owner): anyone can view, modify, or download it — the token itself (a hard-to-guess UUID) is the only access control. Don't leak basket tokens in contexts where that's a problem.
- **Owned basket**: only the matching authenticated user can view, modify, or download it.

## Downloading

```
GET /api/baskets/{token}/download
```

- Returns a `404` if the basket doesn't exist, and denies access per the authorization rules above.
- Returns a `422` if the basket is empty, or if none of its media items resolved to an actual file on disk.
- Otherwise, streams a `basket.zip` archive containing every media file, and then **deletes the basket** — downloading is a one-shot, consuming operation. If you need the same items again, you'll need to re-add them to a new basket.

## Wiring up your own `User` model

To expose the inverse relation from your `User` (or any ownable model) back to its baskets, add the package's trait:

```php
use Gingerminds\LaravelMediaManager\Traits\HasBasket;

class User extends Authenticatable
{
    use HasBasket;

    // exposes $user->baskets(): MorphMany
}
```

This isn't applied automatically — add it explicitly to whichever model(s) you want to be able to own a basket.
