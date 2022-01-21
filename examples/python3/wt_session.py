"""
This module defines a class that manages an authenticated session
with the WikiTree API.

It allows you to log in and perform various queries. These are documented at:
    https://github.com/wikitree/wikitree-api

It requires python3 and the requests library.
"""

# Standard Imports
from getpass import getpass
from json.decoder import JSONDecodeError
import re
from pprint import pprint
from typing import Optional, Union

# Third party imports
import requests


# Define the WikiTree API endpoint
API_URL = "https://api.wikitree.com/api.php"


class WTSession:
    """
    A class to manage an authenticated session on the WikiTree API.

    Provides convenience functions for each of the API calls
    """

    def __init__(self) -> None:
        self._email = ""
        self._authenticated = False
        self._session = None
        self._authcode = None
        self._wt_cookie = None
        self._user_name = ""
        self._user_id = ""

    @property
    def user_name(self) -> str:
        """Return the user name of the authenticated user."""
        return self._user_name

    @property
    def user_id(self) -> int:
        """Return the user ID of the authenticated user."""
        return self._user_id

    @property
    def email(self) -> str:
        """Return the email of the authenticated user."""
        return self._email

    def authenticate(self, email: str, password: str) -> bool:
        """
        Takes an email address and password and attempts to authenticate. Returns
        a boolean to indicate success.

        :param email: The email address of the user.
        :param password: The corresponding password.
        :returns: Boolean indicating success.
        """

        # Deliberately not storing password as instance variable
        self._email = email

        # We use a Session to hold the state (via cookie jar) for the API queries
        print("Starting Session")
        self._session = requests.Session()

        # Step 1 - POST the clientLogin action with our member credentials.
        #
        # We post with allow_redirect = False because we don't want the
        # Python request to automatically follow redirects. We want the
        # initial response (still on api.wikitree.com)
        # and not the redirected-to destination.
        #
        # Note that the response here will, on successful login, be a
        # redirection. In a browser interaction we'd include a returnURL
        # value in post_data to send the browser back to our app. Here
        # we're just capturing the authcode from the Location redirection
        # directly.

        post_data = {
            "action": "clientLogin",
            "doLogin": 1,
            "wpEmail": self._email,
            "wpPassword": password,
        }

        response = self._session.post(
            API_URL,
            data=post_data,
            allow_redirects=False,
            # auth=("wikitree", "wikitree"),
        )

        # If we have a "Location" redirection with an authcode value as our response, then
        # the login was successful. Otherwise, we failed.
        location = response.headers.get("Location")
        if (response.status_code != 302) or (location is None):
            print("\tfailed - clientLogin POST did not return expected 302 Redirect.")

            # On failure, the response content will be a redisplay of the web page with the
            # API Client Login form.
            # print "Response status_code = "+str(response.status_code)
            # print ("Header: "+str(response.headers))
            # print ("Content:"+response.text)

            self._authenticated = False
            return self._authenticated

        matches = re.search("authcode=(.*)", location)
        if matches is None:
            print("\tfailed - clientLogin POST did not return authcode")
            print("\t\tResponse status_code = " + str(response.status_code))
            print("\t\tHeader: " + str(response.headers))

            self._authenticated = False
            return self._authenticated

        self._authcode = matches.group(1)

        # Step 2 - POST back the authcode we got. This completes the login/session setup
        # at api.wikitree.com. Since we use the same Session for the post, the cookies are all
        # saved. A success here is a 200 and we'll have WikiTree session cookies.
        post_data = {"action": "clientLogin", "authcode": self._authcode}
        response = self._session.post(
            API_URL,
            data=post_data,
            allow_redirects=False,
            # auth=("wikitree", "wikitree"),
        )
        if response.status_code != 200:
            print("clientLogin(authcode) failed.")
            print("Response status_code = " + str(response.status_code))
            print("Header: " + str(response.headers))
            self._authenticated = False
            return self._authenticated

        # The cookies set by the API look like:
        # {
        #   'wikitree_wtb__session': '<<session_id>>',
        #   'wikitree_wtb_Token': '<<token>>',
        #   'wikidb_wtb_UserName': '<<WikiTree ID>>',
        #   'wikitree_wtb_UserID': '<<WikiTree user_id>>'
        # }
        self._wt_cookie = self._session.cookies.get_dict()
        if self._wt_cookie is None:
            print("clientLogin(authcode) failed -- No Cookies.")
            self._authenticated = False
            return self._authenticated

        # The successful response looks something like 
        # {
        #     "clientLogin": {
        #         "result": "Success",
        #         "userid": 12345678,
        #         "username": "Fakename-1",
        #     }
        # }
        client_login_response = response.json().get("clientLogin")
        self._user_name = client_login_response.get("username")
        self._user_id = client_login_response.get("userid")

        print(f"Authentication succeeded: {self._user_name} {self._user_id}")

        self._authenticated = True
        return self._authenticated

    def _do_post(self, post_data: dict) -> dict:
        """
        A convenience function to do the actual POST.
        Returns empty dictionary if not authenticated. This is strictly
        not necessary for most queries, and you could remove the
        test for authentication and it would work mostly as
        expected.

        :param post_data: A dictionary with at least the "action" key and
                          other keys as necessary.
        """
        data = {}

        if not self._authenticated:
            return data

        response = self._session.post(
            url=API_URL,
            data=post_data,
            # auth=("wikitree", "wikitree"),
        )

        try:
            # print(response.status_code)
            data = response.json()
        except JSONDecodeError:
            # print("No data returned.")
            pass

        return data

    def get_ancestors(
        self,
        key: Union[str, int],
        depth: int = 1,
        fields: Optional[str] = None,
        bio_format: Optional[str] = None,
    ):
        """
        Uses the getAncestors API call to return one or more
        person profiles.

        :param key: Wanted WikiTree_ID or User_ID
        :param depth: Number of generations
        :param fields: Comma separated list of required fields
        :param bio_format: "wiki", "html", or "both"
        """
        post_data = {
            "action": "getAncestors",
            "key": key,
            "depth": depth,
            "resolveRedirect": 1,
        }
        if fields is not None:
            post_data["fields"] = fields
        if bio_format is not None:
            post_data["bioFormat"] = bio_format

        return self._do_post(post_data)

    def get_bio(
        self,
        key: Union[str, int],
        bio_format: Optional[str] = None,
    ) -> dict:
        """
        Uses the getBio API call to return the bio of the given
        profile.

        :param key: Wanted WikiTree_ID or User_ID
        :param bio_format: "wiki", "html", or "both"
        """
        post_data = {
            "action": "getBio",
            "key": key,
            "resolveRedirect": 1,
        }
        if bio_format is not None:
            post_data["bioFormat"] = bio_format

        return self._do_post(post_data)

    def get_connected_dna_tests_by_profile(self, key: Union[str, int]):
        """
        getConnectedDNATestsByProfile

        :param key: Wanted WikiTree_ID or User_ID
        """
        post_data = {
            "action": "getConnectedDNATestsByProfile",
            "key": key,
        }

        return self._do_post(post_data)

    def get_connected_profiles_by_dna_test(self, key: Union[str, int], dna_id: int):
        """
        getConnectedProfilesByDNATest

        :param key: Wanted WikiTree_ID or User_ID
        :dna_id: ID of DNA test
        """
        post_data = {
            "action": "getConnectedProfilesByDNATest",
            "key": key,
            "dna_id": dna_id,
        }

        return self._do_post(post_data)

    def get_descendants(
        self,
        key: Union[str, int],
        depth: int = 1,
        fields: Optional[str] = None,
        bio_format: Optional[str] = None,
    ):
        """
        Uses the getDescendants API call to return one or more
        person profiles.

        :param key: Wanted WikiTree_ID or User_ID
        :param depth: Number of generations
        :param fields: Comma separated list of required fields
        :param bio_format: "wiki", "html", or "both"
        """
        post_data = {
            "action": "getDescendants",
            "key": key,
            "depth": depth,
            "resolveRedirect": 1,
        }
        if fields is not None:
            post_data["fields"] = fields
        if bio_format is not None:
            post_data["bioFormat"] = bio_format

        return self._do_post(post_data)

    def get_dna_tests_by_test_taker(self, key: Union[str, int]):
        """
        getDNATestsByTestTaker

        :param key: Wanted WikiTree_ID or User_ID
        """
        post_data = {
            "action": "getDNATestsByTestTaker",
            "key": key,
        }

        return self._do_post(post_data)

    def get_person(
        self,
        key: Union[str, int],
        fields: Optional[str] = None,
        bio_format: Optional[str] = None,
    ):
        """
        Uses the getPerson API call to return a person profile.

        :param key: Wanted WikiTree_ID or User_ID
        :param fields: Comma separated list of required fields
        :param bio_format: "wiki", "html", or "both"
        """
        post_data = {
            "action": "getPerson",
            "key": key,
            "resolveRedirect": 1,
        }
        if fields is not None:
            post_data["fields"] = fields
        if bio_format is not None:
            post_data["bioFormat"] = bio_format

        return self._do_post(post_data)

    def get_photos(
        self,
        key: Union[str, int],
        limit: int = 10,
        start: int = 0,
        order: str = "PageId",
    ):
        """
        Uses the getPhotos API call to return a list of the
        photos attached to a profile.

        :param key: Wanted WikiTree_ID or User_ID
        :param limit: Number of photos to return
        :param start: The starting position in the list of photos
        :param order: "PageId", "Uploaded", "ImageName", or "Date"
        """
        post_data = {
            "action": "getPhotos",
            "key": key,
            "resolveRedirect": 1,
            "limit": limit,
            "start": start,
            "order": order,
        }

        return self._do_post(post_data)

    def get_profile(
        self,
        key: Union[str, int],
        fields: Optional[str] = None,
        bio_format: Optional[str] = None,
    ) -> dict:
        """
        getProfile

        :param key: Wanted WikiTree_ID or Page_ID
        :param fields: Comma separated list of required fields
        :param bio_format: "wiki", "html", or "both"
        """
        post_data = {
            "action": "getProfile",
            "key": key,
            "resolveRedirect": 1,
        }
        if fields is not None:
            post_data["fields"] = fields
        if bio_format is not None:
            post_data["bioFormat"] = bio_format

        return self._do_post(post_data)

    def get_relatives(
        self,
        key: Union[str, int],
        fields: Optional[str] = None,
        bio_format: Optional[str] = None,
        get_parents: bool = True,
        get_children: bool = True,
        get_siblings: bool = True,
        get_spouses: bool = True,
    ):
        """
        getRelatives

        :param key: Wanted WikiTree_ID or User_ID
        :param fields: Comma separated list of required fields
        :param bio_format: "wiki", "html", or "both"
        :param get_parents: Whether to get the parents
        :param get_children: Whether to get the children
        :param get_siblings: Whether to get the siblings
        :param get_spouses: Whether to get the spouses
        """
        post_data = {
            "action": "getRelatives",
            "key": key,
            "getParents": int(get_parents),
            "getChildren": int(get_children),
            "getSiblings": int(get_siblings),
            "getSpouses": int(get_spouses),
        }
        if fields is not None:
            post_data["fields"] = fields
        if bio_format is not None:
            post_data["bioFormat"] = bio_format

        return self._do_post(post_data)

    def get_watchlist(
        self,
        limit: int = 100,
        offset: int = 0,
        order: str = "user_id",
        get_person: bool = True,
        get_space: bool = True,
        only_living: bool = False,
        exclude_living: bool = False,
        fields: Optional[str] = None,
        bio_format: Optional[str] = None,
    ):
        """
        getWatchlist
        """
        post_data = {
            "action": "getWatchlist",
            "limit": limit,
            "offset": offset,
            "order": order,
            "getPerson": int(get_person),
            "getSpace": int(get_space),
            "onlyLiving": int(only_living),
            "excludeLiving": int(exclude_living),
        }
        if fields is not None:
            post_data["fields"] = fields
        if bio_format is not None:
            post_data["bioFormat"] = bio_format

        return self._do_post(post_data)

    def search_person(self, **kwargs):
        """
        searchPerson

        keyword args should be from:
            FirstName
            LastName
            BirthDate
            DeathDate
            RealName
            LastNameCurrent
            BirthLocation
            DeathLocation
            Gender
            fatherFirstName
            fatherLastName
            motherFirstName
            motherLastName
            watchlist
            dateInclude
            dateSpread
            centuryTypo
            isLiving
            skipVariants
            lastNameMatch
            sort
            secondarySort
            limit
            start
            fields
        """
        post_data = {"action": "searchPerson"}
        post_data.update(kwargs)
        return self._do_post(post_data)


def main():
    """
    The main function - queries credentials, creates a session,
    performs some queries.
    """

    # Create the WikiTree session object.
    wt_session = WTSession()

    # Loop until we have a successful authentication
    success = False
    while not success:
        email = input("Email: ")
        password = getpass("Password: ")
        success = wt_session.authenticate(email=email, password=password)

    # Leave fields=None if you want default fields
    # Set fields="*" if you want them all
    # Otherwise we want a comma-separated string of the wanted fields,
    # and I prefer to construct it from a list.
    fields = ",".join(["FirstName", "MiddleName", "LastNameCurrent", "BirthDate"])

    # The session now has it's user_name and user_id attributes set,
    # let's use them to perform some queries
    profile = wt_session.get_profile(wt_session.user_name, fields=fields)
    print("\n#### get_profile result ####")
    pprint(profile)

    bio = wt_session.get_bio(wt_session.user_name)
    print("\n#### get_bio result ####")
    pprint(bio)

    ancestors = wt_session.get_ancestors(wt_session.user_name, fields=fields)
    print("\n#### get_ancestors result ####")
    pprint(ancestors)

    descendants = wt_session.get_descendants(wt_session.user_name, fields=fields)
    print("\n#### get_decendants result ####")
    pprint(descendants)

    search = wt_session.search_person(
        FirstName=profile[0]["profile"]["FirstName"],
        LastName=profile[0]["profile"]["LastNameCurrent"],
        # BirthDate=profile[0]["profile"]["BirthDate"],
        fields=fields,
    )
    print("\n#### search_person result ####")
    pprint(search)


if __name__ == "__main__":
    main()
