# WikiTree API: getWatchlist

The getWatchlist action returns data for profiles that are on the Watchlist for the
logged-in user (that have that user on their Trusted List). The profile data returned
is like that from [getProfile](getProfile.md). Since this function only returns the
Watchlist for the logged-in user, there is no "key" value to specify which Watchlist.
However you can use paginate the results with limit & offset parameters (some Watchlists are very large), can can filter the results by profile type and/or living status.

## Parameters

|Param|Value|
|-----|-----|
|action|getWatchlist|
|limit|Integer value = how many Watchlist items to return. Default = 100|
|offset|Starting offset of returned profiles. Default = 0|
|order|The sort order for the returned profiles: user_id, user_name, user_last_name_current, user_birth_date, user_death_date, or page_touched. Default = user_id.|
|getPerson|Default = 1. If 1, the person profiles on the watchlist are returned|
|getSpace|Default = 1. If 1, the space profiles are returned, otherwise not|
|onlyLiving|If 1, then the person profiles returned are limited to those that are living|
|excludeLiving|If 1, then the person profiles returned are limited to those that are not living|
|fields|Optional comma-separated list of fields to return for each profile|
|bioFormat|Optional: "wiki", "html", or "both"|


### fields

The "fields" parameter is optional. If left out, a default set of fields is returned. For Person profile pages, the default is all fields other than the biography, children and spouses. For Free-Space profile pages, the default is to return all fields.

You can specify which fields to return by setting the "fields" parameter to a comma-separated list of those you want. You can also use "*" to indicate "all fields". 

### bioFormat

If you request the "bio" field (the text biography for a Person profile), the default is to return the content as it's stored, with wiki markup. You can instead request that this markup be rendered into HTML (as it would appear on the profile's web page) by specifying a "bioFormat" of "html". If you use a bioFormat value of "both", then both the original wiki text and the rendered HTML will be returned.

## Results

The returned results includes an "items" element which is an array of result items. Each element has the following fields.

|Field|Description|
|-----|-----------|
|watchlistCount|The total number of profiles on the Watchlist|
|watchlist|Array of profiles, with the requested fields|

See [getProfile.md](getProfile.md) for the fields in each Person profile.


## Examples

* [JavaScript](examples/getWatchlist/javascript.html)
