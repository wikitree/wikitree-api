Markdown: https://guides.github.com/features/mastering-markdown/



======================================================================



# WikiTree API

## Introduction

The WikiTree API provides read-only access to much of the profile data on the site. You can use it for your own applications that read and display the contents. If you want to enable updates to the content, you can use the data from the API and send your users through edit functions on the site.

TO DO - need more useful copy here about the API.


## Getting Started


The WikiTree API is called through the end point: *https://api.wikitree.com/api.php*

Parameters can be passed via either GET or POST. For most functions, you will specify an "action", and many also use a "key" to indicate which profile(s) you're working on. The results are returned as JSON. For example:

```
curl 'https://api.wikitree.com/api.php?action=getProfile&key=Clemens-1'

[
  {
    "page_name": "Clemens-1",
    "profile": {
      "Id": 5185,
      "Name": "Clemens-1",
      "FirstName": "Samuel",
      "MiddleName": "Langhorne",
      "LastNameAtBirth": "Clemens",
	...
    },
    "status": 0
  }
]
```

The "status" field is generally zero (0) if there is no error. It can contain an error message if something went wrong.

```
curl 'https://api.wikitree.com/api.php?action=getProfile&key=ThisIsABadWikiTreeID'

[
  {
    "page_name": "ThisIsABadWikiTreeID",
    "status": "Illegal WikiTree ID"
  }
]
```

Person profiles on WikiTree have a Privacy level. Only public profiles can be viewed through the API unless you authenticate as a particular user. Once you've authenticated, you can retrieve the data for profiles that have you on the trusted list. Requesting a private profile does not generate an error, but you only receive a privacy-limited data set.

```
curl 'https://api.wikitree.com/api.php?action=getProfile&key=Burk-1'

[
  {
    "page_name": "Burk-1",
    "profile": {
      "Id": 7352,
      "Name": "Burk-1",
      "IsPerson": 1
    },
    "status": 0
  }
]
```

## Actions

|Action|Result|
|------|------|
|[getProfile](getProfile.md)|Retrieve a Person or Free-Space profile|
|[getBio](getBio.md)|Get the biography text for a person profile|
|[getAncestors](getAncestors.md)|Get multiple generations of ancestors of a person profile, following father and mother|
|[getDescendants](getDescendants.md)|Get multiple generations of descendants of a person profile, following children|
|[getRelatives](getRelatives.md)|Get parents, children, siblings, and/or spouses of a profile|
|[getWatchlist](getWatchlist.md)|Get the profiles on the watch list of the logged-in profile|
|[getDNATestsByTestTaker](getDNATestsByTestTaker.md)|Get the list of DNA Tests taken by a profile|
|[getConnectedProfilesByDNATest](getConnectedProfilesByDNATest.md)|Get the profiles connected via DNA test|
|[getConnectedDNATestsByProfile](getConnectedDNATestsByProfile.md)|Get the DNA Tests connected to a profile|
|[searchPerson](searchPerson.md)|Search for person profiles|


## Basic Examples

### Curl

```
curl 'https://api.wikitree.com/api.php?action=getProfile&key=Franklin-10478'

[
  {
    "page_name": "Franklin-10478",
    "profile": {
      "Id": 20674257,
      "Name": "Franklin-10478",
      "FirstName": "Aretha",
      "MiddleName": "Louise",
      "LastNameAtBirth": "Franklin",
	...
      }
    },
    "status": 0
  }
]
```

### JavaScript + jQuery

```
$.post(
	'https://api.wikitree.com/api.php',
	{
		'action': 'getProfile',
		'key': 'Shoshone-1'
	},
	function(result) {
	}
);
```

### PHP
### Python
### ???




## Authentication

Requires logging in as WikiTree user at wikitree.com. Cookie is set there. ID is carried thru subsequent
API calls with that cookie. This works naturally for interactive use in browser (e.g. JavaScript) which 
includes the cookies for (?).wikitree.com in posts. For command line usage, you have to mimic this manually.

- Interactive = sends viewing user thru wikitree.com, returns with token
	- JS example

- Command line = requires handling cookie and whatnot interaction as with interactive
	- curl example


## Complete/Cookbook Examples

- Web page with user authentication and then manipulation of profiles on their watchlist (JS)

	- use this auth example code/sequence in the others below as optional sign-in


- "Get Family" JavaScript/etc load family circle (ancestors, descendants) via manual walk of ids
- 	and then via getAncestors/getRelatives/

- "Find oldest grand+ parent"

- "Family Story" - JavaScript/etc family story - bio snippets for nuclear family


- Chrome plugin to do something with WikiTree person profile pages. (JS only)


## Apps.WikiTree.com

Hosting space of user applications

- on https://apps.wikitree.com/
- Basic file storage for viewing through web browser
- SFTP access only (no control panel for files or anything)

