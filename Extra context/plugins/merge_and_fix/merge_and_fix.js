/**
 * Roundcube Merge & Fix Contacts Plugin - Client Controller
 *
 * Provides Google Contacts-style "Merge & fix" interface:
 * - Scans and renders duplicate contact cards
 * - Individual "Merge" and "Dismiss"
 * - Batch "Merge all"
 * - Discovers unsaved frequent contacts with "Add" and "Add all"
 * - Live badge in address book sidebar
 *
 * @author Webdotpulse
 * @license GNU GPLv3+
 */

(function(window, document) {
    'use strict';

    var rcmail = window.rcmail;
    if (!rcmail) {
        return;
    }

    var MergeAndFix = {
        duplicates: [],
        frequent: [],
        activeTab: 'duplicates',
        initialized: false,
        isOpen: false,

        /**
         * Initialize plugin hooks and UI
         */
        init: function() {
            if (rcmail.env.task !== 'addressbook') {
                return;
            }

            var self = this;

            // Register toolbar command
            rcmail.register_command('plugin.merge_and_fix-open', function() {
                self.open();
            }, true);

            // Hook init event
            rcmail.addEventListener('init', function() {
                self.injectSidebarNavigation();
                self.checkBackgroundSummary();

                // If loaded via direct action URL
                if (rcmail.env.action === 'plugin.merge_and_fix') {
                    self.isOpen = true;
                    self.bindStudioEvents();
                    self.scan();
                }
            });
        },

        /**
         * Inject Google Contacts-style sidebar navigation item in addressbook sidebar
         */
        injectSidebarNavigation: function() {
            var self = this;
            var directoryList = document.querySelector('#directorylist') ||
                                document.querySelector('#addressbook-sidebar') ||
                                document.querySelector('#sidebar .folder-list') ||
                                document.querySelector('.addressbook-list');

            if (!directoryList || document.querySelector('.mf-sidebar-item')) {
                return;
            }

            var li = document.createElement('li');
            li.className = 'mf-sidebar-item';
            li.setAttribute('role', 'treeitem');

            var link = document.createElement('a');
            link.href = '#merge-and-fix';
            link.className = 'mf-sidebar-link';
            link.setAttribute('title', rcmail.gettext('merge_and_fix.merge_and_fix') || 'Merge & fix');
            link.innerHTML = '<span class="icon mf-sidebar-icon">' +
                             '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                             '<circle cx="18" cy="18" r="3"></circle>' +
                             '<circle cx="6" cy="6" r="3"></circle>' +
                             '<path d="M6 21V9a9 9 0 0 0 9 9"></path>' +
                             '</svg>' +
                             '</span>' +
                             '<span class="name mf-sidebar-label">' + (rcmail.gettext('merge_and_fix.merge_and_fix') || 'Merge & fix') + '</span>' +
                             '<span id="mf-sidebar-badge" class="badge mf-sidebar-badge" style="display:none;">0</span>';

            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.open();
            });

            li.appendChild(link);
            directoryList.appendChild(li);
        },

        /**
         * Light initial scan to populate badge counter in sidebar
         */
        checkBackgroundSummary: function() {
            var self = this;
            rcmail.http_post('plugin.merge_and_fix-scan', {}, false)
                .then(function(res) {
                    var data = self.parseResponse(res);
                    if (data && data.success) {
                        self.duplicates = data.duplicates || [];
                        self.frequent = data.frequent || [];
                        self.updateBadges();
                    }
                })
                .catch(function() {
                    // Silently ignore background badge scan errors
                });
        },

        /**
         * Open the Merge & Fix Studio in place
         */
        open: function() {
            var self = this;
            this.isOpen = true;

            // Highlight sidebar item
            document.querySelectorAll('#directorylist li, .addressbook-list li').forEach(function(el) {
                el.classList.remove('selected');
            });
            var sidebarItem = document.querySelector('.mf-sidebar-item');
            if (sidebarItem) {
                sidebarItem.classList.add('selected');
            }

            // If layout-content container exists, mount view into content pane
            var contentPane = document.getElementById('layout-content');
            if (contentPane) {
                // Ensure layout shows content pane on mobile/responsive
                if (document.documentElement.dataset) {
                    document.documentElement.dataset.panel = 'content';
                }
                var layoutPanels = ['layout-sidebar', 'layout-list', 'layout-content'];
                layoutPanels.forEach(function(p) {
                    var el = document.getElementById(p);
                    if (el) {
                        el.classList.toggle('selected', p === 'layout-content');
                    }
                });

                // Check if studio already in DOM
                var existingStudio = document.getElementById('merge-and-fix-studio');
                if (!existingStudio) {
                    // Load Studio HTML template from action or inject template structure
                    contentPane.innerHTML = self.getStudioTemplate();
                } else {
                    existingStudio.classList.remove('d-none');
                }

                self.bindStudioEvents();
                self.scan();
            } else {
                // Fallback: Navigate to action URL
                rcmail.goto_url('plugin.merge_and_fix');
            }
        },

        /**
         * Bind UI interactions in the Merge & Fix Studio
         */
        bindStudioEvents: function() {
            var self = this;
            var studio = document.getElementById('merge-and-fix-studio');
            if (!studio || studio.dataset.bound === '1') {
                return;
            }
            studio.dataset.bound = '1';

            // Tab switching
            studio.querySelectorAll('.btn-mf-tab').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var tab = this.dataset.tab;
                    self.switchTab(tab);
                });
            });

            // Rescan button
            var btnRefresh = document.getElementById('mf-btn-refresh');
            if (btnRefresh) {
                btnRefresh.addEventListener('click', function() {
                    self.scan();
                });
            }

            // Reset dismissed button
            var btnResetDismissed = document.getElementById('mf-btn-reset-dismissed');
            if (btnResetDismissed) {
                btnResetDismissed.addEventListener('click', function() {
                    self.resetDismissed();
                });
            }

            // Merge All button
            var btnMergeAll = document.getElementById('mf-btn-merge-all');
            if (btnMergeAll) {
                btnMergeAll.addEventListener('click', function() {
                    self.mergeAll();
                });
            }

            // Add All suggested button
            var btnAddAll = document.getElementById('mf-btn-add-all-suggested');
            if (btnAddAll) {
                btnAddAll.addEventListener('click', function() {
                    self.addAllSuggested();
                });
            }
        },

        /**
         * Switch active tab
         */
        switchTab: function(tab) {
            this.activeTab = tab;

            document.querySelectorAll('.btn-mf-tab').forEach(function(btn) {
                btn.classList.toggle('active', btn.dataset.tab === tab);
            });

            var tabDup = document.getElementById('mf-tab-duplicates');
            var tabFreq = document.getElementById('mf-tab-frequent');

            if (tabDup && tabFreq) {
                if (tab === 'duplicates') {
                    tabDup.classList.remove('d-none');
                    tabDup.classList.add('active');
                    tabFreq.classList.add('d-none');
                    tabFreq.classList.remove('active');
                } else {
                    tabFreq.classList.remove('d-none');
                    tabFreq.classList.add('active');
                    tabDup.classList.add('d-none');
                    tabDup.classList.remove('active');
                }
            }
        },

        /**
         * Perform scan for duplicates and frequent contacts
         */
        scan: function() {
            var self = this;
            this.setLoading(true);

            rcmail.http_post('plugin.merge_and_fix-scan', {}, true)
                .then(function(res) {
                    self.setLoading(false);
                    var data = self.parseResponse(res);
                    if (data && data.success) {
                        self.duplicates = data.duplicates || [];
                        self.frequent = data.frequent || [];
                        self.renderDuplicates();
                        self.renderFrequent();
                        self.updateBadges();
                    } else {
                        self.showAlert('danger', (data && data.message) || 'Failed to scan contacts.');
                    }
                })
                .catch(function(err) {
                    self.setLoading(false);
                    self.showAlert('danger', 'Error connecting to server. Please try again.');
                });
        },

        /**
         * Set loading state
         */
        setLoading: function(loading) {
            var loadDup = document.getElementById('mf-duplicates-loading');
            var loadFreq = document.getElementById('mf-frequent-loading');
            var contDup = document.getElementById('mf-duplicates-container');
            var contFreq = document.getElementById('mf-frequent-container');
            var emptyDup = document.getElementById('mf-duplicates-empty');
            var emptyFreq = document.getElementById('mf-frequent-empty');

            if (loading) {
                if (loadDup) loadDup.classList.remove('d-none');
                if (loadFreq) loadFreq.classList.remove('d-none');
                if (contDup) contDup.classList.add('d-none');
                if (contFreq) contFreq.classList.add('d-none');
                if (emptyDup) emptyDup.classList.add('d-none');
                if (emptyFreq) emptyFreq.classList.add('d-none');
            } else {
                if (loadDup) loadDup.classList.add('d-none');
                if (loadFreq) loadFreq.classList.add('d-none');
            }
        },

        /**
         * Render duplicate cards
         */
        renderDuplicates: function() {
            var self = this;
            var container = document.getElementById('mf-duplicates-container');
            var emptyState = document.getElementById('mf-duplicates-empty');
            var btnMergeAll = document.getElementById('mf-btn-merge-all');
            var mergeAllCount = document.getElementById('mf-merge-all-count');

            if (!container || !emptyState) {
                return;
            }

            container.innerHTML = '';

            if (this.duplicates.length === 0) {
                container.classList.add('d-none');
                emptyState.classList.remove('d-none');
                if (btnMergeAll) {
                    btnMergeAll.disabled = true;
                }
                if (mergeAllCount) {
                    mergeAllCount.textContent = '';
                }
                return;
            }

            emptyState.classList.add('d-none');
            container.classList.remove('d-none');

            if (btnMergeAll) {
                btnMergeAll.disabled = false;
            }
            if (mergeAllCount) {
                mergeAllCount.textContent = '(' + this.duplicates.length + ')';
            }

            this.duplicates.forEach(function(dup, index) {
                var card = self.createDuplicateCard(dup, index);
                container.appendChild(card);
            });
        },

        /**
         * Create duplicate card DOM element
         */
        createDuplicateCard: function(dup, index) {
            var self = this;
            var card = document.createElement('div');
            card.className = 'mf-card mf-duplicate-card';
            card.id = 'mf-dup-card-' + index;

            var primary = dup.primary || {};
            var secondary = dup.secondary || {};
            var preview = dup.preview || {};

            var reasonsHtml = (dup.reasons || []).map(function(r) {
                return '<span class="mf-reason-badge">' + self.escapeHtml(r) + '</span>';
            }).join('');

            var confidenceBadge = '<span class="mf-confidence-badge ' + (dup.confidence >= 95 ? 'high' : 'med') + '">' +
                                  dup.confidence + '% ' + (rcmail.gettext('merge_and_fix.match') || 'Match') +
                                  '</span>';

            card.innerHTML = '' +
                '<div class="mf-card-header">' +
                    '<div class="mf-card-header-left">' +
                        '<span class="mf-card-title">' + (rcmail.gettext('merge_and_fix.merge_suggestion') || 'Duplicate Contact') + '</span>' +
                        '<div class="mf-reasons-wrap">' + reasonsHtml + '</div>' +
                    '</div>' +
                    '<div class="mf-card-header-right">' + confidenceBadge + '</div>' +
                '</div>' +

                '<div class="mf-card-body">' +
                    '<div class="mf-compare-grid">' +
                        '<!-- Contact A -->' +
                        '<div class="mf-contact-card">' +
                            '<div class="mf-contact-top">' +
                                self.renderAvatar(primary.name) +
                                '<div class="mf-contact-details">' +
                                    '<h5 class="mf-contact-name">' + self.escapeHtml(primary.name || 'Unnamed') + '</h5>' +
                                    (primary.organization ? '<p class="mf-contact-org">🏢 ' + self.escapeHtml(primary.organization) + '</p>' : '') +
                                '</div>' +
                            '</div>' +
                            '<div class="mf-contact-fields">' +
                                self.renderContactFields(primary) +
                            '</div>' +
                        '</div>' +

                        '<!-- Merge Arrow -->' +
                        '<div class="mf-merge-arrow">' +
                            '<span class="mf-arrow-icon">➔</span>' +
                        '</div>' +

                        '<!-- Contact B -->' +
                        '<div class="mf-contact-card">' +
                            '<div class="mf-contact-top">' +
                                self.renderAvatar(secondary.name) +
                                '<div class="mf-contact-details">' +
                                    '<h5 class="mf-contact-name">' + self.escapeHtml(secondary.name || 'Unnamed') + '</h5>' +
                                    (secondary.organization ? '<p class="mf-contact-org">🏢 ' + self.escapeHtml(secondary.organization) + '</p>' : '') +
                                '</div>' +
                            '</div>' +
                            '<div class="mf-contact-fields">' +
                                self.renderContactFields(secondary) +
                            '</div>' +
                        '</div>' +
                    '</div>' +

                    '<!-- Merged Result Preview Banner -->' +
                    '<div class="mf-merged-preview-banner">' +
                        '<span class="mf-preview-tag">' + (rcmail.gettext('merge_and_fix.merged_preview') || 'Unified Result') + ':</span> ' +
                        '<strong>' + self.escapeHtml(preview.name) + '</strong> &bull; ' +
                        (preview.emails ? preview.emails.join(', ') : '') +
                        (preview.phones && preview.phones.length ? ' &bull; ' + preview.phones.join(', ') : '') +
                    '</div>' +
                '</div>' +

                '<div class="mf-card-footer">' +
                    '<button type="button" class="btn btn-outline-secondary btn-sm mf-btn-dismiss">' +
                        (rcmail.gettext('merge_and_fix.dismiss') || 'Dismiss') +
                    '</button>' +
                    '<button type="button" class="btn btn-primary btn-sm mf-btn-merge">' +
                        '<span class="mf-icon">⚡</span> ' + (rcmail.gettext('merge_and_fix.merge') || 'Merge') +
                    '</button>' +
                '</div>';

            // Bind Dismiss button
            card.querySelector('.mf-btn-dismiss').addEventListener('click', function() {
                self.dismissDuplicate(dup, card);
            });

            // Bind Merge button
            card.querySelector('.mf-btn-merge').addEventListener('click', function() {
                self.mergePair(dup, card);
            });

            return card;
        },

        /**
         * Render contact fields in comparison card
         */
        renderContactFields: function(contact) {
            var html = '';
            var self = this;

            if (contact.emails && contact.emails.length) {
                contact.emails.forEach(function(em) {
                    html += '<div class="mf-field-row"><span class="mf-field-icon">✉️</span> <span class="mf-field-val">' + self.escapeHtml(em) + '</span></div>';
                });
            }

            if (contact.phones && contact.phones.length) {
                contact.phones.forEach(function(ph) {
                    html += '<div class="mf-field-row"><span class="mf-field-icon">📞</span> <span class="mf-field-val">' + self.escapeHtml(ph) + '</span></div>';
                });
            }

            if (contact.jobtitle) {
                html += '<div class="mf-field-row"><span class="mf-field-icon">💼</span> <span class="mf-field-val">' + self.escapeHtml(contact.jobtitle) + '</span></div>';
            }

            if (!html) {
                html = '<div class="mf-field-empty">No additional details</div>';
            }

            return html;
        },

        /**
         * Render frequent contact suggestion cards
         */
        renderFrequent: function() {
            var self = this;
            var container = document.getElementById('mf-frequent-container');
            var emptyState = document.getElementById('mf-frequent-empty');
            var btnAddAll = document.getElementById('mf-btn-add-all-suggested');
            var addAllCount = document.getElementById('mf-add-all-count');

            if (!container || !emptyState) {
                return;
            }

            container.innerHTML = '';

            if (this.frequent.length === 0) {
                container.classList.add('d-none');
                emptyState.classList.remove('d-none');
                if (btnAddAll) {
                    btnAddAll.disabled = true;
                }
                if (addAllCount) {
                    addAllCount.textContent = '';
                }
                return;
            }

            emptyState.classList.add('d-none');
            container.classList.remove('d-none');

            if (btnAddAll) {
                btnAddAll.disabled = false;
            }
            if (addAllCount) {
                addAllCount.textContent = '(' + this.frequent.length + ')';
            }

            this.frequent.forEach(function(item, index) {
                var card = self.createFrequentCard(item, index);
                container.appendChild(card);
            });
        },

        /**
         * Create frequent contact card DOM element
         */
        createFrequentCard: function(item, index) {
            var self = this;
            var card = document.createElement('div');
            card.className = 'mf-card mf-frequent-card';
            card.id = 'mf-freq-card-' + index;

            var badgeText = item.count > 1 ? item.count + ' ' + (rcmail.gettext('merge_and_fix.emails_exchanged') || 'emails exchanged') : 'Frequent contact';

            card.innerHTML = '' +
                '<div class="mf-freq-body">' +
                    '<div class="mf-freq-avatar-wrap">' +
                        self.renderAvatar(item.name || item.email) +
                    '</div>' +
                    '<div class="mf-freq-info">' +
                        '<div class="mf-freq-title-row">' +
                            '<h4 class="mf-freq-name">' + self.escapeHtml(item.name || item.email) + '</h4>' +
                            '<span class="mf-freq-count-badge">' + self.escapeHtml(badgeText) + '</span>' +
                        '</div>' +
                        '<p class="mf-freq-email">✉️ ' + self.escapeHtml(item.email) + '</p>' +
                        (item.organization ? '<p class="mf-freq-org">🏢 ' + self.escapeHtml(item.organization) + '</p>' : '') +
                    '</div>' +
                    '<div class="mf-freq-actions">' +
                        '<button type="button" class="btn btn-outline-secondary btn-sm mf-btn-dismiss-freq">' +
                            (rcmail.gettext('merge_and_fix.dismiss') || 'Dismiss') +
                        '</button>' +
                        '<button type="button" class="btn btn-primary btn-sm mf-btn-add-freq">' +
                            '<span class="mf-icon">➕</span> ' + (rcmail.gettext('merge_and_fix.add_to_contacts') || 'Add to contacts') +
                        '</button>' +
                    '</div>' +
                '</div>';

            // Bind Dismiss
            card.querySelector('.mf-btn-dismiss-freq').addEventListener('click', function() {
                self.dismissFrequent(item, card);
            });

            // Bind Add
            card.querySelector('.mf-btn-add-freq').addEventListener('click', function() {
                self.addFrequent(item, card);
            });

            return card;
        },

        /**
         * Merge individual pair of contacts
         */
        mergePair: function(dup, cardElement) {
            var self = this;
            var mergeBtn = cardElement.querySelector('.mf-btn-merge');
            if (mergeBtn) {
                mergeBtn.disabled = true;
                mergeBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Merging...';
            }

            var postData = {
                _target_id: dup.target_id,
                _source_id: dup.source_id,
                _source: (dup.primary && dup.primary.source) ? dup.primary.source : ''
            };

            rcmail.http_post('plugin.merge_and_fix-merge', postData, true)
                .then(function(res) {
                    var data = self.parseResponse(res);
                    if (data && data.success) {
                        self.animateCardOut(cardElement, function() {
                            // Remove from duplicates list
                            self.duplicates = self.duplicates.filter(function(d) {
                                return d.pair_key !== dup.pair_key &&
                                       d.target_id !== dup.source_id &&
                                       d.source_id !== dup.source_id;
                            });
                            self.renderDuplicates();
                            self.updateBadges();
                            self.refreshAddressBookList();
                        });
                        self.showToast('success', data.message || 'Contacts merged successfully!');
                    } else {
                        if (mergeBtn) {
                            mergeBtn.disabled = false;
                            mergeBtn.innerHTML = '<span class="mf-icon">⚡</span> ' + (rcmail.gettext('merge_and_fix.merge') || 'Merge');
                        }
                        self.showAlert('danger', (data && data.message) || 'Failed to merge contacts.');
                    }
                })
                .catch(function() {
                    if (mergeBtn) {
                        mergeBtn.disabled = false;
                        mergeBtn.innerHTML = '<span class="mf-icon">⚡</span> ' + (rcmail.gettext('merge_and_fix.merge') || 'Merge');
                    }
                    self.showAlert('danger', 'Error merging contacts.');
                });
        },

        /**
         * Merge all duplicates at once
         */
        mergeAll: function() {
            var self = this;
            var btnMergeAll = document.getElementById('mf-btn-merge-all');
            if (btnMergeAll) {
                btnMergeAll.disabled = true;
                btnMergeAll.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Merging all...';
            }

            rcmail.http_post('plugin.merge_and_fix-merge-all', {}, true)
                .then(function(res) {
                    var data = self.parseResponse(res);
                    if (data && data.success) {
                        self.showToast('success', data.message || 'All duplicate contacts merged!');
                        self.duplicates = [];
                        self.renderDuplicates();
                        self.updateBadges();
                        self.refreshAddressBookList();
                    } else {
                        self.showAlert('danger', (data && data.message) || 'Batch merge encountered an issue.');
                    }
                })
                .catch(function() {
                    self.showAlert('danger', 'Network error during batch merge.');
                });
        },

        /**
         * Dismiss duplicate suggestion
         */
        dismissDuplicate: function(dup, cardElement) {
            var self = this;
            var postData = {
                _type: 'duplicate',
                _id1: dup.target_id,
                _id2: dup.source_id
            };

            self.animateCardOut(cardElement, function() {
                self.duplicates = self.duplicates.filter(function(d) {
                    return d.pair_key !== dup.pair_key;
                });
                self.renderDuplicates();
                self.updateBadges();
            });

            rcmail.http_post('plugin.merge_and_fix-dismiss', postData, false);
        },

        /**
         * Add individual frequent contact
         */
        addFrequent: function(item, cardElement) {
            var self = this;
            var addBtn = cardElement.querySelector('.mf-btn-add-freq');
            if (addBtn) {
                addBtn.disabled = true;
                addBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Adding...';
            }

            var postData = {
                _name: item.name || '',
                _email: item.email || '',
                _organization: item.organization || ''
            };

            rcmail.http_post('plugin.merge_and_fix-add-suggested', postData, true)
                .then(function(res) {
                    var data = self.parseResponse(res);
                    if (data && data.success) {
                        self.animateCardOut(cardElement, function() {
                            self.frequent = self.frequent.filter(function(f) {
                                return f.email !== item.email;
                            });
                            self.renderFrequent();
                            self.updateBadges();
                            self.refreshAddressBookList();
                        });
                        self.showToast('success', data.message || 'Contact added!');
                    } else {
                        if (addBtn) {
                            addBtn.disabled = false;
                            addBtn.innerHTML = '<span class="mf-icon">➕</span> ' + (rcmail.gettext('merge_and_fix.add_to_contacts') || 'Add to contacts');
                        }
                        self.showAlert('danger', (data && data.message) || 'Failed to add contact.');
                    }
                })
                .catch(function() {
                    if (addBtn) {
                        addBtn.disabled = false;
                        addBtn.innerHTML = '<span class="mf-icon">➕</span> ' + (rcmail.gettext('merge_and_fix.add_to_contacts') || 'Add to contacts');
                    }
                    self.showAlert('danger', 'Network error adding contact.');
                });
        },

        /**
         * Add all suggested contacts at once
         */
        addAllSuggested: function() {
            var self = this;
            var btnAddAll = document.getElementById('mf-btn-add-all-suggested');
            if (btnAddAll) {
                btnAddAll.disabled = true;
                btnAddAll.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Adding all...';
            }

            var postData = {
                _contacts: JSON.stringify(self.frequent)
            };

            rcmail.http_post('plugin.merge_and_fix-add-all-suggested', postData, true)
                .then(function(res) {
                    var data = self.parseResponse(res);
                    if (data && data.success) {
                        self.showToast('success', data.message || 'All suggested contacts added!');
                        self.frequent = [];
                        self.renderFrequent();
                        self.updateBadges();
                        self.refreshAddressBookList();
                    } else {
                        self.showAlert('danger', (data && data.message) || 'Failed to add all contacts.');
                    }
                })
                .catch(function() {
                    self.showAlert('danger', 'Network error adding all contacts.');
                });
        },

        /**
         * Dismiss frequent contact recommendation
         */
        dismissFrequent: function(item, cardElement) {
            var self = this;
            var postData = {
                _type: 'frequent',
                _email: item.email
            };

            self.animateCardOut(cardElement, function() {
                self.frequent = self.frequent.filter(function(f) {
                    return f.email !== item.email;
                });
                self.renderFrequent();
                self.updateBadges();
            });

            rcmail.http_post('plugin.merge_and_fix-dismiss', postData, false);
        },

        /**
         * Reset all dismissed records
         */
        resetDismissed: function() {
            var self = this;
            if (!confirm(rcmail.gettext('merge_and_fix.confirm_reset_dismissed') || 'Reset all dismissed suggestions and re-scan?')) {
                return;
            }

            rcmail.http_post('plugin.merge_and_fix-reset-dismissed', {}, true)
                .then(function(res) {
                    var data = self.parseResponse(res);
                    if (data && data.success) {
                        self.showToast('info', data.message || 'Dismissals reset. Re-scanning...');
                        self.scan();
                    }
                })
                .catch(function() {
                    self.showAlert('danger', 'Failed to reset dismissed items.');
                });
        },

        /**
         * Animate card removal smoothly
         */
        animateCardOut: function(element, callback) {
            element.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            element.style.opacity = '0';
            element.style.transform = 'translateY(-12px) scale(0.98)';
            element.style.maxHeight = element.offsetHeight + 'px';

            setTimeout(function() {
                element.style.maxHeight = '0';
                element.style.marginTop = '0';
                element.style.marginBottom = '0';
                element.style.paddingTop = '0';
                element.style.paddingBottom = '0';
                element.style.overflow = 'hidden';
            }, 100);

            setTimeout(function() {
                if (element.parentNode) {
                    element.parentNode.removeChild(element);
                }
                if (typeof callback === 'function') {
                    callback();
                }
            }, 320);
        },

        /**
         * Update tab badges and sidebar notification badge
         */
        updateBadges: function() {
            var dupCount = this.duplicates.length;
            var freqCount = this.frequent.length;
            var total = dupCount + freqCount;

            // Update Tab Badges
            var bDup = document.getElementById('mf-badge-duplicates-count');
            var bFreq = document.getElementById('mf-badge-frequent-count');
            if (bDup) bDup.textContent = dupCount;
            if (bFreq) bFreq.textContent = freqCount;

            // Update Sidebar Badge
            var sideBadge = document.getElementById('mf-sidebar-badge');
            if (sideBadge) {
                sideBadge.textContent = total;
                sideBadge.style.display = (total > 0) ? 'inline-block' : 'none';
            }
        },

        /**
         * Refresh address book contact list table in background
         */
        refreshAddressBookList: function() {
            if (typeof rcmail.command === 'function') {
                try {
                    rcmail.command('list');
                } catch (e) {}
            }
        },

        /**
         * Render avatar badge with initials
         */
        renderAvatar: function(name) {
            name = (name || '?').trim();
            var initial = name.charAt(0).toUpperCase();

            // Hash name to color
            var hash = 0;
            for (var i = 0; i < name.length; i++) {
                hash = name.charCodeAt(i) + ((hash << 5) - hash);
            }
            var colors = ['#1a73e8', '#e52592', '#129eaf', '#e8710a', '#1e8e3e', '#9334e6', '#d93025'];
            var color = colors[Math.abs(hash) % colors.length];

            return '<div class="mf-avatar" style="background-color: ' + color + ';">' + this.escapeHtml(initial) + '</div>';
        },

        /**
         * Show non-intrusive toast message
         */
        showToast: function(type, text) {
            if (typeof rcmail.display_message === 'function') {
                var rcType = (type === 'danger') ? 'error' : (type === 'success' ? 'confirmation' : 'notice');
                rcmail.display_message(text, rcType);
            } else {
                this.showAlert(type, text);
            }
        },

        /**
         * Show banner alert in Studio
         */
        showAlert: function(type, text) {
            var container = document.getElementById('mf-alert-container');
            if (!container) return;

            var alert = document.createElement('div');
            alert.className = 'alert alert-' + type + ' alert-dismissible fade show';
            alert.innerHTML = this.escapeHtml(text) +
                              '<button type="button" class="close" data-dismiss="alert">&times;</button>';

            container.appendChild(alert);
            setTimeout(function() {
                alert.classList.remove('show');
                setTimeout(function() { alert.remove(); }, 200);
            }, 4000);
        },

        /**
         * Escape HTML
         */
        escapeHtml: function(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        },

        /**
         * Parse JSON response safely
         */
        parseResponse: function(res) {
            if (typeof res === 'object' && res !== null) {
                return res;
            }
            try {
                return JSON.parse(res);
            } catch (e) {
                return null;
            }
        },

        /**
         * Get dynamic Studio template HTML
         */
        getStudioTemplate: function() {
            var title = rcmail.gettext('merge_and_fix.merge_and_fix') || 'Merge & fix';
            var subtitle = rcmail.gettext('merge_and_fix.merge_and_fix_subtitle') || 'Find duplicate entries, combine profiles, and discover frequent contacts.';
            var rescanText = rcmail.gettext('merge_and_fix.rescan') || 'Rescan';
            var resetDismissedText = rcmail.gettext('merge_and_fix.reset_dismissed') || 'Reset Dismissed';
            var duplicatesTab = rcmail.gettext('merge_and_fix.duplicates_tab') || 'Duplicates';
            var frequentTab = rcmail.gettext('merge_and_fix.frequent_tab') || 'Frequent contacts';
            var mergeDupHeading = rcmail.gettext('merge_and_fix.merge_duplicates_heading') || 'Merge duplicate contacts';
            var mergeDupDesc = rcmail.gettext('merge_and_fix.merge_duplicates_desc') || 'The following cards likely belong to the same person. Review and combine them.';
            var mergeAllText = rcmail.gettext('merge_and_fix.merge_all') || 'Merge all';
            var scanningDuplicates = rcmail.gettext('merge_and_fix.scanning_duplicates') || 'Scanning for duplicate contacts...';
            var noDuplicatesTitle = rcmail.gettext('merge_and_fix.no_duplicates_title') || 'No duplicates found';
            var noDuplicatesDesc = rcmail.gettext('merge_and_fix.no_duplicates_desc') || 'Your contact list is clean and up to date.';
            var freqHeading = rcmail.gettext('merge_and_fix.frequent_contacts_heading') || 'Add frequent contacts';
            var freqDesc = rcmail.gettext('merge_and_fix.frequent_contacts_desc') || 'People you frequently communicate with who aren\'t in your address book.';
            var addAllText = rcmail.gettext('merge_and_fix.add_all') || 'Add all';
            var scanningFreq = rcmail.gettext('merge_and_fix.scanning_frequent') || 'Analyzing recent communication history...';
            var noFreqTitle = rcmail.gettext('merge_and_fix.no_frequent_title') || 'No unsaved frequent contacts';
            var noFreqDesc = rcmail.gettext('merge_and_fix.no_frequent_desc') || 'All of your frequent email correspondents are already saved in your contacts.';

            return '' +
                '<div id="merge-and-fix-studio" class="merge-and-fix-wrapper content formcontent scroller boxcontent uibox">' +
                    '<div class="mf-header-card card mb-4">' +
                        '<div class="mf-header-content">' +
                            '<div class="mf-brand">' +
                                '<div class="mf-brand-icon">' +
                                    '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                        '<circle cx="18" cy="18" r="3"></circle>' +
                                        '<circle cx="6" cy="6" r="3"></circle>' +
                                        '<path d="M6 21V9a9 9 0 0 0 9 9"></path>' +
                                    '</svg>' +
                                '</div>' +
                                '<div>' +
                                    '<h2 class="mf-title">' + title + '</h2>' +
                                    '<p class="mf-subtitle">' + subtitle + '</p>' +
                                '</div>' +
                            '</div>' +
                            '<div class="mf-header-actions">' +
                                '<button type="button" id="mf-btn-refresh" class="btn btn-secondary btn-sm" title="' + rescanText + '">' +
                                    '<span class="mf-icon">🔄</span> ' + rescanText +
                                '</button>' +
                                '<button type="button" id="mf-btn-reset-dismissed" class="btn btn-outline-secondary btn-sm" title="' + resetDismissedText + '">' +
                                    resetDismissedText +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                        '<div class="mf-tabs-bar">' +
                            '<div class="mf-tabs">' +
                                '<button type="button" class="btn btn-mf-tab active" data-tab="duplicates">' +
                                    '<span class="mf-tab-icon">👥</span>' +
                                    '<span class="mf-tab-label">' + duplicatesTab + '</span>' +
                                    '<span id="mf-badge-duplicates-count" class="mf-badge primary">0</span>' +
                                '</button>' +
                                '<button type="button" class="btn btn-mf-tab" data-tab="frequent">' +
                                    '<span class="mf-tab-icon">💡</span>' +
                                    '<span class="mf-tab-label">' + frequentTab + '</span>' +
                                    '<span id="mf-badge-frequent-count" class="mf-badge accent">0</span>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div id="mf-alert-container"></div>' +
                    '<div id="mf-tab-duplicates" class="mf-tab-pane active">' +
                        '<div class="mf-section-header">' +
                            '<div class="mf-section-info">' +
                                '<h3 class="mf-section-title">' + mergeDupHeading + '</h3>' +
                                '<p class="mf-section-desc">' + mergeDupDesc + '</p>' +
                            '</div>' +
                            '<div class="mf-section-actions">' +
                                '<button type="button" id="mf-btn-merge-all" class="btn btn-primary btn-sm mf-btn-action" disabled>' +
                                    '<span class="mf-icon">⚡</span> ' + mergeAllText + ' ' +
                                    '<span id="mf-merge-all-count" class="mf-btn-badge"></span>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                        '<div id="mf-duplicates-loading" class="mf-loading-state">' +
                            '<div class="mf-spinner"></div>' +
                            '<p>' + scanningDuplicates + '</p>' +
                        '</div>' +
                        '<div id="mf-duplicates-empty" class="mf-empty-state d-none">' +
                            '<div class="mf-empty-graphic"><div class="mf-check-circle">✓</div></div>' +
                            '<h4>' + noDuplicatesTitle + '</h4>' +
                            '<p>' + noDuplicatesDesc + '</p>' +
                        '</div>' +
                        '<div id="mf-duplicates-container" class="mf-cards-grid d-none"></div>' +
                    '</div>' +
                    '<div id="mf-tab-frequent" class="mf-tab-pane d-none">' +
                        '<div class="mf-section-header">' +
                            '<div class="mf-section-info">' +
                                '<h3 class="mf-section-title">' + freqHeading + '</h3>' +
                                '<p class="mf-section-desc">' + freqDesc + '</p>' +
                            '</div>' +
                            '<div class="mf-section-actions">' +
                                '<button type="button" id="mf-btn-add-all-suggested" class="btn btn-primary btn-sm mf-btn-action" disabled>' +
                                    '<span class="mf-icon">➕</span> ' + addAllText + ' ' +
                                    '<span id="mf-add-all-count" class="mf-btn-badge"></span>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                        '<div id="mf-frequent-loading" class="mf-loading-state">' +
                            '<div class="mf-spinner"></div>' +
                            '<p>' + scanningFreq + '</p>' +
                        '</div>' +
                        '<div id="mf-frequent-empty" class="mf-empty-state d-none">' +
                            '<div class="mf-empty-graphic"><div class="mf-star-circle">★</div></div>' +
                            '<h4>' + noFreqTitle + '</h4>' +
                            '<p>' + noFreqDesc + '</p>' +
                        '</div>' +
                        '<div id="mf-frequent-container" class="mf-cards-grid d-none"></div>' +
                    '</div>' +
                '</div>';
        }
    };

    window.MergeAndFix = MergeAndFix;
    MergeAndFix.init();

})(window, document);
