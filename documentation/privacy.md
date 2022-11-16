# Privacy

## Privacy Field
The WikiTree API returns an integer representing the [Privacy](https://www.wikitree.com/wiki/Help:Privacy) level of a profile. The Privacy level of a profile determines other fields that are available to be returned. For arrays [...], the same fields are returned as an object for the new person in question, adjusted by the new profile's privacy. If the new profile is <=30, no object will be returned.

For users on the Trusted List, authenticated with the Apps Server, the information returned for a profile will be the same as Open/Public.

### Open (60) and Public (50)
```
all fields
```

### Private with Public Biography and Family Tree (40) and Private with Public Family Tree (35)
```
[
    {
        "page_name": ...,
        "profile": {
            "Id": ...,
            "PageId": ...,
            "Name": ...,
            "IsPerson": ...,
            "MiddleInitial": ...,
            "LastNameAtBirth": ...,
            "LastNameCurrent": ...,
            "LastNameOther": ...,
            "RealName": ...,
            "Prefix": ...,
            "Suffix": ...,
            "BirthDateDecade": ...,
            "DeathDateDecade": ...,
            "Photo": ...,
            "IsLiving": ...,
            "Created": ...,
            "Touched": ...,
            "Privacy": 35,
            "Manager": ...,
            "Creator": ...,
            "HasChildren": ...,
            "NoChildren": ...,
            "IsRedirect": ...,
            "Connected": ...,
            "BirthNamePrivate": ...,
            "LongNamePrivate": ...,
            "Managers": [...],
            "Privacy_IsPrivate": ...,
            "Privacy_IsPublic": ...,
            "Privacy_IsOpen": ...,
            "Privacy_IsAtLeastPublic": ...,
            "Privacy_IsSemiPrivate": ...,
            "Privacy_IsSemiPrivateBio": ...,
            "Father": ...,
            "Mother": ...,
            "Parents": [...],
            "Children": [...],
            "Siblings": [...]
        },
        "status": 0
    }
]

```

### Private with Public Biography (30)
```
[
  {
    "page_name": ...,
    "profile": {
      "Id": ...,
      "PageId": ...,
      "IsPerson": ...,
      "Connected": ...,
      "Managers": [...]
  },
  "status": 0
  }
]
```

### Private (20) and Unlisted (10)
```
[
  {
    "page_name": ...,
    "profile": {
      "Id": ...,
      "PageId": ...,
      "IsPerson": ...,
      "Managers": [...]
  },
  "status": 0
  }
]
```

## Privacy-check Fields
In addition to the integers you can reutrn with the Privacy Field, there are Privacy-check fields (Is?) that can be used to determine the Privacy level of profiles.
| Privacy Level | IsOpen (60) | IsPublic (50) | IsAtLeastPublic (>=50) | IsSemiPrivate (30-40) | IsSemiPrivateBio (30) | IsPrivate (20)
| - | - | - | - | - | - | -
| Open (60) | `true` |  | `true` |  |  | 
| Public (50) |  | `true` | `true` |  |  | 
| Private with Public Biography and Family Tree (40) |  |  |  | `true` | |  
| Private with Public Family Tree (35) |  |  |  | `true` | |  
| Private with Public Biography (30) |  |  |  | `true` | `true`|  
| Private (20) |  |  |  |  | | `true`
| Unlisted (10) |  |  |  |  |  | 
