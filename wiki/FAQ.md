# FAQ

## Does this package protect against SQL injection?

The package uses Eloquent query builder methods and developer-controlled whitelists. This protects the normal request-driven filtering flow.

Custom filters can still be unsafe if they use raw SQL with unbound user input.

## Can I filter relation fields?

Yes, basic relation field filtering is supported through the `relations` config key.

## Can I sort by relation fields?

Not yet. Sorting currently supports columns on the main model only.

## Can I use camelCase request keys?

Yes. Enable `$normalizeFilterKeys = true` on the model or pass `normalize_keys => true` to `filter()`.

## What happens to unknown filters?

Unknown filters are ignored.

## What happens to unknown sort fields?

Unknown or non-whitelisted sort fields are ignored.

## Should I commit `composer.lock` for a package?

It is optional. Many libraries do not commit `composer.lock`, while applications usually do.

