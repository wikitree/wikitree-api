# WikiTree API: getDNATestsByTestTaker

The getDNATestsByTestTaker action returns the DNA Test information for the provided key.
This includes the tests taken along with additional information like haplogroup. These
are the tests displayed at https://www.wikitree.com/wiki/Special:DNATests.

## Parameters

|Param|Value|
|-----|-----|
|action|getDNATestsByTestTaker|
|key|WikiTree ID or Id|

### key

The "key" parameter is used to indicate which profile to return. This can be either a "WikiTree ID" or a "User ID". The WikiTree ID is the name used after "/wiki" in the URL of the Person page. For example, for [Person Profile Pages](https://www.wikitree.com/wiki/Help:Person_Profile) like https://www.wikitree.com/wiki/Whitten-1, the WikiTree ID is "Whitten-1".


## Results

|Field|Description|
|-----|-----------|
|dnaTests|Array of DNA Test information|
|For each DNA Test...|
|dna_id|Integer ID|
|dna_slug|A short descriptive label|
|dna_name|Display Title|
|assigned|Timestamp DNA Test was assigned to key's profile|
|assignedBy|WikiTree ID of member that assigned the Test to the key's profile|
|haplo|[Y chromosome haplogroup](https://www.wikitree.com/wiki/Help:Haplogroups)|
|markers|[Number of markers that were tested](https://www.wikitree.com/wiki/Help:YDNA_Test_Markers)|
|mttype|[Mitochondrial DNA Test Type](https://www.wikitree.com/wiki/Help:MtDNA_Test_Type)|
|ysearch|Ysearch ID (obsolete)|
|mitosearch|Mitosearch ID (obsolete)|
|ancestry|[Ancestry.com user name](https://www.wikitree.com/wiki/Help:AncestryDNA)|
|ftdna|[Family Tree DNA kit number](https://www.wikitree.com/wiki/Help:Family_Tree_DNA)|
|gedmatch|[GEDmatch ID](https://www.wikitree.com/wiki/Help:GEDMatch)|
|haplom|[Mitochondrial haplogroup](https://www.wikitree.com/wiki/Help:Haplogroups)|

## Examples

```
curl 'https://api.wikitree.com/api.php?action=getDNATestsByTestTaker&key=Whitten-1'

[
  {
    "page_name": "Whitten-1",
    "status": 0,
    "dnaTests": [
      {
        "dna_id": "1",
        "dna_slug": "23andme_audna",
        "dna_name": "23andMe",
        "dna_type": "auDNA",
        "assigned": "2020-04-02 13:40:30",
        "assignedBy": "Whitten-1",
        "haplo": "R1b1b2a1a1*",
        "markers": "0",
        "mttype": null,
        "ysearch": null,
        "mitosearch": null,
        "ancestry": null,
        "ftdna": null,
        "gedmatch": "",
        "haplom": "U5a1a1"
      },
      ...
    ]
  }
]
```

* [JavaScript](examples/getDNATestsByTestTaker/javascript.html)
