const targetNode = document.body;
const config = { attributes: true, childList: true, subtree: true };

const callback = function (mutationsList, observer) {
    for (const mutation of mutationsList) {
        if (mutation.type === 'childList') {
            const button = document.querySelector('.execute');
            if (button) {
                const inputElement = document.querySelector('input[placeholder="getProfile"]');
                if (inputElement) {
                    inputElement.readOnly = true;
                }
                button.addEventListener('click', function () {
                    waitForElement('.request-url', function () {
                        var target = document.querySelector('.request-url');
                        const keyValue = document.querySelector('input[placeholder="key"]').value;
                        const fieldsValue = document.querySelector('input[placeholder="fields"]').value;
                        const bioFormatValue = document.querySelector('input[placeholder="bioFormat"]').value;
                        const resolveRedirectValue = document.querySelector('input[placeholder="resolveRedirect"]').value;
                        var bioFormat = "";
                        var resolveRedirect = "";
                        if (bioFormatValue != "") {
                            var bioFormat = `<br>        <span class="keyword">bioFormat</span>: <span class="variable">${bioFormatValue}</span>,`
                        }

                        if (resolveRedirectValue != "") {
                            var resolveRedirect = `<br>        <span class="keyword">resolveRedirect</span>: <span class="variable">${resolveRedirectValue}</span>,`
                        }

                        if (target && !document.getElementById('jsExample')) {
                            var jsExample = document.createElement('div');
                            jsExample.id = 'jsExample';
                            jsExample.innerHTML = `
<h4>Asynchronous JavaScript</h4>
<pre class="curl microlight"><code class="language-bash">
<span>$.</span><span class="keyword">ajax</span>({
    <span class="variable">url</span>: '<span class="string">https://api.wikitree.com/api.php'</span>,
    <span class="variable">xhrFields</span>: { <span class="keyword">withCredentials</span>: <span class="keyword">true</span> },
    <span class="variable">type</span>: '<span class="string">POST</span>',
    <span class="variable">dataType</span>: '<span class="string">json</span>',
    <span class="variable">data</span>: {
        <span class="keyword">action</span>: '<span class="string">getProfle</span>',
        <span class="keyword">key</span>: <span class="variable">${keyValue}</span>,
        <span class="keyword">fields</span>: '<span class="string">${fieldsValue}</span>',${bioFormat}${resolveRedirect}
    }
}).then((<span class="variable">response</span>) => {
    <span class="comment">// Process the response data here</span>
});</code></pre>`;
                            target.appendChild(jsExample);
                        }
                    });
                });
            }
        }
    }
};

const observer = new MutationObserver(callback);
observer.observe(targetNode, config);

function waitForElement(selector, callback) {
    const interval = setInterval(function () {
        const element = document.querySelector(selector);
        if (element) {
            clearInterval(interval);
            callback();
        }
    }, 100); // Check every 100 milliseconds
}
