# REIA Utilities Bundle + Simple History Integration

This integration allows the REIA Utilities Bundle plugin to automatically log all its maintenance actions to Simple History, providing a comprehensive audit trail of all website maintenance activities.

## What Gets Logged

The integration logs the following REIA Utilities activities to Simple History:

### Database Optimizer
- **Autoload Fixes**: Number of large autoload options fixed and space saved
- **Post Revisions**: Number of post revisions cleaned
- **Spam Comments**: Number of spam comments removed
- **Trash Cleanup**: Number of trashed posts deleted
- **Orphaned Data**: Count of orphaned postmeta and usermeta entries removed
- **Table Optimization**: Number of database tables optimized

### Cache Cleaner  
- **Transients**: Number of expired transients cleared
- **Elementor Cache**: Status of Elementor cache clearing
- **Object Cache**: WordPress object cache flush status
- **OPCache**: PHP OPCache reset status
- **Rewrite Rules**: WordPress rewrite rules flush status

### Autoload Fixer
- **Performance Improvements**: Before/after autoload sizes and percentage improvement
- **Options Fixed**: Number of large autoload options optimized
- **Space Saved**: Amount of memory saved from autoload optimization

### Plugin Analyzer
- **Scan Results**: Total plugins analyzed, active vs inactive counts
- **Security Issues**: Number of security issues found across plugins
- **Total Size**: Combined size of all installed plugins

### File Cleaner
- **Files Cleaned**: Number of unnecessary files removed
- **Space Freed**: Amount of disk space reclaimed
- **File Types**: Types of files that were cleaned (when cleaning, not scanning)

### Security Scanner
- **Security Score**: Overall security rating
- **Vulnerabilities Found**: Count of security issues identified
- **Check Categories**: Results from core, file permissions, user, plugin/theme, and configuration security checks

### Performance Booster
- **Optimizations Applied**: List of performance improvements made
- **Performance Analysis**: Results from performance analysis scans

### Backup Manager
- **Backup Creation**: Details of backups created (type, size, location)
- **Backup Restoration**: Information about restored backups
- **Backup Deletion**: Details of deleted backup files
- **Cleanup Operations**: Results of old backup cleanup operations

## How It Works

### 1. Automatic Detection
The integration automatically detects if Simple History is installed and active. If it's not available, the REIA Utilities Bundle continues to work normally without any errors.

### 2. Contextual Logging
Each logged entry includes:
- **Action Type**: What maintenance action was performed
- **Detailed Statistics**: Specific numbers and results from the operation
- **User Information**: Which admin user performed the action
- **Timestamp**: When the action was completed

### 3. Log Format
All REIA Utilities entries in Simple History are prefixed with `[REIA Utilities]` for easy identification and filtering.

## Example Log Entries

Here are examples of what you'll see in Simple History:

```
[REIA Utilities] Database optimization completed: Fixed autoload options, Cleaned post revisions, Emptied trash
- Statistics: 15 large autoload options fixed, saved 2.3 MB; 847 post revisions deleted; 23 trashed posts deleted

[REIA Utilities] Cache cleaning completed: Cleared transients, Flushed object cache, Reset OPCache  
- Statistics: 156 transients deleted; WordPress object cache flushed; PHP OPCache reset

[REIA Utilities] Autoload optimization completed: Fixed large autoload options
- Statistics: Total performance improvement: 34.7%, reduced autoload from 5.2 MB to 3.4 MB

[REIA Utilities] Backup manager create completed
- Statistics: Database backup: created; Files backup: created; File size: 245.7 MB

[REIA Utilities] Security scanner scan completed  
- Statistics: Overall score: 85; Core security: pass; File permissions: warning
```

## Installation

The integration is already built into the latest version of REIA Utilities Bundle. No additional setup is required - just ensure both plugins are installed and active:

1. **Install Simple History** (if not already installed)
2. **Install REIA Utilities Bundle** (with the integrated logging code)
3. **Activate both plugins**
4. **Start using REIA Utilities** - all actions will be automatically logged

## Viewing the Logs

1. Go to **Tools → Simple History** in your WordPress admin
2. Use the search/filter to look for "[REIA Utilities]" entries
3. Click on any entry to see detailed context information
4. Export logs to CSV/JSON to include REIA Utilities maintenance data

## Benefits

### For Site Administrators
- **Complete Audit Trail**: Know exactly what maintenance was performed and when
- **Performance Tracking**: See the impact of optimizations over time  
- **Accountability**: Track which admin user performed which actions
- **Compliance**: Maintain detailed records for security and compliance requirements

### For Developers & Agencies
- **Client Reporting**: Show clients exactly what maintenance work was done
- **Troubleshooting**: Correlate site issues with recent maintenance activities
- **Performance Monitoring**: Track effectiveness of optimization efforts
- **Multi-Site Management**: Centralized logging across multiple client sites

### For Security & Forensics
- **Change Detection**: Identify unauthorized or unexpected maintenance activities
- **Incident Response**: Review maintenance history when investigating issues
- **Baseline Establishment**: Understand normal maintenance patterns
- **Risk Assessment**: Track security-related activities and their outcomes

## Advanced Features

### Filtering & Searching
Use Simple History's built-in filtering to focus on specific REIA Utilities activities:
- Filter by log level (info, warning, error)
- Search for specific actions (e.g., "database optimization")
- Filter by date range to see maintenance history
- Filter by user to see who performed which actions

### Exporting & Reporting
- Export maintenance logs to CSV for spreadsheet analysis
- Include REIA Utilities data in JSON exports for external systems
- Generate maintenance reports showing optimization results over time
- Archive logs for long-term compliance requirements

### Integration with Other Tools
- Combine with other Simple History loggers for complete site activity monitoring
- Use with monitoring tools that can parse Simple History logs
- Integrate with backup schedules to ensure maintenance is logged before backups
- Coordinate with security scanning schedules for comprehensive auditing

## Troubleshooting

### No Logs Appearing
1. Verify both plugins are active
2. Check that you have the latest version of REIA Utilities Bundle with logging integration
3. Ensure the current user has `manage_options` capability
4. Try performing a maintenance action and check Simple History immediately

### Missing Context Data
1. Check Simple History settings to ensure full context is being saved
2. Verify database permissions for Simple History tables
3. Review WordPress error logs for any integration issues

### Performance Considerations
1. The logging integration adds minimal overhead to maintenance operations
2. Simple History automatically manages log retention based on your settings
3. Large maintenance operations may generate detailed logs - adjust retention settings if needed

## Technical Implementation

The integration uses Simple History's `SimpleLogger()` function to create log entries:

```php
// Example of how actions are logged
$this->log_to_simple_history('info', $message, $context);
```

Where:
- **Level**: `info`, `warning`, or `error` based on the operation outcome
- **Message**: Human-readable description of the action performed  
- **Context**: Array containing detailed statistics and metadata

This approach ensures compatibility with Simple History's architecture while providing rich, actionable information about maintenance activities.
