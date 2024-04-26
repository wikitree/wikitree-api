# WikiTree API Authentication

## Introduction

Public profiles can be retrieved without any prior authentication using getProfile, getAncestors, etc.. However if you want to retrieve data for a [private](https://www.wikitree.com/wiki/Help:Privacy) profile, then you need to first authenticate as a WikiTree.com member. Then you'll be able to retrieve data for any profile which has that member on its [Trusted List](https://www.wikitree.com/wiki/Help:Trusted_List).

Authentication for the WikiTree API is a multi-step process. The user goes to api.wikitree.com where they login with their WikiTree credentials. Upon login, the user's browser receives a session cookie for future requests to the API and is then sent back to the application with a token. The application can then validate the token with the API and store the user's ID.

Note that the token is not used for future queries to the API. The authentication is stored in a cookie on the api.wikitree.com hostname where the user logged in.

# Step-by-Step Authentication

## 1. Authenticate at api.wikitree.com

The first step is to send the user to api.wikitree.com where they can authenticate themselves. This lets the end user login at WikiTree without providing credentials to a third-party application. You should _not_ ask users directly for their login information. Instead link/redirect them to:

https://api.wikitree.com/api.php?action=clientLogin&returnURL=[encoded URL of application]

At that URL, the user will be presented with a login page. After a successful login, their browser will receive session cookies for api.wikitree.com and the user's browser will be sent back to your application at the **returnURL** you provided.

For example, for an application running in a web page in the "Apps" hosting for WikiTree, you could have:

```html
<form action="https://api.wikitree.com/api.php" method="POST">
  <input type="hidden" name="action" value="clientLogin">
  <input type="hidden" name="returnURL" value="https://apps.wikitree.com/apps/user1234/myapplication.html">
  <input type="submit" class="button small" value="Client Login">
</form>
```

## 2. Confirm user with authcode

The API redirects the user back to **returnURL** with an **authcode** parameter. This parameter is then used with a request to the API endpoint with the clientLogin action.

When the authcode is confirmed, the API returns a result of "Success" along with the user_id and user_name (aka WikiTree ID) for the logged-in user, which can be stored for future use.

For example, in our **returnURL** "myapplication.html" can process the returned authcode:

```js
  var x = window.location.href.split('?');
	var queryParams = new URLSearchParams( x[1] );
  ...
```

Alternatively:

```js
const authcode = new URLSearchParams(location.search).get('authcode');
```

Note that this step lets your application know whether the user is logged in or not. If they succesfully logged in at api.wikitree.com, their browser has a session cookie for that login and your future posts to the API on their behalf will use that authentication. Confirming the authcode is primarily to provide your application with the logged-in status and the user_id/user_name of the logged-in user.

## 3. Send session information for authenticated user

For future requests to the API, you must tell the browser to send along the session cookies for api.wikitree.com so that the API recognizes the authenticated user.

In vanilla JavaScript, this used to be done by setting `XMLHttpRequest.withCredentials = true`. With jQuery, you would've set `"xhrFields: { withCredentials: true }"` in the `$.ajax` call.

In modern JavaScript, this is instead done by setting `{ credentials: 'include' }` in the `fetch` request.

## Examples

- [JavaScript](examples/authentication/javascript.html)

```js
const $ = document.querySelector.bind(document);
const $$ = selector => [...document.querySelectorAll(selector)];

async function authenticate() {
    $('[name="returnURL"]').value = location.href;
    const userName = localStorage.getItem('userName');
    const userId = localStorage.getItem('userId');
    const urlSearchParams = new URLSearchParams(location.search);
    const authcode = urlSearchParams.get('authcode');
    if (authcode) {
        console.log('Logging in…');
        const json = await login(authcode);
        if (json.clientLogin.result === 'Success') {
            const { userid: userId, username: userName } = json.clientLogin;
            localStorage.setItem('userName', userName);
            localStorage.setItem('userId', userId);
            new URL(location.href).searchParams.delete('authcode');
            history.pushState({}, document.title, url);
            console.log(`Logged in as ${userName}.`);
        } else {
            console.log('Login failed.');
        }
    } else if (userId) {
        const json = await checkLogin(userId);
        if (json.clientLogin.result === 'ok') {
            console.log(`Already logged in as ${userName}.`);
        } else {
            console.log('Not logged in anymore.');
            localStorage.clear();
        }
    } else {
        console.log('Not logged in yet.');
    }
}

$('#logout').onclick = async function () {
    await logout();
    localStorage.clear();
    console.log('Logged out.');
}

async function login(authcode) {
    const url = `https://api.wikitree.com/api.php?action=clientLogin&authcode=${authcode}`;
    const resp = await fetch(url, { credentials: 'include' });
    const json = await resp.json();
    return json;
}

async function checkLogin(userId) {
    const url = `https://api.wikitree.com/api.php?action=clientLogin&checkLogin=${userId}`;
    const resp = await fetch(url, { credentials: 'include' });
    const json = await resp.json();
    return json;
}

async function logout() {
    const url = `https://api.wikitree.com/api.php?action=clientLogin&doLogout=1`;
    const resp = await fetch(url, { credentials: 'include' });
    const json = await resp.json();
    return json;
}
```

# Authentication for offline/non-browser applications

The clientLogin steps described above are used for applications running in-browser, where the browser holds the session information for the user after they've logged in at api.wikitree.com. If you are developing an application that runs outside of a browser (e.g. a script to run using your own credentials), then you'll need to manage the session yourself.

## Examples

- [Python command line](examples/authentication/python.py)

# Checking Login Status

An application will store the user Id of the user logged into the API, e.g. when confirming an authcode. That way the app knows that the user is signed in, and can proceed accordingly. However it's possible that the user was logged out at api.wikitree.com, but that the saved cookie on the app's site (e.g. apps.wikitree.com) is still present. A login can be confirmed by sending the Id as the checkLogin parameter to the clientLogin action. If the given user Id is signed into the API, the result will be "ok", otherwise "error".

```json
https://api.wikitree.com/api.php?action=clientLogin&checkLogin=123

{
  "clientLogin": {
    "checkLogin": 123,
    "result": "error"
  }
}


https://api.wikitree.com/api.php?action=clientLogin&checkLogin=456
{
  "clientLogin": {
    "checkLogin": 456,
    "result": "ok"
  }
}
```

# Logging Out

You can provide a way for the user to log out of the API by sending them to the clientLogin action with doLogout=1. As with the login, you can also pass along returnURL to indicate where the user should be sent after they are logged out. Logging out removes their session data at api.wikitree.com.

```url
https://api.wikitree.com/api.php?action=clientLogin&doLogout=1&returnURL=https://www.wikitree.com/
```
