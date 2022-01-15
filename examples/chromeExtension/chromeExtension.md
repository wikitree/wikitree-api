# WikiTree API Example - Chrome Extension

## Introduction
The WikiTree API can be used inside a Chrome extension to add functionality to WikiTree pages (or pull WikiTree data into other pages). In this example, the API is used to display some reference information and links when using the WikiTree EditFamily feature to add relatives to an existing person profile.

## Installation 
To use the extension, go to chrome://extensions in you Chrome browser. Then click "Load unpacked" and select the "editfamily-reference-box" directory. This should add the extension code to your browser. It will activate and run on Special:EditFamily pages at WikiTree.com.

## Usage
If you visit a Special:EditFamily page, e.g. by clicking to add a parent or child to an existing profile, the extension will activate. The first time this happens the reference box will display an "API Login" button. Click that to be taken to the API login page where you can login with your WikiTree credentials. Afterwards you'll be retuned to the EditFamily page.

After logging in (which you'll only need to do the first time), the reference box will be filled with some basic information about the profile you are editing and existing relatives (parents and children). The displayed names will be linked to their profiles, opening in a new tab.

## Implementation Notes

This examples follows the pattern from the [authentication](../../authenciation.md) example to check the user's logged-in status with the API and log them in if necessary.

Once logged in, a simple [getPerson](../../getPerson.md) call is made to gather names and birthdates of the edited profile and its parents and children. These are added to the reference-box div.