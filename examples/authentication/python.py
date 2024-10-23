"""
This is a bare-bones example of authenticating as a "known" (not interactive through a browser) WikiTree member 
for use with the WikiTree API. In order to carry the authentication through a Session is maintained 
with https://api.wikitree.com which holds the session state via cookie, as a browser would.
"""

from requests import Session
import re
from getpass import getpass
import os

import urllib
import json


# WikiTree API endpoint
API_URL = "https://api.wikitree.com/api.php"


def get_credentials() -> tuple[str, str]:
    """Loads credentials from environment variables (if provided), otherwise asks user for them."""

    email = os.environ.get("LOGIN_EMAIL") or input("Email: ")
    password = os.environ.get("LOGIN_PASSWORD") or getpass(f"Password: ")

    return email, password


def authenticate_session(session: Session, email: str, password: str) -> None:
    """Authenticates the session with provided credentials"""

    # Step 1: Obtain the authcode - will be sent from the API in Location header

    data = {
        "action": "clientLogin",
        "doLogin": 1,
        "wpEmail": email,
        "wpPassword": password,
    }
    resp = session.post(
        API_URL,
        data=data,
        allow_redirects=False,  # necessary to POST without redirect, we need to capture the Location header
    )
    resp.raise_for_status()

    if (
        resp.status_code != 302
        or (location := resp.headers.get("Location")) is None
        or "authcode" not in location
    ):
        print("Cannot authenticate with Wikitree API: authcode was not obtained")
        exit()

    matches = re.search(r"authcode=(?P<authcode>.+$)", location).groupdict()
    authcode = matches.get("authcode")

    # Step 2: Send back the authcode to finish the authentication

    data = {"action": "clientLogin", "authcode": authcode}
    resp = session.post(API_URL, data=data, allow_redirects=False)
    resp.raise_for_status()

    if not resp.ok:
        print("Cannot authenticate with Wikitree API: failed the authcode verification")
        exit()

    # Since we are communicating with the same session, cookies should be already set, example:
    # {
    #   'wikitree_wtb__session': '<<session_id>>',
    #   'wikitree_wtb_Token': '<<token>>',
    #   'wikidb_wtb_UserName': '<<WikiTree ID>>',
    #   'wikitree_wtb_UserID': '<<WikiTree user_id>>'
    # }

    cookies = session.cookies.get_dict()

    if "wikidb_wtb__session" not in cookies:
        print("Cannot authenticate with Wikitree API: cookies were not set properly")
        exit()

    print(
        'User "'
        + urllib.parse.unquote(cookies["wikidb_wtb_UserName"])
        + '" is successfully authenticated!'
    )


def prepare_session(email: str | None, password: str | None) -> Session:
    """
    Prepares the session for the communication with Wikitree API,
    for authenticated access to API, fill in optional fields: email & password
    """

    session: Session = Session()

    if email and password:
        authenticate_session(session, email, password)

    return session


def load_profile(session: Session, key: str) -> dict[str, any]:
    """Loads the info for the user specified by the key parameter"""

    fields = "Id,Name,FirstName,LastNameAtBirth,LastNameCurrent,BirthDate"

    print(f"\nPOST /api.php?getProfile={key}fields={fields}\n")
    resp = session.post(API_URL, {"action": "getProfile", "key": key, "fields": fields})
    resp.raise_for_status()

    return resp.json()


if __name__ == "__main__":
    # email, password = None, None # uncomment to test without authentication
    email, password = get_credentials()  # comment to test without authentication

    print(f"Starting the session as" + (f'"{email}"' if email else "unauthenticated user") + ".")
    session = prepare_session(email, password)

    # As an example, get the logged-in member's profile data itself
    key = session.cookies.get("wikidb_wtb_UserName") or "Windsor-1" # uses Windsor-1 when not authenticated
    profile = load_profile(session, urllib.parse.unquote(key))

    print(json.dumps(profile, indent=2, ensure_ascii=False))
