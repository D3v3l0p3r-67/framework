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

        getAny(columnName) {

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
            return Array.from(new URLSearchParams(queryString)).reduce((params, [key, value]) => {
                params[key] = value;
                return params;
            }, {});
        },
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

            console.log(`%c[${kind.toUpperCase()}] ${message}`, styles[kind] || styles.default);
        }
    }

    class TabManagerStatic {
        constructor() {
            this.tabs = [];
        }
        // Metoda za inicializacijo zavihkov, ki sprejme kontekst (HTML strukturo)
        initializeTabs(context) {
            const buttonContainer = $(context).find('.tab-buttons');
            const tabContentContainer = $(context).find('.tab-contents');
            const buttons = buttonContainer.find('.tab-button');
            const tabContents = tabContentContainer.find('.tab-content');

            buttons.each((index, button) => {
                const tabId = `tab_${Utils.getGuid()}`;
                const tabElement = $(tabContents[index]);

                tabElement.attr('id', tabId);

                const buttonElement = $(button);
                buttonElement.attr('data-tab-id', tabId);

                buttonElement.on('click', () => {
                    buttonContainer.find('.tab-button').removeClass('active');
                    buttonElement.addClass('active');
                    this.showTab(tabId);
                });

                // Ustvari objekt zavihek
                const tab = { id: tabId, element: tabElement, button: buttonElement };
                this.tabs.push(tab);

                // Preveri, kateri zavihek je aktiven
                if (buttonElement.hasClass('active')) {
                    this.showTab(tabId);
                } else {
                    tabElement.hide();
                }
            });
        }

        showTab(tabId) {
            this.tabs.forEach(tab => {
                if (tab.id === tabId) {
                    tab.element.show();
                } else {
                    tab.element.hide();
                }
            });
        }
    }

    class TabManager {
        constructor(containerId) {
            this.container = $(`#${containerId}`);
            this.buttonContainer = $('<div>', {
                id: `${containerId}_buttons`,
                class: 'tab-buttons sticky-tabs'
            }).appendTo(this.container);
            this.tabContentContainer = $('<div>', {
                id: `${containerId}_content`,
                class: 'tab-contents'
            }).appendTo(this.container);

            this.tabs = [];
            this.currentTabId = null;
        }

        showTab(tabId) {
            //display content
            this.tabs.forEach(tab => {
                if (tab.id === tabId) {
                    tab.element.show();
                } else {
                    tab.element.hide();
                }
            });
            //update tab visual
            this.buttonContainer.find('.tab-button').removeClass('active');
            this.tabs.find(tab => tab.id === tabId).button.addClass('active');

            this.currentTabId = tabId;
        }

        addTab(title, content) {
            const tabId = `tab_${Utils.getGuid()}`;
            const tabElement = $('<div>', { id: tabId, class: 'tab-content' })
                .css('display', 'none')
                .html(content)
                .appendTo(this.tabContentContainer);

            const button = $('<button>', { class: 'btn tab-button' }).text(title);
            const closeButton = $('<span>', { class: 'close-btn' }).html('<i class="material-icons">close</i>');

            closeButton.on('click', (e) => {
                e.stopPropagation();
                this.removeTab(tabId);
            });

            button.on('click', () => {
                this.showTab(tabId);
            });

            button.append(closeButton);
            this.buttonContainer.append(button);

            const tab = { id: tabId, element: tabElement, button: button };
            this.tabs.push(tab);

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

        static monacoEditors = {}; // Globalni objekt za shranjevanje instanc

        static init(context = document) {
            this.initMonacoEditor(context);
            this.initMaterializeCss();
            this.initStaticTabs();
        }

        static initStaticTabs() {
            $('.tab-component-static').each(function () {
                var tabsId = $(this).attr('id');
                new TabManagerStatic().initializeTabs('#' + tabsId);
            });
        }

        static initMonacoEditor(context = document) {
            require(["vs/editor/editor.main"], function () {
                var $context = $(context);

                $context.find(".monaco-editor").each(function () {
                    var editorElement = $(this);
                    var editorId = this.id;
                    var container = $("#" + editorId);

                    if (UIManager.monacoEditors[editorId]) {
                        return;
                    }

                    var lang = editorElement.data("lang");
                    var code = editorElement.data("code");
                    var readonly = editorElement.data("readonly");

                    var editorInstance = monaco.editor.create(
                        document.getElementById(this.id),
                        {
                            value: atob(code),
                            language: lang,
                            theme: "vs-light",
                            readOnly: readonly,
                            scrollBeyondLastLine: false,
                            wordWrap: 'on',
                            wrappingStrategy: 'advanced',
                            overviewRulerLanes: 0,
                            minimap: {
                                enabled: false
                            },
                            scrollbar: {
                                handleMouseWheel: false,
                            },
                        }
                    );
                    UIManager.monacoEditors[editorId] = editorInstance;

                    const updateHeight = () => {
                        const contentHeight = editorInstance.getContentHeight();
                        container.css('height', contentHeight + 'px');
                        container.css('width', '100%');
                        editorInstance.layout();
                    };
                    editorInstance.onDidContentSizeChange(updateHeight);


                    /*
                    function updateEditorHeight() {
                        const contentHeight = Math.max(editorInstance.getContentHeight() + 50) + 10; // Set minimum height to 200px

                        ConsoleLogger.log(`contentHeight: ${contentHeight}`, 'error');

                        $("#" + editorId).css('height', contentHeight + 'px');
                        editorInstance.layout();
                    }

                    // Initial height adjustment
                    updateEditorHeight();
                    */
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
                UIManager.init(view);
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
                    type_id: data.length > 0 ? data[0].type_id : null,
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

            const formId = '#' + actionParametersProcessed['form-id'];
            if (formId) {
                $(formId + ' .form-field:not(.monaco-editor)').each(function () {
                    const key = $(this).attr('name');
                    const val = $(this).val();
                    actionParametersProcessed[key] = val;
                });

                $(formId + ' .monaco-editor').each(function () {
                    const editorElement = $(this);
                    const key = editorElement.attr('name');
                    const editorId = this.id; // Uporabimo ID elementa

                    // Pridobi instanco urejevalnika iz globalnega objekta
                    const editorInstance = UIManager.monacoEditors[editorId];

                    ConsoleLogger.log('Monaco Editor 1', 'error');
                    ConsoleLogger.log('key: ' + key, 'error');
                    ConsoleLogger.log('editorId: ' + editorId, 'error');
                    ConsoleLogger.log('editorInstance: ' + editorInstance, 'error');

                    if (editorInstance && key) {
                        const val = editorInstance.getValue();
                        actionParametersProcessed[key] = val;

                        ConsoleLogger.log('Monaco Editor => : key: ' + key + ', value: ' + val, 'error');
                    }
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
            this.requestManager = requestManager;
            this.createLinkEventHandler();

            $(document).on('click', '#confirmAction', this.handleConfirmAction.bind(this));
            $(document).on('click', '#cancelAction', this.handleCancelAction.bind(this));
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
