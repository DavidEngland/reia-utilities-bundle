jQuery(document).ready(function($) {
    
    // Initialize autoload stats on page load
    loadAutoloadStats();
    
    // Handle utility button clicks
    $('.utility-btn').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var utility = button.data('utility');
        var action = button.data('action') || 'run';
        var card = button.closest('.utility-card');
        
        if (button.prop('disabled')) {
            return;
        }
        
        runUtility(utility, card, button, action);
    });
    
    function runUtility(utility, card, button, action = 'run') {
        var isDryRun = action === 'preview';
        var actionName = isDryRun ? 'Previewing' : 'Running';
        
        // Show progress
        showGlobalProgress(actionName + ' ' + utility.replace('_', ' ') + '...');
        
        // Disable button and show loading
        var originalText = button.html();
        button.prop('disabled', true).html('<span class="loading-spinner"></span>Processing...');
        card.addClass('processing');
        
        // Collect options for this utility
        var options = [];
        card.find('input[type="checkbox"]:checked').each(function() {
            options.push($(this).val());
        });
        
        // Prepare AJAX data
        var ajaxData = {
            action: 'reia_' + utility,
            nonce: reia_utils.nonce,
            dry_run: isDryRun ? 'true' : 'false',
            action_type: action
        };
        
        // Add utility-specific options
        switch (utility) {
            case 'db_optimizer':
                ajaxData.db_optimize_options = options;
                break;
            case 'cache_cleaner':
                ajaxData.clean_options = options;
                break;
            case 'file_cleaner':
                ajaxData.clean_options = options;
                break;
            case 'autoload_fixer':
                ajaxData.fix_options = options;
                break;
            default:
                ajaxData[utility.replace('reia_', '') + '_options'] = options;
        }
        
        // AJAX request
        $.ajax({
            url: reia_utils.ajax_url,
            type: 'POST',
            data: ajaxData,
            success: function(response) {
                hideGlobalProgress();
                button.prop('disabled', false).html(originalText);
                card.removeClass('processing');
                
                if (response.success) {
                    if (isDryRun) {
                        showPreviewResults(card, response.data);
                    } else {
                        showUtilityResults(card, response.data, 'success');
                        updateResultsSummary(utility, response.data);
                    }
                } else {
                    showUtilityResults(card, {error: response.data || 'Unknown error'}, 'error');
                }
            },
            error: function(xhr, status, error) {
                hideGlobalProgress();
                showUtilityResults(card, {error: 'Network error: ' + error}, 'error');
            },
            complete: function() {
                // Re-enable button
                button.prop('disabled', false).html(button.data('original-text') || getButtonText(utility));
                card.removeClass('processing');
                
                // Reload autoload stats if it was the autoload fixer
                if (utility === 'autoload_fixer') {
                    loadAutoloadStats();
                }
            }
        });
    }
    
    function showUtilityResults(card, data, type) {
        var resultsDiv = card.find('.utility-results');
        var html = '<h4>Results</h4>';
        
        if (type === 'success') {
            resultsDiv.removeClass('error warning').addClass('success');
            
            // Database Optimizer results
            if (data.autoload) {
                html += '<ul>';
                html += '<li>Fixed ' + data.autoload.fixed_options + ' autoload options</li>';
                html += '<li>Saved ' + data.autoload.saved_mb + ' MB from autoload</li>';
                html += '</ul>';
            }
            
            if (data.revisions) {
                html += '<p>Deleted ' + data.revisions.deleted_count + ' post revisions</p>';
            }
            
            if (data.spam) {
                html += '<p>Removed ' + data.spam.deleted_count + ' spam comments</p>';
            }
            
            if (data.transients) {
                html += '<p>Cleared ' + data.transients.deleted_count + ' transients</p>';
            }
            
            // Plugin Analyzer results
            if (data.plugins && data.plugins.length > 0) {
                html += '<div class="plugin-analysis">';
                html += '<h5>Plugin Analysis</h5>';
                html += '<p><strong>' + data.total_plugins + '</strong> total plugins (' + data.active_plugins + ' active, ' + data.inactive_plugins + ' inactive)</p>';
                html += '<p><strong>Total size:</strong> ' + data.total_size + '</p>';
                
                // Show largest plugins
                var largestPlugins = data.plugins.slice(0, 5);
                html += '<h6>Largest Plugins:</h6><ul>';
                largestPlugins.forEach(function(plugin) {
                    var status = plugin.active ? 'Active' : 'Inactive';
                    var security = plugin.security_issues > 0 ? ' ⚠️ ' + plugin.security_issues + ' security issues' : '';
                    html += '<li><strong>' + plugin.name + '</strong> (' + plugin.size + ', ' + status + ')' + security + '</li>';
                });
                html += '</ul></div>';
            }
            
            // File Cleaner results
            if (data.action === 'scan' && data.files) {
                html += '<div class="file-scan-results">';
                html += '<h5>Unnecessary Files Found</h5>';
                html += '<p><strong>' + data.total_files + '</strong> files can be cleaned (' + data.total_size + ')</p>';
                
                if (data.files.length > 0) {
                    html += '<div class="file-list">';
                    data.files.slice(0, 10).forEach(function(file) {
                        html += '<div class="file-item">';
                        html += '<span class="file-name">' + file.name + '</span>';
                        html += '<span class="file-size">(' + file.size + ')</span>';
                        html += '<span class="file-type">[' + file.type + ']</span>';
                        html += '</div>';
                    });
                    if (data.files.length > 10) {
                        html += '<p>... and ' + (data.files.length - 10) + ' more files</p>';
                    }
                    html += '</div>';
                }
                html += '</div>';
            } else if (data.action === 'clean') {
                html += '<div class="file-clean-results">';
                html += '<h5>Files Cleaned</h5>';
                html += '<p>Cleaned <strong>' + data.total_cleaned + '</strong> files</p>';
                html += '<p>Space freed: <strong>' + data.space_freed + '</strong></p>';
                html += '</div>';
            }
            
            // Security Scanner results
            if (data.overall_score !== undefined) {
                var scoreClass = data.overall_score >= 80 ? 'good' : data.overall_score >= 60 ? 'warning' : 'error';
                html += '<div class="security-results">';
                html += '<h5>Security Scan Results</h5>';
                html += '<div class="security-score ' + scoreClass + '">Overall Score: ' + data.overall_score + '%</div>';
                
                // Show critical issues
                var criticalIssues = [];
                Object.keys(data).forEach(function(category) {
                    if (typeof data[category] === 'object' && data[category] !== null) {
                        Object.keys(data[category]).forEach(function(check) {
                            if (data[category][check].status === 'fail') {
                                criticalIssues.push(data[category][check].message);
                            }
                        });
                    }
                });
                
                if (criticalIssues.length > 0) {
                    html += '<h6>Critical Issues:</h6><ul>';
                    criticalIssues.forEach(function(issue) {
                        html += '<li class="security-issue">' + issue + '</li>';
                    });
                    html += '</ul>';
                }
                html += '</div>';
            }
            
            // Performance Booster results
            if (data.overall_score !== undefined && data.database) {
                html += '<div class="performance-results">';
                html += '<h5>Performance Analysis</h5>';
                html += '<div class="performance-score">Performance Score: ' + data.overall_score + '%</div>';
                
                if (data.database && data.database.autoload_size) {
                    html += '<p><strong>Autoload size:</strong> ' + data.database.autoload_size.size + '</p>';
                }
                
                if (data.plugins && data.plugins.plugin_count) {
                    html += '<p><strong>Active plugins:</strong> ' + data.plugins.plugin_count.count + '</p>';
                }
                html += '</div>';
            }
            
            // Backup Manager results
            if (data.backups) {
                html += '<div class="backup-results">';
                html += '<h5>Available Backups</h5>';
                html += '<p><strong>' + data.total_backups + '</strong> backups found</p>';
                
                if (data.backups.length > 0) {
                    html += '<div class="backup-list">';
                    data.backups.slice(0, 5).forEach(function(backup) {
                        html += '<div class="backup-item">';
                        html += '<span class="backup-name">' + backup.filename + '</span>';
                        html += '<span class="backup-size">(' + backup.size + ')</span>';
                        html += '<span class="backup-date">' + backup.created + '</span>';
                        html += '</div>';
                    });
                    html += '</div>';
                }
                html += '</div>';
            } else if (data.success !== undefined) {
                // Backup creation result
                html += '<div class="backup-creation">';
                if (data.success) {
                    html += '<h5>Backup Created Successfully</h5>';
                    html += '<p><strong>File:</strong> ' + data.filename + '</p>';
                    html += '<p><strong>Size:</strong> ' + data.size + '</p>';
                    html += '<p><strong>Files:</strong> ' + data.files_count + '</p>';
                } else {
                    html += '<h5>Backup Failed</h5>';
                    html += '<p class="error">' + data.message + '</p>';
                }
                html += '</div>';
            }
            
            // Summary for database operations
            if (data.summary) {
                html += '<div class="summary-stats">';
                html += '<p><strong>Performance Improvement:</strong> ' + data.summary.performance_improvement + '%</p>';
                html += '<p><strong>Autoload size reduced:</strong> ' + data.summary.old_size_mb + ' MB → ' + data.summary.new_size_mb + ' MB</p>';
                html += '</div>';
            }
            
        } else if (type === 'error') {
            resultsDiv.removeClass('success warning').addClass('error');
            html += '<p>Error: ' + (data.error || 'Unknown error occurred') + '</p>';
        }
        
        resultsDiv.html(html).show();
    }
    
    function showPreviewResults(card, data) {
        var resultsDiv = card.find('.utility-results');
        var html = '<h4>🔍 Preview - What Would Be Changed</h4>';
        
        resultsDiv.removeClass('error success').addClass('preview');
        
        if (data.dry_run) {
            html += '<div class="preview-notice">This is a preview. No changes have been made yet.</div>';
            
            // Database Optimizer preview
            if (data.preview) {
                if (data.preview.autoload) {
                    html += '<div class="preview-section">';
                    html += '<h5>🔧 Autoload Optimization</h5>';
                    html += '<p>Would fix <strong>' + data.preview.autoload.will_fix_count + '</strong> large autoloaded options</p>';
                    html += '<p>Potential savings: <strong>' + data.preview.autoload.total_savings + '</strong></p>';
                    if (data.preview.autoload.options && data.preview.autoload.options.length > 0) {
                        html += '<details><summary>Show affected options (first 10)</summary><ul>';
                        data.preview.autoload.options.forEach(function(option) {
                            html += '<li>' + option.name + ' (' + option.size + ')</li>';
                        });
                        html += '</ul></details>';
                    }
                    html += '</div>';
                }
                
                if (data.preview.revisions) {
                    html += '<div class="preview-section">';
                    html += '<h5>📝 Post Revisions</h5>';
                    html += '<p>Would delete <strong>' + data.preview.revisions.will_delete_count + '</strong> post revisions</p>';
                    html += '<p>' + data.preview.revisions.description + '</p>';
                    html += '</div>';
                }
                
                if (data.preview.spam) {
                    html += '<div class="preview-section">';
                    html += '<h5>🗑️ Spam Comments</h5>';
                    html += '<p>Would delete <strong>' + data.preview.spam.will_delete_count + '</strong> spam comments</p>';
                    html += '<p>' + data.preview.spam.description + '</p>';
                    html += '</div>';
                }
                
                if (data.preview.trash) {
                    html += '<div class="preview-section">';
                    html += '<h5>🗑️ Trash Posts</h5>';
                    html += '<p>Would delete <strong>' + data.preview.trash.will_delete_count + '</strong> trashed posts</p>';
                    html += '<p>' + data.preview.trash.description + '</p>';
                    html += '</div>';
                }
                
                if (data.preview.orphaned) {
                    html += '<div class="preview-section">';
                    html += '<h5>🧹 Orphaned Data</h5>';
                    html += '<p>Would delete <strong>' + data.preview.orphaned.postmeta_count + '</strong> orphaned post metadata entries</p>';
                    html += '<p>Would delete <strong>' + data.preview.orphaned.usermeta_count + '</strong> orphaned user metadata entries</p>';
                    html += '<p>' + data.preview.orphaned.description + '</p>';
                    html += '</div>';
                }
                
                if (data.preview.optimize) {
                    html += '<div class="preview-section">';
                    html += '<h5>⚡ Table Optimization</h5>';
                    html += '<p>Would optimize <strong>' + data.preview.optimize.table_count + '</strong> database tables</p>';
                    html += '<p>Total overhead to reclaim: <strong>' + data.preview.optimize.total_overhead + '</strong></p>';
                    if (data.preview.optimize.tables && data.preview.optimize.tables.length > 0) {
                        html += '<details><summary>Show tables (first 10)</summary><ul>';
                        data.preview.optimize.tables.forEach(function(table) {
                            html += '<li>' + table.name + ' (overhead: ' + table.overhead + ')</li>';
                        });
                        html += '</ul></details>';
                    }
                    html += '</div>';
                }
                
                // Cache Cleaner preview
                if (data.preview.transients) {
                    html += '<div class="preview-section">';
                    html += '<h5>🧹 Transients</h5>';
                    html += '<p>Would clear <strong>' + data.preview.transients.count + '</strong> transients</p>';
                    html += '<p>' + data.preview.transients.description + '</p>';
                    html += '</div>';
                }
                
                if (data.preview.elementor) {
                    html += '<div class="preview-section">';
                    html += '<h5>🎨 Elementor Cache</h5>';
                    html += '<p>Cache size: <strong>' + data.preview.elementor.cache_size + '</strong></p>';
                    html += '<p>' + data.preview.elementor.description + '</p>';
                    html += '</div>';
                }
            }
            
            // File Cleaner preview
            if (data.files || data.file_breakdown) {
                html += '<div class="preview-section">';
                html += '<h5>📁 Files to Clean</h5>';
                html += '<p>' + data.preview_message + '</p>';
                
                if (data.file_breakdown) {
                    html += '<div class="file-breakdown">';
                    Object.keys(data.file_breakdown).forEach(function(type) {
                        var breakdown = data.file_breakdown[type];
                        html += '<div class="breakdown-item">';
                        html += '<strong>' + type + ':</strong> ' + breakdown.count + ' files (' + breakdown.size_formatted + ')';
                        html += '</div>';
                    });
                    html += '</div>';
                }
                
                if (data.files && data.files.length > 0) {
                    html += '<details><summary>Show files (first 50)</summary><ul>';
                    data.files.forEach(function(file) {
                        html += '<li>' + file.name + ' (' + formatBytes(file.size) + ') - ' + file.type + '</li>';
                    });
                    html += '</ul></details>';
                }
                html += '</div>';
            }
            
            // Backup Manager preview
            if (data.preview_action === 'delete_backup') {
                html += '<div class="preview-section warning">';
                html += '<h5>⚠️ Backup Deletion</h5>';
                html += '<p>' + data.message + '</p>';
                html += '</div>';
            }
            
            if (data.old_backups) {
                html += '<div class="preview-section">';
                html += '<h5>🧹 Old Backups Cleanup</h5>';
                html += '<p>' + data.message + '</p>';
                if (data.old_backups.length > 0) {
                    html += '<details><summary>Show backups to be deleted</summary><ul>';
                    data.old_backups.forEach(function(backup) {
                        html += '<li>' + backup.filename + ' (' + backup.size + ') - ' + backup.age_days + ' days old</li>';
                    });
                    html += '</ul></details>';
                }
                html += '</div>';
            }
            
            // Add execute button after preview
            html += '<div class="preview-actions" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">';
            html += '<button class="utility-btn primary execute-action" data-utility="' + card.attr('id').replace('-', '_') + '">✅ Execute Changes</button>';
            html += '<button class="utility-btn secondary cancel-preview">❌ Cancel</button>';
            html += '</div>';
        }
        
        resultsDiv.html(html).show();
        
        // Handle execute and cancel buttons
        resultsDiv.find('.execute-action').on('click', function() {
            var utility = $(this).data('utility');
            var button = card.find('.utility-btn.primary').not('.execute-action');
            runUtility(utility, card, button, 'run');
        });
        
        resultsDiv.find('.cancel-preview').on('click', function() {
            resultsDiv.hide();
        });
    }

    function showGlobalProgress(message) {
        $('#progress-text').text(message);
        $('#global-progress').show();
        
        // Animate progress bar
        var progress = 0;
        var interval = setInterval(function() {
            progress += Math.random() * 10;
            if (progress > 90) progress = 90;
            $('.progress-fill').css('width', progress + '%');
        }, 200);
        
        // Store interval ID for cleanup
        $('#global-progress').data('interval', interval);
    }
    
    function hideGlobalProgress() {
        var interval = $('#global-progress').data('interval');
        if (interval) {
            clearInterval(interval);
        }
        
        $('.progress-fill').css('width', '100%');
        setTimeout(function() {
            $('#global-progress').hide();
            $('.progress-fill').css('width', '0%');
        }, 500);
    }
    
    function loadAutoloadStats() {
        $.ajax({
            url: reia_utils.ajax_url,
            type: 'POST',
            data: {
                action: 'reia_get_autoload_stats',
                nonce: reia_utils.nonce
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    $('#autoload-size').text(data.size_mb + ' MB (' + data.count + ' options)');
                    
                    var potential = '';
                    if (data.large_options_count > 0) {
                        potential = data.large_options_count + ' large options can be optimized (' + data.potential_savings_mb + ' MB)';
                    } else {
                        potential = 'Already optimized!';
                    }
                    $('#optimization-potential').text(potential);
                }
            }
        });
    }
    
    function updateResultsSummary(utility, data) {
        var summaryDiv = $('#results-summary');
        var contentDiv = $('#summary-content');
        
        if (!summaryDiv.is(':visible')) {
            summaryDiv.show();
            contentDiv.html('<div class="summary-grid"></div>');
        }
        
        var grid = contentDiv.find('.summary-grid');
        var utilityName = utility.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        
        // Add summary item for this utility
        var summaryHtml = '<div class="summary-item">';
        summaryHtml += '<span class="summary-number">✓</span>';
        summaryHtml += '<div class="summary-label">' + utilityName + ' Complete</div>';
        summaryHtml += '</div>';
        
        grid.append(summaryHtml);
    }
    
    function getButtonText(utility) {
        var texts = {
            'db_optimizer': 'Optimize Database',
            'cache_cleaner': 'Clear Cache',
            'autoload_fixer': 'Fix Autoload Issues',
            'plugin_analyzer': 'Analyze Plugins',
            'file_cleaner': 'Clean Files',
            'security_scanner': 'Run Security Scan',
            'performance_booster': 'Boost Performance',
            'backup_manager': 'Create Backup'
        };
        
        return texts[utility] || 'Run Utility';
    }
    
    // Store original button text
    $('.utility-btn').each(function() {
        $(this).data('original-text', $(this).text());
    });
    
    // Auto-refresh stats every 30 seconds
    setInterval(loadAutoloadStats, 30000);
    
    // Helper function to format bytes
    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        var k = 1024;
        var sizes = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

});
