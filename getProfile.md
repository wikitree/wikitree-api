# WikiTree API: getProfile

## Parameters

|Param|Value|
|-----|-----|
|action|getProfile|
|key|WikiTree ID or PageId|
|fields|Optional comma-separated list of fields to return|
|bioFormat|Optional: "wiki", "html", or "both"|
|resolveRedirect|Optional. If 1, then requested profiles that are redirections are followed to the final profile|

### key

The "key" parameter is used to indicate which profile to return. This can be either a "WikiTree ID" or a "Page ID". The WikiTree ID is the name used after "/wiki" in the URL of the page. For example, for [Person Profile Pages](https://www.wikitree.com/wiki/Help:Person_Profile) like https://www.wikitree.com/wiki/Shoshone-1, the WikiTree ID is "Shonshone-1". For [Free-Space Profile Pages](https://www.wikitree.com/wiki/Help:Free-Space_Profile), the "Space:" prefix is required. For example, for https://www.wikitree.com/wiki/Space:Space:Edward_D._Whitten%27s_Model_Ships, the WikiTree ID is "Space:Edward_D._Whitten%27s_Model_Ships". 

### fields

The "fields" parameter is optional. If left out, a default set of fields is returned. For Person profile pages, the default is all fields other than the biography, children and spouses. For Free-Space profile pages, the default is to return all fields.

You can specify which fields to return by setting the "fields" parameter to a comma-separated list of those you want. You can also use "*" to indicate "all fields". 

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
|IsPerson|1 for Person profiles|
|FirstName|First Name|
|MiddleName|Middle Name|
|MiddleInitial|First letter of Middle Name|
|LastNameAtBirth|Last name at birth, used for WikiTree ID|
|LastNameCurrent|Current last name|
|Nicknames|Nicknames|
|LastNameOther|Other last names|
|RealName|The "Preferred" first name of the profile|
|Prefix|Prefix|
|Suffix|Suffix|
|BirthDate|The date of birth, YYYY-MM-DD. The Month (MM) and Day (DD) may be zeros.|
|DeathDate|The date of death, YYYY-MM-DD. The Month (MM) and Day (DD) may be zeros.|
|BirthLocation|Birth location|
|DeathLocation|Death location|
|BirthDateDecade|Date of birth rounded to a decade, e.g. 1960s|
|DeathDateDecade|Date of death rounded to a decade, e.g. 1960s|
|Gender|Male or Female|
|Photo|The base filename of the primary photo for the profile|
|IsLiving|1 if the person is considered "living", 0 otherwise|
|Touched|The timestamp the profile was last modified, YYYYMMDDHHMMSS
|Privacy|An integer representing the [Privacy](https://www.wikitree.com/wiki/Help:Privacy) setting on the profile. The Privacy determines which fields are available.|
|Privacy_IsPrivate|True if Privacy = 20|
|Privacy_IsPublic|True if Privacy = 50|
|Privacy_IsOpen|True if Privacy = 60|
|Privacy_IsAtLeastPublic|True if Privacy >= 50|
|Privacy_IsSemiPrivate|True if Privacy = 30-40|
|Privacy_IsSemiPrivateBio|True if Privacy = 30|
|Manager|The Id (user_id) of the (a) manager of the profile|
|Father|The Id (user_id) of the father of the profile. Zero if empty. Null if excluded by privacy.|
|Mother|The Id (user_id) of the mother of the profile. Zero if empty. Null if excluded by privacy.|
|HasChildren|1 if the profile has at least one child|
|NoChildren|1 if the "No more children" box is checked on the profile|
|IsRedirect|1 if the profile is a redirection to another profile, e.g. if the LastNameAtBirth was changed.|
|DataStatus|An array of the "guess", "certain", etc. flags for the data fields.
|PhotoData|Detailed information for the primary photo|

The following fields are derived from other fields. They can be requested with "Derived.FieldName".

|Field|Description|
|-----|-----------|
|ShortName|RealName (LastNameAtBirth) LastNameCurrent Suffix
|BirthName|FirstName MiddleName|
|BirthNamePrivate|RealName LastNameAtBirth Suffix|(LastNameAtBirth) LastNameCurrent Suffix|
|LongName|FirstName MiddleName (LastNameAtBirth) LastNameCurrent Suffix|
|LongNamePrivate|RealName MiddleInitial (LastNameAtBirth) LastNameCurrent Suffix |

The relative fields are arrays of Profile items, indexed by Id, each with the same fields as for the returned profile.
|Field|Description|
|-----|-----------|
|Parents|
|Children|
|Spouses|
|Siblings|

You can also request **Managers** or **TrustedList**. Either one returns an array of profile data that includes Id, Page Id, and Name for the person on the list. For TrustedList, the list is all people on the Trusted List of the profile and each entry includes an "IsManager" field, set to 1 if the person is a manager and zero otherwise. For Managers, only those that are managers are included.

Finally, you can request **Categories**. The returned field is an array of the Category titles which are connected to the proifle.

### DataStatus Details

Certain, Guess, Blank, etc.


### PhotoData Details
|Field|Description|
|-----|-----------|
|path|Relative URL to full-size image|
|url|Relative URL to 75px thumbnail|
|file|Filename of 75px thumbnail|
|dir|Directory path to 75px thumbnail|
|width|Width in px of image (75)|
|height|Height in px of image|
|orig_width|Width in px of original full-size image|
|orig_height|Height in px of original full-size image|



## Examples

```
curl 'https://api.wikitree.com/api.php?action=getProfile&key=Clemens-1&fields=Id,PageId,Name,FirstName,LastNameAtBirth,BirthDate,DeathDate'

or 

curl 'https://api.wikitree.com/api.php?action=getProfile&key=7146&fields=Id,PageId,Name,FirstName,LastNameAtBirth,BirthDate,DeathDate'


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

* [JavaScript](examples/getProfile/javascript.html)
* Python - web page, command line
* [PHP](examples/getProfile/phpWebPage.php) (also have command-live example)
