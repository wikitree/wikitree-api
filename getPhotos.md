# WikiTree API: getPhotos

## Parameters

|Param|Value|
|-----|-----|
|action|getPhotos|
|key|Surname-# WikiTree ID, Space:PageName free-space profile name, or PageId|
|resolveRedirect|Optional. If 1, then requested profiles that are redirections are followed to the final profile|
|limit|The number of photos to return. Default = 10. Maximum = 100.|
|start|The starting photo of the set. Default = 0.|
|order|The sort order of the photos. Valid values are: "PageId", "Uploaded", "ImageName", "Date". Default is "PageId".|

### key

The "key" parameter is used to indicate which profile to return. This can be either a "WikiTree ID" or a "Page ID". The WikiTree ID is the name used after "/wiki" in the URL of the page. For example, for [Person Profile Pages](https://www.wikitree.com/wiki/Help:Person_Profile) like https://www.wikitree.com/wiki/Shoshone-1, the WikiTree ID is "Shonshone-1". For [Free-Space Profile Pages](https://www.wikitree.com/wiki/Help:Free-Space_Profile), the "Space:" prefix is required. For example, for https://www.wikitree.com/wiki/Space:Space:Edward_D._Whitten%27s_Model_Ships, the WikiTree ID is "Space:Edward_D._Whitten%27s_Model_Ships". 


### resolveRedirect

Generally if you start at a valid profile and follow use the ids associated with relationships (mother, father) you should get a valid/complete profile in return. However, in some circumstances you may end up requesting a profile that has been merged away into another profile, or otherwise is redirected. If you set resolveRedirect=1 in your POST to the API, then any profiles that would be returned that are redirections will be followed to their end point, and *that* final profile will be returned.

### limit

Each profile can have more than one photo attached. This parameter determines the number of photos returned, starting with the "start" parameter index, with the photos sorted by the "order". If no "limit" is given, 10 is used. The maximum allowed value is 100.

### start

The starting index of the result set of photos. The default is 0.

### order

The result set can be sorted by "PageId" (the default), "Uploaded" (the uploaded timestamp), "Date" (the descriptive date), or "ImageName" (the base filename of the image).


## Results

|Field|Description|
|-----|-----------|
|page_name or page_id|The translated "key" parameter|
|limit|Incoming "limit" parameter|
|start|Incoming "start" parameter|
|order|Incoming "order" parameter|
|photos|Array of photo results|

Each photo in the "photos" array contains:

|Field|Description|
|-----|-----------|
|PageId|The PageId of the image/photo|
|ImageName|Base filename of the photo|
|Title|Title from Image Details|
|Location|Location from Image Details|
|Date|Date from Image Details|
|Type|Type from ImageDetails: "photo" or "source"|
|Size|Size of image in bytes|
|Width|Width of image in pixels|
|Height|Height of image in pixels|
|Uploaded|Date/Time when the image was uploaded to WikiTree.com|
|URL|The URL (relative to https://wikitree.com/) of the Image page. Usually /photo/ext/ImageName.|
|URL_300|The relative URL of the 300px version of the image|
|URL_75|The relative URL of the 75px version of the image|


## Examples

```
curl 'https://api.wikitree.com/api.php?action=getPhotos&key=Clemens-1&limit=2&start=5&order=Date'

[
  {
    "page_name": "Clemens-1",
    "limit": 2,
    "start": 5,
    "order": "Date",
    "photos": [
      {
        "PageId": "7299",
        "ImageName": "1860-_Clemens.jpg",
        "Title": "Samuel L. Clemens",
        "Location": "",
        "Date": "1860-00-00",
        "Type": "photo",
        "Size": "31673",
        "Width": "571",
        "Height": "750",
        "Uploaded": "2009-01-08 00:25:37",
        "URL": "/photo/jpg/1860-_Clemens",
        "URL_300": "/photo.php/thumb/f/f1/1860-_Clemens.jpg/300px-1860-_Clemens.jpg",
        "URL_75": "/photo.php/thumb/f/f1/1860-_Clemens.jpg/75px-1860-_Clemens.jpg"
      },
      {
        "PageId": "7300",
        "ImageName": "1871-Samuel_Clemens.jpg",
        "Title": "Mark Twain (Samuel L. Clemens)",
        "Location": "",
        "Date": "1871-00-00",
        "Type": "photo",
        "Size": "34494",
        "Width": "382",
        "Height": "600",
        "Uploaded": "2009-01-08 00:26:44",
        "URL": "/photo/jpg/1871-Samuel_Clemens",
        "URL_300": "/photo.php/thumb/0/08/1871-Samuel_Clemens.jpg/300px-1871-Samuel_Clemens.jpg",
        "URL_75": "/photo.php/thumb/0/08/1871-Samuel_Clemens.jpg/75px-1871-Samuel_Clemens.jpg"
      }
    ],
    "status": 0
  }
]
```

* [JavaScript](examples/getPhotos/javascript.html)
* Python - web page, command line
* [PHP](examples/getProfile/phpWebPage.php) (also have command-live example)
