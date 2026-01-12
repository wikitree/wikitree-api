"""
example.py

An example showing:
1) Unauthenticated call to getPerson('Windsor-1')
2) Optional authentication, then call getPerson('Windsor-1') again

Requirements:
- wt_session.py in the same folder (your updated version that supports unauthenticated use + appId)
- requests installed
"""

from getpass import getpass
from pprint import pprint
import sys
import time

from wt_session import WTSession


APP_ID = "Steve-Python3" # REQUIRED: Add an appId here
KEY = "Windsor-1"


def print_get_person_summary(label: str, result):
    print(f"\n=== {label} ===")
    print("type:", type(result))

    # The examples you tested return a list with an item containing 'status' and 'person'
    if not isinstance(result, list) or not result:
        print("Unexpected response payload:")
        pprint(result)
        return

    item0 = result[0]
    status = item0.get("status")
    print("status:", status)

    person = item0.get("person") or {}
    print("Name:", person.get("Name"))
    print("RealName:", person.get("RealName"))
    print("Privacy:", person.get("Privacy"))


def get_person_with_retry(session: WTSession, key: str, tries: int = 3, sleep_seconds: int = 2):
    """
    If the API responds with 'Limit exceeded.', pause briefly and retry.
    This is only for the demo script to reduce confusion when testing.
    """
    last = None
    for attempt in range(1, tries + 1):
        last = session.get_person(key)
        if isinstance(last, list) and last and last[0].get("status") == "Limit exceeded.":
            if attempt < tries:
                time.sleep(sleep_seconds)
                continue
        break
    return last


def main():
    # Create the WikiTree session object (unauthenticated)
    wt_session = WTSession(app_id=APP_ID)

    # 1) Unauthenticated call
    unauth_result = get_person_with_retry(wt_session, KEY)
    print_get_person_summary("Unauthenticated get_person('Windsor-1')", unauth_result)

    # 2) Optional authentication, then call again
    print("\nAuthenticate? (y/N): ", end="")
    ans = input().strip().lower()
    if ans not in ("y", "yes"):
        print("\nDone (skipped authentication).")
        return

    success = False
    while not success:
        email = input("Email (or quit): ").strip()
        if email.lower() == "quit":
            sys.exit(0)
        password = getpass("Password: ")
        success = wt_session.authenticate(email=email, password=password)
        if not success:
            print("Login failed, try again.\n")

    print("\nLogged in as:", wt_session.user_name, "(UserID:", wt_session.user_id, ")")

    auth_result = get_person_with_retry(wt_session, KEY)
    print_get_person_summary("Authenticated get_person('Windsor-1')", auth_result)


if __name__ == "__main__":
    main()
