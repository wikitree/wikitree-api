# WikiTree API: getRelatives

The getRelatives action returns one or more sets of person profiles like those from [getProfile](getProfile.md). From a starting profile, specified by a "key" that is a WikiTree ID or User ID, you can retrieve parents, children, siblings, and/or spouses.

## Parameters

|Param|Value|
|-----|-----|
|action|getRelatives|
|keys|Comma-separated list of WikiTree IDs|
|fields|Optional comma-separated list of fields to return for each profile|
|bioFormat|Optional: "wiki", "html", or "both"|
|getParents|If true, the parents are returned|
|getChildren|If true, the children are returned|
|getSiblings|If true, the siblings are returned|
|getSpouses|If true, the spouses are returned|

### keys

The "keys" parameter is used to indicate which profile(s) to return relatives for. The value should be a comma-separated list of keys. Each key can be either a "WikiTree ID" or a "User ID". The WikiTree ID is the name used after "/wiki" in the URL of the Person page. For example, for [Person Profile Pages](https://www.wikitree.com/wiki/Help:Person_Profile) like https://www.wikitree.com/wiki/Shoshone-1, the WikiTree ID is "Shonshone-1".

### fields

The "fields" parameter is optional. If left out, a default set of fields is returned. For Person profile pages, the default is all fields other than the biography, children and spouses. For Free-Space profile pages, the default is to return all fields.

You can specify which fields to return by setting the "fields" parameter to a comma-separated list of those you want. You can also use "*" to indicate "all fields". 

### bioFormat

If you request the "bio" field (the text biography for a Person profile), the default is to return the content as it's stored, with wiki markup. You can instead request that this markup be rendered into HTML (as it would appear on the profile's web page) by specifying a "bioFormat" of "html". If you use a bioFormat value of "both", then both the original wiki text and the rendered HTML will be returned.

## Results

The returned results includes an "items" element which is an array of result items. Each element has the following fields.

|Field|Description|
|-----|-----------|
|key|The key of the starting profile|
|user_id|The User/Person ID of the starting profile|
|user_name|The WikiTree ID of the starting profile|
|person|The Person fields requested|
|Parents|Array of Person profiles, keyed by the User/Person ID, with the requested fields, of each parent of the starting profile|
|Children|Array of Person profiles, keyed by the User/Person ID, with the requested fields, of each child of the starting profile|
|Siblings|Array of Person profiles, keyed by the User/Person ID, with the requested fields, of each sibling of the starting profile|
|Spouses|Array of marriage information, keyed by the User/Person ID of each spouse of the starting profile|

See [getProfile.md](getProfile.md) for the fields in each Person profile.


## Examples

```
curl 'https://api.wikitree.com/api.php?action=getRelatives&keys=Clemens-1,Adams-35&fields=Id,PageId,Name&getChildren=1&getParents=1&getSiblings=1&getSpouses=1'

[
  {
    "items": [
      {
        "key": "Clemens-1",
        "user_id": 5185,
        "user_name": "Clemens-1",
        "person": {
          "Id": 5185,
          "PageId": 7146,
          "Name": "Clemens-1",
          "Father": 5186,
          "Mother": 5188,
          "Parents": {
            "5186": {
              "Id": 5186,
              "PageId": 7147,
              "Name": "Clemens-2",
              "Father": 5201,
              "Mother": 5202
            },
            "5188": {
              "Id": 5188,
              "PageId": 7150,
              "Name": "Lampton-1",
              "Father": 5189,
              "Mother": 5190
            }
          },
          "Spouses": {
            "5256": {
              "Id": 5256,
              "PageId": 7284,
              "Name": "Langdon-1",
              "Father": 5258,
              "Mother": 5259,
              "marriage_location": "Elmira, New York, USA",
              "marriage_date": "1870-02-02",
              "marriage_end_date": "0000-00-00",
              "do_not_display": "0"
            }
          },
          "Children": {
            "5260": {
              "Id": 5260,
              "PageId": 7289,
              "Name": "Clemens-12",
              "Father": 5185,
              "Mother": 5256
            },
            "5261": {
              "Id": 5261,
              "PageId": 7290,
              "Name": "Clemens-13",
              "Father": 5185,
              "Mother": 5256
            },
            "5262": {
              "Id": 5262,
              "PageId": 7292,
              "Name": "Clemens-14",
              "Father": 5185,
              "Mother": 5256
            },
            "5265": {
              "Id": 5265,
              "PageId": 7296,
              "Name": "Clemens-15",
              "Father": 5185,
              "Mother": 5256
            }
          },
          "Siblings": {
            "5191": {
              "Id": 5191,
              "PageId": 7155,
              "Name": "Clemens-3",
              "Father": 5186,
              "Mother": 5188
            },
            "5194": {
              "Id": 5194,
              "PageId": 7160,
              "Name": "Clemens-5",
              "Father": 5186,
              "Mother": 5188
            },
            "5195": {
              "Id": 5195,
              "PageId": 7162,
              "Name": "Clemens-6",
              "Father": 5186,
              "Mother": 5188
            },
            "5198": {
              "Id": 5198,
              "PageId": 7166,
              "Name": "Clemens-7",
              "Father": 5186,
              "Mother": 5188
            },
            "5199": {
              "Id": 5199,
              "PageId": 7167,
              "Name": "Clemens-8",
              "Father": 5186,
              "Mother": 5188
            },
            "5200": {
              "Id": 5200,
              "PageId": 7168,
              "Name": "Clemens-9",
              "Father": 5186,
              "Mother": 5188
            }
          }
        }
      },
      {
        "key": "Adams-35",
        "user_id": 3636,
        "user_name": "Adams-35",
        ...
        }
      }
    ],
    "status": 0
  }
]
```

* [JavaScript](examples/getRelatives/javascript.html)
