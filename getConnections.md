# WikiTree API: getConnections

`getConnections` allows you to find the relationship path and/or calculate the degrees of separation between two WikiTree profiles. This is useful for discovering how two people are related through the family tree, whether through blood relations, marriage, or other connections.

## Parameters

| Param     | Value                                                                    |
| --------- | ------------------------------------------------------------------------ |
| action    | getConnections                                                           |
| keys      | Two WikiTree IDs or User IDs separated by commas.                        |
| appId     | Required identifier for your app.                                        |
| fields    | Optional comma-separated list of fields to return.                       |
| relation  | A digit representing one of the options listed at [relation](#relation). |
| ignoreIds | Optional comma-separated list of User IDs to ignore.                     |
| nopath    | Set to 1 if you only want `pathLength` and not the full `path`.          |

### keys

You can provide two WikiTree IDs (e.g., "Adams-35,Windsor-1"), or two User IDs (e.g., "3636,64662").

If either of the keys is for a profile you don't have permission to view, a path won't be returned.

### appId

This is required, otherwise you will get an error message.

### fields

See [getProfile](getProfile.md#results) for the fields that are available.

### relation

Controls the type of relationship path to find. Default is 0 (shortest path).

| Option | Description                                                                                       |
| ------ | ------------------------------------------------------------------------------------------------- |
| 0      | Shortest path                                                                                     |
| 1      | Shortest path excluding spouses                                                                   |
| 2      | Shortest path through a common ancestor                                                           |
| 3      | Shortest path through a common descendant                                                         |
| 4      | Shortest path through fathers only                                                                |
| 5      | Shortest path through mothers only                                                                |
| 6      | Shortest path through yDNA                                                                        |
| 7      | Shortest path through mtDNA                                                                       |
| 8      | Shortest path through auDNA                                                                       |
| 11     | Shortest path through ancestors (2) (if found), otherwise shortest path through all relations (0) |

### ignoreIds

You can provide a comma-separated list of User IDs you want to ignore in the path calculation.

WikiTree IDs won't work here.

## Results

| Field      | Description                                                                     |
| ---------- | ------------------------------------------------------------------------------- |
| status     | Error message if any, blank if successful.                                      |
| userid1    | User ID of the first profile.                                                   |
| userid2    | User ID of the second profile.                                                  |
| relation   | The `relation` specified above. Default 0.                                      |
| ignoreids  | User IDs that were ignored in the path calculation.                             |
| path       | See [path](#path) below.                                                        |
| pathType   | Will be the same as `relation` specified above, unless 11 is chosen. Default 0. |
| pathLength | Length of the path between the two profiles.                                    |

### path

An array containing information about each profile in the path.

If you don't have permission to view a profile in the path, that profile will have a negative number as an ID.

| Field      | Description                                                                                                                     |
| ---------- | ------------------------------------------------------------------------------------------------------------------------------- |
| Id         | The User ID of the profile e.g. 3636                                                                                            |
| Name       | The WikiTree ID of the profile e.g. Adams-35                                                                                    |
| pathType   | A description of the relationship between the previous profile in the path, and the current profile. Examples: 'mother', 'son'. |
| pathStatus | The certainty status for the relationship 30=confident 20=unknown 0=?                                                           |

## Examples

Get the path between two profiles.

```
curl 'https://api.wikitree.com/api.php?action=getConnections&appId=apiDocumentation&keys=Adams-35,Windsor-1&fields=Id,Name'


[
    {
        "status":"",
        "userid1":3636,
        "userid2":64662,
        "relation":0,
        "ignoreids":[],
        "path":[
            {
                "Id":3636,
                "Name":"Adams-35"
            },
            {
                "Id":3586,
                "Name":"Adams-10",
                "pathType":"child",
                "pathStatus":"20"
            },
            {
                "Id": 3589,
                "Name": "Adams-12",
                "pathType": "child",
                "pathStatus": "20"
            },
            {
                "Id": 3595,
                "Name": "Johnson-24",
                "pathType": "spouse",
                "pathStatus": 0
            },
            {
                "Id": 16668996,
                "Name": "Johnson-59008",
                "pathType": "sibling",
                "pathStatus": 0
            },
            {
                "Id": 415948,
                "Name": "Buchanan-51",
                "pathType": "spouse",
                "pathStatus": 0
            },
            {
                "Id": 20771641,
                "Name": "Buchanan-4953",
                "pathType": "parent",
                "pathStatus": 0
            },
            {
                "Id": 16560608,
                "Name": "Buchanan-3978",
                "pathType": "sibling",
                "pathStatus": "20"
            },
            {
                "Id": 26733157,
                "Name": "Gittings-199",
                "pathType": "child",
                "pathStatus": 0
            },
            {
                "Id": 21375339,
                "Name": "Gittings-177",
                "pathType": "child",
                "pathStatus": 0
            },
            {
                "Id": 14119532,
                "Name": "Emory-194",
                "pathType": "child",
                "pathStatus": 0
            },
            {
                "Id": 22169,
                "Name": "Warfield-7",
                "pathType": "child",
                "pathStatus": 0
            },
            {
                "Id": 22172,
                "Name": "Warfield-8",
                "pathType": "child",
                "pathStatus": "20"
            },
            {
                "Id": 4928583,
                "Name": "Sachsen-Coburg_und_Gotha-5",
                "pathType": "spouse",
                "pathStatus": 0
            },
            {
                "Id": 4368920,
                "Name": "Sachsen-Coburg_und_Gotha-4",
                "pathType": "sibling",
                "pathStatus": "20"
            },
            {
                "Id": 64662,
                "Name": "Windsor-1",
                "pathType": "child",
                "pathStatus": "20"
            }
        ],
        "pathType": 0,
        "pathLength": 16
    }
]
```

Get the length of the path between two profiles.

```
curl 'https://api.wikitree.com/api.php?action=getConnections&appId=apiDocumentation&keys=Adams-35,Windsor-1&nopath=1'


[
    {
        "status": "",
        "userid1": 3636,
        "userid2": 64662,
        "relation": 0,
        "ignoreids": [],
        "path": [],
        "pathType": 0,
        "pathLength": 16
    }
]
```

Get the path between Adams-35 and Windsor-1, but don't go through Gittings-177 (21375339).

```
curl 'https://api.wikitree.com/api.php?action=getConnections&appId=apiDocumentation&keys=Adams-35,Windsor-1&fields=Id,Name&ignoreIds=21375339'

[
    {
        "status": "",
        "userid1": 3636,
        "userid2": 64662,
        "relation": 0,
        "ignoreids": [21375339],
        "path": [
            {
                "Id": 3636,
                "Name": "Adams-35"
            },
            {
                "Id": 3654,
                "Name": "Adams-44",
                "pathType": "sibling",
                "pathStatus": "10"
            },
            {
                "Id": 12083726,
                "Name": "Owen-4113",
                "pathType": "spouse",
                "pathStatus": 0
            },
            {
                "Id": 2578190,
                "Name": "Owens-943",
                "pathType": "parent",
                "pathStatus": 0
            },
            {
                "Id": 1508431,
                "Name": "Owen-623",
                "pathType": "sibling",
                "pathStatus": "20"
            },
            {
                "Id": 9038342,
                "Name": "Williams-27227",
                "pathType": "child",
                "pathStatus": "20"
            },
            {
                "Id": 13226688,
                "Name": "Dewey-964",
                "pathType": "child",
                "pathStatus": 0
            },
            {
                "Id": 15788248,
                "Name": "Mosely-256",
                "pathType": "child",
                "pathStatus": "20"
            },
            {
                "Id": 16457067,
                "Name": "Mosely-276",
                "pathType": "child",
                "pathStatus": "20"
            },
            {
                "Id": 10814307,
                "Name": "Moseley-1093",
                "pathType": "child",
                "pathStatus": "20"
            },
            {
                "Id": 10814315,
                "Name": "Jessup-375",
                "pathType": "child",
                "pathStatus": 0
            },
            {
                "Id": 14419879,
                "Name": "Jessup-525",
                "pathType": "child",
                "pathStatus": 0
            },
            {
                "Id": 3262187,
                "Name": "Bowes-Lyon-19",
                "pathType": "spouse",
                "pathStatus": 0
            },
            {
                "Id": 64658,
                "Name": "Bowes-Lyon-4",
                "pathType": "sibling",
                "pathStatus": 0
            },
            {
                "Id": 64660,
                "Name": "Bowes-Lyon-5",
                "pathType": "child",
                "pathStatus": "20"
            },
            {
                "Id": 64662,
                "Name": "Windsor-1",
                "pathType": "child",
                "pathStatus": "20"
            }
        ],
        "pathType": 0,
        "pathLength": 16
    }
]
```
