# WikiTree API: getPeople

## Parameters

| Param         | Value                                                                                                                           |
| ------------- | ------------------------------------------------------------------------------------------------------------------------------- |
| action        | getPeople                                                                                                                       |
| keys          | One or more WikiTree ID or PageId values separated by commas                                                                    |
| fields        | Optional comma-separated list of fields to return                                                                               |
| bioFormat     | Optional: "wiki", "html", or "both"                                                                                             |
| siblings      | If 1, then get siblings of profiles, If 0 (default), do not get siblings                                                        |
| ancestors     | Number of generations of ancestors (parents) to return from the starting id(s). Default 0.                                      |
| descendants   | Number of generations of descendants (children) to return from the starting id(s). Default 0.                                   |
| nuclear       | Number of generations of nuclear relatives (parents, children, siblings, spouses) to return from the starting id(s). Default 0. |
| minGeneration | Generation number to start at when gathering relatives                                                                          |

### keys

The "keys" parameter is used to indicate the starting set of which profile(s) to return. The value is a comma-delimited list of key values, where each key can be either a "WikiTree Id" or a "User Id". The WikiTree Id is the name used after "/wiki/" in the URL of the page. For example, for https://www.wikitree.com/wiki/Shoshone-1, the WikiTree Id is "Shonshone-1". The User Id is the value used in all of the person-to-person relationship references, like Father and Mother, and is the "Id" value returned for a profile.

### fields

The "fields" parameter is optional. If left out, a default set of fields is returned. The default is all fields other than the biography. With getPeople, you cannot request additional relatives for each profile, so the fields "Parents", "Children", "Siblings", and "Spouses" are not allowed. You can instead retrieve those profiles by using "nuclear=1" or setting ancestors/descendants to one or more generations. You can specify which fields to return by setting the "fields" parameter to a comma-separated list of those you want. You can also use "\*" to indicate "all fields". See [getProfile.md](getProfile.md) for the fields in each Person profile.

### bioFormat

If you request the "bio" field (the text biography for a Person profile), the default is to return the content as it's stored, with wiki markup. You can instead request that this markup be rendered into HTML (as it would appear on the profile's web page) by specifying a "bioFormat" of "html". If you use a bioFormat value of "both", then both the original wiki text and the rendered HTML will be returned.

## Results

| Field        | Description                                                                                                                     |
| ------------ | ------------------------------------------------------------------------------------------------------------------------------- |
| status       | Empty, or an error message for the full request                                                                                 |
| resultsByKey | Array where the keys are the incoming key values and the values are an array containing "Id" and, if necessary, "status"        |
| people       | Array where the keys are the Ids of every profile being returned and the values are the returned fields for that person profile |

## Examples

Get data for multiple profiles with one query:

```
curl 'https://api.wikitree.com/api.php?action=getPeople&keys=Clemens-1,Federer-4,Windsor-1,Hamill-277&fields=Id,PageId,Name,FirstName,LastNameAtBirth,BirthDate,DeathDate,Father,Mother'
[
  {
    "status": "",
    "resultByKey": {
      "Clemens-1": {
        "Id": 5185
      },
      "Federer-4": {
        "Id": 9505478
      },
      "Windsor-1": {
        "Id": 64662
      },
      "Hamill-277": {
        "Id": 11945631
      }
    },
    "people": {
      "5185": {
        "Id": 5185,
        "PageId": 7146,
        "Name": "Clemens-1",
        "FirstName": "Samuel",
        "LastNameAtBirth": "Clemens",
        "BirthDate": "1835-11-30",
        "DeathDate": "1910-04-21",
        "Father": 5186,
        "Mother": 5188
      },
      "64662": {
        "Id": 64662,
        "PageId": 74517,
        "Name": "Windsor-1",
        "FirstName": "Elizabeth",
        "LastNameAtBirth": "Windsor",
        "BirthDate": "1926-04-21",
        "DeathDate": "2022-09-08",
        "Father": 4368920,
        "Mother": 64660
      },
      "9505478": {
        "Id": 9505478,
        "PageId": 10000771,
        "Name": "Federer-4",
        "LastNameAtBirth": "Federer",
        "Father": 35997386,
        "Mother": 35997442
      },
      "11945631": {
        "Id": 11945631,
        "PageId": 12708307,
        "Name": "Hamill-277",
        "LastNameAtBirth": "Hamill",
        "Father": 12116601,
        "Mother": 12199752
      }
    }
  }
]
```

Get a profile, its parents, and its grandparents:

```
curl 'https://api.wikitree.com/api.php?action=getPeople&keys=Hamill-277&fields=Id,PageId,Name,FirstName,LastNameAtBirth,BirthDate,DeathDate,Father,Mother&ancestors=2'
[
  {
    "status": "",
    "resultByKey": {
      "Hamill-277": {
        "Id": 11945631
      }
    },
    "people": {
      "11945631": {
        "Id": 11945631,
        "PageId": 12708307,
        "Name": "Hamill-277",
        "LastNameAtBirth": "Hamill",
        "Father": 12116601,
        "Mother": 12199752
      },
      "12114913": {
        "Id": 12114913,
        "PageId": 12896009,
        "Name": "Hamill-298",
        "FirstName": "William",
        "LastNameAtBirth": "Hamill",
        "BirthDate": "1901-10-31",
        "DeathDate": "1987-06-04",
        "Father": 12114919,
        "Mother": 12577706
      },
      "12116601": {
        "Id": 12116601,
        "PageId": 12898009,
        "Name": "Hamill-300",
        "FirstName": "William",
        "LastNameAtBirth": "Hamill",
        "BirthDate": "1926-11-20",
        "DeathDate": "2014-01-28",
        "Father": 12114913,
        "Mother": 12577779
      },
      "12199752": {
        "Id": 12199752,
        "PageId": 12993464,
        "Name": "Johnson-43024",
        "FirstName": "Virginia",
        "LastNameAtBirth": "Johnson",
        "BirthDate": "1927-00-00",
        "DeathDate": "1998-00-00",
        "Father": 12199854,
        "Mother": 12199874
      },
      "12199854": {
        "Id": 12199854,
        "PageId": 12993571,
        "Name": "Johnson-43025",
        "FirstName": "Luther",
        "LastNameAtBirth": "Johnson",
        "BirthDate": "1894-10-31",
        "DeathDate": "1949-09-09",
        "Father": 12205211,
        "Mother": 12206976
      },
      "12199874": {
        "Id": 12199874,
        "PageId": 12993596,
        "Name": "Rhodes-3836",
        "FirstName": "Alice",
        "LastNameAtBirth": "Rhodes",
        "BirthDate": "1899-03-31",
        "DeathDate": "1952-11-11",
        "Father": 13795892,
        "Mother": 13795898
      },
      "12577779": {
        "Id": 12577779,
        "PageId": 13414894,
        "Name": "Mumford-663",
        "FirstName": "Helyn",
        "LastNameAtBirth": "Mumford",
        "BirthDate": "1906-04-03",
        "DeathDate": "1984-10-08",
        "Father": 19739190,
        "Mother": 33445592
      }
    }
  }
]
```

Get a profile's great-grandparents:

```
curl 'https://api.wikitree.com/api.php?action=getPeople&keys=Hamill-277&fields=Id,PageId,Name,FirstName,LastNameAtBirth,BirthDate,DeathDate,Father,Mother&ancestors=3&minGeneration=3'
[
  {
    "status": "",
    "resultByKey": {
      "Hamill-277": {
        "Id": 11945631
      }
    },
    "people": {
      "11945631": {
        "Id": 11945631,
        "PageId": 12708307,
        "Name": "Hamill-277",
        "LastNameAtBirth": "Hamill",
        "Father": 12116601,
        "Mother": 12199752
      },
      "12114919": {
        "Id": 12114919,
        "PageId": 12896015,
        "Name": "Hamill-299",
        "FirstName": "Hockley",
        "LastNameAtBirth": "Hamill",
        "BirthDate": "1873-09-21",
        "DeathDate": "1940-12-11",
        "Father": 19718315,
        "Mother": 12114941
      },
      "12205211": {
        "Id": 12205211,
        "PageId": 12999882,
        "Name": "Johnson-43059",
        "FirstName": "Sven",
        "LastNameAtBirth": "Johnson",
        "BirthDate": "1860-06-04",
        "DeathDate": "1946-05-19",
        "Father": 33462276,
        "Mother": 33461505
      },
      "12206976": {
        "Id": 12206976,
        "PageId": 13001915,
        "Name": "Peterson-6022",
        "FirstName": "Hanna",
        "LastNameAtBirth": "Peterson",
        "BirthDate": "1870-04-09",
        "DeathDate": "1950-11-24",
        "Father": 35949648,
        "Mother": 36097852
      },
      "12577706": {
        "Id": 12577706,
        "PageId": 13414810,
        "Name": "Haggart-23",
        "FirstName": "Katherine",
        "LastNameAtBirth": "Haggart",
        "BirthDate": "1879-00-00",
        "DeathDate": "1961-08-31",
        "Father": 33445555,
        "Mother": 35967875
      },
      "13795892": {
        "Id": 13795892,
        "PageId": 14762752,
        "Name": "Rhodes-4392",
        "FirstName": "Edward",
        "LastNameAtBirth": "Rhodes",
        "BirthDate": "1856-02-00",
        "DeathDate": "1946-12-08",
        "Father": 36097790,
        "Mother": 36147989
      },
      "13795898": {
        "Id": 13795898,
        "PageId": 14762758,
        "Name": "Alexander-8622",
        "FirstName": "Effie",
        "LastNameAtBirth": "Alexander",
        "BirthDate": "1865-07-00",
        "DeathDate": "1954-04-09",
        "Father": 36102591,
        "Mother": 36107186
      },
      "19739190": {
        "Id": 19739190,
        "PageId": 21403701,
        "Name": "Mumford-1008",
        "FirstName": "Frederick",
        "LastNameAtBirth": "Mumford",
        "BirthDate": "1872-01-18",
        "DeathDate": "1957-12-21",
        "Father": 19741233,
        "Mother": 36098182
      },
      "33445592": {
        "Id": 33445592,
        "PageId": 36825005,
        "Name": "Keating-2581",
        "FirstName": "Eliza",
        "LastNameAtBirth": "Keating",
        "BirthDate": "1873-12-06",
        "DeathDate": "1959-02-07",
        "Father": 36098110,
        "Mother": 36098129
      }
    }
  }
]
```
