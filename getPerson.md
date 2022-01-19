# WikiTree API: getPerson

The getPerson action returns person profiles like [getProfile](getProfile.md). The differences are:
- The "key" is a WikiTree ID or *(User)Id* (rather than *PageId*)
- It can *only* return Person profiles, not Free-Space profiles

## Parameters

|Param|Value|
|-----|-----|
|action|getProfile|
|key|WikiTree ID or Id|
|fields|Optional comma-separated list of fields to return|
|bioFormat|Optional: "wiki", "html", or "both"|
|resolveRedirect|Optional. If 1, then requested profiles that are redirections are followed to the final profile|

### key

The "key" parameter is used to indicate which profile to return. This can be either a "WikiTree ID" or a "User ID". The WikiTree ID is the name used after "/wiki" in the URL of the Person page. For example, for [Person Profile Pages](https://www.wikitree.com/wiki/Help:Person_Profile) like https://www.wikitree.com/wiki/Shoshone-1, the WikiTree ID is "Shonshone-1".

Note that the Mother and Father fields in a Person profile are (User) IDs. Depending on what data you retrieve for Mother/Father it may be easier to use getPerson to follow those than getProfile (which requires a Page ID or WikiTree ID)

### fields

The "fields" parameter is optional. If left out, a default set of fields is returned. For Person profile pages, the default is all fields other than the biography, children and spouses. For Free-Space profile pages, the default is to return all fields.

You can specify which fields to return by setting the "fields" parameter to a comma-separated list of those you want. You can also use "*" to indicate "all fields". 

### bioFormat

If you request the "bio" field (the text biography for a Person profile), the default is to return the content as it's stored, with wiki markup. You can instead request that this markup be rendered into HTML (as it would appear on the profile's web page) by specifying a "bioFormat" of "html". If you use a bioFormat value of "both", then both the original wiki text and the rendered HTML will be returned.

### resolveRedirect

Generally if you start at a valid profile and follow use the ids associated with relationships (mother, father) you should get a valid/complete profile in return. However, in some circumstances you may end up requesting a profile that has been merged away into another profile, or otherwise is redirected. If you set resolveRedirect=1 in your POST to the API, then any profiles that would be returned that are redirections will be followed to their end point, and *that* final profile will be returned.

## Results

See [getProfile.md](getProfile.md) for the fields in each Person profile.


## Examples

```
curl 'https://api.wikitree.com/api.php?action=getPerson&key=Clemens-1&fields=Id,PageId,Name,FirstName,LastNameAtBirth,BirthDate,DeathDate'

or 

curl 'https://api.wikitree.com/api.php?action=getPerson&key=5185&fields=Id,PageId,Name,FirstName,LastNameAtBirth,BirthDate,DeathDate'

[
  {
    "page_name": "Clemens-1",
    "profile": {
      "Id": 5185,
      "PageId": 7146,
      "Name": "Clemens-1",
      "FirstName": "Samuel",
      "LastNameAtBirth": "Clemens",
      "BirthDate": "1835-11-30",
      "DeathDate": "1910-04-21"
    },
    "status": 0
  }
]
```

* [JavaScript](examples/getPerson/javascript.html)
* Python - web page, command line
* [PHP](examples/getPerson/phpWebPage.php) (also have command-live example)
