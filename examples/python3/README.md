# Python3 Example

THis python3 example class provides a complete, single-class solution to both authenticated
and un-authenticated sessions.

It provides convenience methods for all currently documented API calls.

The file, as run, will request credentials and then perform a few queries based on the
user logged in.

You could also import the class in your own code:

```python
from wt_session import WTSession
```

First, create an instance.

```python
    # Create the WikiTree session object.
    wt_session = WTSession()
```

If you wish to authenticate, try something like:

```python
    # Loop until we have a successful authentication
    success = False
    while not success:
        email = input("Email (or quit): ")
        if email.lower() == "quit":
            sys.exit(0)
        password = getpass("Password: ")
        success = wt_session.authenticate(email=email, password=password)
```

## WTSession class help

```python
from wt_session import WTSession

help(WTSession)

Help on class WTSession in module wt_session:

class WTSession(builtins.object)
 |  WTSession() -> None
 |  
 |  A class to manage a session on the WikiTree API.
 |  If authenticated, manages the session cookie to allow
 |  calls the require it.
 |  
 |  Provides convenience functions for each of the API calls.
 |  
 |  Methods defined here:
 |  
 |  __init__(self) -> None
 |      Just default some values.
 |  
 |  authenticate(self, email: str, password: str) -> bool
 |      Takes an email address and password and attempts to authenticate. Returns
 |      a boolean to indicate success.
 |      
 |      :param email: The email address of the user.
 |      :param password: The corresponding password.
 |      :returns: Boolean indicating success.
 |  
 |  get_ancestors(self, key: Union[str, int], depth: int = 1, fields: Union[str, NoneType] = None, bio_format: Union[str, NoneType] = None)
 |      Uses the getAncestors API call to return one or more
 |      person profiles.
 |      
 |      :param key: Wanted WikiTree_ID or User_ID
 |      :param depth: Number of generations
 |      :param fields: Comma separated list of required fields
 |      :param bio_format: "wiki", "html", or "both"
 |  
 |  get_bio(self, key: Union[str, int], bio_format: Union[str, NoneType] = None) -> dict
 |      Uses the getBio API call to return the bio of the given
 |      profile.
 |      
 |      :param key: Wanted WikiTree_ID or User_ID
 |      :param bio_format: "wiki", "html", or "both"
 |  
 |  get_connected_dna_tests_by_profile(self, key: Union[str, int])
 |      getConnectedDNATestsByProfile
 |      
 |      :param key: Wanted WikiTree_ID or User_ID
 |  
 |  get_connected_profiles_by_dna_test(self, key: Union[str, int], dna_id: int)
 |      getConnectedProfilesByDNATest
 |      
 |      :param key: Wanted WikiTree_ID or User_ID
 |      :dna_id: ID of DNA test
 |  
 |  get_descendants(self, key: Union[str, int], depth: int = 1, fields: Union[str, NoneType] = None, bio_format: Union[str, NoneType] = None)
 |      Uses the getDescendants API call to return one or more
 |      person profiles.
 |      
 |      :param key: Wanted WikiTree_ID or User_ID
 |      :param depth: Number of generations
 |      :param fields: Comma separated list of required fields
 |      :param bio_format: "wiki", "html", or "both"
 |  
 |  get_dna_tests_by_test_taker(self, key: Union[str, int])
 |      getDNATestsByTestTaker
 |      
 |      :param key: Wanted WikiTree_ID or User_ID
 |  
 |  get_person(self, key: Union[str, int], fields: Union[str, NoneType] = None, bio_format: Union[str, NoneType] = None)
 |      Uses the getPerson API call to return a person profile.
 |      
 |      :param key: Wanted WikiTree_ID or User_ID
 |      :param fields: Comma separated list of required fields
 |      :param bio_format: "wiki", "html", or "both"
 |  
 |  get_photos(self, key: Union[str, int], limit: int = 10, start: int = 0, order: Union[wt_session.PhotoOrder, str] = <PhotoOrder.PAGE_ID: 'PageId'>)
 |      Uses the getPhotos API call to return a list of the
 |      photos attached to a profile.
 |      
 |      :param key: Wanted WikiTree_ID or User_ID
 |      :param limit: Number of photos to return
 |      :param start: The starting position in the list of photos
 |      :param order: The order in which to return the results. Can be one
 |                    of the PhotoOrder enumerations or the corresponding string.
 |  
 |  get_profile(self, key: Union[str, int], fields: Union[str, NoneType] = None, bio_format: Union[str, NoneType] = None) -> dict
 |      getProfile
 |      
 |      :param key: Wanted WikiTree_ID or Page_ID
 |      :param fields: Comma separated list of required fields
 |      :param bio_format: "wiki", "html", or "both"
 |  
 |  get_relatives(self, key: Union[str, int], fields: Union[str, NoneType] = None, bio_format: Union[str, NoneType] = None, get_parents: bool = True, get_children: bool = True, get_siblings: bool = True, get_spouses: bool = True)
 |      getRelatives
 |      
 |      :param key: Wanted WikiTree_ID or User_ID
 |      :param fields: Comma separated list of required fields
 |      :param bio_format: "wiki", "html", or "both"
 |      :param get_parents: Whether to get the parents
 |      :param get_children: Whether to get the children
 |      :param get_siblings: Whether to get the siblings
 |      :param get_spouses: Whether to get the spouses
 |  
 |  get_watchlist(self, limit: int = 100, offset: int = 0, order: Union[wt_session.WatchlistOrder, str] = <WatchlistOrder.USER_ID: 'user_id'>, get_person: bool = True, get_space: bool = True, only_living: bool = False, exclude_living: bool = False, fields: Union[str, NoneType] = None, bio_format: Union[str, NoneType] = None)
 |      getWatchlist
 |  
 |  search_person(self, **kwargs)
 |      searchPerson
 |      
 |      keyword args should be from:
 |          FirstName
 |          LastName
 |          BirthDate
 |          DeathDate
 |          RealName
 |          LastNameCurrent
 |          BirthLocation
 |          DeathLocation
 |          Gender
 |          fatherFirstName
 |          fatherLastName
 |          motherFirstName
 |          motherLastName
 |          watchlist
 |          dateInclude
 |          dateSpread
 |          centuryTypo
 |          isLiving
 |          skipVariants
 |          lastNameMatch
 |          sort
 |          secondarySort
 |          limit
 |          start
 |          fields
 |  
 |  ----------------------------------------------------------------------
 |  Data descriptors defined here:
 |  
 |  __dict__
 |      dictionary for instance variables (if defined)
 |  
 |  __weakref__
 |      list of weak references to the object (if defined)
 |  
 |  authenticated
 |      Returns whether you are logged in or not.
 |  
 |  email
 |      Return the email of the authenticated user.
 |  
 |  user_id
 |      Return the user ID of the authenticated user.
 |  
 |  user_name
 |      Return the user name of the authenticated user.
```
