// Inicializacija aplikacije, ko je dokument pripravljen
$(document).ready(() => {
    Framework.Application.init();
});

const Framework = (() => {
    const ENDPOINT = 'https://dev.tittlus.com/framework/server/';
    const DISPLAY_TIME = 4000;
    const TOAST_DISPLAY_LENGTH = 60000;
    const DISPLAY_KINDS = ['error', 'user'];


    const Utils = {
        getGuid() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
                const r = Math.random() * 16 | 0,
                    v = c === 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        },

        formatDate(dateStr) {
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${day}.${month}.${year} ${hours}:${minutes}`;
        },

        queryStringParamsToObject(queryString) {
            return queryString.split('&').reduce((acc, pair) => {
                const [key, value] = pair.split('=');
                acc[key] = decodeURIComponent(value);
                return acc;
            }, {});
        }
    };

    class ConsoleLogger {
        static log(message, kind = 'log') {
            if (typeof message === 'object') {
                message = JSON.stringify(message, null, 2);
            }

            const styles = {
                log: 'color: #bdbdbd;',
                error: 'color: #ee6e73;',
                user: 'color: #0097A7;',
                default: 'color: black;'
            };

            console.log(`%c${message}`, styles[kind] || styles.default);
        }
    }

    class TabManager {
        constructor(containerId) {
            this.container = $(`#${containerId}`);
            this.buttonContainer = $('<div>', {
                id: `${containerId}_buttons`,
                class: 'tab-buttons'
            }).appendTo(this.container);
            this.tabContentContainer = $('<div>', {
                id: `${containerId}_content`,
                class: 'tab-contents'
            }).appendTo(this.container);

            this.tabs = [];
            this.currentTabId = null;
        }

        showTab(tabId) {
            this.tabs.forEach(tab => {
                if (tab.id === tabId) {
                    tab.element.show();
                } else {
                    tab.element.hide();
                }
            });
            this.currentTabId = tabId;
        }

        addTab(title, content) {
            const tabId = `tab_${this.tabs.length}`;
            const tabElement = $('<div>', { id: tabId, class: 'tab-content' })
                .css('display', 'none')
                .html(content)
                .appendTo(this.tabContentContainer);

            const button = $('<button>', { class: 'btn tab-button' }).text(title);
            const closeButton = $('<span>', { class: 'close-btn' }).html('&times;');

            closeButton.on('click', (e) => {
                e.stopPropagation();
                this.removeTab(tabId);
            });

            button.on('click', () => this.showTab(tabId));
            button.append(closeButton);
            this.buttonContainer.append(button);

            this.tabs.push({ id: tabId, element: tabElement, button });

            this.showTab(tabId);
        }

        removeTab(tabId) {
            const tabIndex = this.tabs.findIndex(tab => tab.id === tabId);
            if (tabIndex !== -1) {
                const [tab] = this.tabs.splice(tabIndex, 1);
                tab.element.remove();
                tab.button.remove();

                if (tabId === this.currentTabId) {
                    if (this.tabs.length > 0) {
                        this.showTab(this.tabs[this.tabs.length - 1].id);
                    } else {
                        this.currentTabId = null;
                    }
                }
            }
        }

        focusTab(tabId) {
            const tab = this.tabs.find(tab => tab.id === tabId);
            if (tab) {
                this.showTab(tabId);
            }
        }
    }

    // UIManager za inicializacijo Materialize CSS komponent
    class UIManager {

        static init() {
            this.initMonacoEditor();
            this.initMaterializeCss();
        }

        static initMonacoEditor() {
            var editors = {}; // To store editor instances by ID

            require(["vs/editor/editor.main"], function () {
                $(".monaco-editor").each(function () {
                    var editorElement = $(this); // The current div element
                    var mode = editorElement.data("mode"); // Get the mode from the data-mode attribute
                    var code = editorElement.data("code");
                    var readonly = editorElement.data("readonly");
                    var editorId = this.id; // Get the id of the current element

                    // Initialize Monaco Editor for this element
                    editors[editorId] = monaco.editor.create(
                        document.getElementById(editorId),
                        {
                            value: atob(code),
                            language: mode, // Use the mode from data-mode attribute
                            theme: "vs-dark", // Set a theme (you can change this)
                            readOnly: readonly,
                            scrollBeyondLastLine: false,
                        }
                    );
                });
            });

        }

        static initMaterializeCss() {
            M.AutoInit();

            M.Collapsible.init($('.collapsible.expandable'), {
                accordion: false
            });

            M.Dropdown.init($('.dropdown-trigger'), {
                constrainWidth: false,
                coverTrigger: false,
                alignment: 'right'
            });

            M.FormSelect.init($('.control-select'), {
                dropdownOptions: {
                    constrainWidth: true,
                    coverTrigger: false,
                    alignment: 'center'
                }
            });

            M.Sidenav.init($('#sidenav'), {
                draggable: true,
                preventScrolling: true
            });

            M.Tooltip.init($('.tooltipped'), {
                position: 'left'
            });

            M.Tabs.init($('.tabs'), {});
            M.Modal.init($('.modal'), {});

            M.updateTextFields();

            $('textarea').each(function () {
                M.textareaAutoResize(this);
            });
        }
    }

    // RequestManager za pošiljanje zahtevkov in obdelavo odgovorov
    class RequestManager {
        constructor(endpoint, tabManager) {
            this.endpoint = endpoint;
            this.tabManager = tabManager;
        }

        sendRequestAndProcessResponse(request, target = null) {
            $.post(this.endpoint, { request: request })
                .done((response) => {
                    this._handleResponse(response, target);
                })
                .fail((jqXHR, textStatus, errorThrown) => {
                    ConsoleLogger.log(`Network error: ${textStatus}`, 'error');
                });
        }

        _handleResponse(response, target) {
            if (response && response.form) {
                target = target || response.form.target || 'main';

                const form = response.form;
                const data = response.data;
                const template = response.form.template;

                const view = this.processView(template, data, target, form);

                if (['new', 'main'].includes(target)) {
                    this.tabManager.addTab(form.title, view);
                } else {
                    $(`#${target}`).html(view);
                }

                EventManager.createLinkEventHandler();
                UIManager.init();
            }

            this._handleMessages(response);
            this._handleRefresh(response);
        }

        _handleMessages(response) {
            if (response && response.messages && response.messages.length > 0) {
                response.messages.forEach(message => {
                    if (DISPLAY_KINDS.includes(message.kind)) {
                        const cssClass = `message-${message.kind}`;
                        M.toast({ html: message.text, classes: cssClass, displayLength: DISPLAY_TIME });
                    }
                    ConsoleLogger.log(message.text, message.kind);
                });
            }
        }

        _handleRefresh(response) {
            if (response && response.refresh === true) {
                location.reload();
            }
        }

        processView(template, data, target, form) {
            if (template.includes("#data")) {
                data = {
                    data,
                    formatDate: () => (text, render) => Utils.formatDate(render(text))
                };
            }

            let view = Mustache.render(template, data);
            view = this.processViewIncludes(view);
            return view;
        }

        processViewIncludes(viewInput) {
            const pattern = /<include\s+actionkey="([^"]+)"\s*\/>/g;
            return viewInput.replace(pattern, (match, actionKey) => {
                const target = `included-div-${Utils.getGuid()}`;
                const request = this.createRequest(actionKey);
                this.sendRequestAndProcessResponse(request, target);
                return `<div id="${target}"></div>`;
            });
        }

        createRequest(queryString) {
            const [actionKey, actionParameters = ''] = queryString.split('?');
            let actionParametersProcessed = Utils.queryStringParamsToObject(actionParameters);

            const formId = actionParametersProcessed['form-id'];
            if (formId) {
                $(`#${formId} .form-field`).each(function () {
                    const key = $(this).attr('name');
                    const val = $(this).val();
                    actionParametersProcessed[key] = val;
                });
                delete actionParametersProcessed['form-id'];
            }

            const requestMsg = {
                actionKey,
                actionParameters: [actionParametersProcessed]
            };

            return JSON.stringify(requestMsg, null, 2);
        }
    }

    // EventManager za upravljanje z dogodki
    class EventManager {
        static init(requestManager) {
            $(document).on('keydown', this.handleKeyDown.bind(this));
            this.requestManager = requestManager;
            this.createLinkEventHandler();

            $(document).on('click', '#confirmAction', this.handleConfirmAction.bind(this));
            $(document).on('click', '#cancelAction', this.handleCancelAction.bind(this));
        }

        static handleKeyDown(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                ConsoleLogger.log("Enter key pressed but action is prevented!", 'user');
            }
        }

        static createLinkEventHandler() {
            $('a[href^="internal:"]').off('click').on('click', this.handleLinkClick.bind(this));
        }

        static handleLinkClick(event) {
            event.preventDefault();
            const href = $(event.currentTarget).attr('href').replace('internal:', '');

            if (Framework.Application.isMustConfirmAction(href)) {
                M.toast({
                    html: `
                        Ali ste prepričani?
                        <button id="cancelAction" class="btn-flat waves-effect waves-light white-text">
                            Ne <i class="material-icons white-text left">close</i>
                        </button>
                        <button id="confirmAction" class="btn-flat waves-effect waves-light white-text" data-href="${href}">
                            Da <i class="material-icons white-text left">check</i>
                        </button>
                    `,
                    displayLength: TOAST_DISPLAY_LENGTH
                });
            } else {
                const request = Framework.Application.requestManager.createRequest(href);
                Framework.Application.requestManager.sendRequestAndProcessResponse(request);
            }

            event.stopPropagation();
        }

        static handleConfirmAction(event) {
            const href = $(event.currentTarget).data('href');
            const request = Framework.Application.requestManager.createRequest(href);
            Framework.Application.requestManager.sendRequestAndProcessResponse(request);
            M.Toast.dismissAll();
        }

        static handleCancelAction() {
            M.Toast.dismissAll();
        }
    }

    // Glavni Application razred
    class Application {
        static init() {
            this.tabManager = new TabManager("tab-container");
            this.requestManager = new RequestManager(ENDPOINT, this.tabManager);
            EventManager.init(this.requestManager);
            UIManager.initMaterializeCss();

            // Pošlji začetni zahtevek
            const initialRequest = this.requestManager.createRequest('Application.ColdStart?id=1');
            this.requestManager.sendRequestAndProcessResponse(initialRequest);

            // Procesiraj poizvedbo iz URL
            this.processQueryStringRequest();
        }

        static processQueryStringRequest() {
            let queryString = window.location.search.replace('?', '').replace('&', '?');

            if (queryString && queryString.includes('.')) {
                const request = this.requestManager.createRequest(queryString);
                this.requestManager.sendRequestAndProcessResponse(request);
            }
        }

        static isMustConfirmAction(queryString) {
            const [actionKey, actionParameters = ''] = queryString.split('?');
            const actionParametersProcessed = Utils.queryStringParamsToObject(actionParameters);
            return actionParametersProcessed['must-confirm'] === 'true';
        }
    }

    return {
        TabManager,
        RequestManager,
        EventManager,
        UIManager,
        ConsoleLogger,
        Application
    };
})();
