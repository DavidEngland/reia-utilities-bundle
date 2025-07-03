<?php
/**
 * Plugin Name: REIA Utilities Bundle
 * Plugin URI: https://realestate-huntsville.com
 * Description: Complete suite of WordPress maintenance and optimization utilities. Open source, battle-tested, AI-enhanced.
 * Version: 1.2.1
 * Author: David E. England, Ph.D. & Claude Sonnet (Anthropic)
 * License: GPL v2 or later
 * Text Domain: reia-utilities
 */

/*
 * TODO - Development Roadmap:
 *
 * HIGH PRIORITY (Beta Testing Phase):
 * - [ ] Real-world validation across different hosting environments
 * - [ ] Backup functionality testing and reliability verification
 * - [ ] Cross-hosting compatibility testing
 * - [ ] Performance benchmarking with real-world data
 * - [ ] Enhanced error handling for edge cases
 *
 * MEDIUM PRIORITY (Post-Beta):
 * - [ ] Code modularization - split utilities into separate loadable modules
 * - [ ] Dry-run capabilities for destructive operations (preview mode)
 * - [ ] Optimize long previews/summaries for better UX
 * - [ ] Add comprehensive logging system
 * - [ ] Implement advanced scheduling with cron jobs
 *
 * LOW PRIORITY (Future):
 * - [ ] Multi-site network support
 * - [ ] REST API endpoints for external integrations
 * - [ ] Mobile-responsive design improvements
 * - [ ] Internationalization (i18n) support
 *
 * COMPLETED:
 * - [x] WordPress roles/capabilities (using built-in system with manage_options)
 * - [x] All 8 core utilities implemented
 * - [x] AJAX-based UI with real-time progress
 * - [x] Local/staging testing completed
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('REIA_UTILITIES_VERSION', '1.2.0');
define('REIA_UTILITIES_DIR', plugin_dir_path(__FILE__));
define('REIA_UTILITIES_URL', plugin_dir_url(__FILE__));

class REIA_Utilities_Bundle {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

        // AJAX handlers for each utility
        add_action('wp_ajax_reia_db_optimizer', array($this, 'handle_db_optimizer'));
        add_action('wp_ajax_reia_cache_cleaner', array($this, 'handle_cache_cleaner'));
        add_action('wp_ajax_reia_autoload_fixer', array($this, 'handle_autoload_fixer'));
        add_action('wp_ajax_reia_plugin_analyzer', array($this, 'handle_plugin_analyzer'));
        add_action('wp_ajax_reia_file_cleaner', array($this, 'handle_file_cleaner'));
        add_action('wp_ajax_reia_security_scanner', array($this, 'handle_security_scanner'));
        add_action('wp_ajax_reia_performance_booster', array($this, 'handle_performance_booster'));
        add_action('wp_ajax_reia_backup_manager', array($this, 'handle_backup_manager'));
        add_action('wp_ajax_reia_get_autoload_stats', array($this, 'handle_get_autoload_stats'));
    }

    /**
     * Helper method to log actions to Simple History if available
     * @param string $level Log level (info, warning, error)
     * @param string $message Log message
     * @param array $context Additional context data
     */
    private function log_to_simple_history($level = 'info', $message = '', $context = array()) {
        // Check if Simple History plugin is active and SimpleLogger function exists
        if (function_exists('SimpleLogger')) {
            $full_message = '[REIA Utilities] ' . $message;
            
            switch($level) {
                case 'warning':
                    SimpleLogger()->warning($full_message, $context);
                    break;
                case 'error':
                    SimpleLogger()->error($full_message, $context);
                    break;
                case 'info':
                default:
                    SimpleLogger()->info($full_message, $context);
                    break;
            }
        }
    }

    public function add_admin_menu() {
        add_management_page(
            'REIA Utilities',
            'REIA Utilities',
            'manage_options',
            'reia-utilities',
            array($this, 'admin_page')
        );
    }

    public function enqueue_scripts($hook) {
        if ($hook !== 'tools_page_reia-utilities') {
            return;
        }

        wp_enqueue_script(
            'reia-utilities-js',
            REIA_UTILITIES_URL . 'assets/js/utilities.js',
            array('jquery'),
            REIA_UTILITIES_VERSION,
            true
        );

        wp_localize_script('reia-utilities-js', 'reia_utils', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('reia_utilities_nonce')
        ));

        wp_enqueue_style(
            'reia-utilities-css',
            REIA_UTILITIES_URL . 'assets/css/utilities.css',
            array(),
            REIA_UTILITIES_VERSION
        );
    }

    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>🏠 REIA Utilities Bundle</h1>
            <p>Complete suite of WordPress maintenance and optimization utilities.</p>

            <div class="reia-utilities-grid">

                <!-- Database Optimizer -->
                <div class="utility-card" id="db-optimizer">
                    <div class="card-header">
                        <h2>🗄️ Database Optimizer</h2>
                        <p>Clean and optimize your WordPress database</p>
                    </div>
                    <div class="card-content">
                        <div class="utility-options">
                            <label><input type="checkbox" name="db_optimize[]" value="autoload" checked> Fix Autoload Issues</label>
                            <label><input type="checkbox" name="db_optimize[]" value="revisions" checked> Clean Post Revisions</label>
                            <label><input type="checkbox" name="db_optimize[]" value="spam" checked> Remove Spam Comments</label>
                            <label><input type="checkbox" name="db_optimize[]" value="trash" checked> Empty Trash</label>
                            <label><input type="checkbox" name="db_optimize[]" value="orphaned" checked> Remove Orphaned Data</label>
                            <label><input type="checkbox" name="db_optimize[]" value="optimize" checked> Optimize Tables</label>
                        </div>
                        <button class="utility-btn secondary" data-utility="db_optimizer" data-action="preview">🔍 Preview Changes</button>
                        <button class="utility-btn primary" data-utility="db_optimizer" data-action="optimize">🗄️ Optimize Database</button>
                    </div>
                    <div class="utility-results" style="display: none;"></div>
                </div>

                <!-- Cache Cleaner -->
                <div class="utility-card" id="cache-cleaner">
                    <div class="card-header">
                        <h2>🧹 Cache Cleaner</h2>
                        <p>Clear all types of WordPress cache</p>
                    </div>
                    <div class="card-content">
                        <div class="utility-options">
                            <label><input type="checkbox" name="cache_clean[]" value="object" checked> Object Cache</label>
                            <label><input type="checkbox" name="cache_clean[]" value="transients" checked> Transients</label>
                            <label><input type="checkbox" name="cache_clean[]" value="elementor" checked> Elementor Cache</label>
                            <label><input type="checkbox" name="cache_clean[]" value="opcache" checked> OPCache</label>
                            <label><input type="checkbox" name="cache_clean[]" value="rewrite" checked> Rewrite Rules</label>
                            <label><input type="checkbox" name="cache_clean[]" value="thumbnails" checked> Regenerate Thumbnails</label>
                        </div>
                        <button class="utility-btn secondary" data-utility="cache_cleaner" data-action="preview">🔍 Preview Cache to Clear</button>
                        <button class="utility-btn primary" data-utility="cache_cleaner" data-action="clear">🧹 Clear Cache</button>
                    </div>
                    <div class="utility-results" style="display: none;"></div>
                </div>

                <!-- Autoload Fixer -->
                <div class="utility-card" id="autoload-fixer">
                    <div class="card-header">
                        <h2>⚡ Autoload Fixer</h2>
                        <p>Automatically fix autoload performance issues</p>
                    </div>
                    <div class="card-content">
                        <div class="utility-stats">
                            <div class="stat-item">
                                <span class="stat-label">Current Autoload Size:</span>
                                <span class="stat-value" id="autoload-size">Calculating...</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Optimization Potential:</span>
                                <span class="stat-value" id="optimization-potential">Analyzing...</span>
                            </div>
                        </div>
                        <div class="utility-options">
                            <label><input type="checkbox" name="autoload_fix[]" value="large_options" checked> Disable autoload for large options (>5KB)</label>
                            <label><input type="checkbox" name="autoload_fix[]" value="transients" checked> Fix transient autoload issues</label>
                            <label><input type="checkbox" name="autoload_fix[]" value="analytics" checked> Fix analytics data autoload</label>
                            <label><input type="checkbox" name="autoload_fix[]" value="backup" checked> Create backup before changes</label>
                        </div>
                        <button class="utility-btn primary" data-utility="autoload_fixer">Fix Autoload Issues</button>
                    </div>
                    <div class="utility-results" style="display: none;"></div>
                </div>

                <!-- Plugin Analyzer -->
                <div class="utility-card" id="plugin-analyzer">
                    <div class="card-header">
                        <h2>🔌 Plugin Analyzer</h2>
                        <p>Analyze plugin performance and database impact</p>
                    </div>
                    <div class="card-content">
                        <div class="utility-options">
                            <label><input type="checkbox" name="plugin_analyze[]" value="inactive" checked> Find inactive plugins with DB data</label>
                            <label><input type="checkbox" name="plugin_analyze[]" value="performance" checked> Performance impact analysis</label>
                            <label><input type="checkbox" name="plugin_analyze[]" value="duplicates" checked> Find duplicate functionality</label>
                            <label><input type="checkbox" name="plugin_analyze[]" value="updates" checked> Check for updates</label>
                            <label><input type="checkbox" name="plugin_analyze[]" value="security" checked> Security scan</label>
                        </div>
                        <button class="utility-btn primary" data-utility="plugin_analyzer">Analyze Plugins</button>
                    </div>
                    <div class="utility-results" style="display: none;"></div>
                </div>

                <!-- File Cleaner -->
                <div class="utility-card" id="file-cleaner">
                    <div class="card-header">
                        <h2>📁 File Cleaner</h2>
                        <p>Clean up unnecessary files and uploads</p>
                    </div>
                    <div class="card-content">
                        <div class="utility-options">
                            <label><input type="checkbox" name="file_clean[]" value="unused_uploads" checked> Unused media files</label>
                            <label><input type="checkbox" name="file_clean[]" value="temp_files" checked> Temporary files</label>
                            <label><input type="checkbox" name="file_clean[]" value="log_files" checked> Log files</label>
                            <label><input type="checkbox" name="file_clean[]" value="backup_files" checked> Old backup files</label>
                            <label><input type="checkbox" name="file_clean[]" value="cache_files" checked> Cache files</label>
                            <label><input type="checkbox" name="file_clean[]" value="duplicate_images" checked> Duplicate images</label>
                        </div>
                        <button class="utility-btn secondary" data-utility="file_cleaner" data-action="preview">🔍 Preview Files to Clean</button>
                        <button class="utility-btn primary" data-utility="file_cleaner" data-action="clean">🧹 Clean Files</button>
                    </div>
                    <div class="utility-results" style="display: none;"></div>
                </div>

                <!-- Security Scanner -->
                <div class="utility-card" id="security-scanner">
                    <div class="card-header">
                        <h2>🔒 Security Scanner</h2>
                        <p>Scan for security vulnerabilities and issues</p>
                    </div>
                    <div class="card-content">
                        <div class="utility-options">
                            <label><input type="checkbox" name="security_scan[]" value="file_permissions" checked> File permissions</label>
                            <label><input type="checkbox" name="security_scan[]" value="wp_config" checked> wp-config.php security</label>
                            <label><input type="checkbox" name="security_scan[]" value="admin_users" checked> Admin user security</label>
                            <label><input type="checkbox" name="security_scan[]" value="malware" checked> Malware scan</label>
                            <label><input type="checkbox" name="security_scan[]" value="brute_force" checked> Brute force protection</label>
                            <label><input type="checkbox" name="security_scan[]" value="ssl" checked> SSL configuration</label>
                        </div>
                        <button class="utility-btn primary" data-utility="security_scanner">Run Security Scan</button>
                    </div>
                    <div class="utility-results" style="display: none;"></div>
                </div>

                <!-- Performance Booster -->
                <div class="utility-card" id="performance-booster">
                    <div class="card-header">
                        <h2>🚀 Performance Booster</h2>
                        <p>Apply performance optimizations automatically</p>
                    </div>
                    <div class="card-content">
                        <div class="utility-options">
                            <label><input type="checkbox" name="performance_boost[]" value="image_optimization" checked> Optimize images</label>
                            <label><input type="checkbox" name="performance_boost[]" value="css_minification" checked> Minify CSS</label>
                            <label><input type="checkbox" name="performance_boost[]" value="js_minification" checked> Minify JavaScript</label>
                            <label><input type="checkbox" name="performance_boost[]" value="gzip_compression" checked> Enable GZIP compression</label>
                            <label><input type="checkbox" name="performance_boost[]" value="browser_caching" checked> Optimize browser caching</label>
                            <label><input type="checkbox" name="performance_boost[]" value="database_optimization" checked> Database optimization</label>
                        </div>
                        <button class="utility-btn primary" data-utility="performance_booster">Boost Performance</button>
                    </div>
                    <div class="utility-results" style="display: none;"></div>
                </div>

                <!-- Backup Manager -->
                <div class="utility-card" id="backup-manager">
                    <div class="card-header">
                        <h2>💾 Backup Manager</h2>
                        <p>Create and manage site backups</p>
                    </div>
                    <div class="card-content">
                        <div class="backup-type-selector">
                            <h4>📦 Backup Type</h4>
                            <label><input type="radio" name="backup_type" value="content" checked> Content Only (Recommended)</label>
                            <small>Database + wp-content (excludes WP core files)</small>
                            
                            <label><input type="radio" name="backup_type" value="database"> Database Only</label>
                            <small>MySQL database export only</small>
                            
                            <label><input type="radio" name="backup_type" value="uploads"> Media Only</label>
                            <small>wp-content/uploads directory</small>
                            
                            <label><input type="radio" name="backup_type" value="full"> Full Site</label>
                            <small>⚠️ Everything including WP core (very large!)</small>
                        </div>
                        
                        <div class="backup-options">
                            <h4>⚙️ Options</h4>
                            <label><input type="checkbox" name="backup_options[]" value="exclude_logs" checked> Exclude log files</label>
                            <label><input type="checkbox" name="backup_options[]" value="exclude_cache" checked> Exclude cache files</label>
                            <label><input type="checkbox" name="backup_options[]" value="compress_images"> Compress images (slower)</label>
                        </div>
                        
                        <div class="backup-actions">
                            <button class="utility-btn primary" data-utility="backup_manager" data-action="create">📦 Create Backup</button>
                            <button class="utility-btn secondary" data-utility="backup_manager" data-action="list">📋 Manage Backups</button>
                        </div>
                    </div>
                    <div class="utility-results" style="display: none;"></div>
                </div>

            </div>

            <!-- Global Progress -->
            <div id="global-progress" style="display: none;">
                <h3>Processing...</h3>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: 0%"></div>
                </div>
                <p id="progress-text">Initializing...</p>
            </div>

            <!-- Results Summary -->
            <div id="results-summary" style="display: none;">
                <h3>📊 Results Summary</h3>
                <div id="summary-content"></div>
            </div>

        </div>
        <?php
    }

    // Database Optimizer Handler
    public function handle_db_optimizer() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        check_ajax_referer('reia_utilities_nonce', 'nonce');

        $optimize_options = isset($_POST['db_optimize_options']) ? $_POST['db_optimize_options'] : array();
        $dry_run = isset($_POST['dry_run']) && $_POST['dry_run'] === 'true';
        $results = array();

        global $wpdb;

        if ($dry_run) {
            // Dry run mode - show what would be affected without making changes
            $results['dry_run'] = true;
            $results['preview'] = array();

            if (in_array('autoload', $optimize_options)) {
                $large_options = $wpdb->get_results("
                    SELECT option_name, LENGTH(option_value) as size_bytes
                    FROM {$wpdb->options}
                    WHERE autoload = 'yes' AND LENGTH(option_value) > 5000
                    ORDER BY size_bytes DESC
                ");

                $critical_options = array('wp_user_roles', 'active_plugins', 'cron', 'stylesheet', 'template');
                $will_fix = array();
                $total_savings = 0;

                foreach ($large_options as $option) {
                    if (!in_array($option->option_name, $critical_options)) {
                        $will_fix[] = array(
                            'name' => $option->option_name,
                            'size' => $this->format_bytes($option->size_bytes),
                            'size_bytes' => $option->size_bytes
                        );
                        $total_savings += $option->size_bytes;
                    }
                }

                $results['preview']['autoload'] = array(
                    'will_fix_count' => count($will_fix),
                    'options' => array_slice($will_fix, 0, 10), // Show first 10
                    'total_savings' => $this->format_bytes($total_savings),
                    'description' => 'Large autoloaded options that will be changed to non-autoload'
                );
            }

            if (in_array('revisions', $optimize_options)) {
                $revision_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'revision'");
                $results['preview']['revisions'] = array(
                    'will_delete_count' => $revision_count,
                    'description' => 'Post revisions that will be permanently deleted'
                );
            }

            if (in_array('spam', $optimize_options)) {
                $spam_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'");
                $results['preview']['spam'] = array(
                    'will_delete_count' => $spam_count,
                    'description' => 'Spam comments that will be permanently deleted'
                );
            }

            if (in_array('trash', $optimize_options)) {
                $trash_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'trash'");
                $results['preview']['trash'] = array(
                    'will_delete_count' => $trash_count,
                    'description' => 'Trashed posts that will be permanently deleted'
                );
            }

            if (in_array('orphaned', $optimize_options)) {
                $orphaned_postmeta = $wpdb->get_var("
                    SELECT COUNT(*) FROM {$wpdb->postmeta} pm
                    LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                    WHERE p.ID IS NULL
                ");

                $orphaned_usermeta = $wpdb->get_var("
                    SELECT COUNT(*) FROM {$wpdb->usermeta} um
                    LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
                    WHERE u.ID IS NULL
                ");

                $results['preview']['orphaned'] = array(
                    'postmeta_count' => $orphaned_postmeta,
                    'usermeta_count' => $orphaned_usermeta,
                    'description' => 'Orphaned metadata with no associated posts/users'
                );
            }

            if (in_array('optimize', $optimize_options)) {
                $tables_with_overhead = $wpdb->get_results("
                    SHOW TABLE STATUS WHERE Data_free > 0
                ");

                $table_info = array();
                $total_overhead = 0;

                foreach ($tables_with_overhead as $table) {
                    $table_info[] = array(
                        'name' => $table->Name,
                        'overhead' => $this->format_bytes($table->Data_free)
                    );
                    $total_overhead += $table->Data_free;
                }

                $results['preview']['optimize'] = array(
                    'table_count' => count($tables_with_overhead),
                    'tables' => array_slice($table_info, 0, 10), // Show first 10
                    'total_overhead' => $this->format_bytes($total_overhead),
                    'description' => 'Database tables that will be optimized to reclaim overhead space'
                );
            }

            $results['preview_message'] = 'Preview of database optimization changes:';
        } else {
            // Actual execution mode
            // Actual execution mode
            if (in_array('autoload', $optimize_options)) {
                $large_options = $wpdb->get_results("
                    SELECT option_name, LENGTH(option_value) as size_bytes
                    FROM {$wpdb->options}
                    WHERE autoload = 'yes' AND LENGTH(option_value) > 5000
                    ORDER BY size_bytes DESC
                ");

                $fixed_count = 0;
                $saved_bytes = 0;

                foreach ($large_options as $option) {
                    $critical_options = array('wp_user_roles', 'active_plugins', 'cron', 'stylesheet', 'template');
                    if (!in_array($option->option_name, $critical_options)) {
                        $wpdb->update(
                            $wpdb->options,
                            array('autoload' => 'no'),
                            array('option_name' => $option->option_name)
                        );
                        $fixed_count++;
                        $saved_bytes += $option->size_bytes;
                    }
                }

                $results['autoload'] = array(
                    'fixed_options' => $fixed_count,
                    'saved_bytes' => $saved_bytes,
                    'saved_mb' => round($saved_bytes / 1024 / 1024, 2)
                );
            }

            if (in_array('revisions', $optimize_options)) {
                $deleted_revisions = $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_type = 'revision'");
                $results['revisions'] = array('deleted_count' => $deleted_revisions);
            }

            if (in_array('spam', $optimize_options)) {
                $deleted_spam = $wpdb->query("DELETE FROM {$wpdb->comments} WHERE comment_approved = 'spam'");
                $results['spam'] = array('deleted_count' => $deleted_spam);
            }

            if (in_array('trash', $optimize_options)) {
                $deleted_trash = $wpdb->query("DELETE FROM {$wpdb->posts} WHERE post_status = 'trash'");
                $results['trash'] = array('deleted_count' => $deleted_trash);
            }

            if (in_array('orphaned', $optimize_options)) {
                $orphaned_postmeta = $wpdb->query("
                    DELETE pm FROM {$wpdb->postmeta} pm
                    LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
                    WHERE p.ID IS NULL
                ");

                $orphaned_usermeta = $wpdb->query("
                    DELETE um FROM {$wpdb->usermeta} um
                    LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
                    WHERE u.ID IS NULL
                ");

                $results['orphaned'] = array(
                    'postmeta_deleted' => $orphaned_postmeta,
                    'usermeta_deleted' => $orphaned_usermeta
                );
            }

            if (in_array('optimize', $optimize_options)) {
                $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
                $optimized_tables = 0;

                foreach ($tables as $table) {
                    $wpdb->query("OPTIMIZE TABLE " . $table[0]);
                    $optimized_tables++;
                }

                $results['optimize'] = array('optimized_tables' => $optimized_tables);
            }

            // Log to Simple History if not in dry run mode
            $this->log_database_optimization_to_history($optimize_options, $results);
        }

        wp_send_json_success($results);
    }

    // Cache Cleaner Handler
    public function handle_cache_cleaner() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        check_ajax_referer('reia_utilities_nonce', 'nonce');

        $clean_options = isset($_POST['clean_options']) ? $_POST['clean_options'] : array();
        $dry_run = isset($_POST['dry_run']) && $_POST['dry_run'] === 'true';
        $results = array();

        global $wpdb;

        if ($dry_run) {
            // Dry run mode - show what would be cleared without making changes
            $results['dry_run'] = true;
            $results['preview'] = array();

            if (in_array('transients', $clean_options)) {
                $transient_count = $wpdb->get_var("
                    SELECT COUNT(*) FROM {$wpdb->options}
                    WHERE option_name LIKE '_transient_%'
                    OR option_name LIKE '_site_transient_%'
                ");

                $results['preview']['transients'] = array(
                    'count' => $transient_count,
                    'description' => 'Expired transients and temporary cache data'
                );
            }

            if (in_array('elementor', $clean_options)) {
                $elementor_cache_size = $wpdb->get_var("
                    SELECT LENGTH(option_value) FROM {$wpdb->options}
                    WHERE option_name = 'elementor_remote_info_library'
                ");

                $results['preview']['elementor'] = array(
                    'cache_size' => $elementor_cache_size ? $this->format_bytes($elementor_cache_size) : 'N/A',
                    'description' => 'Elementor template library cache and generated CSS/JS files'
                );
            }

            if (in_array('object', $clean_options)) {
                $results['preview']['object'] = array(
                    'description' => 'WordPress object cache (if enabled)'
                );
            }

            if (in_array('opcache', $clean_options)) {
                $results['preview']['opcache'] = array(
                    'description' => 'PHP OPCache (compiled PHP scripts)'
                );
            }

            if (in_array('rewrite', $clean_options)) {
                $results['preview']['rewrite'] = array(
                    'description' => 'WordPress rewrite rules cache'
                );
            }

            $results['preview_message'] = 'These cache types will be cleared:';
        } else {
            // Actual cleaning
            if (in_array('transients', $clean_options)) {
                $deleted_transients = $wpdb->query("
                    DELETE FROM {$wpdb->options}
                    WHERE option_name LIKE '_transient_%'
                    OR option_name LIKE '_site_transient_%'
                ");
                $results['transients'] = array('deleted_count' => $deleted_transients);
            }

            if (in_array('elementor', $clean_options)) {
                // Clear Elementor cache and that massive template library cache
                $wpdb->delete($wpdb->options, array('option_name' => 'elementor_remote_info_library'));
                delete_option('elementor_remote_info_library');

                if (class_exists('\Elementor\Plugin')) {
                    \Elementor\Plugin::$instance->files_manager->clear_cache();
                }

                $results['elementor'] = array('status' => 'cleared');
            }

            if (in_array('object', $clean_options)) {
                if (function_exists('wp_cache_flush')) {
                    wp_cache_flush();
                    $results['object'] = array('status' => 'flushed');
                }
            }

            if (in_array('opcache', $clean_options)) {
                if (function_exists('opcache_reset')) {
                    opcache_reset();
                    $results['opcache'] = array('status' => 'reset');
                }
            }

            if (in_array('rewrite', $clean_options)) {
                flush_rewrite_rules();
                $results['rewrite'] = array('status' => 'flushed');
            }

            // Log to Simple History if not in dry run mode
            $this->log_cache_cleaning_to_history($clean_options, $results);
        }

        wp_send_json_success($results);
    }

    // Autoload Fixer Handler
    public function handle_autoload_fixer() {
        if (!wp_verify_nonce($_POST['nonce'], 'reia_utilities_nonce') || !current_user_can('manage_options')) {
            wp_die('Security check failed');
        }

        global $wpdb;

        // Get current autoload size
        $current_size = $wpdb->get_var("
            SELECT SUM(LENGTH(option_value))
            FROM {$wpdb->options}
            WHERE autoload = 'yes'
        ");

        $fix_options = isset($_POST['fix_options']) ? $_POST['fix_options'] : array();
        $results = array();
        $total_saved = 0;

        if (in_array('large_options', $fix_options)) {
            $large_options = $wpdb->get_results("
                SELECT option_name, LENGTH(option_value) as size_bytes
                FROM {$wpdb->options}
                WHERE autoload = 'yes' AND LENGTH(option_value) > 5000
            ");

            $fixed = 0;
            $saved_bytes = 0;

            // Critical options that should stay autoloaded
            $keep_autoload = array('wp_user_roles', 'active_plugins', 'cron', 'stylesheet', 'template');

            foreach ($large_options as $option) {
                if (!in_array($option->option_name, $keep_autoload)) {
                    $wpdb->update(
                        $wpdb->options,
                        array('autoload' => 'no'),
                        array('option_name' => $option->option_name)
                    );
                    $fixed++;
                    $saved_bytes += $option->size_bytes;
                    $total_saved += $option->size_bytes;
                }
            }

            $results['large_options'] = array(
                'fixed_count' => $fixed,
                'saved_bytes' => $saved_bytes,
                'saved_mb' => round($saved_bytes / 1024 / 1024, 2)
            );
        }

        // Calculate new autoload size
        $new_size = $wpdb->get_var("
            SELECT SUM(LENGTH(option_value))
            FROM {$wpdb->options}
            WHERE autoload = 'yes'
        ");

        $results['summary'] = array(
            'old_size_mb' => round($current_size / 1024 / 1024, 2),
            'new_size_mb' => round($new_size / 1024 / 1024, 2),
            'total_saved_mb' => round(($current_size - $new_size) / 1024 / 1024, 2),
            'performance_improvement' => $current_size > 0 ? round((($current_size - $new_size) / $current_size) * 100, 1) : 0
        );

        // Log to Simple History
        $this->log_autoload_optimization_to_history($fix_options, $results);

        wp_send_json_success($results);
    }

    // Get Autoload Stats Handler
    public function handle_get_autoload_stats() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        check_ajax_referer('reia_utilities_nonce', 'nonce');

        global $wpdb;

        // Get current autoload size and count
        $autoload_stats = $wpdb->get_row("
            SELECT
                SUM(LENGTH(option_value)) as total_size,
                COUNT(*) as total_count
            FROM {$wpdb->options}
            WHERE autoload = 'yes'
        ");

        // Get large options that could be optimized
        $large_options = $wpdb->get_results("
            SELECT
                option_name,
                LENGTH(option_value) as size_bytes
            FROM {$wpdb->options}
            WHERE autoload = 'yes' AND LENGTH(option_value) > 5000
            ORDER BY size_bytes DESC
        ");

        // Calculate potential savings
        $critical_options = array('wp_user_roles', 'active_plugins', 'cron', 'stylesheet', 'template');
        $potential_savings = 0;
        $optimizable_count = 0;

        foreach ($large_options as $option) {
            if (!in_array($option->option_name, $critical_options)) {
                $potential_savings += $option->size_bytes;
                $optimizable_count++;
            }
        }

        $results = array(
            'size_mb' => round($autoload_stats->total_size / 1024 / 1024, 2),
            'count' => $autoload_stats->total_count,
            'large_options_count' => $optimizable_count,
            'potential_savings_mb' => round($potential_savings / 1024 / 1024, 2)
        );

        wp_send_json_success($results);
    }

    // Helper Methods
    private function get_directory_size($directory) {
        $size = 0;
        if (is_dir($directory)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        }
        return $size;
    }

    /**
     * Log database optimization actions to Simple History
     */
    private function log_database_optimization_to_history($optimize_options, $results) {
        if (!function_exists('SimpleLogger')) {
            return;
        }

        $actions_performed = array();
        $stats = array();

        if (in_array('autoload', $optimize_options) && isset($results['autoload'])) {
            $actions_performed[] = 'Fixed autoload options';
            $stats[] = sprintf('%d large autoload options fixed, saved %s', 
                $results['autoload']['fixed_options'], 
                $this->format_bytes($results['autoload']['saved_bytes'])
            );
        }

        if (in_array('revisions', $optimize_options) && isset($results['revisions'])) {
            $actions_performed[] = 'Cleaned post revisions';
            $stats[] = sprintf('%d post revisions deleted', $results['revisions']['deleted_count']);
        }

        if (in_array('spam', $optimize_options) && isset($results['spam'])) {
            $actions_performed[] = 'Removed spam comments';
            $stats[] = sprintf('%d spam comments deleted', $results['spam']['deleted_count']);
        }

        if (in_array('trash', $optimize_options) && isset($results['trash'])) {
            $actions_performed[] = 'Emptied trash';
            $stats[] = sprintf('%d trashed posts deleted', $results['trash']['deleted_count']);
        }

        if (in_array('orphaned', $optimize_options) && isset($results['orphaned'])) {
            $actions_performed[] = 'Removed orphaned data';
            $stats[] = sprintf('%d orphaned postmeta and %d orphaned usermeta entries deleted', 
                $results['orphaned']['postmeta_deleted'], 
                $results['orphaned']['usermeta_deleted']
            );
        }

        if (in_array('optimize', $optimize_options) && isset($results['optimize'])) {
            $actions_performed[] = 'Optimized database tables';
            $stats[] = sprintf('%d database tables optimized', $results['optimize']['optimized_tables']);
        }

        if (!empty($actions_performed)) {
            $message = 'Database optimization completed: ' . implode(', ', $actions_performed);
            $context = array(
                'actions' => $actions_performed,
                'statistics' => $stats,
                'user_id' => get_current_user_id(),
                'user_login' => wp_get_current_user()->user_login
            );

            $this->log_to_simple_history('info', $message, $context);
        }
    }

    /**
     * Log cache cleaning actions to Simple History
     */
    private function log_cache_cleaning_to_history($clean_options, $results) {
        if (!function_exists('SimpleLogger')) {
            return;
        }

        $actions_performed = array();
        $stats = array();

        if (in_array('transients', $clean_options) && isset($results['transients'])) {
            $actions_performed[] = 'Cleared transients';
            $stats[] = sprintf('%d transients deleted', $results['transients']['deleted_count']);
        }

        if (in_array('elementor', $clean_options) && isset($results['elementor'])) {
            $actions_performed[] = 'Cleared Elementor cache';
            $stats[] = 'Elementor cache and template library cleared';
        }

        if (in_array('object', $clean_options) && isset($results['object'])) {
            $actions_performed[] = 'Flushed object cache';
            $stats[] = 'WordPress object cache flushed';
        }

        if (in_array('opcache', $clean_options) && isset($results['opcache'])) {
            $actions_performed[] = 'Reset OPCache';
            $stats[] = 'PHP OPCache reset';
        }

        if (in_array('rewrite', $clean_options) && isset($results['rewrite'])) {
            $actions_performed[] = 'Flushed rewrite rules';
            $stats[] = 'WordPress rewrite rules flushed';
        }

        if (!empty($actions_performed)) {
            $message = 'Cache cleaning completed: ' . implode(', ', $actions_performed);
            $context = array(
                'actions' => $actions_performed,
                'statistics' => $stats,
                'user_id' => get_current_user_id(),
                'user_login' => wp_get_current_user()->user_login
            );

            $this->log_to_simple_history('info', $message, $context);
        }
    }

    /**
     * Log autoload optimization actions to Simple History
     */
    private function log_autoload_optimization_to_history($fix_options, $results) {
        if (!function_exists('SimpleLogger')) {
            return;
        }

        $actions_performed = array();
        $stats = array();

        if (in_array('large_options', $fix_options) && isset($results['large_options'])) {
            $actions_performed[] = 'Fixed large autoload options';
            $stats[] = sprintf('%d large autoload options fixed, saved %s', 
                $results['large_options']['fixed_count'], 
                $this->format_bytes($results['large_options']['saved_bytes'])
            );
        }

        if (isset($results['summary'])) {
            $stats[] = sprintf('Total performance improvement: %s%%, reduced autoload from %s MB to %s MB',
                $results['summary']['performance_improvement'],
                $results['summary']['old_size_mb'],
                $results['summary']['new_size_mb']
            );
        }

        if (!empty($actions_performed)) {
            $message = 'Autoload optimization completed: ' . implode(', ', $actions_performed);
            $context = array(
                'actions' => $actions_performed,
                'statistics' => $stats,
                'user_id' => get_current_user_id(),
                'user_login' => wp_get_current_user()->user_login
            );

            $this->log_to_simple_history('info', $message, $context);
        }
    }

    /**
     * Log other utility actions to Simple History (generic method for remaining utilities)
     */
    private function log_utility_action_to_history($utility_name, $action, $results) {
        if (!function_exists('SimpleLogger')) {
            return;
        }

        $stats = array();
        
        // Extract meaningful statistics from results
        if (is_array($results)) {
            foreach ($results as $key => $value) {
                if (is_array($value)) {
                    // Handle nested array results
                    foreach ($value as $subkey => $subvalue) {
                        if (is_numeric($subvalue)) {
                            $stats[] = ucfirst($key) . ' ' . $subkey . ': ' . $subvalue;
                        } elseif (is_string($subvalue) && strlen($subvalue) < 100) {
                            $stats[] = ucfirst($key) . ' ' . $subkey . ': ' . $subvalue;
                        }
                    }
                } elseif (is_numeric($value)) {
                    $stats[] = ucfirst($key) . ': ' . $value;
                } elseif (is_string($value) && strlen($value) < 100) {
                    $stats[] = ucfirst($key) . ': ' . $value;
                }
            }
        }

        $message = sprintf('%s %s completed', ucfirst($utility_name), $action);
        $context = array(
            'utility' => $utility_name,
            'action' => $action,
            'statistics' => $stats,
            'user_id' => get_current_user_id(),
            'user_login' => wp_get_current_user()->user_login
        );

        $this->log_to_simple_history('info', $message, $context);
    }

    private function format_bytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    private function check_plugin_security($plugin_path) {
        $issues = array();

        if (!is_dir($plugin_path)) {
            return $issues;
        }

        // Check for common security issues
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($plugin_path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->isFile() && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $content = file_get_contents($file);

                // Check for eval() usage
                if (preg_match('/\beval\s*\(/', $content)) {
                    $issues[] = 'Contains eval() function in ' . basename($file);
                }

                // Check for file_get_contents with URLs
                if (preg_match('/file_get_contents\s*\(\s*[\'"]https?:/', $content)) {
                    $issues[] = 'Remote file inclusion in ' . basename($file);
                }

                // Check for unescaped SQL
                if (preg_match('/\$wpdb->(query|get_|prepare)\s*\([^)]*\$[^)]*\)/', $content)) {
                    if (!preg_match('/\$wpdb->prepare/', $content)) {
                        $issues[] = 'Potential SQL injection in ' . basename($file);
                    }
                }

                // Check for unescaped output
                if (preg_match('/echo\s+\$_(GET|POST|REQUEST)/', $content)) {
                    $issues[] = 'Unescaped user input output in ' . basename($file);
                }
            }
        }

        return $issues;
    }

    private function get_plugin_last_modified($plugin_path) {
        $latest = 0;

        if (!is_dir($plugin_path)) {
            return date('Y-m-d H:i:s', $latest);
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($plugin_path, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $mtime = $file->getMTime();
                if ($mtime > $latest) {
                    $latest = $mtime;
                }
            }
        }

        return date('Y-m-d H:i:s', $latest);
    }

    private function scan_unnecessary_files($scan_options = array()) {
        $unnecessary_files = array();

        // Default scan options if none provided
        if (empty($scan_options)) {
            $scan_options = array('temp_files', 'log_files', 'backup_files', 'cache_files');
        }

        // Common unnecessary file patterns based on options
        $patterns = array();

        if (in_array('temp_files', $scan_options)) {
            $patterns = array_merge($patterns, array('*.tmp', '*.temp', '*~'));
        }

        if (in_array('log_files', $scan_options)) {
            $patterns = array_merge($patterns, array('*.log'));
        }

        if (in_array('backup_files', $scan_options)) {
            $patterns = array_merge($patterns, array('*.bak', '*.backup', '*.old', '*.orig'));
        }

        if (in_array('cache_files', $scan_options)) {
            $patterns = array_merge($patterns, array('*.cache'));
        }

        // Always include these system files
        $patterns = array_merge($patterns, array('.DS_Store', 'Thumbs.db'));

        // Directories to scan
        $scan_dirs = array(
            WP_CONTENT_DIR,
            ABSPATH . 'wp-admin',
            get_temp_dir()
        );

        foreach ($scan_dirs as $dir) {
            if (!is_dir($dir)) continue;

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $filename = $file->getFilename();
                    $filepath = $file->getPathname();

                    // Check against patterns
                    foreach ($patterns as $pattern) {
                        if (fnmatch($pattern, $filename)) {
                            $unnecessary_files[] = array(
                                'path' => $filepath,
                                'name' => $filename,
                                'size' => $file->getSize(),
                                'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                                'type' => $this->get_file_type($filename),
                                'directory' => dirname($filepath)
                            );
                            break;
                        }
                    }

                    // Check for old backups (older than 30 days) if backup_files option is selected
                    if (in_array('backup_files', $scan_options) &&
                        preg_match('/backup|bak|old/i', $filename) &&
                        $file->getMTime() < (time() - 30 * DAY_IN_SECONDS)) {
                        $unnecessary_files[] = array(
                            'path' => $filepath,
                            'name' => $filename,
                            'size' => $file->getSize(),
                            'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                            'type' => 'Old Backup',
                            'directory' => dirname($filepath)
                        );
                    }
                }
            }
        }

        return $unnecessary_files;
    }

    private function clean_unnecessary_files($files_to_clean) {
        $cleaned = array(
            'files' => array(),
            'count' => 0,
            'size' => 0
        );

        foreach ($files_to_clean as $file_path) {
            $file_path = sanitize_text_field($file_path);

            if (file_exists($file_path) && is_file($file_path)) {
                $size = filesize($file_path);

                // Safety check - don't delete core WordPress files
                if ($this->is_safe_to_delete($file_path)) {
                    if (unlink($file_path)) {
                        $cleaned['files'][] = basename($file_path);
                        $cleaned['count']++;
                        $cleaned['size'] += $size;
                    }
                }
            }
        }

        return $cleaned;
    }

    private function get_file_type($filename) {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $types = array(
            'log' => 'Log File',
            'tmp' => 'Temporary File',
            'temp' => 'Temporary File',
            'cache' => 'Cache File',
            'bak' => 'Backup File',
            'backup' => 'Backup File',
            'old' => 'Old File',
            'orig' => 'Original File'
        );

        return $types[$extension] ?? 'Other';
    }

    private function is_safe_to_delete($file_path) {
        // Don't delete core WordPress files
        $wp_core_dirs = array(
            ABSPATH . 'wp-admin',
            ABSPATH . 'wp-includes'
        );

        foreach ($wp_core_dirs as $core_dir) {
            if (strpos($file_path, $core_dir) === 0) {
                // Only allow deletion of log files in core directories
                if (!preg_match('/\.(log|tmp|cache)$/i', $file_path)) {
                    return false;
                }
            }
        }

        // Don't delete active theme or plugin files
        $active_theme_dir = get_template_directory();
        $plugin_dir = WP_PLUGIN_DIR;

        if (strpos($file_path, $active_theme_dir) === 0 ||
            strpos($file_path, $plugin_dir) === 0) {
            // Only allow deletion of cache/log files
            if (!preg_match('/\.(log|tmp|cache|bak)$/i', $file_path)) {
                return false;
            }
        }

        return true;
    }

    // Security Scanner Helper Methods
    private function run_core_security_checks() {
        $checks = array();

        // WordPress version check
        global $wp_version;
        $latest_version = $this->get_latest_wp_version();
        $checks['wp_version'] = array(
            'status' => version_compare($wp_version, $latest_version, '>=') ? 'pass' : 'warning',
            'message' => "WordPress {$wp_version} (Latest: {$latest_version})",
            'recommendation' => version_compare($wp_version, $latest_version, '<') ? 'Update WordPress to the latest version' : null
        );

        // Admin user check
        $admin_users = get_users(array('role' => 'administrator'));
        $weak_admins = array();
        foreach ($admin_users as $user) {
            if (in_array($user->user_login, array('admin', 'administrator', 'root', 'test'))) {
                $weak_admins[] = $user->user_login;
            }
        }

        $checks['admin_usernames'] = array(
            'status' => empty($weak_admins) ? 'pass' : 'fail',
            'message' => empty($weak_admins) ? 'No weak admin usernames found' : 'Weak admin usernames: ' . implode(', ', $weak_admins),
            'recommendation' => !empty($weak_admins) ? 'Change weak admin usernames' : null
        );

        // Table prefix check
        global $wpdb;
        $checks['table_prefix'] = array(
            'status' => $wpdb->prefix !== 'wp_' ? 'pass' : 'warning',
            'message' => "Database prefix: {$wpdb->prefix}",
            'recommendation' => $wpdb->prefix === 'wp_' ? 'Consider changing the database table prefix' : null
        );

        return $checks;
    }

    private function check_file_permissions() {
        $checks = array();

        $files_to_check = array(
            'wp-config.php' => array('expected' => 0600, 'critical' => true),
            '.htaccess' => array('expected' => 0644, 'critical' => false),
            'wp-content' => array('expected' => 0755, 'critical' => false),
            'wp-content/uploads' => array('expected' => 0755, 'critical' => false)
        );

        foreach ($files_to_check as $file => $settings) {
            $filepath = ABSPATH . $file;
            if (file_exists($filepath)) {
                $current_perms = fileperms($filepath) & 0777;
                $expected_perms = $settings['expected'];

                $status = ($current_perms === $expected_perms) ? 'pass' :
                         ($settings['critical'] ? 'fail' : 'warning');

                $checks[$file] = array(
                    'status' => $status,
                    'current' => decoct($current_perms),
                    'expected' => decoct($expected_perms),
                    'message' => sprintf('%s permissions: %o (expected: %o)', $file, $current_perms, $expected_perms)
                );
            }
        }

        return $checks;
    }

    private function check_user_security() {
        $checks = array();

        // Check for users with weak passwords (basic check)
        $users = get_users();
        $weak_password_users = 0;

        foreach ($users as $user) {
            // Check if username equals display name (potential security issue)
            if ($user->user_login === $user->display_name) {
                $weak_password_users++;
            }
        }

        $checks['user_display_names'] = array(
            'status' => $weak_password_users === 0 ? 'pass' : 'warning',
            'message' => $weak_password_users === 0 ? 'No users with login as display name' : "{$weak_password_users} users using login as display name",
            'recommendation' => $weak_password_users > 0 ? 'Change display names to not match usernames' : null
        );

        // Check for inactive users
        $inactive_users = 0;
        $cutoff_date = date('Y-m-d H:i:s', strtotime('-6 months'));

        foreach ($users as $user) {
            $last_login = get_user_meta($user->ID, 'last_login', true);
            if (empty($last_login) || $last_login < $cutoff_date) {
                $inactive_users++;
            }
        }

        $checks['inactive_users'] = array(
            'status' => $inactive_users === 0 ? 'pass' : 'warning',
            'message' => $inactive_users === 0 ? 'No inactive users found' : "{$inactive_users} users inactive for 6+ months",
            'recommendation' => $inactive_users > 0 ? 'Review and possibly remove inactive users' : null
        );

        return $checks;
    }

    private function check_plugin_theme_security() {
        $checks = array();

        // Check for outdated plugins
        $outdated_plugins = 0;
        $updates = get_site_transient('update_plugins');
        if ($updates && isset($updates->response)) {
            $outdated_plugins = count($updates->response);
        }

        $checks['plugin_updates'] = array(
            'status' => $outdated_plugins === 0 ? 'pass' : 'warning',
            'message' => $outdated_plugins === 0 ? 'All plugins up to date' : "{$outdated_plugins} plugins need updates",
            'recommendation' => $outdated_plugins > 0 ? 'Update outdated plugins' : null
        );

        // Check for outdated themes
        $outdated_themes = 0;
        $theme_updates = get_site_transient('update_themes');
        if ($theme_updates && isset($theme_updates->response)) {
            $outdated_themes = count($theme_updates->response);
        }

        $checks['theme_updates'] = array(
            'status' => $outdated_themes === 0 ? 'pass' : 'warning',
            'message' => $outdated_themes === 0 ? 'All themes up to date' : "{$outdated_themes} themes need updates",
            'recommendation' => $outdated_themes > 0 ? 'Update outdated themes' : null
        );

        return $checks;
    }

    private function check_configuration_security() {
        $checks = array();

        // Check if file editing is disabled
        $checks['file_editing'] = array(
            'status' => defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT ? 'pass' : 'warning',
            'message' => defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT ? 'File editing disabled' : 'File editing enabled',
            'recommendation' => !(defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT) ? 'Add DISALLOW_FILE_EDIT to wp-config.php' : null
        );

        // Check debug mode
        $checks['debug_mode'] = array(
            'status' => defined('WP_DEBUG') && WP_DEBUG ? 'warning' : 'pass',
            'message' => defined('WP_DEBUG') && WP_DEBUG ? 'Debug mode enabled' : 'Debug mode disabled',
            'recommendation' => defined('WP_DEBUG') && WP_DEBUG ? 'Disable debug mode in production' : null
        );

        // Check for security plugins
        $security_plugins = array('wordfence', 'sucuri', 'ithemes-security', 'all-in-one-wp-security');
        $has_security_plugin = false;

        foreach ($security_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_security_plugin = true;
                break;
            }
        }

        $checks['security_plugin'] = array(
            'status' => $has_security_plugin ? 'pass' : 'warning',
            'message' => $has_security_plugin ? 'Security plugin detected' : 'No security plugin detected',
            'recommendation' => !$has_security_plugin ? 'Consider installing a security plugin' : null
        );

        return $checks;
    }

    private function calculate_security_score($core, $permissions, $users, $plugins, $config) {
        $total_checks = 0;
        $passed_checks = 0;

        $all_checks = array_merge($core, $permissions, $users, $plugins, $config);

        foreach ($all_checks as $check) {
            $total_checks++;
            if ($check['status'] === 'pass') {
                $passed_checks++;
            }
        }

        return $total_checks > 0 ? round(($passed_checks / $total_checks) * 100) : 0;
    }

    private function get_latest_wp_version() {
        // Simple version check - in production you'd want to cache this
        $version_check = wp_remote_get('https://api.wordpress.org/core/version-check/1.7/');
        if (!is_wp_error($version_check)) {
            $body = wp_remote_retrieve_body($version_check);
            $data = json_decode($body, true);
            if (isset($data['offers'][0]['version'])) {
                return $data['offers'][0]['version'];
            }
        }

        // Fallback to current version if API fails
        global $wp_version;
        return $wp_version;
    }

    // Performance Booster Helper Methods
    private function analyze_site_performance() {
        $analysis = array();

        // Database performance
        $db_analysis = $this->analyze_database_performance();
        $analysis['database'] = $db_analysis;

        // Plugin performance
        $plugin_analysis = $this->analyze_plugin_performance();
        $analysis['plugins'] = $plugin_analysis;

        // Theme performance
        $theme_analysis = $this->analyze_theme_performance();
        $analysis['theme'] = $theme_analysis;

        // WordPress configuration
        $config_analysis = $this->analyze_wp_configuration();
        $analysis['configuration'] = $config_analysis;

        // Overall score
        $analysis['overall_score'] = $this->calculate_performance_score($analysis);

        return $analysis;
    }

    private function apply_performance_optimizations() {
        $optimizations = array();

        // Optimize database
        $db_optimizations = $this->optimize_database_for_performance();
        $optimizations['database'] = $db_optimizations;

        // Optimize WordPress settings
        $wp_optimizations = $this->optimize_wp_settings();
        $optimizations['wordpress'] = $wp_optimizations;

        // Clean up performance-related issues
        $cleanup_optimizations = $this->cleanup_performance_issues();
        $optimizations['cleanup'] = $cleanup_optimizations;

        return $optimizations;
    }

    private function analyze_database_performance() {
        global $wpdb;

        $analysis = array();

        // Check table overhead
        $tables = $wpdb->get_results("SHOW TABLE STATUS", ARRAY_A);
        $total_overhead = 0;
        $overhead_tables = array();

        foreach ($tables as $table) {
            if ($table['Data_free'] > 0) {
                $total_overhead += $table['Data_free'];
                $overhead_tables[] = array(
                    'name' => $table['Name'],
                    'overhead' => $this->format_bytes($table['Data_free'])
                );
            }
        }

        $analysis['table_overhead'] = array(
            'total' => $this->format_bytes($total_overhead),
            'tables' => $overhead_tables,
            'status' => $total_overhead > 1048576 ? 'warning' : 'good' // 1MB threshold
        );

        // Check autoload size
        $autoload_size = $wpdb->get_var(
            "SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload = 'yes'"
        );

        $analysis['autoload_size'] = array(
            'size' => $this->format_bytes($autoload_size),
            'size_bytes' => $autoload_size,
            'status' => $autoload_size > 1048576 ? 'warning' : 'good' // 1MB threshold
        );

        // Check for slow queries (if query log available)
        $analysis['slow_queries'] = array(
            'status' => 'info',
            'message' => 'Enable slow query log for detailed analysis'
        );

        return $analysis;
    }

    private function analyze_plugin_performance() {
        $analysis = array();

        // Get active plugins
        $active_plugins = get_option('active_plugins', array());
        $plugin_count = count($active_plugins);

        $analysis['plugin_count'] = array(
            'count' => $plugin_count,
            'status' => $plugin_count > 30 ? 'warning' : 'good',
            'message' => "{$plugin_count} active plugins"
        );

        // Check for known performance-heavy plugins
        $heavy_plugins = array(
            'jetpack/jetpack.php' => 'Jetpack',
            'revslider/revslider.php' => 'Revolution Slider',
            'LayerSlider/layerslider.php' => 'LayerSlider',
            'woocommerce/woocommerce.php' => 'WooCommerce'
        );

        $active_heavy_plugins = array();
        foreach ($heavy_plugins as $plugin_file => $plugin_name) {
            if (in_array($plugin_file, $active_plugins)) {
                $active_heavy_plugins[] = $plugin_name;
            }
        }

        $analysis['heavy_plugins'] = array(
            'plugins' => $active_heavy_plugins,
            'status' => empty($active_heavy_plugins) ? 'good' : 'info',
            'message' => empty($active_heavy_plugins) ? 'No known heavy plugins active' : 'Heavy plugins: ' . implode(', ', $active_heavy_plugins)
        );

        return $analysis;
    }

    private function analyze_theme_performance() {
        $analysis = array();

        $current_theme = wp_get_theme();

        // Check theme size
        $theme_path = $current_theme->get_stylesheet_directory();
        $theme_size = $this->get_directory_size($theme_path);

        $analysis['theme_size'] = array(
            'size' => $this->format_bytes($theme_size),
            'status' => $theme_size > 10485760 ? 'warning' : 'good', // 10MB threshold
            'message' => "Theme size: " . $this->format_bytes($theme_size)
        );

        // Check for common performance issues
        $style_css = $theme_path . '/style.css';
        $functions_php = $theme_path . '/functions.php';

        $issues = array();

        if (file_exists($style_css)) {
            $css_size = filesize($style_css);
            if ($css_size > 1048576) { // 1MB
                $issues[] = 'Large CSS file (' . $this->format_bytes($css_size) . ')';
            }
        }

        if (file_exists($functions_php)) {
            $functions_content = file_get_contents($functions_php);
            if (preg_match_all('/wp_enqueue_script|wp_enqueue_style/', $functions_content, $matches)) {
                $enqueue_count = count($matches[0]);
                if ($enqueue_count > 10) {
                    $issues[] = "Many enqueued scripts/styles ({$enqueue_count})";
                }
            }
        }

        $analysis['theme_issues'] = array(
            'issues' => $issues,
            'status' => empty($issues) ? 'good' : 'warning'
        );

        return $analysis;
    }

    private function analyze_wp_configuration() {
        $analysis = array();

        // Check WP_DEBUG
        $analysis['debug_mode'] = array(
            'enabled' => defined('WP_DEBUG') && WP_DEBUG,
            'status' => defined('WP_DEBUG') && WP_DEBUG ? 'warning' : 'good',
            'message' => defined('WP_DEBUG') && WP_DEBUG ? 'Debug mode enabled' : 'Debug mode disabled'
        );

        // Check object cache
        $analysis['object_cache'] = array(
            'enabled' => wp_using_ext_object_cache(),
            'status' => wp_using_ext_object_cache() ? 'good' : 'warning',
            'message' => wp_using_ext_object_cache() ? 'External object cache in use' : 'No external object cache'
        );

        // Check memory limit
        $memory_limit = ini_get('memory_limit');
        $memory_bytes = $this->convert_to_bytes($memory_limit);

        $analysis['memory_limit'] = array(
            'limit' => $memory_limit,
            'status' => $memory_bytes >= 268435456 ? 'good' : 'warning', // 256MB threshold
            'message' => "PHP memory limit: {$memory_limit}"
        );

        return $analysis;
    }

    private function calculate_performance_score($analysis) {
        $score = 100;

        // Deduct points for issues
        foreach ($analysis as $category => $data) {
            if ($category === 'overall_score') continue;

            if (is_array($data)) {
                foreach ($data as $check) {
                    if (isset($check['status'])) {
                        if ($check['status'] === 'warning') {
                            $score -= 10;
                        }
                    }
                }
            }
        }

        return max(0, $score);
    }

    private function optimize_database_for_performance() {
        global $wpdb;

        $optimizations = array();

        // Optimize tables with overhead
        $tables = $wpdb->get_results("SHOW TABLE STATUS", ARRAY_A);
        $optimized_tables = array();

        foreach ($tables as $table) {
            if ($table['Data_free'] > 0) {
                $result = $wpdb->query("OPTIMIZE TABLE `{$table['Name']}`");
                if ($result !== false) {
                    $optimized_tables[] = $table['Name'];
                }
            }
        }

        $optimizations['table_optimization'] = array(
            'optimized_tables' => $optimized_tables,
            'count' => count($optimized_tables)
        );

        return $optimizations;
    }

    private function optimize_wp_settings() {
        $optimizations = array();

        // Enable/update WordPress built-in caching
        if (!wp_using_ext_object_cache()) {
            // Note: This would require installing a caching plugin
            $optimizations['object_cache'] = array(
                'message' => 'Consider installing an object caching plugin (Redis, Memcached)'
            );
        }

        // Update rewrite rules
        flush_rewrite_rules(false);
        $optimizations['rewrite_rules'] = array(
            'message' => 'Rewrite rules flushed'
        );

        return $optimizations;
    }

    private function cleanup_performance_issues() {
        $optimizations = array();

        // Clean expired transients
        global $wpdb;
        $deleted = $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%' AND option_value < UNIX_TIMESTAMP()"
        );

        $optimizations['expired_transients'] = array(
            'deleted' => $deleted,
            'message' => "Deleted {$deleted} expired transients"
        );

        return $optimizations;
    }

    private function convert_to_bytes($value) {
        $value = trim($value);
        $last = strtolower($value[strlen($value)-1]);
        $value = (int)$value;

        switch($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    // Backup Manager Helper Methods
    private function get_backup_directory() {
        $backup_dir = WP_CONTENT_DIR . '/reia-backups';
        if (!file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);

            // Create .htaccess to protect backup directory
            $htaccess_content = "Order deny,allow\nDeny from all\n";
            file_put_contents($backup_dir . '/.htaccess', $htaccess_content);
        }
        return $backup_dir;
    }

    private function list_backups() {
        $backup_dir = $this->get_backup_directory();
        $backups = array();

        if (is_dir($backup_dir)) {
            $files = scandir($backup_dir);
            foreach ($files as $file) {
                if (preg_match('/^reia-backup-(.+)\.zip$/', $file, $matches)) {
                    $filepath = $backup_dir . '/' . $file;
                    $backups[] = array(
                        'filename' => $file,
                        'date' => $matches[1],
                        'size' => $this->format_bytes(filesize($filepath)),
                        'type' => $this->get_backup_type_from_filename($file),
                        'created' => date('Y-m-d H:i:s', filemtime($filepath))
                    );
                }
            }
        }

        // Sort by creation date (newest first)
        usort($backups, function($a, $b) {
            return strtotime($b['created']) - strtotime($a['created']);
        });

        return array(
            'backups' => $backups,
            'backup_dir' => $backup_dir,
            'total_backups' => count($backups)
        );
    }

    private function create_backup($backup_type, $backup_options = array()) {
        set_time_limit(600); // 10 minutes for large backups

        $backup_dir = $this->get_backup_directory();
        $timestamp = date('Y-m-d_H-i-s');
        $backup_filename = "reia-backup-{$backup_type}-{$timestamp}.zip";
        $backup_filepath = $backup_dir . '/' . $backup_filename;

        if (!class_exists('ZipArchive')) {
            return array(
                'success' => false,
                'message' => 'ZipArchive class not available. Please install php-zip extension.'
            );
        }

        $zip = new ZipArchive();
        $result = $zip->open($backup_filepath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== TRUE) {
            return array(
                'success' => false,
                'message' => 'Could not create backup zip file. Error code: ' . $result
            );
        }

        $files_added = 0;
        $exclude_patterns = array('wp-content/reia-backups');
        
        // Add exclusions based on options
        if (in_array('exclude_logs', $backup_options)) {
            $exclude_patterns[] = '*.log';
        }
        if (in_array('exclude_cache', $backup_options)) {
            $exclude_patterns[] = '*/cache/*';
            $exclude_patterns[] = '*/w3tc-config/*';
            $exclude_patterns[] = '*/wp-rocket-config/*';
        }

        try {
            switch ($backup_type) {
                case 'content':
                    // Recommended: Database + wp-content (no WP core)
                    $db_sql = $this->export_database();
                    if ($db_sql) {
                        $zip->addFromString('database.sql', $db_sql);
                        $files_added++;
                    }
                    
                    // Add wp-content directory
                    if (is_dir(WP_CONTENT_DIR)) {
                        $files_added += $this->add_directory_to_zip($zip, WP_CONTENT_DIR, 'wp-content/', $exclude_patterns);
                    }
                    
                    // Add wp-config.php if it exists
                    if (file_exists(ABSPATH . 'wp-config.php')) {
                        $zip->addFile(ABSPATH . 'wp-config.php', 'wp-config.php');
                        $files_added++;
                    }
                    break;

                case 'full':
                    // Everything including WP core (large!)
                    $files_added += $this->add_directory_to_zip($zip, ABSPATH, '', $exclude_patterns);
                    $db_sql = $this->export_database();
                    if ($db_sql) {
                        $zip->addFromString('database.sql', $db_sql);
                        $files_added++;
                    }
                    break;

                case 'database':
                    // Database only
                    $db_sql = $this->export_database();
                    if ($db_sql) {
                        $zip->addFromString('database.sql', $db_sql);
                        $files_added++;
                    }
                    break;

                case 'uploads':
                    // Uploads directory only
                    $upload_dir = wp_upload_dir();
                    if (is_dir($upload_dir['basedir'])) {
                        $files_added += $this->add_directory_to_zip($zip, $upload_dir['basedir'], 'wp-content/uploads/', $exclude_patterns);
                    }
                    break;
            }

            $zip->close();

            if ($files_added === 0) {
                unlink($backup_filepath);
                return array(
                    'success' => false,
                    'message' => 'No files were added to the backup.'
                );
            }

            $file_size = filesize($backup_filepath);
            
            return array(
                'success' => true,
                'message' => "Backup created successfully: {$backup_filename}",
                'filename' => $backup_filename,
                'size' => $this->format_bytes($file_size),
                'files_count' => $files_added,
                'type' => $backup_type,
                'download_url' => admin_url('admin-ajax.php') . '?action=reia_backup_manager&action_type=download&backup_file=' . urlencode($backup_filename) . '&nonce=' . wp_create_nonce('reia_utilities_nonce'),
                'can_download' => true,
                'created' => date('Y-m-d H:i:s')
            );

        } catch (Exception $e) {
            $zip->close();
            if (file_exists($backup_filepath)) {
                unlink($backup_filepath);
            }

            return array(
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage()
            );
        }
    }

    private function restore_backup($backup_file) {
        // Note: Backup restoration is a complex and potentially dangerous operation
        // This is a simplified implementation - in production, you'd want more safeguards

        return array(
            'success' => false,
            'message' => 'Backup restoration feature requires manual implementation due to security concerns. Please restore manually or use specialized backup plugins.'
        );
    }

    private function delete_backup($backup_file) {
        $backup_dir = $this->get_backup_directory();
        $backup_filepath = $backup_dir . '/' . basename($backup_file);

        // Security check - only allow deletion of backup files in our backup directory
        if (!preg_match('/^reia-backup-.+\.zip$/', basename($backup_file))) {
            return array(
                'success' => false,
                'message' => 'Invalid backup filename.'
            );
        }

        if (file_exists($backup_filepath) && unlink($backup_filepath)) {
            return array(
                'success' => true,
                'message' => 'Backup deleted successfully.'
            );
        } else {
            return array(
                'success' => false,
                'message' => 'Could not delete backup file.'
            );
        }
    }

    private function preview_backup_deletion($backup_file) {
        $backup_dir = $this->get_backup_directory();
        $backup_filepath = $backup_dir . '/' . basename($backup_file);

        // Security check - only allow deletion of backup files in our backup directory
        if (!preg_match('/^reia-backup-.+\.zip$/', basename($backup_file))) {
            return array(
                'success' => false,
                'message' => 'Invalid backup filename.'
            );
        }

        if (file_exists($backup_filepath)) {
            $file_size = filesize($backup_filepath);
            $file_date = date('Y-m-d H:i:s', filemtime($backup_filepath));

            return array(
                'success' => true,
                'dry_run' => true,
                'preview_action' => 'delete_backup',
                'backup_file' => basename($backup_file),
                'file_size' => $this->format_bytes($file_size),
                'file_date' => $file_date,
                'message' => sprintf('Would delete backup: %s (%s, created %s)',
                    basename($backup_file),
                    $this->format_bytes($file_size),
                    $file_date
                )
            );
        } else {
            return array(
                'success' => false,
                'message' => 'Backup file not found.'
            );
        }
    }

    private function preview_old_backup_cleanup() {
        $backup_dir = $this->get_backup_directory();
        $old_backups = array();
        $cutoff_time = time() - (30 * DAY_IN_SECONDS); // 30 days ago
        $total_size = 0;

        if (is_dir($backup_dir)) {
            $files = scandir($backup_dir);
            foreach ($files as $file) {
                if (preg_match('/^reia-backup-.+\.zip$/', $file)) {
                    $filepath = $backup_dir . '/' . $file;
                    $file_time = filemtime($filepath);

                    if ($file_time < $cutoff_time) {
                        $file_size = filesize($filepath);
                        $old_backups[] = array(
                            'filename' => $file,
                            'size' => $this->format_bytes($file_size),
                            'date' => date('Y-m-d H:i:s', $file_time),
                            'age_days' => floor((time() - $file_time) / DAY_IN_SECONDS)
                        );
                        $total_size += $file_size;
                    }
                }
            }
        }

        return array(
            'success' => true,
            'dry_run' => true,
            'preview_action' => 'cleanup_old_backups',
            'old_backups' => $old_backups,
            'total_old_backups' => count($old_backups),
            'total_size_to_free' => $this->format_bytes($total_size),
            'message' => count($old_backups) > 0 ?
                sprintf('Would delete %d old backups (30+ days), freeing %s of space',
                    count($old_backups),
                    $this->format_bytes($total_size)
                ) : 'No old backups found to clean up'
        );
    }

    private function cleanup_old_backups() {
        $backup_dir = $this->get_backup_directory();
        $deleted_backups = array();
        $cutoff_time = time() - (30 * DAY_IN_SECONDS); // 30 days ago
        $total_size_freed = 0;

        if (is_dir($backup_dir)) {
            $files = scandir($backup_dir);
            foreach ($files as $file) {
                if (preg_match('/^reia-backup-.+\.zip$/', $file)) {
                    $filepath = $backup_dir . '/' . $file;
                    $file_time = filemtime($filepath);

                    if ($file_time < $cutoff_time) {
                        $file_size = filesize($filepath);
                        if (unlink($filepath)) {
                            $deleted_backups[] = $file;
                            $total_size_freed += $file_size;
                        }
                    }
                }
            }
        }

        return array(
            'success' => true,
            'deleted_backups' => $deleted_backups,
            'total_deleted' => count($deleted_backups),
            'space_freed' => $this->format_bytes($total_size_freed),
            'message' => sprintf('Successfully deleted %d old backups, freed %s of space',
                count($deleted_backups),
                $this->format_bytes($total_size_freed)
            )
        );
    }

    private function add_directory_to_zip($zip, $source_dir, $zip_path_prefix = '', $exclude_dirs = array()) {
        $files_added = 0;
        $source_dir = rtrim($source_dir, '/\\') . '/';

        if (!is_dir($source_dir)) {
            return $files_added;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $file_path = $file->getPathname();
            $relative_path = substr($file_path, strlen($source_dir));

            // Check if this path should be excluded
            $should_exclude = false;
            foreach ($exclude_dirs as $exclude_dir) {
                if (strpos($relative_path, $exclude_dir) === 0) {
                    $should_exclude = true;
                    break;
                }
            }

            if ($should_exclude) {
                continue;
            }

            $zip_path = $zip_path_prefix . $relative_path;

            if ($file->isDir()) {
                $zip->addEmptyDir($zip_path);
            } else if ($file->isFile()) {
                // Skip very large files (over 100MB) to prevent memory issues
                if ($file->getSize() < 104857600) {
                    $zip->addFile($file_path, $zip_path);
                    $files_added++;
                }
            }

            // Prevent timeout on large sites
            if ($files_added % 100 === 0) {
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }
            }
        }

        return $files_added;
    }

    private function export_database() {
        global $wpdb;

        try {
            $sql = '';

            // Get all tables
            $tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
            
            // Check for database errors
            if ($wpdb->last_error) {
                wp_send_json_error('Database error: ' . $wpdb->last_error);
                return;
            }
            
            if (empty($tables)) {
                wp_send_json_error('No tables found in database');
                return;
            }

            foreach ($tables as $table) {
                $table_name = $table[0];

                // Skip non-WordPress tables
                if (strpos($table_name, $wpdb->prefix) !== 0) {
                    continue;
                }

                // Get table structure
                $create_table = $wpdb->get_row("SHOW CREATE TABLE `{$table_name}`", ARRAY_N);
                
                // Skip table if we can't get its structure
                if (!$create_table || !isset($create_table[1])) {
                    continue;
                }
                
                $sql .= "\n\n-- Table structure for table `{$table_name}`\n";
                $sql .= "DROP TABLE IF EXISTS `{$table_name}`;\n";
                $sql .= $create_table[1] . ";\n";

                // Get table data
                $rows = $wpdb->get_results("SELECT * FROM `{$table_name}`", ARRAY_A);
                
                // Check for database errors on data retrieval
                if ($wpdb->last_error) {
                    $sql .= "\n-- Error retrieving data for table `{$table_name}`: " . $wpdb->last_error . "\n";
                    continue;
                }

                if (!empty($rows)) {
                    $sql .= "\n-- Dumping data for table `{$table_name}`\n";

                    foreach ($rows as $row) {
                        $values = array();
                        foreach ($row as $value) {
                            if (is_null($value)) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = "'" . $wpdb->_real_escape($value) . "'";
                            }
                        }
                        $sql .= "INSERT INTO `{$table_name}` VALUES (" . implode(', ', $values) . ");\n";
                    }
                }
            }

            return $sql;

        } catch (Exception $e) {
            return false;
        }
    }

    private function get_backup_type_from_filename($filename) {
        if (strpos($filename, '-full-') !== false) return 'Full';
        if (strpos($filename, '-files-') !== false) return 'Files';
        if (strpos($filename, '-database-') !== false) return 'Database';
        if (strpos($filename, '-uploads-') !== false) return 'Uploads';
        return 'Unknown';
    }

    /**
     * Download a backup file
     */
    private function download_backup($backup_file) {
        $backup_dir = $this->get_backup_directory();
        $backup_filepath = $backup_dir . '/' . basename($backup_file);
        
        // Security check - only allow download of backup files in our backup directory
        if (!preg_match('/^reia-backup-.+\.zip$/', basename($backup_file))) {
            wp_die('Invalid backup filename.');
        }
        
        if (!file_exists($backup_filepath)) {
            wp_die('Backup file not found.');
        }
        
        // Force download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($backup_file) . '"');
        header('Content-Length: ' . filesize($backup_filepath));
        header('Cache-Control: no-cache');
        
        // Output file and exit
        readfile($backup_filepath);
        exit;
    }
    
    /**
     * Get estimated backup size before creating
     */
    private function get_backup_size_estimate($backup_type) {
        $estimate = array(
            'type' => $backup_type,
            'estimated_size' => 0,
            'estimated_files' => 0,
            'warning' => '',
            'recommendations' => array()
        );
        
        switch ($backup_type) {
            case 'content':
                // Database size
                $db_size = $this->get_database_size();
                $content_size = $this->get_directory_size(WP_CONTENT_DIR);
                $estimate['estimated_size'] = $db_size + $content_size;
                $estimate['breakdown'] = array(
                    'database' => $this->format_bytes($db_size),
                    'wp_content' => $this->format_bytes($content_size)
                );
                $estimate['recommendations'][] = 'Recommended backup type - includes your data without WP core files';
                break;
                
            case 'full':
                $total_size = $this->get_directory_size(ABSPATH);
                $db_size = $this->get_database_size();
                $estimate['estimated_size'] = $total_size + $db_size;
                $estimate['breakdown'] = array(
                    'database' => $this->format_bytes($db_size),
                    'all_files' => $this->format_bytes($total_size)
                );
                if ($total_size > 1073741824) { // 1GB
                    $estimate['warning'] = 'This backup will be very large and may take a long time to create and download.';
                }
                $estimate['recommendations'][] = 'Consider "Content Only" backup instead to exclude WP core files';
                break;
                
            case 'database':
                $db_size = $this->get_database_size();
                $estimate['estimated_size'] = $db_size;
                $estimate['breakdown'] = array(
                    'database' => $this->format_bytes($db_size)
                );
                $estimate['recommendations'][] = 'Quick backup option - only your database content';
                break;
                
            case 'uploads':
                $upload_dir = wp_upload_dir();
                $uploads_size = is_dir($upload_dir['basedir']) ? $this->get_directory_size($upload_dir['basedir']) : 0;
                $estimate['estimated_size'] = $uploads_size;
                $estimate['breakdown'] = array(
                    'uploads' => $this->format_bytes($uploads_size)
                );
                $estimate['recommendations'][] = 'Media files only - good for backing up images, videos, and documents';
                break;
        }
        
        $estimate['estimated_size_formatted'] = $this->format_bytes($estimate['estimated_size']);
        $estimate['estimated_time'] = $this->estimate_backup_time($estimate['estimated_size']);
        
        return $estimate;
    }
    
    /**
     * Get database size
     */
    private function get_database_size() {
        global $wpdb;
        
        $result = $wpdb->get_var("
            SELECT SUM(data_length + index_length) 
            FROM information_schema.TABLES 
            WHERE table_schema = DATABASE()
        ");
        
        return $result ? (int)$result : 0;
    }
    
    /**
     * Estimate backup creation time based on size
     */
    private function estimate_backup_time($size_bytes) {
        // Rough estimates based on typical server performance
        $mb = $size_bytes / 1048576;
        
        if ($mb < 50) {
            return 'Less than 1 minute';
        } elseif ($mb < 200) {
            return '1-3 minutes';
        } elseif ($mb < 500) {
            return '3-8 minutes';
        } elseif ($mb < 1000) {
            return '8-15 minutes';
        } else {
            return 'More than 15 minutes';
        }
    }

    // Plugin Analyzer Handler
    public function handle_plugin_analyzer() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        check_ajax_referer('reia_utilities_nonce', 'nonce');

        $results = array();

        // Get all plugins
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins = get_plugins();
        $active_plugins = get_option('active_plugins', array());
        $network_active_plugins = is_multisite() ? get_site_option('active_sitewide_plugins', array()) : array();

        $plugin_analysis = array();

        foreach ($all_plugins as $plugin_file => $plugin_data) {
            $is_active = in_array($plugin_file, $active_plugins) || array_key_exists($plugin_file, $network_active_plugins);
            $plugin_path = WP_PLUGIN_DIR . '/' . dirname($plugin_file);

            // Check plugin size
            $size = $this->get_directory_size($plugin_path);

            // Check for updates
            $update_available = false;
            $updates = get_site_transient('update_plugins');
            if (isset($updates->response[$plugin_file])) {
                $update_available = true;
            }

            // Security check - look for common vulnerabilities
            $security_issues = $this->check_plugin_security($plugin_path);

            $plugin_analysis[] = array(
                'name' => $plugin_data['Name'],
                'version' => $plugin_data['Version'],
                'author' => $plugin_data['Author'],
                'file' => $plugin_file,
                'active' => $is_active,
                'size' => $this->format_bytes($size),
                'size_bytes' => $size,
                'update_available' => $update_available,
                'security_issues' => count($security_issues),
                'security_details' => $security_issues,
                'last_updated' => $this->get_plugin_last_modified($plugin_path)
            );
        }

        // Sort by size (largest first)
        usort($plugin_analysis, function($a, $b) {
            return $b['size_bytes'] - $a['size_bytes'];
        });

        $results['plugins'] = $plugin_analysis;
        $results['total_plugins'] = count($all_plugins);
        $results['active_plugins'] = count($active_plugins) + count($network_active_plugins);
        $results['inactive_plugins'] = count($all_plugins) - $results['active_plugins'];
        $results['total_size'] = $this->format_bytes(array_sum(array_column($plugin_analysis, 'size_bytes')));

        // Log to Simple History
        $this->log_utility_action_to_history('plugin analyzer', 'scan', $results);

        wp_send_json_success($results);
    }

    public function handle_file_cleaner() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        check_ajax_referer('reia_utilities_nonce', 'nonce');

        $action = sanitize_text_field($_POST['action_type'] ?? 'scan');
        $clean_options = isset($_POST['clean_options']) ? $_POST['clean_options'] : array();
        $dry_run = isset($_POST['dry_run']) && $_POST['dry_run'] === 'true';
        $results = array();

        if ($action === 'scan' || $action === 'preview' || $dry_run) {
            // Scan for unnecessary files
            $unnecessary_files = $this->scan_unnecessary_files($clean_options);

            $results = array(
                'action' => $dry_run ? 'preview' : 'scan',
                'dry_run' => $dry_run,
                'files' => array_slice($unnecessary_files, 0, 50), // Show first 50 files
                'total_files' => count($unnecessary_files),
                'total_size' => $this->format_bytes(array_sum(array_column($unnecessary_files, 'size'))),
                'preview_message' => count($unnecessary_files) > 0 ?
                    sprintf('Found %d files that can be cleaned, freeing up %s of space.',
                        count($unnecessary_files),
                        $this->format_bytes(array_sum(array_column($unnecessary_files, 'size')))
                    ) : 'No unnecessary files found to clean.',
                'file_breakdown' => $this->get_file_breakdown($unnecessary_files)
            );

        } else if ($action === 'clean') {
            // Clean unnecessary files
            $files_to_clean = $_POST['files'] ?? array();

            if (empty($files_to_clean)) {
                // If no specific files provided, scan first
                $unnecessary_files = $this->scan_unnecessary_files($clean_options);
                $files_to_clean = array_column($unnecessary_files, 'path');
            }

            $cleaned = $this->clean_unnecessary_files($files_to_clean);
            $results = array(
                'action' => 'clean',
                'cleaned_files' => $cleaned['files'],
                'total_cleaned' => $cleaned['count'],
                'space_freed' => $this->format_bytes($cleaned['size']),
                'success_message' => sprintf('Successfully cleaned %d files and freed %s of space.',
                    $cleaned['count'],
                    $this->format_bytes($cleaned['size'])
                )
            );

            // Log to Simple History (only for actual cleaning, not scanning)
            $this->log_utility_action_to_history('file cleaner', 'clean', $results);
        }

        wp_send_json_success($results);
    }

    public function handle_security_scanner() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        check_ajax_referer('reia_utilities_nonce', 'nonce');

        $results = array();

        // WordPress Core Security Checks
        $core_checks = $this->run_core_security_checks();

        // File Permission Checks
        $permission_checks = $this->check_file_permissions();

        // User Security Checks
        $user_checks = $this->check_user_security();

        // Plugin/Theme Security
        $plugin_theme_checks = $this->check_plugin_theme_security();

        // Configuration Security
        $config_checks = $this->check_configuration_security();

        $results = array(
            'core_security' => $core_checks,
            'file_permissions' => $permission_checks,
            'user_security' => $user_checks,
            'plugins_themes' => $plugin_theme_checks,
            'configuration' => $config_checks,
            'overall_score' => $this->calculate_security_score($core_checks, $permission_checks, $user_checks, $plugin_theme_checks, $config_checks)
        );

        // Log to Simple History
        $this->log_utility_action_to_history('security scanner', 'scan', $results);

        wp_send_json_success($results);
    }

    public function handle_performance_booster() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        check_ajax_referer('reia_utilities_nonce', 'nonce');

        $action = sanitize_text_field($_POST['action_type'] ?? 'analyze');
        $results = array();

        if ($action === 'analyze') {
            // Analyze current performance
            $results = $this->analyze_site_performance();
        } else if ($action === 'optimize') {
            // Apply performance optimizations
            $results = $this->apply_performance_optimizations();
        }

        // Log to Simple History
        $this->log_utility_action_to_history('performance booster', $action, $results);

        wp_send_json_success($results);
    }

    public function handle_backup_manager() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        check_ajax_referer('reia_utilities_nonce', 'nonce');

        $action = sanitize_text_field($_POST['action_type'] ?? 'list');
        $dry_run = isset($_POST['dry_run']) && $_POST['dry_run'] === 'true';
        $results = array();

        switch ($action) {
            case 'list':
                $results = $this->list_backups();
                break;
            case 'create':
                $backup_type = sanitize_text_field($_POST['backup_type'] ?? 'content');
                $backup_options = isset($_POST['backup_options']) ? $_POST['backup_options'] : array();
                $results = $this->create_backup($backup_type, $backup_options);
                break;
            case 'download':
                $backup_file = sanitize_text_field($_POST['backup_file'] ?? '');
                $this->download_backup($backup_file);
                return; // Download handles its own response
            case 'delete':
                $backup_file = sanitize_text_field($_POST['backup_file'] ?? '');
                if ($dry_run) {
                    $results = $this->preview_backup_deletion($backup_file);
                } else {
                    $results = $this->delete_backup($backup_file);
                }
                break;
            case 'cleanup_old':
                if ($dry_run) {
                    $results = $this->preview_old_backup_cleanup();
                } else {
                    $results = $this->cleanup_old_backups();
                }
                break;
            case 'get_size_estimate':
                $backup_type = sanitize_text_field($_POST['backup_type'] ?? 'content');
                $results = $this->get_backup_size_estimate($backup_type);
                break;
            default:
                wp_send_json_error('Invalid action');
        }

        // Log to Simple History (only for actual actions, not dry runs)
        if (!$dry_run && in_array($action, array('create', 'delete', 'cleanup_old'))) {
            $this->log_utility_action_to_history('backup manager', $action, $results);
        }

        wp_send_json_success($results);
    }

    private function get_file_breakdown($files) {
        $breakdown = array();

        foreach ($files as $file) {
            $type = $file['type'];
            if (!isset($breakdown[$type])) {
                $breakdown[$type] = array(
                    'count' => 0,
                    'size' => 0,
                    'files' => array()
                );
            }

            $breakdown[$type]['count']++;
            $breakdown[$type]['size'] += $file['size'];

            // Keep a sample of files (first 5)
            if (count($breakdown[$type]['files']) < 5) {
                $breakdown[$type]['files'][] = array(
                    'name' => $file['name'],
                    'size' => $this->format_bytes($file['size']),
                    'modified' => $file['modified']
                );
            }
        }

        // Format the breakdown
        foreach ($breakdown as $type => &$data) {
            $data['size_formatted'] = $this->format_bytes($data['size']);
        }

        return $breakdown;
    }
}

// Initialize the plugin
new REIA_Utilities_Bundle();
