export function showMessages(message, color, timeout = 2000) {
    let messageContainer = document.createElement('div');

    messageContainer.style.position = 'fixed';
    messageContainer.style.top = '50%';
    messageContainer.style.left = '50%';
    messageContainer.style.padding = '20px';
    messageContainer.style.border = '1px solid white';
    messageContainer.style.borderRadius = '5px';
    messageContainer.style.zIndex = '9999';
    messageContainer.style.color = 'black';
    messageContainer.style.display = 'block';
    messageContainer.style.backgroundColor = color;
    messageContainer.textContent = message;

    document.body.appendChild(messageContainer);

    setTimeout(function () {
        messageContainer.style.display = 'none';
    }, timeout);
}

export function sendFormData(form, method = 'POST') {

    return fetch(form.get('action'), {
        method: method,
        body: form
    })
        .then(response => {
            if (!response.ok) {
                showMessages('Response was not ok', "red");
                throw new Error('Response was not ok');
            }
            return response.text();
        })
        .then(data => {
            let dataObj = JSON.parse(data);
            if (dataObj.status === 200) {
                showMessages(dataObj.message, "#DEF1D8");
                return true;
            } else {
                showMessages(dataObj.message, "red");
                return false;
            }
        })
        .catch(error => {
            console.error('There has been a problem with your fetch operation:', error);
            return Promise.reject(error);
        });
}