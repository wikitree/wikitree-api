# WikiTree API: searchPerson

The searchPerson action returns one or more sets person profiles like those from [getProfile](getProfile.md) based on a search that runs like that at [WikiTree](https://www.wikitree.com/wiki/Special:SearchPerson).

## Parameters

|Param|Value|
|-----|-----|
|FirstName|First Name|
|LastName|Last Name|
|BirthDate|Birth Date (YYYY-MM-DD)|
|DeathDate|Death Date (YYYY-MM-DD)|
|RealName|Real/Colloquial Name|
|LastNameCurrent|Current Last Name|
|BirthLocation|Birth Location|
|DeathLocation|Death Location|
|Gender|Gender (Male|Female)|
||
|fatherFirstName|Father\'s First Name|
|fatherLastName|Father\'s Last Name|
|motherFirstName|Mother\'s First Name|
|motherLastName|Mother\'s Last Name|
||
|watchlist|1 (restrict to watchlist)|
|dateInclude|both (require dates on matched profiles) or neither (include matches without dates)|
|dateSpread|1-20 (spread of years for date matches)|
|centuryTypo|1 (include possible century typos in date matches)|
|isLiving|1 (restrict matches to profiles of living people)|
|skipVariants|1 (skip variant last names in matches, only match exact surname)|
|lastNameMatch|Last Name Matching (all, current, birth, strict)|
||
|sort|Sort Order [first, last, birth, death, manager]|
|secondarySort|Secondary Sort Order [first, last, birth, death, manager]|
|limit|Number of results to return (1-100, default 10)|
|start|Starting offset of return set (default 0)|
|fields|Comma-delimited list of profile data fields to retrieve.|


## Results

The returned results includes a "matches" element which is an array of person profiles that matched the search.
Each element has the following fields.

|Field|Description|
|-----|-----------|
|matches|Array of profiles matching the search parameters|
|total|The total number of matches found|
|start|The result number the returned matches started with|
|limit|The number of matching items returned in this batch|

See [getProfile.md](getProfile.md) for the fields in each matched Person profile.


## Examples

```
curl 'https://api.wikitree.com/api.php?action=searchPerson&FirstName=Sam&LastName=Clemens&fields=Id,Name,FirstName,BirthDate'

[
  {
    "status": 0,
    "matches": [
      {
        "Id": 20082962,
        "index": 0
      },
      {
        "Id": 32222805,
        "Name": "Clemens-2682",
        "FirstName": "Samuel",
        "BirthDate": "1816-02-16",
        "index": 1
      },
      {
        "Id": 32229307,
        "Name": "Clemens-2686",
        "FirstName": "Samuel",
        "BirthDate": "1853-00-00",
        "index": 2
      },
      {
        "Id": 5185,
        "Name": "Clemens-1",
        "FirstName": "Samuel",
        "BirthDate": "1835-11-30",
        "index": 3
      },
      {
        "Id": 5201,
        "Name": "Clemens-10",
        "FirstName": "Samuel",
        "BirthDate": "1773-00-00",
        "index": 4
      },
      {
        "Id": 9963413,
        "Name": "Clemens-1011",
        "FirstName": "Samuel",
        "BirthDate": "1822-00-00",
        "index": 5
      },
      {
        "Id": 13762593,
        "Name": "Clemens-1357",
        "FirstName": "Samuel Pickens",
        "BirthDate": "1857-04-00",
        "index": 6
      },
      {
        "Id": 20436160,
        "Name": "Clemens-1798",
        "FirstName": "Samuel",
        "BirthDate": "1855-00-00",
        "index": 7
      },
      {
        "Id": 21745848,
        "Name": "Clemens-1903",
        "FirstName": "Samuel",
        "BirthDate": "1871-00-00",
        "index": 8
      },
      {
        "Id": 30300257,
        "Name": "Clemens-2444",
        "FirstName": "Samuel",
        "BirthDate": "1853-05-16",
        "index": 9
      }
    ],
    "total": 164,
    "start": 0,
    "limit": 10
  }
]
```

* [JavaScript](examples/searchPerson/javascript.html)
