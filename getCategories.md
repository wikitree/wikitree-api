# WikiTree API: getCategories

The getCategories action returns the [Categories](https://www.wikitree.com/wiki/Help:Categorization) connected to a profile.

## Parameters

|Param|Value|
|-----|-----|
|action|getCategories|
|key|Free Space Profile Name, WikiTree ID, or Page ID|

### key

The "key" parameter is used to indicate which profile to return. This can be either a "WikiTree ID" or a "Page ID". The WikiTree ID is the name used after "/wiki" in the URL of the page. For example, for [Person Profile Pages](https://www.wikitree.com/wiki/Help:Person_Profile) like https://www.wikitree.com/wiki/Shoshone-1, the WikiTree ID is "Shonshone-1". For [Free-Space Profile Pages](https://www.wikitree.com/wiki/Help:Free-Space_Profile), the "Space:" prefix is required. For example, for https://www.wikitree.com/wiki/Space:Space:Edward_D._Whitten%27s_Model_Ships, the WikiTree ID is "Space:Edward_D._Whitten%27s_Model_Ships". 

## Results

|Field|Description|
|-----|-----------|
|categories|Array of Category titles connected to the profile|


## Examples

```
curl 'https://api.wikitree.com/api.php?action=getCategories&key=Shoshone-1'

[
  {
    "page_name": "Shoshone-1",
    "categories": [
      "Example_Profiles_of_the_Week",
      "Lemhi_Shoshone",
      "Lewis_and_Clark_Expedition",
      "Lewis_and_Clark_Expedition_Project",
      "National_Women's_Hall_of_Fame_(United_States)",
      "Notables",
      "Shoshone",
      "United_States_of_America,_Notables"
    ],
    "status": 0
  }
]
```

* [JavaScript](examples/getCategories/javascript.html)
