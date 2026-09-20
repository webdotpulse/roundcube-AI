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

    function safeSetBusy(busy, msg, lock) {
        if (window.rcmail && typeof window.rcmail.set_busy === 'function') {
            return window.rcmail.set_busy(busy, msg, lock);
        }
        return null;
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

        // 1. Clean up any misplaced or duplicated toolbars / buttons in compose area
        $('#compose-attachments .rc-server-att-toolbar, #composeform .rc-server-att-toolbar').remove();
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

        var lock = safeSetBusy(true, 'uploading');
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

        var modalHtml = '<div id="rc-server-att-overlay" class="rc-server-att-overlay" style="display:none;"></div>'
            + '<div id="rc-server-att-modal" class="rc-server-att-modal" style="display:none;" role="dialog" aria-modal="true">'
            + '  <div class="rc-server-att-modal-header">'
            + '    <h3 class="rc-server-att-modal-title">' + (rcmail.gettext('server_attachments', 'roundcube_attachments') || 'Server Attachments') + '</h3>'
            + '    <button type="button" class="rc-server-att-close" id="rc-server-att-close-btn">&times;</button>'
            + '  </div>'
            + '  <div class="rc-server-att-modal-body">'
            + '    <div class="rc-server-att-toolbar">'
            + '      <div class="rc-server-att-search-box">'
            + '        <input type="text" id="rc-server-att-search" placeholder="' + (rcmail.gettext('search_placeholder', 'roundcube_attachments') || 'Search files...') + '" class="rc-server-att-search-input">'
            + '      </div>'
            + '      <div class="rc-server-att-actions">'
            + '        <input type="file" id="rc-server-att-file-input" style="display:none;" multiple>'
            + '        <button type="button" class="btn btn-secondary rc-btn-upload" id="rc-server-att-upload-btn">'
            + '          <span class="icon">⬆</span> ' + (rcmail.gettext('upload_file', 'roundcube_attachments') || 'Upload File')
            + '        </button>'
            + '      </div>'
            + '    </div>'
            + '    <div id="rc-server-att-list-container" class="rc-server-att-list-container">'
            + '      <div id="rc-server-att-loading" class="rc-server-att-loading" style="display:none;">'
            + '        <span>' + (rcmail.gettext('loading', 'roundcube_attachments') || 'Loading...') + '</span>'
            + '      </div>'
            + '      <div id="rc-server-att-empty" class="rc-server-att-empty" style="display:none;">'
            + '        <p>' + (rcmail.gettext('no_attachments_found', 'roundcube_attachments') || 'No server attachments found') + '</p>'
            + '      </div>'
            + '      <ul id="rc-server-att-list" class="rc-server-att-list"></ul>'
            + '    </div>'
            + '  </div>'
            + '  <div class="rc-server-att-modal-footer">'
            + '    <button type="button" class="btn btn-secondary" id="rc-server-att-cancel-btn">' + (rcmail.gettext('cancel', 'roundcube_attachments') || 'Cancel') + '</button>'
            + '    <button type="button" class="btn btn-primary" id="rc-server-att-attach-btn" disabled>' + (rcmail.gettext('attach_selected', 'roundcube_attachments') || 'Attach Selected') + '</button>'
            + '  </div>'
            + '</div>';

        $('body').append(modalHtml);

        // Bind modal event listeners
        $('#rc-server-att-close-btn, #rc-server-att-cancel-btn, #rc-server-att-overlay').on('click', function() {
            closeServerAttachmentsModal();
        });

        // Search filter in modal
        $('#rc-server-att-search').on('input', function() {
            var q = $(this).val().toLowerCase().trim();
            $('#rc-server-att-list li').each(function() {
                var name = $(this).data('name') || '';
                var desc = $(this).data('description') || '';
                if (!q || name.indexOf(q) !== -1 || desc.indexOf(q) !== -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Upload button triggers hidden file input
        $('#rc-server-att-upload-btn').on('click', function() {
            $('#rc-server-att-file-input').val('').click();
        });

        $('#rc-server-att-file-input').on('change', function() {
            if (this.files && this.files.length) {
                uploadServerFiles(this.files);
            }
        });

        // Attach selected button
        $('#rc-server-att-attach-btn').on('click', function() {
            var selectedIds = [];
            var selectedFiles = [];
            $('#rc-server-att-list input[type="checkbox"]:checked').each(function() {
                var fid = $(this).val();
                selectedIds.push(fid);
                selectedFiles.push($(this).closest('li').data('file'));
            });

            if (!selectedIds.length) {
                return;
            }

            var context = $('#rc-server-att-modal').data('context');
            var onSelect = $('#rc-server-att-modal').data('onSelect');

            if (typeof onSelect === 'function') {
                onSelect(selectedIds, selectedFiles);
            } else if (context === 'compose') {
                attachFilesToCompose(selectedIds);
            }

            closeServerAttachmentsModal();
        });
    }

    function loadServerAttachmentsList() {
        $('#rc-server-att-loading').show();
        $('#rc-server-att-empty').hide();
        $('#rc-server-att-list').empty();

        $.ajax({
            url: rcmail.url('plugin.roundcube_attachments_list'),
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                $('#rc-server-att-loading').hide();
                if (res && res.status === 'success' && res.files) {
                    renderServerAttachmentsList(res.files);
                } else {
                    $('#rc-server-att-empty').show();
                }
            },
            error: function() {
                $('#rc-server-att-loading').hide();
                $('#rc-server-att-empty').show();
            }
        });
    }

    function renderServerAttachmentsList(files) {
        var $list = $('#rc-server-att-list');
        $list.empty();

        if (!files || !files.length) {
            $('#rc-server-att-empty').show();
            return;
        }

        $('#rc-server-att-empty').hide();

        files.forEach(function(file) {
            var id = file.id;
            var name = file.name;
            var desc = file.description || '';
            var size = formatBytes(file.size);
            var date = file.created ? new Date(file.created * 1000).toLocaleDateString() : '';
            var icon = getFileIcon(file.mimetype, name);

            var descHtml = desc ? '<div class="rc-att-item-desc text-muted">' + $('<div>').text(desc).html() + '</div>' : '';

            var $li = $('<li class="rc-server-att-item">'
                + '<label class="rc-att-item-label">'
                + '  <input type="checkbox" value="' + id + '" class="rc-att-item-checkbox">'
                + '  <span class="rc-att-item-icon">' + icon + '</span>'
                + '  <div class="rc-att-item-details">'
                + '    <span class="rc-att-item-name font-weight-bold">' + $('<div>').text(name).html() + '</span>'
                +      descHtml
                + '    <span class="rc-att-item-meta text-muted">' + size + (date ? ' &bull; ' + date : '') + '</span>'
                + '  </div>'
                + '</label>'
                + '<div class="rc-att-item-actions">'
                + '  <a href="' + rcmail.url('plugin.roundcube_attachments_download', { _id: id }) + '" target="_blank" class="rc-btn-download" title="' + (rcmail.gettext('download', 'roundcube_attachments') || 'Download') + '">⬇</a>'
                + '  <button type="button" class="rc-btn-delete-item" data-id="' + id + '" title="' + (rcmail.gettext('delete_attachment', 'roundcube_attachments') || 'Delete') + '">🗑</button>'
                + '</div>'
                + '</li>');

            $li.data('name', (name || '').toLowerCase());
            $li.data('description', (desc || '').toLowerCase());
            $li.data('file', file);
            $list.append($li);
        });

        // Checkbox change updates attach button state
        $list.find('input[type="checkbox"]').on('change', function() {
            var anyChecked = $list.find('input[type="checkbox"]:checked').length > 0;
            $('#rc-server-att-attach-btn').prop('disabled', !anyChecked);
        });

        // Delete button inside modal list
        $list.find('.rc-btn-delete-item').on('click', function(e) {
            e.stopPropagation();
            var id = $(this).data('id');
            var confirmMsg = rcmail.gettext('confirm_delete', 'roundcube_attachments') || 'Are you sure you want to delete this attachment from the server?';
            if (window.confirm(confirmMsg)) {
                deleteServerAttachment(id);
            }
        });
    }

    function uploadServerFiles(files, onUploadedCallback) {
        if (!files || !files.length) {
            return;
        }

        var formData = new FormData();
        for (var i = 0; i < files.length; i++) {
            formData.append('_attachments[]', files[i]);
        }

        var lock = safeSetBusy(true, 'uploading');
        $.ajax({
            url: rcmail.url('plugin.roundcube_attachments_upload'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                safeSetBusy(false, null, lock);
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
                safeSetBusy(false, null, lock);
                rcmail.display_message('Upload error', 'error');
            }
        });
    }

    function deleteServerAttachment(id) {
        var lock = safeSetBusy(true, 'loading');
        $.ajax({
            url: rcmail.url('plugin.roundcube_attachments_delete'),
            type: 'POST',
            data: { _id: id },
            dataType: 'json',
            success: function(res) {
                safeSetBusy(false, null, lock);
                if (res && res.status === 'success') {
                    loadServerAttachmentsList();
                } else {
                    rcmail.display_message(res.message || 'Error deleting', 'error');
                }
            },
            error: function() {
                safeSetBusy(false, null, lock);
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
        var descHtml = file.description ? ' • <span class="rc-chip-desc text-muted font-italic" title="' + $('<div>').text(file.description).html() + '">' + $('<div>').text(file.description).html() + '</span>' : '';

        var $chip = $('<div class="rc-reaction-att-chip" data-id="' + file.id + '"></div>');
        $chip.html('<span class="rc-chip-icon">' + icon + '</span>'
            + '<span class="rc-chip-name" title="' + file.name + '">' + file.name + '</span>'
            + descHtml
            + '<span class="rc-chip-size text-muted">' + sizeStr + '</span>'
            + '<button type="button" class="rc-reaction-att-remove" data-id="' + file.id + '" title="Remove">&times;</button>');

        $list.append($chip);
    }

    // ========================================================
    // 3.5. Native Settings Page Controller (Serverbijlagen)
    // ========================================================

    function initSettingsPage(targetDoc) {
        var doc = targetDoc || document;
        var $doc = $(doc);
        var $container = $doc.find('#rc-server-att-settings');

        if (!$container.length || $container.data('rc-settings-inited')) {
            return;
        }
        $container.data('rc-settings-inited', true);

        // 1. Live Search & Filter
        var $search = $container.find('#rc-settings-search');
        var $clearBtn = $container.find('#rc-settings-search-clear');
        var $table = $container.find('#rc-server-att-table');
        var $tbody = $container.find('#rc-server-att-tbody');
        var $empty = $container.find('#rc-server-att-empty');

        function filterRows(e) {
            var inputVal = (e && e.target && e.target.value !== undefined)
                ? e.target.value
                : ($container.find('#rc-settings-search').val() || '');
            var q = inputVal.toLowerCase().trim();
            $clearBtn.toggle(q.length > 0);

            var visibleCount = 0;
            $tbody.find('tr.rc-server-att-row').each(function() {
                var $row = $(this);
                var name = ($row.attr('data-name') || '').toLowerCase();
                var desc = ($row.attr('data-description') || '').toLowerCase();
                if (!q || name.indexOf(q) !== -1 || desc.indexOf(q) !== -1) {
                    $row.show();
                    visibleCount++;
                } else {
                    $row.hide();
                }
            });

            if (visibleCount === 0) {
                $table.hide();
                $empty.show();
            } else {
                $table.show();
                $empty.hide();
            }
        }

        $container.on('input keyup change', '#rc-settings-search', filterRows);
        $search.on('input keyup change', filterRows);
        $clearBtn.on('click', function() {
            $search.val('').trigger('input').focus();
        });

        // 2. Toggle Upload Dropzone Panel
        var $dropzonePanel = $container.find('#rc-server-att-dropzone-panel');
        $container.on('click', '#rc-btn-toggle-upload, .rc-btn-empty-upload', function() {
            $dropzonePanel.toggle();
            if ($dropzonePanel.is(':visible')) {
                $dropzonePanel.find('#rc-settings-file-desc').focus();
            }
        });

        // 3. Dropzone & File Input Uploading
        var $fileInput = $container.find('#rc-settings-file-input');
        var $dropzone = $container.find('#rc-server-att-dropzone');
        var $progressBar = $container.find('#rc-settings-upload-progress');
        var $progressInner = $progressBar.find('.progress-bar');
        var $uploadStatus = $container.find('#rc-settings-upload-status');

        $container.on('click', '#rc-settings-browse-btn', function() {
            $fileInput.trigger('click');
        });

        $dropzone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $dropzone.addClass('rc-dragover border-primary');
        });

        $dropzone.on('dragleave dragend drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $dropzone.removeClass('rc-dragover border-primary');
        });

        $dropzone.on('drop', function(e) {
            var dt = e.originalEvent && e.originalEvent.dataTransfer;
            if (dt && dt.files && dt.files.length) {
                handleSettingsUpload(dt.files);
            }
        });

        $fileInput.on('change', function() {
            if (this.files && this.files.length) {
                handleSettingsUpload(this.files);
            }
        });

        function handleSettingsUpload(files) {
            if (!files || !files.length) return;

            var desc = ($container.find('#rc-settings-file-desc').val() || '').trim();
            var formData = new FormData();
            for (var i = 0; i < files.length; i++) {
                formData.append('_attachments[]', files[i]);
            }
            if (desc) {
                formData.append('description', desc);
            }
            formData.append('_token', rcmail.env.request_token);

            $progressBar.show();
            $progressInner.css('width', '0%').attr('aria-valuenow', 0);
            $uploadStatus.text('Uploading ' + files.length + ' file(s)...');

            $.ajax({
                url: './?_task=settings&_action=plugin.roundcube_attachments_upload',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            var pct = Math.round((evt.loaded / evt.total) * 100);
                            $progressInner.css('width', pct + '%').attr('aria-valuenow', pct);
                        }
                    }, false);
                    return xhr;
                },
                success: function(res) {
                    $progressBar.hide();
                    $uploadStatus.empty();
                    $fileInput.val('');
                    $container.find('#rc-settings-file-desc').val('');

                    if (res && res.status === 'success') {
                        rcmail.display_message(res.message || 'File(s) uploaded successfully', 'confirmation');
                        if (res.files && res.files.length) {
                            res.files.forEach(function(f) {
                                if (f.html_row) {
                                    var $newRow = $(f.html_row).hide();
                                    $tbody.prepend($newRow);
                                    $newRow.fadeIn(400);
                                }
                            });
                            $table.show();
                            $empty.hide();
                            updateSettingsStats();
                        }
                    } else {
                        rcmail.display_message((res && res.message) || 'Upload failed', 'error');
                    }
                },
                error: function() {
                    $progressBar.hide();
                    $uploadStatus.empty();
                    $fileInput.val('');
                    rcmail.display_message('Upload failed. Check server connection.', 'error');
                }
            });
        }

        // 4. Inline Description Editing
        $container.on('click', '.rc-desc-view, .rc-btn-edit-desc', function(e) {
            e.stopPropagation();
            var $row = $(this).closest('tr');
            var $view = $row.find('.rc-desc-view');
            var $edit = $row.find('.rc-desc-edit');
            var $input = $row.find('.rc-desc-input');

            $view.hide();
            $edit.show();
            $input.focus().select();
        });

        $container.on('click', '.rc-btn-cancel-desc', function(e) {
            e.stopPropagation();
            var $row = $(this).closest('tr');
            var $view = $row.find('.rc-desc-view');
            var $edit = $row.find('.rc-desc-edit');
            var currentText = $row.attr('data-description') || '';

            $row.find('.rc-desc-input').val(currentText);
            $edit.hide();
            $view.show();
        });

        $container.on('keydown', '.rc-desc-input', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).closest('tr').find('.rc-btn-save-desc').trigger('click');
            } else if (e.key === 'Escape') {
                e.preventDefault();
                $(this).closest('tr').find('.rc-btn-cancel-desc').trigger('click');
            }
        });

        $container.on('click', '.rc-btn-save-desc', function(e) {
            e.stopPropagation();
            var $btn = $(this);
            var $row = $btn.closest('tr');
            var id = $row.attr('data-id');
            var $input = $row.find('.rc-desc-input');
            var newDesc = ($input.val() || '').trim();
            var $view = $row.find('.rc-desc-view');
            var $edit = $row.find('.rc-desc-edit');
            var $textSpan = $row.find('.rc-desc-text');

            $btn.prop('disabled', true).text('…');

            $.ajax({
                url: './?_task=settings&_action=plugin.roundcube_attachments_update_file',
                type: 'POST',
                data: {
                    _id: id,
                    description: newDesc,
                    _token: rcmail.env.request_token
                },
                dataType: 'json',
                success: function(res) {
                    $btn.prop('disabled', false).text('✓');
                    if (res && res.status === 'success') {
                        $row.attr('data-description', newDesc.toLowerCase());
                        if (newDesc) {
                            $textSpan.text(newDesc).removeClass('text-muted font-italic');
                        } else {
                            var noDescText = rcmail.gettext('no_description', 'roundcube_attachments') || 'No description';
                            $textSpan.html('<span class="text-muted font-italic">' + $('<div>').text(noDescText).html() + '</span>');
                        }
                        $edit.hide();
                        $view.show();
                        rcmail.display_message(res.message || 'Description updated', 'confirmation');
                    } else {
                        rcmail.display_message((res && res.message) || 'Failed to update description', 'error');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).text('✓');
                    rcmail.display_message('Server error while saving description', 'error');
                }
            });
        });

        // 5. Delete File
        $container.on('click', '.rc-btn-delete', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $btn = $(this);
            var $row = $btn.closest('tr');
            var id = $row.attr('data-id');
            var name = $row.find('.rc-file-link').text() || 'this file';
            var confirmMsg = rcmail.gettext('delete_confirm', 'roundcube_attachments') || ('Are you sure you want to delete ' + name + '?');

            if (!window.confirm(confirmMsg)) {
                return;
            }

            $btn.prop('disabled', true);
            $.ajax({
                url: './?_task=settings&_action=plugin.roundcube_attachments_delete',
                type: 'POST',
                data: {
                    _id: id,
                    _token: rcmail.env.request_token
                },
                dataType: 'json',
                success: function(res) {
                    if (res && res.status === 'success') {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                            updateSettingsStats();
                            if ($tbody.find('tr.rc-server-att-row').length === 0) {
                                $table.hide();
                                $empty.show();
                            }
                        });
                        rcmail.display_message(res.message || 'File deleted', 'confirmation');
                    } else {
                        $btn.prop('disabled', false);
                        rcmail.display_message((res && res.message) || 'Failed to delete file', 'error');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false);
                    rcmail.display_message('Error deleting file', 'error');
                }
            });
        });

        // 6. Update Stats Helper
        function updateSettingsStats() {
            var $rows = $tbody.find('tr.rc-server-att-row');
            var totalCount = $rows.length;
            var totalBytes = 0;

            $rows.each(function() {
                var sz = parseInt($(this).attr('data-size'), 10) || 0;
                totalBytes += sz;
            });

            $container.find('#rc-stat-count .stat-number').text(totalCount);
            $container.find('#rc-stat-size .stat-number').text(formatBytes(totalBytes));
        }
    }

    function checkAndInitSettings() {
        initSettingsPage(document);
        var iframes = document.querySelectorAll('iframe#preferences-frame, iframe[name="preferences-frame"], iframe');
        for (var i = 0; i < iframes.length; i++) {
            try {
                var fdoc = iframes[i].contentDocument || iframes[i].contentWindow.document;
                if (fdoc) {
                    initSettingsPage(fdoc);
                }
            } catch (e) {}
        }
    }

    // ========================================================
    // 4. Initialization & Event Bindings
    // ========================================================

    rcmail.addEventListener('init', function() {
        initCompose();
        initResponses();
        checkAndInitSettings();
        removeAboutButton();
    });

    $(document).ready(function() {
        initCompose();
        initResponses();
        checkAndInitSettings();
        removeAboutButton();
    });

})(window, window.document, window.jQuery);

