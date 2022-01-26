#!/usr/bin/python
#
# This is a bare-bones example of authenticating as a "known" (not interactive through a browser)
# WikiTree member for use with the WikiTree API. In order to carry the authentication through,
# a Session is maintained with https://api.wikitree.com which holds the session state via 
# cookie, as a browser would.
#
#####################################################################

import requests
import re
import sys
from getpass import getpass


# Define the WikiTree API endpoint
API_URL = "https://api.wikitree.com/api.php"

# Optionally define the WikiTree member to use for API requests.
# These values should never be committed to a repository, and could be instead loaded
# from a settings file, etc. If these are left blank, then they are queried for on the
# command line.
LOGIN_EMAIL = ''
LOGIN_PASSWORD = ''

#####################################################################

##########
# If we don't have the login information, query the user for it.
##########
if (LOGIN_EMAIL):
    email = LOGIN_EMAIL
else:
    email = raw_input("Email:")

if (LOGIN_PASSWORD):
    password = LOGIN_PASSWORD
else:
    password = getpass("Password for "+email+":")


##########
# We use a Session to hold the state (via cookie jar) for the API queries
##########
print "Starting Session.\n"
apiSession = requests.Session()

##########
# Step 1 - POST the clientLogin action with our member credentials.
#
# We post with allow_redirect = False because we don't want the
# Python request to automatically follow redirects. We want the initial response (still on api.wikitree.com)
# and not the redirected-to destination.
#
# Note that the apiResponse here will, on successful login, be a redirection.
# In a browser interaction we'd include a returnURL value in postData to send the browser
# back to our app. Here we're just capturing the authcode from the Location redirection
# directly.
##########
print "POSTing clientLogin(email,password)"
postData = { 'action':'clientLogin', 'doLogin': 1, 'wpEmail': email, 'wpPassword': password }
apiResponse = apiSession.post(API_URL, data=postData, allow_redirects=False, auth=('wikitree','wikitree'))


##########
# If we have a "Location" redirection with an authcode value as our response, then
# the login was successful. Otherwise, we failed.
##########
if ((apiResponse.status_code != 302) or (apiResponse.headers['Location'] is None)):
    print "clientLogin POST did not return expected 302 Redirect."

    # On failure, the response content will be a redisplay of the web page with the 
    # API Client Login form.
    #print "Response status_code = "+str(apiResponse.status_code)
    #print ("Header: "+str(apiResponse.headers))
    #print ("Content:"+apiResponse.text)

    print "\nDie\n";
    sys.exit()

matches = re.search('authcode=(.*)', apiResponse.headers['Location'])
if (matches == None):
    print "clientLogin POST failed - couldn't find authcode"
    print "Response status_code = "+str(apiResponse.status_code)
    print ("Header: "+str(apiResponse.headers))
    print "\nDie\n"
    sys.exit()

authcode = matches.group(1)
print "clientLogin succeeded: authcode="+authcode


##########
# Step 2 - POST back the authcode we got. This completes the login/session setup
# at api.wikitree.com. Since we use the same Session for the post, the cookies are all 
# saved. A success here is a 200 and we'll have WikiTree session cookies.
##########
print "POSTing clientLogin(authcode)"
postData = { 'action': 'clientLogin', 'authcode': authcode }
apiResponse = apiSession.post(API_URL, data=postData, allow_redirects=False)
if (apiResponse.status_code != 200):
    print "clientLogin(authcode) failed."
    print "Response status_code = "+str(apiResponse.status_code)
    print ("Header: "+str(apiResponse.headers))
    print "\nDie\n"
    sys.exit()

# The cookies set by the API look like:
# {'wikitree_wtb__session': '<<session_id>>', 'wikitree_wtb_Token': '<<token>>', 'wikidb_wtb_UserName': '<<WikiTree ID>>', 'wikitree_wtb_UserID': '<<WikiTree user_id>>'}
wtCookie = apiSession.cookies.get_dict()
if (wtCookie is None):
    print "clientLogin(authcode) failed -- No Cookies."
    print "\nDie\n"
    sys.exit()
print wtCookie

#if ('wikidb_wtb_UserName' not in wtCookie):
#   print "clientLogin(authcode) failed -- No WikiTree Session Info found in Cookies."
#    print "\nDie\n"
#    sys.exit()

print "clientLogin(authcode) succeeded: "+wtCookie['wikidb_wtb_UserName']


##########
# At this point the member should be logged into the API site, with their 
# session data stored in a CookieJar in our Session. We can now query the 
# API for data that is restricted to profiles with that member on the Trusted List.
# As an example, get the logged-in member's profile data itself.
##########
key = wtCookie['wikidb_wtb_UserName']
fields = 'Id,Name,FirstName,LastNameAtBirth,LastNameCurrent,BirthDate'
print "\ngetProfile("+key+")"
apiResponse = apiSession.post(API_URL, {'action':'getProfile', 'key':key, 'fields':fields}, auth=('wikitree','wikitree'))

data = apiResponse.json()
print "Data from JSON:"
print data
print "\n"

print "First Name: "+ data[0]['profile']['FirstName']
print "Last Name: "+ data[0]['profile']['LastNameAtBirth']
print "Birth Date: "+ data[0]['profile']['BirthDate']

print "\n\nDone\n"
