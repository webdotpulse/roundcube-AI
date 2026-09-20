/**
 * Frontend JavaScript for roundcube_attachments plugin
 *
 * Handles:
 * 1. Server attachments modal & file management in compose window (clean singleton injection, no duplicates).
 * 2. Automatic insertion of subject & attachments when inserting canned responses (reactions).
 * 3. Subject field & attachment manager injection in Settings -> Responses (responseedit).
 * 4. Removal of the About button from the right sidebar.
 */

(function(window, document, $) {
    'use strict';

    if (!window.rcmail) {
        return;
    }

    // Helper: format byte size into human readable string
    function formatBytes(bytes) {
        if (!bytes || bytes <= 0) return '0 B';
        var k = 1024;
        var sizes = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    // Helper: get appropriate icon for mimetype/filename
    function getFileIcon(mimetype, filename) {
        var ext = (filename || '').split('.').pop().toLowerCase();
        if (ext === 'pdf' || (mimetype && mimetype.indexOf('pdf') !== -1)) return '📄';
        if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].indexOf(ext) !== -1 || (mimetype && mimetype.indexOf('image') !== -1)) return '🖼️';
        if (['zip', 'tar', 'gz', 'rar', '7z'].indexOf(ext) !== -1 || (mimetype && mimetype.indexOf('zip') !== -1)) return '📦';
        if (['doc', 'docx', 'odt', 'rtf'].indexOf(ext) !== -1) return '📝';
        if (['xls', 'xlsx', 'ods', 'csv'].indexOf(ext) !== -1) return '📊';
        if (['ppt', 'pptx', 'odp'].indexOf(ext) !== -1) return '📑';
        return '📎';
    }

    // ========================================================
    // 0. Remove About Button from Right Sidebar
    // ========================================================

    function removeAboutButton() {
        var selectors = '#layout-menu a.about, #layout-menu a.button-about, #taskmenu a.about, #taskmenu a.button-about, .special-buttons a.about, .special-buttons a.button-about, a.button-about, a.about[onclick*="about_dialog"], a[href="#about"]';
        var docs = [document];
        try {
            if (window.parent && window.parent.document && window.parent.document !== document) {
                docs.push(window.parent.document);
            }
            if (window.top && window.top.document && docs.indexOf(window.top.document) === -1) {
                docs.push(window.top.document);
            }
        } catch (e) {}

        docs.forEach(function(d) {
            try {
                var items = d.querySelectorAll(selectors);
                for (var i = 0; i < items.length; i++) {
                    items[i].remove();
                }
            } catch (e) {}
        });
    }

    // ========================================================
    // 1. Compose Window: Server Attachments Modal & Clean Injection
    // ========================================================

    function initCompose() {
        var env = rcmail.env;
        var isCompose = (env.action === 'compose') || $('#compose-attachments, #composeattachments, .compose-attachments, form#form').length > 0;
        if (!isCompose) {
            return;
        }

        // Add "Server Attachments" button into compose attachments area (guaranteed singleton)
        injectComposeButton();

        // Hook rcmail.insert_response to automatically apply subject and attachments
        hookInsertResponse();
    }

    function injectComposeButton() {
        var label = rcmail.gettext('server_attachments', 'roundcube_attachments') || 'Server Attachments';

        // 1. Clean up any misplaced or duplicated toolbars / buttons
        $('.rc-server-att-toolbar').remove();
        $('#compose-attachments > button#rc-server-att-compose-btn').remove();
        $('#compose-attachments .header #rc-server-att-compose-btn').remove();

        // Clean up any duplicate vcard button accidentally appended to header
        $('#compose-attachments .header button.vcard, #compose-attachments > button.vcard').remove();

        // Remove any unstyled stray buttons that match the label text outside #rc-server-att-compose-btn
        $('button').filter(function() {
            return $(this).attr('id') !== 'rc-server-att-compose-btn' && $(this).text().trim() === label;
        }).remove();

        // 2. Check if the properly placed button already exists
        if ($('#rc-server-att-compose-btn').length > 0) {
            return;
        }

        var btnHtml = '<button type="button" id="rc-server-att-compose-btn" class="btn btn-secondary attach rc-btn-server-att" title="' + label + '">'
            + '<span class="rc-att-btn-icon">📁</span> '
            + '<span class="rc-att-btn-text">' + label + '</span>'
            + '</button>';

        // 3. Target the primary attachment button or the buttons container inside the dropzone
        var $attachBtn = $('#compose-attachments button.attach, .compose-attachments button.attach, .upload-form button.attach').not('.vcard, .rc-btn-server-att');
        var $btnGroup = $('#compose-attachments .buttons, .compose-attachments .buttons, .upload-form .buttons');

        if ($attachBtn.length) {
            // Insert directly next to "Bijlage toevoegen"
            $attachBtn.first().after(' ' + btnHtml);
        } else if ($btnGroup.length) {
            // Append into the buttons group
            $btnGroup.first().append(' ' + btnHtml);
        } else {
            // Fallback: toolbar or compose form
            var $toolbar = $('#compose-toolbar, #messagetoolbar');
            if ($toolbar.length) {
                $toolbar.append(btnHtml);
            }
        }

        // Delegate click handler cleanly
        $(document).off('click.rc_server_att', '#rc-server-att-compose-btn').on('click.rc_server_att', '#rc-server-att-compose-btn', function(e) {
            e.preventDefault();
            openServerAttachmentsModal('compose');
        });
    }

    // Intercept rcmail.insert_response
    function hookInsertResponse() {
        if (window.__rc_att_hooked) {
            return;
        }
        window.__rc_att_hooked = true;

        var orig_insert_response = rcmail.insert_response;
        rcmail.insert_response = function(response) {
            if (typeof response === 'object' && response !== null) {
                // 1. Auto-set Subject if provided
                if (response.subject && response.subject.trim() !== '') {
                    var $subject = $('#_subject, input[name="_subject"]');
                    if ($subject.length) {
                        var curVal = ($subject.val() || '').trim();
                        if (!curVal) {
                            $subject.val(response.subject).trigger('change');
                        }
                    }
                }

                // 2. Auto-attach files if provided
                if (response.attachments && response.attachments.length > 0) {
                    attachFilesToCompose(response.attachments);
                }
            }

            if (typeof orig_insert_response === 'function') {
                return orig_insert_response.apply(this, arguments);
            }
        };
    }

    // Attach server file IDs to active compose email
    function attachFilesToCompose(fileIds, callback) {
        if (!fileIds || !fileIds.length) {
            return;
        }

        var composeId = rcmail.env.compose_id;
        var uploadId = 'rcm_att_' + (new Date()).getTime();

        var lock = rcmail.set_busy(true, 'uploading');
        rcmail.http_post('plugin.roundcube_attachments_attach_to_compose', {
            composeId: composeId,
            uploadId: uploadId,
            fileIds: fileIds
        }, lock);

        if (typeof callback === 'function') {
            callback();
        }
    }

    // ========================================================
    // 2. Server Attachments Modal (Reusable for Compose & Reactions)
    // ========================================================

    function openServerAttachmentsModal(context, onSelectCallback) {
        ensureModalHtml();

        var $modal = $('#rc-server-att-modal');
        var $overlay = $('#rc-server-att-overlay');

        $modal.data('context', context || 'compose');
        $modal.data('onSelect', onSelectCallback || null);

        // Reset search & selection
        $('#rc-server-att-search').val('');
        $('#rc-server-att-attach-btn').prop('disabled', true);

        // Load files from server
        loadServerAttachmentsList();

        $overlay.fadeIn(150);
        $modal.fadeIn(150);
    }

    function closeServerAttachmentsModal() {
        $('#rc-server-att-overlay').fadeOut(150);
        $('#rc-server-att-modal').fadeOut(150);
    }

    function ensureModalHtml() {
        if ($('#rc-server-att-modal').length) {
            return;
        }

        var title = rcmail.gettext('select_attachments', 'roundcube_attachments') || 'Select Server Attachments';
        var btnAttach = rcmail.gettext('attach_selected', 'roundcube_attachments') || 'Attach Selected';
        var btnUpload = rcmail.gettext('upload_to_server', 'roundcube_attachments') || 'Upload to Server';
        var searchPlaceholder = rcmail.gettext('search_placeholder', 'roundcube_attachments') || 'Search saved attachments...';

        var html = '<div id="rc-server-att-overlay" class="rc-att-overlay" style="display:none;"></div>'
            + '<div id="rc-server-att-modal" class="rc-att-modal" style="display:none;">'
            + '  <div class="rc-att-modal-header">'
            + '    <div class="rc-att-modal-title">📁 ' + title + '</div>'
            + '    <button type="button" class="rc-att-modal-close" id="rc-server-att-close">&times;</button>'
            + '  </div>'
            + '  <div class="rc-att-modal-toolbar">'
            + '    <input type="text" id="rc-server-att-search" class="form-control" placeholder="' + searchPlaceholder + '">'
            + '    <button type="button" id="rc-server-att-upload-btn" class="btn btn-outline-primary rc-btn-upload">⬆️ ' + btnUpload + '</button>'
            + '    <input type="file" id="rc-server-att-file-input" multiple style="display:none;">'
            + '  </div>'
            + '  <div class="rc-att-modal-body">'
            + '    <div id="rc-server-att-loading" class="rc-att-loading" style="display:none;">Loading...</div>'
            + '    <div id="rc-server-att-empty" class="rc-att-empty" style="display:none;">'
            +        (rcmail.gettext('no_attachments_found', 'roundcube_attachments') || 'No saved attachments found.')
            + '    </div>'
            + '    <div id="rc-server-att-list" class="rc-att-file-list"></div>'
            + '  </div>'
            + '  <div class="rc-att-modal-footer">'
            + '    <span id="rc-server-att-selected-count" class="rc-att-selected-count">0 selected</span>'
            + '    <div class="rc-att-modal-footer-btns">'
            + '      <button type="button" class="btn btn-secondary" id="rc-server-att-cancel">Cancel</button>'
            + '      <button type="button" class="btn btn-primary" id="rc-server-att-attach-btn" disabled>' + btnAttach + '</button>'
            + '    </div>'
            + '  </div>'
            + '</div>';

        $('body').append(html);

        // Bind modal event listeners
        $('#rc-server-att-close, #rc-server-att-cancel, #rc-server-att-overlay').on('click', closeServerAttachmentsModal);

        $('#rc-server-att-search').on('input', function() {
            var q = $(this).val().toLowerCase().trim();
            $('#rc-server-att-list .rc-att-item').each(function() {
                var name = $(this).find('.rc-att-item-name').text().toLowerCase();
                $(this).toggle(name.indexOf(q) !== -1);
            });
        });

        // Trigger file input
        $('#rc-server-att-upload-btn').on('click', function() {
            $('#rc-server-att-file-input').val('').trigger('click');
        });

        // Handle file upload
        $('#rc-server-att-file-input').on('change', function() {
            var files = this.files;
            if (!files || !files.length) return;
            uploadFilesToServer(files);
        });

        // Item checkbox change
        $(document).on('change', '#rc-server-att-list input.rc-att-checkbox', function() {
            updateSelectedCount();
        });

        // Click on item row toggles checkbox
        $(document).on('click', '#rc-server-att-list .rc-att-item', function(e) {
            if ($(e.target).is('input, button, a, .rc-att-del-btn')) return;
            var $chk = $(this).find('input.rc-att-checkbox');
            $chk.prop('checked', !$chk.prop('checked')).trigger('change');
        });

        // Delete button
        $(document).on('click', '.rc-att-del-btn', function(e) {
            e.stopPropagation();
            var id = $(this).data('id');
            var name = $(this).data('name');
            var confirmMsg = rcmail.gettext('delete_confirm', 'roundcube_attachments') || 'Are you sure you want to delete this saved attachment?';
            if (confirm(confirmMsg + '\n\n' + name)) {
                deleteServerAttachment(id);
            }
        });

        // Attach Selected button
        $('#rc-server-att-attach-btn').on('click', function() {
            var selectedIds = [];
            var selectedRecords = [];
            $('#rc-server-att-list input.rc-att-checkbox:checked').each(function() {
                var id = $(this).val();
                selectedIds.push(id);
                selectedRecords.push($(this).closest('.rc-att-item').data('record'));
            });

            if (!selectedIds.length) return;

            var $modal = $('#rc-server-att-modal');
            var context = $modal.data('context');
            var onSelect = $modal.data('onSelect');

            if (typeof onSelect === 'function') {
                onSelect(selectedIds, selectedRecords);
                closeServerAttachmentsModal();
            } else if (context === 'compose') {
                attachFilesToCompose(selectedIds, function() {
                    closeServerAttachmentsModal();
                });
            } else {
                closeServerAttachmentsModal();
            }
        });
    }

    function updateSelectedCount() {
        var count = $('#rc-server-att-list input.rc-att-checkbox:checked').length;
        $('#rc-server-att-selected-count').text(count + ' selected');
        $('#rc-server-att-attach-btn').prop('disabled', count === 0);
    }

    function loadServerAttachmentsList() {
        var $list = $('#rc-server-att-list').empty();
        var $loading = $('#rc-server-att-loading').show();
        var $empty = $('#rc-server-att-empty').hide();

        $.ajax({
            url: rcmail.url('plugin.roundcube_attachments_list'),
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                $loading.hide();
                if (res && res.status === 'success' && res.files && res.files.length) {
                    window.__rc_server_files_cache = res.files;
                    renderServerAttachmentsList(res.files);
                } else {
                    $empty.show();
                }
            },
            error: function() {
                $loading.hide();
                $empty.show();
            }
        });
    }

    function renderServerAttachmentsList(files) {
        var $list = $('#rc-server-att-list').empty();
        var deleteLabel = rcmail.gettext('delete_attachment', 'roundcube_attachments') || 'Delete';

        files.forEach(function(file) {
            var icon = getFileIcon(file.mimetype, file.name);
            var sizeStr = formatBytes(file.size);
            var dateStr = file.created ? new Date(file.created * 1000).toLocaleDateString() : '';

            var $item = $('<div class="rc-att-item"></div>').data('record', file);
            var html = '<div class="rc-att-item-left">'
                + '  <input type="checkbox" class="rc-att-checkbox" value="' + file.id + '">'
                + '  <span class="rc-att-item-icon">' + icon + '</span>'
                + '  <div class="rc-att-item-details">'
                + '    <div class="rc-att-item-name" title="' + file.name + '">' + file.name + '</div>'
                + '    <div class="rc-att-item-meta">' + sizeStr + (dateStr ? ' • ' + dateStr : '') + '</div>'
                + '  </div>'
                + '</div>'
                + '<div class="rc-att-item-actions">'
                + '  <a href="' + rcmail.url('plugin.roundcube_attachments_download', {_id: file.id}) + '" target="_blank" class="btn btn-sm btn-link" title="Preview/Download">⬇️</a>'
                + '  <button type="button" class="btn btn-sm btn-link text-danger rc-att-del-btn" data-id="' + file.id + '" data-name="' + file.name + '" title="' + deleteLabel + '">🗑️</button>'
                + '</div>';

            $item.html(html);
            $list.append($item);
        });

        updateSelectedCount();
    }

    function uploadFilesToServer(files, onUploadedCallback) {
        var formData = new FormData();
        for (var i = 0; i < files.length; i++) {
            formData.append('_attachments[]', files[i]);
        }

        var lock = rcmail.set_busy(true, 'uploading');
        $.ajax({
            url: rcmail.url('plugin.roundcube_attachments_upload'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                rcmail.set_busy(false, null, lock);
                if (res && res.status === 'success') {
                    rcmail.display_message(res.message || 'Attachment saved', 'confirmation');
                    loadServerAttachmentsList();
                    if (typeof onUploadedCallback === 'function') {
                        onUploadedCallback(res.files);
                    }
                } else {
                    rcmail.display_message(res.message || 'Upload error', 'error');
                }
            },
            error: function() {
                rcmail.set_busy(false, null, lock);
                rcmail.display_message('Upload error', 'error');
            }
        });
    }

    function deleteServerAttachment(id) {
        var lock = rcmail.set_busy(true, 'loading');
        $.ajax({
            url: rcmail.url('plugin.roundcube_attachments_delete'),
            type: 'POST',
            data: { _id: id },
            dataType: 'json',
            success: function(res) {
                rcmail.set_busy(false, null, lock);
                if (res && res.status === 'success') {
                    loadServerAttachmentsList();
                } else {
                    rcmail.display_message(res.message || 'Error deleting', 'error');
                }
            },
            error: function() {
                rcmail.set_busy(false, null, lock);
            }
        });
    }

    // ========================================================
    // 3. Settings -> Responses (Reactions) Integration
    // ========================================================

    function initResponses() {
        checkAndInjectResponses(document);

        // Check if on settings/responses list page with preferences-frame iframe
        var iframe = document.getElementById('preferences-frame');
        if (iframe) {
            $(iframe).on('load', function() {
                [40, 150, 400].forEach(function(delay) {
                    setTimeout(function() {
                        try {
                            var doc = iframe.contentDocument || (iframe.contentWindow && iframe.contentWindow.document);
                            if (doc) {
                                checkAndInjectResponses(doc);
                                observeResponseDoc(doc);
                            }
                        } catch (e) {}
                    }, delay);
                });
            });

            try {
                var doc = iframe.contentDocument || (iframe.contentWindow && iframe.contentWindow.document);
                if (doc) {
                    checkAndInjectResponses(doc);
                    observeResponseDoc(doc);
                }
            } catch (e) {}
        }

        // Observe main document for dynamic AJAX/pjax view updates
        observeResponseDoc(document);
    }

    function observeResponseDoc(doc) {
        if (!doc || doc.__rc_att_observed || !window.MutationObserver) {
            return;
        }
        doc.__rc_att_observed = true;

        var target = doc.getElementById('layout-content') || doc.body;
        if (!target) return;

        var observer = new MutationObserver(function() {
            if (doc.getElementById('fftext') || doc.getElementById('ffname')) {
                if (!doc.getElementById('rc-reaction-attachments-container') || !doc.getElementById('ffsubject')) {
                    checkAndInjectResponses(doc);
                }
            }
            removeAboutButton();
        });

        observer.observe(target, { childList: true, subtree: true });
    }

    function checkAndInjectResponses(doc) {
        if (!doc) return;
        var $doc = $(doc);

        // Robust check: if response edit fields (#fftext or #ffname) exist
        if (!$doc.find('#fftext, #ffname').length) {
            return;
        }

        injectResponsesFormFields(doc);
    }

    function injectResponsesFormFields(doc) {
        var $doc = $(doc);
        var pluginEnv = rcmail.env.roundcube_attachments || {};
        var responseMeta = pluginEnv.response_meta || { subject: '', attachments: [], files: [] };

        // Determine current response ID from input, environment, or URL
        var currentId = $doc.find('input[name="_id"], #ffid').val() || pluginEnv.response_id || '';
        if (!currentId) {
            var searchStr = (doc.location && doc.location.search) || window.location.search || '';
            var idMatch = searchStr.match(/[?&]_id=([^&]+)/);
            if (idMatch) {
                currentId = decodeURIComponent(idMatch[1]);
            }
        }

        // 1. Inject Subject Field directly below Name (#ffname) if not present
        if (!$doc.find('#ffsubject').length) {
            var labelSubject = rcmail.gettext('reaction_subject', 'roundcube_attachments') || 'Subject';
            var placeholderSubject = rcmail.gettext('reaction_subject_placeholder', 'roundcube_attachments') || 'Optional subject for this reaction';
            var existingSubject = responseMeta.subject || '';

            var $nameInput = $doc.find('#ffname');
            if ($nameInput.length) {
                var $nameRow = $nameInput.closest('.form-group, tr, .row');
                if (!$nameRow.length) {
                    $nameRow = $nameInput.parent();
                }

                if ($nameRow.is('tr')) {
                    var trHtml = '<tr id="rc-att-subject-row">'
                        + '<th class="title"><label for="ffsubject">' + labelSubject + '</label></th>'
                        + '<td><input type="text" id="ffsubject" name="_subject" class="form-control" placeholder="' + placeholderSubject + '" value="' + $('<div>').text(existingSubject).html() + '"></td>'
                        + '</tr>';
                    $nameRow.after(trHtml);
                } else {
                    var divHtml = '<div class="form-group row" id="rc-att-subject-group">'
                        + '<label for="ffsubject" class="col-sm-2 col-form-label">' + labelSubject + '</label>'
                        + '<div class="col-sm-10">'
                        + '  <input type="text" id="ffsubject" name="_subject" class="form-control" placeholder="' + placeholderSubject + '" value="' + $('<div>').text(existingSubject).html() + '">'
                        + '</div>'
                        + '</div>';
                    $nameRow.after(divHtml);
                }
            }
        }

        // 2. Inject Attachments Management Section if not present
        if (!$doc.find('#rc-reaction-attachments-container').length) {
            injectReactionAttachmentsSection($doc, responseMeta);
        }

        // 3. If a response ID exists, ensure its latest metadata (subject & attachments) is loaded
        if (currentId) {
            loadResponseMetaDynamically($doc, currentId);
        }

        // 4. Hook form submission to guarantee subject & attachments persistence
        hookResponseFormSubmit($doc, currentId);
    }

    function injectReactionAttachmentsSection($doc, responseMeta) {
        var labelAtt = rcmail.gettext('reaction_attachments', 'roundcube_attachments') || 'Attachments';
        var descAtt = rcmail.gettext('reaction_attachments_desc', 'roundcube_attachments') || 'Attachments automatically added when inserting this reaction into an email';
        var btnAddServer = rcmail.gettext('add_server_attachment', 'roundcube_attachments') || 'Attach from Server';
        var btnUploadNew = rcmail.gettext('upload_to_server', 'roundcube_attachments') || 'Upload & Attach';

        var initialIds = responseMeta.attachments || [];
        var initialFiles = responseMeta.files || [];

        var boxHtml = '<div id="rc-reaction-attachments-container" class="rc-reaction-attachments-box">'
            + '  <div class="rc-reaction-att-header">'
            + '    <label class="rc-reaction-att-label">📎 ' + labelAtt + '</label>'
            + '    <div class="rc-reaction-att-desc text-muted">' + descAtt + '</div>'
            + '  </div>'
            + '  <div id="rc-reaction-attached-list" class="rc-reaction-attached-list"></div>'
            + '  <input type="hidden" id="ffattachments" name="_attachments" value="' + $('<div>').text(JSON.stringify(initialIds)).html() + '">'
            + '  <div class="rc-reaction-att-actions">'
            + '    <button type="button" id="rc-btn-reaction-add-server" class="btn btn-outline-secondary btn-sm">📁 ' + btnAddServer + '</button>'
            + '    <button type="button" id="rc-btn-reaction-upload" class="btn btn-outline-primary btn-sm">⬆️ ' + btnUploadNew + '</button>'
            + '    <input type="file" id="rc-reaction-file-input" multiple style="display:none;">'
            + '  </div>'
            + '</div>';

        // Insert container directly before the save button row, or right after editor
        var $saveBtn = $doc.find('.formbuttons, button.mainaction, input.mainaction, button[type="submit"], input[type="submit"]');
        var $saveRow = $saveBtn.closest('.formbuttons, .form-group, tr, div');
        var $editorContainer = $doc.find('.tox-tinymce, .mce-tinymce, #fftext').closest('.form-group, tr, td, div');

        if ($saveRow.length) {
            if ($saveRow.is('tr')) {
                $saveRow.first().before('<tr id="rc-att-box-row"><th></th><td>' + boxHtml + '</td></tr>');
            } else if ($saveRow.hasClass('form-group') || $saveRow.hasClass('row') || $saveRow.hasClass('formbuttons')) {
                $saveRow.first().before('<div class="form-group row" id="rc-att-box-group"><div class="col-sm-10 offset-sm-2">' + boxHtml + '</div></div>');
            } else {
                $saveRow.first().before(boxHtml);
            }
        } else if ($editorContainer.length) {
            if ($editorContainer.is('tr')) {
                $editorContainer.first().after('<tr id="rc-att-box-row"><th></th><td>' + boxHtml + '</td></tr>');
            } else {
                $editorContainer.first().after('<div class="form-group row" id="rc-att-box-group"><div class="col-sm-10 offset-sm-2">' + boxHtml + '</div></div>');
            }
        } else {
            var $form = $doc.find('form').first();
            if ($form.length) {
                $form.append(boxHtml);
            } else {
                $doc.find('#fftext').parent().append(boxHtml);
            }
        }

        // Render initial files
        renderReactionAttachedItems($doc, initialFiles, initialIds);

        // Bind Add from Server button
        $doc.off('click.rc_reaction_server', '#rc-btn-reaction-add-server').on('click.rc_reaction_server', '#rc-btn-reaction-add-server', function(e) {
            e.preventDefault();
            openServerAttachmentsModal('reaction', function(selectedIds, selectedRecords) {
                var curIds = getReactionCurrentIds($doc);
                selectedRecords.forEach(function(rec) {
                    if (curIds.indexOf(rec.id) === -1) {
                        curIds.push(rec.id);
                        addReactionAttachedItem($doc, rec);
                    }
                });
                setReactionCurrentIds($doc, curIds);
            });
        });

        // Bind Upload & Attach button
        $doc.off('click.rc_reaction_upload', '#rc-btn-reaction-upload').on('click.rc_reaction_upload', '#rc-btn-reaction-upload', function(e) {
            e.preventDefault();
            $doc.find('#rc-reaction-file-input').val('').trigger('click');
        });

        $doc.off('change.rc_reaction_file', '#rc-reaction-file-input').on('change.rc_reaction_file', '#rc-reaction-file-input', function() {
            var files = this.files;
            if (!files || !files.length) return;

            uploadFilesToServer(files, function(uploadedRecords) {
                var curIds = getReactionCurrentIds($doc);
                uploadedRecords.forEach(function(rec) {
                    if (curIds.indexOf(rec.id) === -1) {
                        curIds.push(rec.id);
                        addReactionAttachedItem($doc, rec);
                    }
                });
                setReactionCurrentIds($doc, curIds);
            });
        });

        // Remove item button
        $doc.off('click.rc_reaction_remove', '.rc-reaction-att-remove').on('click.rc_reaction_remove', '.rc-reaction-att-remove', function() {
            var id = $(this).data('id');
            var curIds = getReactionCurrentIds($doc);
            var idx = curIds.indexOf(id);
            if (idx !== -1) {
                curIds.splice(idx, 1);
                setReactionCurrentIds($doc, curIds);
            }
            $(this).closest('.rc-reaction-att-chip').remove();
        });
    }

    function loadResponseMetaDynamically($doc, responseId) {
        if (!responseId || $doc.data('rc_att_loaded_id') === responseId) {
            return;
        }
        $doc.data('rc_att_loaded_id', responseId);

        $.ajax({
            url: rcmail.url('plugin.roundcube_attachments_get_meta'),
            type: 'GET',
            data: { response_id: responseId },
            dataType: 'json',
            success: function(res) {
                if (res && res.status === 'success') {
                    if (res.subject && !$doc.find('#ffsubject').val()) {
                        $doc.find('#ffsubject').val(res.subject);
                    }
                    if (res.attachments && res.attachments.length) {
                        setReactionCurrentIds($doc, res.attachments);
                        renderReactionAttachedItems($doc, res.files || [], res.attachments);
                    }
                }
            }
        });
    }

    function hookResponseFormSubmit($doc, initialId) {
        var $form = $doc.find('form').filter(':has(#fftext, #ffname)').first();
        if (!$form.length) return;

        $form.off('submit.rc_att_meta').on('submit.rc_att_meta', function() {
            var currentId = $doc.find('input[name="_id"], #ffid').val() || initialId;
            var currentSubject = $doc.find('#ffsubject').val() || '';
            var currentAtts = getReactionCurrentIds($doc);

            if (currentId) {
                $.ajax({
                    url: rcmail.url('plugin.roundcube_attachments_save_meta'),
                    type: 'POST',
                    data: {
                        response_id: currentId,
                        subject: currentSubject,
                        attachments: JSON.stringify(currentAtts),
                        _token: rcmail.env.request_token
                    }
                });
            }
        });
    }

    function getReactionCurrentIds($doc) {
        var val = $doc.find('#ffattachments').val() || '[]';
        try {
            var parsed = JSON.parse(val);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function setReactionCurrentIds($doc, ids) {
        $doc.find('#ffattachments').val(JSON.stringify(ids)).trigger('change');
    }

    function renderReactionAttachedItems($doc, files, ids) {
        var $list = $doc.find('#rc-reaction-attached-list').empty();
        var fileMap = {};
        files.forEach(function(f) { fileMap[f.id] = f; });

        ids.forEach(function(id) {
            var file = fileMap[id] || { id: id, name: id, size: 0, mimetype: '' };
            addReactionAttachedItem($doc, file);
        });
    }

    function addReactionAttachedItem($doc, file) {
        var $list = $doc.find('#rc-reaction-attached-list');
        var icon = getFileIcon(file.mimetype, file.name);
        var sizeStr = file.size ? ' (' + formatBytes(file.size) + ')' : '';

        var $chip = $('<div class="rc-reaction-att-chip" data-id="' + file.id + '"></div>');
        $chip.html('<span class="rc-chip-icon">' + icon + '</span>'
            + '<span class="rc-chip-name" title="' + file.name + '">' + file.name + '</span>'
            + '<span class="rc-chip-size text-muted">' + sizeStr + '</span>'
            + '<button type="button" class="rc-reaction-att-remove" data-id="' + file.id + '" title="Remove">&times;</button>');

        $list.append($chip);
    }

    // ========================================================
    // 4. Initialization & Event Bindings
    // ========================================================

    rcmail.addEventListener('init', function() {
        initCompose();
        initResponses();
        removeAboutButton();
    });

    $(document).ready(function() {
        initCompose();
        initResponses();
        removeAboutButton();
    });

})(window, window.document, window.jQuery);
