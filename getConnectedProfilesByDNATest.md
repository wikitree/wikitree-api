# WikiTree API: getConnectedProfilesByDNATest

The getConnectedProfilesByDNATest action returns profiles connected to a test-taker profile
through a particular DNA Test.

## Parameters

|Param|Value|
|-----|-----|
|action|getConnectedProfilesByDNATest|
|key|WikiTree ID or Id|
|dna_id|ID of DNA Test|

### key

The "key" parameter is used to indicate the DNA test taker profile. This can be either a "WikiTree ID" or a "User ID". The WikiTree ID is the name used after "/wiki" in the URL of the Person page. For example, for [Person Profile Pages](https://www.wikitree.com/wiki/Help:Person_Profile) like https://www.wikitree.com/wiki/Whitten-1, the WikiTree ID is "Whitten-1".

### dna_id

This is the integer ID that specifies a DNA test assigned to the key's test taker which should be used to find connected profiles.

The options are:

|ID|Test Type|
|--|---------|
|1|23andMe|
|2|AncestryDNA|
|3|AncestryDNA Paternal Lineage|
|4|AncestryDNA Maternal Lineage|
|6|Family Tree DNA Family Finder|
|7|Family Tree DNA mtDNA|
|8|Family Tree DNA yDNA|
|9|Other auDNA|
|10|Other mtDNA|
|11|Other yDNA|
|12|MyHeritage DNA|
|13|Living DNA|


## Results

|Field|Description|
|-----|-----------|
|dnaTest|Data for selected test #dna_id assigned to test-taker key. See [getDNATestsByTestTaker](getDNATestsByTestTaker.md).|
|connections|Array of connected-profiles|
|For each connections item...|
|ID|The User ID of the connected profile|
|PageID|The Page ID of the connected profile|
|Name|The WikiTree ID of the connected profile|

## Examples

```
curl 'https://api.wikitree.com/api.php?action=getConnectedProfilesByDNATest&key=Whitten-1&dna_id=8'

[
  {
    "page_name": "Whitten-1",
    "status": 0,
    "dnaTest": {
      "dna_id": "8",
      "dna_slug": "ftdna_ydna",
      "dna_name": "FTDNA Y-Chromosome",
      "dna_type": "yDNA",
      "assigned": null,
      "assignedBy": null,
      "haplo": null,
      "markers": null,
      "mttype": null,
      "ysearch": null,
      "mitosearch": null,
      "ancestry": null,
      "ftdna": null,
      "gedmatch": null,
      "haplom": null
    },
    "connections": [
      {
        "Id": "13654071",
        "PageId": "14605469",
        "Name": "Whitten-1205"
      },
      {
        "Id": "7156327",
        "PageId": "7419651",
        "Name": "Whitten-692"
      },
      {
        "Id": "45783",
        "PageId": "53867",
        "Name": "Whitten-56"
      },
      ...
    ]
  }
]
```

* [JavaScript](examples/getDNATestsByTestTaker/javascript.html)
