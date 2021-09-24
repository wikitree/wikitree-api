# WikiTree API: getBio

The getBio action returns the biography text of person profiles. This is the same data returned by [getPerson](getPerson.md)/[getProfile](getProfile.md) when you request the field "Bio".

## Parameters

|Param|Value|
|-----|-----|
|action|getProfile|
|key|WikiTree ID or Id|
|bioFormat|Optional: "wiki", "html", or "both"|
|resolveRedirect|Optional. If 1, then requested profiles that are redirections are followed to the final profile|

### key

The "key" parameter is used to indicate which profile to return. This can be either a "WikiTree ID" or a "User ID". The WikiTree ID is the name used after "/wiki" in the URL of the Person page. For example, for [Person Profile Pages](https://www.wikitree.com/wiki/Help:Person_Profile) like https://www.wikitree.com/wiki/Shoshone-1, the WikiTree ID is "Shonshone-1".

Note that the Mother and Father fields in a Person profile are (User) IDs. Depending on what data you retrieve for Mother/Father it may be easier to use getPerson to follow those than getProfile (which requires a Page ID or WikiTree ID)


### bioFormat

If you request the "bio" field (the text biography for a Person profile), the default is to return the content as it's stored, with wiki markup. You can instead request that this markup be rendered into HTML (as it would appear on the profile's web page) by specifying a "bioFormat" of "html". If you use a bioFormat value of "both", then both the original wiki text and the rendered HTML will be returned.

### resolveRedirect

Generally if you start at a valid profile and follow use the ids associated with relationships (mother, father) you should get a valid/complete profile in return. However, in some circumstances you may end up requesting a profile that has been merged away into another profile, or otherwise is redirected. If you set resolveRedirect=1 in your POST to the API, then any profiles that would be returned that are redirections will be followed to their end point, and *that* final profile will be returned.

## Results

|Field|Description|
|-----|-----------|
|Id|Integer "user/person" id of profile|
|PageId|Integer ID used in getProfile to request the content|
|Name|The WikiTree ID|
|bio|The wiki-text biography|
|bioHTML|The HTML rendered from the wiki-text, returned if bioFormat = "html" or "both"|

## Examples

```
curl 'https://api.wikitree.com/api.php?action=getBio&key=Clemens-1'

[
  {
    "user_id": 5185,
    "Id": 5185,
    "PageId": 7146,
    "Name": "Clemens-1",
    "status": 0,
    "bio": "[[Category: American Heroes]]\n[[Category:This Day In History April 21]]\n[[Category:This Day In History November 30]]\n[[Category:Authors]][[Category: United States, Novelists]]\n[[Category: United States, Authors]]\n[[Category:Famous Authors of the 19th Century]]\n[[Category:Featured Connections]]\n[[Category:Example Profiles of the Week]]\n{{United States}}\n== Biography ==\n{{Notables Sticker}}  \n'''Samuel L. Clemens'''  aka '''Mark Twain''' &mdash; ''Author, Lecturer  and Humorist''\n\nSamuel was born Nov 30 1835 in Missouri. ... "
  }
]
```

* [JavaScript](examples/getBio/javascript.html)
