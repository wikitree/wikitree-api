"""
This is a bare-bones example of authenticating as a "known" (not interactive through a browser)
WikiTree member for use with the WikiTree API. In order to carry the authentication through,
a Session is maintained with https://api.wikitree.com which holds the session state via
cookie, as a browser would.
"""

import os
import re
import requests
from dotenv import load_dotenv


if __name__ == "__main__":
    load_dotenv()  # loads environment variables (LOGIN_EMAIL, LOGIN_PASSWORD,...) from `.env` file

    AUTH = {  
        # never commit these, just load them e.g. from the environment
        # Create `.env` file alongside this one with following content:
        # LOGIN_EMAIL=<your username>
        # LOGIN_PASSWORD=<your password>
        
        "user": os.getenv("LOGIN_EMAIL"),
        "pass": os.getenv("LOGIN_PASSWORD"),
    }

    API_URL = "https://api.wikitree.com/api.php"

    session = requests.Session()

    # Step #1: we need to obtain the `authcode`

    data = {
        "action": "clientLogin",
        "doLogin": 1,
        "wpEmail": AUTH["user"],
        "wpPassword": AUTH["pass"],
    }
    resp = session.post(API_URL, data=data, allow_redirects=False)

    if (resp.status_code != 302) or not (location := resp.headers.get("Location")):
        raise Exception(
            "Authentication server either hasn't redirected you or hasn't send `Location` header."
        )

    if not (match := re.search("authcode=(.*)", location)):
        raise Exception("Authentication failed. Server hasn't send the `authcode`.")

    AUTH["code"] = match.group(1)

    # Step #2: obtain authentication cookies (will be stored in session object after success)

    data = {"action": "clientLogin", "authcode": AUTH["code"]}
    resp = session.post(API_URL, data=data, allow_redirects=False)

    resp.raise_for_status()  # will raise the exception if not success (status_code != 200)

    # successful resuponse will contain similar json (resp.json()):
    # {'clientLogin': {'result': 'Success', 'userid': <your_id: int>, 'username': <your_username: str>}}

    # Now, you should be logged in, authentication cookies are stored in the session object
    # and you are ready to fetch some restricted data ;-), just fetch data of the logged user...

    data = {
        "action": "getProfile",
        "key": resp.json()["clientLogin"]["username"],
        "fields": "Id,Name,FirstName,LastNameAtBirth,LastNameCurrent,BirthDate",
    }
    resp = session.post(API_URL, data=data)

    print(resp.json())

    """
    Response would look like:

    [
        {
            "page_name": <key you used in POST request>,
            "profile": {
                "Id": <profile ID>,
                "Name": <key you used in POST request>,
                "FirstName": <first name>,
                "LastNameAtBirth": <last name at birth>,
                "LastNameCurrent": <last name current>,
                "BirthDate": <birth date in form: YYYY-MM-DD>,
            },
            "status": 0,
        }
    ]
    """
