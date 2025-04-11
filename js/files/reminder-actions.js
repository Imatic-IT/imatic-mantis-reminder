import {showMessages} from './utils';
import {sendFormData} from './utils';
function addEventListenersToButtons() {
    let editButtons = document.querySelectorAll('.edit-reminder');
    let deleteButtons = document.querySelectorAll('.delete-reminder');

    editButtons.forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault()
            const reminderId = this.getAttribute("data-id");
            const action = this.getAttribute("data-action");
            let table = document.getElementById('reminder-table-' + reminderId);

            let formData = new FormData();
            table.querySelectorAll('input, textarea').forEach(function (input) {
                formData.append(input.name, input.value);
            });
            formData.append('action', action);

            const icon = table.querySelector('.fa-check');
            if (icon) {
                icon.classList.remove('fa-check', 'text-success');
                icon.classList.add('fa-close', 'text-danger');
            }

            sendFormData(formData);
        });
    });

    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault()

            const reminderId = this.getAttribute("data-id");
                const action = this.getAttribute("data-action");
            let table = document.getElementById('reminder-table-' + reminderId);

            let formData = new FormData();
            table.querySelectorAll('input, textarea').forEach(function (input) {
                formData.append(input.name, input.value);
            });
            formData.append('action', action);

            sendFormData(formData)
                .then(result => {
                    if (result) {
                        if (table) {
                            table.remove();
                        }
                    }
                });
        });
    });
}


document.addEventListener('DOMContentLoaded', function () {
    let reminderCreateForm = document.getElementById('imatic-reminder-create-form');

    reminderCreateForm.addEventListener('submit', function (e) {
        e.preventDefault()
        let formData = new FormData(this);
        let action = this.getAttribute('action');
        formData.append('action', action);

        fetch(action, {
            method: 'POST',
            body: formData
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.status === 200) {
                    showMessages(data.message, "#DEF1D8");

                    document.getElementById('remind_at').value = '';
                    document.getElementById('comment').value = '';

                    let table = document.getElementById('reminders-table');
                    let tbody = document.createElement('tbody');
                    let rows = data.reminders;

                    rows.forEach(function (rowData) {
                        let row = document.createElement('tr');
                        row.id = 'reminder-table-' + rowData.id;

                        row.innerHTML = `
                            <td>${rowData.id}</td>
                            <td>${rowData.username}</td>
                            <td>
                                <input type="datetime-local" name="remind_at" class="form-control" value="${rowData.remind_at}"></td>
                            <td>
                                <textarea name="message" class="form-control" rows="1" cols="20">${rowData.message}</textarea>
                            </td>
                            <td class="text-center"><i class="fa fa-close text-danger" /></td>
                            <td>
                                <button type="button" class="btn btn-primary btn-xs edit-reminder" data-id="${rowData.id}"
                                        data-action="${rowData.edit_action}">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-xs delete-reminder" data-id="${rowData.id}"
                                        data-action="${rowData.delete_action}">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </button>
                                <input type="hidden" name="id" value="${rowData.id}">
                                <input type="hidden" name="user_id" value="${rowData.user_id}">
                            </td>
                        `;
                        tbody.appendChild(row);

                        table.appendChild(tbody);
                    })

                    addEventListenersToButtons()

                } else {
                    showMessages('An error occurred', "red");
                }
            })
            .catch(error => {
                console.error('There has been a problem with your fetch operation:', error);
            });
    })
    addEventListenersToButtons()
})