jQuery(document).ready($ => {
    function loading(state) {
        if (state) {
            $('.loader').css('display', 'flex');
        } else {
            $('.loader').fadeOut(300);
        }
    }

    function initShiftCheckbox() {
        let $lastChecked = $('<div>');

        $('.item-cb').on('click', event => {
            const $el = $(event.currentTarget);
            const type = $el.attr('data-type');
            const index = $el.attr('data-index');
            const lastIndex = $lastChecked.attr('data-index');
            const lastType = $lastChecked.attr('data-type');

            if (event.shiftKey && lastType === type && lastIndex !== index) {
                const max = Math.max(+index, +lastIndex) + 1;
                const min = Math.min(+index, +lastIndex);

                for (let i = min; i < max; i++) {
                    const $item = $('.item-cb[data-type=' + type + '][data-index=' + i + ']').filter(':visible');

                    $item.prop('checked', $lastChecked.prop('checked'));
                }
            }

            $lastChecked = $el;
        });
    }

    function initNavTab() {
        $('.db-nav .nav-item').on('click', event => {
            const $el = $(event.currentTarget);
            const $navLink = $el.find('.nav-link');
            const content = $el.attr('data-content');
            if ($navLink.hasClass('active')) {
                return false;
            }

            $('.db-nav .nav-item .nav-link').removeClass('active');
            $navLink.addClass('active');

            $('.tab-content').removeClass('active');
            $('.tab-content[data-content=' + content + ']').addClass('active');

            saveActiveTab(content);
        });
    }

    function saveActiveTab(content) {
        sessionStorage.setItem('tab:' + location.href, content);
    }

    function initActiveTab(data) {
        const content = data || 'extension';

        $('.nav-item[data-content=' + content + ']').find('.nav-link').addClass('active');
        $('.tab-content[data-content=' + content + ']').addClass('active');
    }

    function initFilter($container) {
        let type = '*';
        let search = '';

        $container.find('.filter-item span').on('click', event => {
            const $el = $(event.currentTarget);
            if (type === $el.attr('data-type')) {
                return;
            }

            $container.find('.filter-item').removeClass('active');
            $el.parent().addClass('active');

            type = $el.attr('data-type');
            showMatchedElements(type, search)
        });

        $container.find('.input-filter-name').on('input', event => {
            search = $(event.currentTarget).val().trim().toLowerCase();
            showMatchedElements(type, search);
        })

        function showMatchedElements(type, search) {
            $container.find('.item').addClass('hidden');

            const className = '.item';
            const attr = type === '*' ? '' : '[data-type=' + type + ']';
            const $byTypeItems = $container.find(className + attr);

            $byTypeItems.each((idx, el) => {
                const $el = $(el);
                const $name = $el.find('.item-name');
                const $element = $el.find('.item-element');
                const _name = $name.text().toLowerCase();
                const _element = $element.text().toLowerCase();

                if (!search || _name.indexOf(search) !== -1 || _element.indexOf(search) !== -1) {
                    $el.removeClass('hidden');
                    $name.unmark({
                        done() {
                            $name.mark(search, {
                                separateWordSearch: false,
                                diacritics: false,
                            });
                        }
                    })
                    $element.unmark({
                        done() {
                            $element.mark(search, {
                                separateWordSearch: false,
                                diacritics: false,
                            });
                        }
                    })
                }
            })
        }
    }

    function initProfileSelection() {
        const $form = $('.form-export');

        $form.find('.btn-new-profile').on('click', () => {
            const name = prompt('New Profile');

            if (name === null) {
                return;
            }
            
            if (!name || !/^[\da-zA-Z_]+$/.test(name)) {
                return alert('Error. Profile name only contains alphabet, number and underscore ( _ )');
            };

            $form.find('.new-profile-input').val(name);

            $form.find('input[name="task"]').val('new_profile');
            $form.submit();
        });

        $form.find('.btn-delete-profile').on('click', () => {
            const profile = $form.find('.profile-selection').val();
            if (!profile) {
                return alert('Please select a profile');
            }

            const ok = confirm('Are you sure to delete this profile?');

            if (ok) {
                $form.find('input[name="task"]').val('delete_profile');
                $form.submit();
            }
        })

        $form.find('.btn-save-profile').on('click', () => {
            const profile = $form.find('.profile-selection').val();
            if (!profile) {
                return alert('Please select a profile');
            }

            $form.find('input[name="task"]').val('save_profile');
            $form.submit();
        })

        const $selection = $form.find('.profile-selection');
        
        $selection.select2();
        $selection.on('change', () => {
            $form.find('input[name="task"]').val('change_profile');
            $form.submit();
        })
    }

    function initExportButton() {
        const $form = $('.form-export');
        const $task = $form.find('input[name=task]');
        const $select = $form.find('.svn-folder-selection');
        const $dbConfig = $('.db-config');

        $form.find('.btn-export').on('click', event => {
            if (!$select.val()) {
                return alert('Please choose folder');
            }
            
            if (!$dbConfig.length) {
                return alert('Please load a project');
            }

            const taskName = $(event.target).attr('task');
            if (taskName === 'commit') {
                const msg = prompt('Commit Message');
                if (msg === null) {
                    return;
                }

                if (!msg) {
                    return alert('empty message')
                }

                $form.find('input[name=msg]').val(msg);
            }

            loading(true);
            $task.val(taskName);
            $.ajax({
                url: window.uri_root,
                dataType: 'json',
                method: 'post',
                data: $form.serializeArray(),
            })
            .done(res => {
                if (res.error) {
                    return alert('Error: ' + res.error);
                }

                if (res.success) {
                    const message = (taskName === 'export' || taskName === 'export_prefix')
                        ? 'Success. Check files at /local/' + $select.val() + '/'
                        : 'Commit success.';

                    return alert(message);
                }
            })
            .fail(error => {
                return alert('ajax error');
            })
            .always(() => {
                loading(false)
            });
        })

        $form.find('.btn-commit').on('click', () => {
            if (!$select.val()) {
                return alert('Please choose folder');
            }
        })

        $select.select2();
        $.ajax({
            url: window.uri_rooot,
            dataType: 'json',
            method: 'post',
            data: {
                task: 'get_list_svn_folder',
            }
        })
        .done(res => {
            if (res.error) {
                return alert(res.error);
            }

            if (res.list && res.list.length) {
                res.list.forEach(str => {
                    $select.append('<option value="'+str+'">'+str+'</option>');
                })
            }
        })
        .fail(error => {
            alert('ajax error');
        });

        $form.find('.btn-new-svn-folder').on('click', () => {
            const name = prompt('New SVN Folder');
            
            if (name === null) {
                return;
            }
            
            if (!name || !/^[\da-zA-Z_]+$/.test(name)) {
                return alert('Error. Folder name only contains alphabet, number and underscore ( _ )');
            };

            loading(true);
            $.ajax({
                url: window.uri_rooot,
                dataType: 'json',
                method: 'post',
                data: {
                    task: 'new_svn_folder',
                    name: name,
                }
            })
            .done(res => {
                if (res.error) {
                    return alert(res.error);
                }
                
                if (res.success) {
                    alert('success');
                    window.location.reload();
                }
            })
            .fail(error => {
                alert('ajax error');
            })
            .always(() => {
                loading(false);
            });
        });
    }

    initExportButton();
    initProfileSelection();
    initShiftCheckbox();
    initNavTab();
    initActiveTab(sessionStorage.getItem('tab:' + location.href));
    initFilter($('.config-extension'));
    initFilter($('.config-table'));
});