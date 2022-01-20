"""
This module defines a class that manages an authenticated session
with the WikiTree API.

It allows you to log in and perform various queries.

It requires python3 and the requests library.
"""

# Standard Imports
from getpass import getpass
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
        self._api_session = None
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
        self._api_session = requests.Session()

        # Step 1 - POST the clientLogin action with our member credentials.
        #
        # We post with allow_redirect = False because we don't want the
        # Python request to automatically follow redirects. We want the
        # initial response (still on api.wikitree.com)
        # and not the redirected-to destination.
        #
        # Note that the api_response here will, on successful login, be a
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
        # print("POSTing clientLogin...")
        api_response = self._api_session.post(
            API_URL,
            data=post_data,
            allow_redirects=False,
            auth=("wikitree", "wikitree"),
        )

        # If we have a "Location" redirection with an authcode value as our response, then
        # the login was successful. Otherwise, we failed.
        if (api_response.status_code != 302) or (
            api_response.headers["Location"] is None
        ):
            print("\tfailed - clientLogin POST did not return expected 302 Redirect.")

            # On failure, the response content will be a redisplay of the web page with the
            # API Client Login form.
            # print "Response status_code = "+str(api_response.status_code)
            # print ("Header: "+str(api_response.headers))
            # print ("Content:"+api_response.text)

            self._authenticated = False
            return self._authenticated

        matches = re.search("authcode=(.*)", api_response.headers["Location"])
        if matches is None:
            print("\tfailed - clientLogin POST did not return authcode")
            print("\t\tResponse status_code = " + str(api_response.status_code))
            print("\t\tHeader: " + str(api_response.headers))

            self._authenticated = False
            return self._authenticated

        self._authcode = matches.group(1)

        # Step 2 - POST back the authcode we got. This completes the login/session setup
        # at api.wikitree.com. Since we use the same Session for the post, the cookies are all
        # saved. A success here is a 200 and we'll have WikiTree session cookies.
        post_data = {"action": "clientLogin", "authcode": self._authcode}
        api_response = self._api_session.post(
            API_URL,
            data=post_data,
            allow_redirects=False,
            auth=("wikitree", "wikitree"),
        )
        if api_response.status_code != 200:
            print("clientLogin(authcode) failed.")
            print("Response status_code = " + str(api_response.status_code))
            print("Header: " + str(api_response.headers))
            self._authenticated = False
            return self._authenticated

        # The cookies set by the API look like:
        # {
        #   'wikitree_wtb__session': '<<session_id>>',
        #   'wikitree_wtb_Token': '<<token>>',
        #   'wikidb_wtb_UserName': '<<WikiTree ID>>',
        #   'wikitree_wtb_UserID': '<<WikiTree user_id>>'
        # }
        self._wt_cookie = self._api_session.cookies.get_dict()
        if self._wt_cookie is None:
            print("clientLogin(authcode) failed -- No Cookies.")
            self._authenticated = False
            return self._authenticated

        self._user_name = self._wt_cookie["wikidb_wtb_UserName"]
        self._user_id = self._wt_cookie["wikidb_wtb_UserID"]

        print(f"Authentication succeeded: {self._user_name} {self._user_id}")

        self._authenticated = True
        return self._authenticated

    def _do_post(self, post_data) -> dict:
        """
        A convenience function to do the actual POST.
        Returns empty dictionary if not authenticated.
        """
        if not self._authenticated:
            return None

        api_response = self._api_session.post(
            url=API_URL, data=post_data, auth=("wikitree", "wikitree")
        )

        data = api_response.json()

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
        getBio
        """
        post_data = {
            "action": "getBio",
            "key": key,
            "resolveRedirect": 1,
        }
        if bio_format is not None:
            post_data["bioFormat"] = bio_format

        return self._do_post(post_data)

    def get_connected_dna_tests_by_profile(self):
        """getConnectedDNATestsByProfile"""

    def get_connected_profiles_by_dna_test(self):
        """getConnectedProfilesByDNATest"""

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

    def get_dna_tests_by_test_taker(self):
        """getDNATestsByTestTaker"""

    def get_person(
        self,
        key: Union[str, int],
        fields: Optional[str] = None,
        bio_format: Optional[str] = None,
    ):
        """getPerson"""
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


    def get_photos(self):
        """getPhotos"""

    def get_profile(
        self,
        key: Union[str, int],
        fields: Optional[str] = None,
        bio_format: Optional[str] = None,
    ) -> dict:
        """
        getProfile
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

    def get_relatives(self):
        """getRelatives"""

    def get_watchlist(self):
        """getWatchlist"""

    def search_person(self):
        """searchPerson"""


def main():
    """
    The main function - queries credentials, creates a session,
    performs some queries.
    """

    wt_session = WTSession()
    success = False
    while not success:
        email = input("Email: ")
        password = getpass("Password: ")

        success = wt_session.authenticate(email=email, password=password)

    profile = wt_session.get_profile(wt_session.user_name)
    pprint(profile)

    bio = wt_session.get_bio(wt_session.user_name)
    pprint(bio)

    ancestors = wt_session.get_ancestors(wt_session.user_name)
    pprint(ancestors)

    descendants = wt_session.get_descendants(wt_session.user_name)
    pprint(descendants)


if __name__ == "__main__":
    main()
